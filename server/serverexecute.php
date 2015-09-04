<?php
	// Execute this file on server every X second/minute
	// This is depend on the server
	// For the Query, the default is using MongoDB feel free to change method 	
	set_time_limit(0);
	error_reporting(~E_WARNING);
    $connection = new MongoClient();
	$db = $connection->selectDB("test");
	$collection = $db->twitter;
	$pointer = $db->pointer;
	$maximum = 100;
	$delay = 60;
	$last_cursor = 0;

	$curr_cursor = $collection->count();
	$index = $pointer->find(array());
	foreach ($index as $doc) {
		$last_cursor = $doc['num'];
	}

	// Post enviroment
	$url = "http://localhost:8888/server/testClient.php";
	$accessToken = "974763a20335837fb135f10cead6e1d651870247";
	$pattern = "/\n|\r/";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

	function sendPost($addr, $post_Data, $optional_headers = null){
		$params = array('http'=>array('method'=>'POST', 'content'=>$post_Data));
		if($optional_headers !== null){
			$params['http']['header'] = $optional_headers;
		}
		$context = stream_context_create($params);
		$fp = @fopen($addr, 'rb', false, $context);
		if(!$fp){
			throw new Exception("Problem with $addr, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if($response == false){
			throw new Exception("Problem reading data from $addr, $php_errormsg");			
		}
		return $response;
	}

	if($curr_cursor != $last_cursor){
		if($curr_cursor > $maximum){
			$collection->remove(array(),array('safe' => true));
		}else{
			$data_array = [];
			$len = $curr_cursor - $last_cursor;
			$start_index = $last_cursor;
			for($i=0;$i<$len;$i++){
				$target = $collection->findOne(array('index'=>($start_index+$i)));
				$data = $target['userID'] . ":  " . $target['data'];
				$data_array[$i] = $data;
			}
			foreach ($data_array as $data) {
				$post_array = array('name' => 'receive_text_event', 'data'=>$data, 'access_token'=>$accessToken);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_array));
    			$contents = curl_exec($ch);
   				curl_close($ch);
				sleep($delay);
			}
			$pointer->insert(array('num'=>$curr_cursor));
		}
	}
?>