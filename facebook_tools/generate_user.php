<?php
/**
 * Facebook Test User Manager
 *
 * Based on the work by jide
 * http://www.jide.fr/english/easily-manage-test-accounts-for-facebook-app-developers
 *
 * The Facebook Test Account Generator by incarnated.net is licensed under a
 * Creative Commons Attribution 3.0 Unported License
 * http://creativecommons.org/licenses/by/3.0/
 *
 * If you want more info on Facebook test users, check out
 * the doco here: https://developers.facebook.com/docs/test_users/
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
$app_id = 'INSERT APP ID HERE';
$secret = 'INSERT APP SECRET HERE';


/**
 *
 * All set, shouldn't need to touch anything below here!
 *
 */

$users = array(); 
$messages = array();
 
// Set up our Facebook connection first
$facebook = new Facebook(array(
            'appId' => $app_id,
            'secret' => $secret,
            'cookie' => true,
        ));

    
// Get the user list 
try {
  $users = $facebook->api("{$app_id}/accounts/test-users");
} catch (FacebookApiException $e) {
  echo 'Failed getting your apps users :-( <br /><br />';
  echo $e->getType() . '<br />';
  echo $e->getMessage();
  die();
}
        
// Are we doing something?
if (isset($_GET['op'])) {

  // What to do?
  switch ($_GET['op']) {

    // Show the details of a test user
    case 'Details':
      
      if (isset($_GET['id'])) {
        $response = $facebook->api("/" . $_GET['id']);
          
        $messages[] = "<b>User details</b>
        <ul><li>ID: {$response['id']}</li>
        <li>Name: {$response['name']}</li>
        <li>Email: {$response['email']}</li>
        <li>Gender: {$response['gender']}</li>
        </ul>";
        
      }
    
      break;

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
          
          $messages[] = "<b>User Created</b>
          <ul> <li>ID: {$response['id']}</li>
          <li>Access Token: {$response['access_token']}</li>
          <li>Login URL: <a href=\"{$response['login_url']}\">{$response['login_url']}</a></li>
          <li>Email: {$response['email']}</li>
          <li>Password: {$response['password']}</li>
          </ul>";
          
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

    // Delete all test users
    case 'Delete':
      if (isset($_GET['id'])) {
        try {
          $response = $facebook->api("/" . $_GET['id'], 'DELETE');
          if ($response) {
            $messages[] = "Deleted user ID {$_GET['id']}<br>";
          }
        } catch (Exception $e) {
          print $e->getMessage() . "\n";
        }
        flush();
      } else {
        foreach ($users['data'] as $user) {
          try {
            $response = $facebook->api("/" . $user['id'], 'DELETE');
            if ($response) {
              $messages[] = "Deleted user ID {$user['id']}<br>";
            }
          } catch (Exception $e) {
            print $e->getMessage() . "\n";
          }
          flush();
        }
      }
      break;

    // No decision?
    default:
      print 'Please choose an operation';
  }
}
     
// Generate the page
?>
<html>
  <head>
    <title>Facebook Test User Manager</title>
  </head>
  <link href="fbtools.css" rel="stylesheet" type="text/css">
  <body>
    <div id="page"> 
      <h1>Facebook Test User Manager</h1>
      Allows the creation, management & deletion of test Facebook users.
      
<?php
    
    if (!empty($messages)) {
      echo "<br /><br />";
      foreach ($messages as $message) {
        echo "$message<br />";
      }
      echo "<br />";
    }
  
?>      
      
      <form action="?" method="GET">

        <h2>Create test users</h2>
        <div class="choice">
          <b>How Many?</b> (1-500) <input type="text" name="amount" value="1" />
        </div>
        <div class="choice">
          <b>Permissions?</b><br />
          <select name="permissions" multiple="multiple" size="19">
            <option value="email" selected>email</option>
            <option value="publish_actions" selected>publish_actions</option>
            <option value="publish_stream">read_insights</option>
            <option value="read_friendlists">read_friendlists</option>
            <option value="user_about_me">user_about_me</option>
            <option value="read_insights">read_insights</option>
            <option value="read_mailbox">read_mailbox</option>
            <option value="read_requests">read_requests</option>
            <option value="read_stream">read_stream</option>
            <option value="xmpp_login">xmpp_login</option>
            <option value="ads_management">ads_management</option>
            <option value="manage_friendlists">manage_friendlists</option>
            <option value="manage_notifications">manage_notifications</option>
            <option value="offline_access">offline_access</option>
            <option value="publish_checkins">publish_checkins</option>
            <option value="publish_stream">publish_stream</option>
            <option value="create_event">create_event</option>
            <option value="rsvp_event">rsvp_event</option>
            <option value="sms">sms</option>
          </select>
        </div>
        <div class="choice">
          <b>App installed?</b>
          <input type="radio" name="installed" value="True" checked/> <label for="True">Yes</label>
          <input type="radio" name="installed" value="False" /> <label for="False">No</label>
        </div>
        <input type="submit" name="op" value="Create" />
      </form>

      <div id="clear" />
      <div id="otheractions">
        <form action="?" method="GET">
          <input type="submit" name="op" value="Friends" /> Make all test users friends
          <input type="submit" name="op" value="Delete" /> Delete all test users
        </form>
      </div>
      <br /><br />
      <div>
<?php

    // List users
    if (!empty($users)) {
      foreach($users['data'] as $user) {
        echo "<li> 
                User: {$user['id']} 
                <a href=\"?op=Details&id={$user['id']}\">details</a>
                <a href=\"{$user['login_url']}\">login</a>
                <a href=\"?op=Delete&id={$user['id']}\">delete</a>
              </li>";
      }
		} else {
      echo "Your app has no users";
    }

?>
      </div>
    </div>
  </body>
</html>