<?php
/**
 * Created by PhpStorm.
 * User: GaryZren
 * Date: 15-09-01
 * Time: 1:28 PM
 */
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    # Get the data from database
    $connection = new MongoClient();
    $db = $connection->selectDB("test");
    $collection = $db->twitter;
    #initializeing 
    $num = $collection->count();
    # See if the num in the database is over 10

    function sendMsg($id, $msg, $date){
    	$data = array('name' => $id, 'text' => $msg, 'date' => $date);
    	$temp = json_encode($data);	
   	    echo "data: $temp" . PHP_EOL;
	    echo PHP_EOL;
	    ob_flush();
	    flush();
    }

    if($num != 0){
    	$index = $num - 10; 
    	for($i=0;$i<10;$i++){
    		$temp = $collection->findOne(array('index' => ($index+$i)));
    		sendMsg($temp['userID'], $temp['data'], $temp['date']);
     	}
    }

    sleep(5);
?>