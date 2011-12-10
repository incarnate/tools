<?php
/**
 * Facebook Test Account Generator
 *
 * Based on the work by jide
 * http://www.jide.fr/english/easily-manage-test-accounts-for-facebook-app-developers
 *
 * The Facebook Test Account Generator by incarnated.net is licensed under a
 * Creative Commons Attribution 3.0 Unported License
 * http://creativecommons.org/licenses/by/3.0/
 *
 * 
 *
 */

/*
 * Below are the bits you will have to change.
 *
 * You'll need the Facebook PHP SDK
 * from here: https://github.com/facebook/php-sdk
 *
 * This code was written using version 3.1.1 and uses the Graph API
 *
 */
require_once('Facebook.php');

/**
 * Put your APP ID and Secret here:
 */
$app_id = 'YOUR APP ID';
$secret = 'YOUR APP SECRET';


/**
 *
 * All set, shouldn't need to touch anything below here!
 *
 */

// Set up our Facebook connection first
$facebook = new Facebook(array(
            'appId' => $app_id,
            'secret' => $secret,
            'cookie' => true,
        ));


// Generate the page
?>


<html>
    <head>
        <title>Facebook test users</title>
    </head>
    <body>
        <form action="?" method="GET">
            <ul>
                <b>Create test users</b>
                    <ul>
                        <li>How Many? (1-500) <input type="text" name="amount" value="1" /></li>
                        <li>Permissions?
                            <select name="permissions" multiple="multiple" size="19">
                                <option value="email" selected>email</option>
                                <option value="publish_actions" selected>publish_actions</option>
                                <option value="publish_stream">read_insights</option>
                                <option value="read_friendlists">read_friendlists</option>
                                <option value="user_about_me">user_about_me</option>
                                <option value="read_insights">read_insights</option>
                                <option value="read_mailbox">read_mailbox</option>
                                <option value="read_requests">read_requests</option>
                                <option value="read_stream">read_insights</option>
                                <option value="xmpp_login">read_insights</option>
                                <option value="ads_management">read_insights</option>
                                <option value="manage_friendlists">read_insights</option>
                                <option value="manage_notifications">read_insights</option>
                                <option value="offline_access">read_insights</option>
                                <option value="publish_checkins">read_insights</option>
                                <option value="publish_stream">read_insights</option>
                                <option value="create_event">create_event</option>
                                <option value="rsvp_event">rsvp_event</option>
                                <option value="sms">sms</option>
                            </select>
                        </li>
                        <li>App installed? <input type="radio" name="installed" value="True" checked/> Yes <input type="radio" name="installed" value="False" /> No</li>
                        <li><input type="submit" name="op" value="Create" /></li>
                    </ul>
                <li><input type="submit" name="op" value="List" /> - List all test users</li>
                <li><input type="submit" name="op" value="Friends" /> Make test users friends</li>
                <li><input type="submit" name="op" value="Delete" /> - Delete all test users</li>
            </ul>
        </form>
        <pre>
<?php

// Are we doing something?
if (isset($_GET['op'])) {

    // Get the user list first
    try {
        $users = $facebook->api("{$app_id}/accounts/test-users");
    } catch(FacebookApiException $e) {
        echo 'Failed getting your apps friends :-( <br /><br />';
        echo $e->getType() . '<br />';
        echo $e->getMessage();
    }

    // What to do?
    switch ($_GET['op']) {

        // Create user(s)
        case 'Create':

            // input management
            $amount = (int) $_GET['amount'];
            if (!$amount) {
                $amount = 1;
            } else if ($amount > 500) {
                $amount = 500;
            }
            $installed = $_GET['installed'];

            // Loop through and create user(s)
            for ($i = 0; $i < $amount; $i++) {
                try {
                    $response = $facebook->api("/$app_id/accounts/test-users?installed=true&permissions=read_friendlists,user_about_me,email", 'POST', $attachment);
                    /*
                     *
                     * {
                     *    "id": "1234...",
                     *    "access_token":"1234567..." ,
                     *    "login_url":"https://www.facebook.com/platform/test_account..."
                     *    "email": "example...@tfbnw.net",
                     *    "password": "1234..."
                     *  }
                     *
                     *
                     */
                    print_r($response);
                } catch (Exception $e) {
                    print $e->getMessage() . "\n";
                }
                flush();
            }
            break;

        // Make our users friends
        case 'Friends':

            // loop through each user, then loop through them to make them friends
            foreach ($users['data'] as $user) {
                foreach ($users['data'] as $friend) {
                    if ($user['id'] == $friend['id']) {
                        continue;
                    }
                    try {
                        $response = $facebook->api("/" . $user['id'] . "/friends/" . $friend['id']);
                        print "Success: " . $response . "\n";
                        print_r($user);
                    } catch (Exception $e) {
                        print $e->getMessage() . "\n";
                    }
                    try {
                        $response = $facebook->api("/" . $friend['id'] . "/friends/" . $user['id']);
                        print "Success: " . $response . "\n";
                        print_r($user);
                    } catch (Exception $e) {
                        print $e->getMessage() . "\n";
                    }
                    flush();
                }
            }
            break;

        // List all test users
        case 'List':
            try {
                print_r($users);
            } catch (Exception $e) {
                print $e->getMessage() . "\n";
            }
            flush();
            break;

        // Delete all test users
        case 'Delete':
            foreach ($users['data'] as $user) {
                try {
                    $response = $facebook->api("/" . $user['id'], 'DELETE');
                    print "Success: " . $response . "\n";
                } catch (Exception $e) {
                    print $e->getMessage() . "\n";
                }
                flush();
            }
            break;

        // No decision?
        default:
            print 'Please choose an operation';
            
    }
    
}

?>

        </pre>
    </body>
</html>