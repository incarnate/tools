<?php
/**
 * Get Tweets!
 *
 * A simple script demonstrating how to search and pull tweets
 * from Twitter.
 *
 *
 * The only requirement for this script is if you want to output
 * XML you'll need the XML Serializer Pear library. Get it from:
 * http://pear.php.net/package/XML_Serializer
 *
 *
 * For more information on the Twitter Search API, see
 * https://dev.twitter.com/docs/api/1/get/search
 *
 * Twitter Scraper by incarnated.net is licensed under a
 * Creative Commons Attribution 3.0 Unported License
 * http://creativecommons.org/licenses/by/3.0/
 *
 */

// Defines the maximum number of tweets to pull back
$max = 100;

$baseurl = "https://search.twitter.com/search.json";

// Are we doing a search?
if (isset($_POST['search_term'])) {
	
	// Get the output format
	if (isset($_POST['output'])) {
		$output = $_POST['output'];
	}
	
	// Build the search query
	$searchterm = urlencode($_POST['search_term']);
	$starturl = $baseurl."?q={$searchterm}&rpp=100&result_type=recent";
	
	// Get the data
	$contents = file_get_contents($starturl); 
	$jscontents = json_decode($contents, true);
	$nexturl = $jscontents['next_page'];
	$results = $jscontents['results'];
	$thisresults = $jscontents['results'];
	$loop = 1;
	
	// Twitter returns 100 max, loop through each set of results
	// (this is defined in the 'next_page' variable from Twitter)
	while (!empty($thisresults) && !empty($nexturl) && ($loop*100) < $max) {
		
		$loop++;
		sleep(1); // rate limit to prevent wrath of Twitter
		$contents = file_get_contents($baseurl.$nexturl); 
		$jscontents = json_decode($contents, true);
		$nexturl = $jscontents['next_page'];
		$thisresults = $jscontents['results'];
		$results = array_merge($results, $thisresults); // adds results to the ones we have

	}
	
	// Decode search term for display
	$searchterm = urldecode($searchterm);
	
	// *** XML output! ***
	if ($output == 'xml') {
		
		// Requires the PEAR XML Serializer (see comments at top)
		include_once("XML/Serializer.php");

		$options = array (
		  'addDecl' => TRUE,
		  'encoding' => 'UTF-8',
		  'indent' => '  ',
		  'rootName' => 'tweet',
		  'mode' => 'simplexml'
		);

		$serializer = new XML_Serializer($options);
		$obj = json_decode(json_encode($results));
		if ($serializer->serialize($obj)) {
			echo $serializer->getSerializedData();
		} else {
			return null;
		}
	
	// *** PHP Array output ***
	} elseif($output == 'array') {
		
		// We already have these in a PHP array, so dump what we have
		var_dump($results);
	
	// *** HTML output ***
	} elseif ($output == 'html') {
		
		echo 'Found '.count($results).' tweets about '.$searchterm.' ...<br><br>';
		
		foreach ($results as $result) {
			echo $result['from_user']. ' tweeted <br>"'.$result['text'].'"<br> on '.$result['created_at'].'<br/><br/>';	
		}
	
	// *** Geo output - plots tweets with geo on a map ***
	} elseif ( $output == 'geo' ) {
	
	// Output HTML & Javascript to display the map
?>

<html>
<head>
<script type="text/javascript"
    src="http://maps.googleapis.com/maps/api/js?sensor=false">
</script>
<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(0, 0);
    var myOptions = {
      zoom: 2,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP,

    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);
				
<?php
	$looper = 1;
	foreach ($results as $result) {
		
		// Generate points on a google map
		if ( !empty($result['geo']) ) {
				
			$looper++;
			echo '
	var contentstring'.$looper.' = \''.str_replace("'", "\'", $result['from_user']).' tweeted <br> "'.str_replace("'", "\'", $result['text']).'"<br> at '.str_replace("'", "\'", $result['created_at']).' \'; 
	var infowindow'.$looper.' = new google.maps.InfoWindow({
        content: contentstring'.$looper.'
    });
	var myLatLng = new google.maps.LatLng('.$result['geo']['coordinates'][0].', '.$result['geo']['coordinates'][1].');
	var pmarker'.$looper.' = new google.maps.Marker({
          map: map,
          position: myLatLng,
    });
	google.maps.event.addListener(pmarker'.$looper.', \'click\', function() {
      infowindow'.$looper.'.open(map,pmarker'.$looper.');
    });';
		}
	}

?>			
	// Output the map and tweets
}

</script>	
				
</head>
<body onload="initialize()">

	<div id="map_canvas" style="float:right; width:50%; height:600px; margin-right: 10em;"></div>

<?php
		
	echo "Searching for GEO data in ".count($results)." tweets about {$searchterm} ...<br>
	found ".--$looper." with GEO<br><br>";
	echo "<div style='width:30%;'>";
	foreach ($results as $result) {
		if ( !empty($result['geo']) ) {
			echo $result['from_user'] .' tweeted from '.$result['geo']['coordinates'][0].', '.$result['geo']['coordinates'][1].'<br>'; 
			
		}
	
		//echo $result['from_user']. ' tweeted <br>"'.$result['text'].'"<br> on '.$result['created_at'].'<br/><br/>';	
	}
	
?>
	</div>

</body>
</html>

<?php
	
	// *** JSON output (default) *** 
	// This is just what Twitter gave us in the first place!
	} else {	
		header('Content-Type: application/json');
		echo json_encode($results);
	}
	
// No search made, show the input form
} else {

?>
<form action="?" method="POST">
	
	Search for <input type="text" name="search_term" /> <input type="submit" value="Go!" /><br />
	(use a hash for a tag - e.g. #isitdown).<br /><br />
	Output as: 
	<ul>
		<li><input type="radio" name="output" value="xml" /> XML </li>
		<li><input type="radio" name="output" value="array" /> PHP Array </li>
		<li><input type="radio" name="output" value="html" /> HTML </li>
		<li><input type="radio" name="output" value="geo" /> Geo - only works when tweets have a geo tag</li> 
		<li><input type="radio" name="output" value="json" /> JSON </li>
	</ul>
</form>

<?php 

}

?>