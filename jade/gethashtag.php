<?php
	session_start();
	require_once("twitter/TwitterAPIExchange.php"); //Path to TwitterAPIExchange library
	$search = $_POST["gethashtag"];
	echo $search . PHP_EOL;
	// User related set up
	$twitteruser = "mimiv587";
	$notweets = 10;
    $consumerkey = "gsaG03TUan3AgHAxZf4tZVynh";
    $consumersecret = "15szo3ev3Vkg1VKLd7W4vQh53VsZ6P0mXnLfch2YFpz8d23Mjb";
    $accesstoken = "2259667512-Ri8jsTaw1AcNKVYpuGDHM1z6zfN3CWAWSmqCRlX";
    $accesstokensecret = "NRg4bPkgM0eiLXbG2IJi925nhtcWywOXdWCfJpbxEugoh";

    $settings = array(
      'oauth_access_token' => $accesstoken,
      'oauth_access_token_secret' => $accesstokensecret,
      'consumer_key' => $consumerkey,
      'consumer_secret' => $consumersecret
    );

    $url = 'https://api.twitter.com/1.1/search/tweets.json';
	$requestMethod = 'GET';
	//replase hash tags with character that is searchable via url
  	$search = str_replace("#", "%23", $search);
  	$getfield = '?q='.$search.'&count='.$notweets;
  	$twitter = new TwitterAPIExchange($settings);
  	$tweets = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
  	//Check twitter response for errors.
	if(isset( $tweets->errors[0]->code)){
	// If errors exist, print the first error for a simple notification.
		echo "Error encountered: ".$tweets->errors[0]->message." Response code:" .$tweets->errors[0]->code;
	}
	else
	{
		# This is the file insert method

		$file = "tweets.txt";
		$fh = fopen($file, 'w') or die("can't open file");
	    fwrite($fh, json_encode($tweets));
	    fclose($fh);

	    if (file_exists($file)) {
	    	echo $file . " successfully written (" .round(filesize($file)/1024)."KB)";
	    	echo "<br>";
	    } else {
	    	echo "Error encountered. File could not be written.";
	    }

	    //this double checks to see if the latest tweet is a new one
	    $tweetID = $tweets->statuses[0]->id;
	    echo "currentID: ".json_encode($tweetID);
	    echo "<br>";
	      
	    $fileTwitterID = "twitterID.txt";

	    if (file_exists($fileTwitterID)) {

	        //retrieve the previously stored twitter ID
	        $IDContents = json_decode(file_get_contents($fileTwitterID));
	        $previousID = $IDContents->previousID;
	        $previousCount = $IDContents->TwitterCount;

	        echo "previousID".$previousID;
	        echo "<br>";
	        echo "previousCount".$previousCount;

	        //if new tweet, hold this state for 2 seconds
	        if ($previousCount >= 1){
	            //wait 2 seconds before resetting 0 so all browsers have time to finish animation
	            sleep(2);
	            $previousCount = 0;
	        }

	        //check to see if this is a new tweet
	        if ($tweetID != $previousID) {
	          
	            $currentCount = $previousCount+1;  
	           
	        } else {
	          $currentCount = $previousCount;
	        }

	        //write the results to text file so javascript can read
	        $DataArray = array('previousID' => $tweetID, 'TwitterCount' => $currentCount);
	        $fhTwitterID = fopen($fileTwitterID, 'w') or die("can't open file");
	        fwrite($fhTwitterID, json_encode($DataArray));
	        fclose($fhTwitterID);
	      }

	      # Now insert decode twitter item and insert it into local mongoDB
	      # If no mongoDB installed please comment it 
	      $connection = new MongoClient();
	      $db = $connection->selectDB("test");
	      $collection = $db->twitter;
	      $cursor = $collection->count();
	      echo "Now the num of doc inside the database is: "."<br>";
	      echo($cursor);

	      # Now Decoding twitter data
	      # We got the latest data
	      $userID = [];
	      $publishdate = [];
	      $tweetdata = [];
	      for($i=0; $i<$notweets; $i++){
	      	$userID[$i] = $tweets->statuses[$i]->id;
	      	$publishdate[$i] = $tweets->statuses[$i]->created_at;
	      	$tweetdata[$i] = $tweets->statuses[$i]->text;
	      	$index = $cursor+$i;
	      	$insert_data = array('userID' => $userID[$i], 'date' => $publishdate[$i], 'data' => $tweetdata[$i], 'index' => $index);
	      	$collection->insert($insert_data);
	      }
  }
?>