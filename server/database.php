<?php
	// Establish the connection to the local mongodb
	set_time_limit(0);
	error_reporting(~E_WARNING);
    $connection = new MongoClient();
	$db = $connection->selectDB("test");
	$collection = $db->twitter;
	$cursor = $collection->count();
	$prev_cursor = $cursor;
	$maximum = 100;
	$delay = 60;
	// Establish the post request envirment
	$url = "https://api.particle.io/v1/devices/events";
	$accessToken = "974763a20335837fb135f10cead6e1d651870247";

	$patten = "/\n|\r/";
	$ch = curl_init($url);
    $header = 'Content-Type: application/x-www-form-urlencoded; charset:utf-8';
    curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

	// Use Loop to simulate the looping request
	// This could be changed once have a dedicated server to run the code 
	while (1) {
		$cursor = $collection->count();
		echo($cursor);
		if($cursor>$maximum){
			$collection->remove(array(),array('safe' => true));
		}
		else{ 
			if($cursor!=$prev_cursor){
				// New Doc Appear
				// Initial an array to store data 
				$data_array = [];
				$len = $cursor - $prev_cursor;
				$start_index = $prev_cursor;
				for($i=0;$i<$len;$i++){
					$target = $collection->findOne(array('index'=>($start_index+$i)));
					$data = $target['userID'] . ":  " . $target['data'];
					$post_array = array('name' => 'receive_text_event', 'data'=>$data, 'access_token'=>$accessToken);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_array));
    				$contents = curl_exec($ch);
   					curl_close($ch);
					sleep($delay);
				}
			}
		}
	}

?>