<?php
/**
 * Created by PhpStorm.
 * User: GaryZren
 * Date: 15-09-01
 * Time: 1:28 PM
 */
    $connection = new MongoClient();
    $db = $connection->selectDB("test");
    $collection = $db->twitter;
    $obj = $collection->findOne(array('index'=>3));
    $data = $obj['userID'] . ": " . $obj['data'];
    $patten = "/\n|\r/";
    $data = preg_replace($patten, "", $data);
    $url = "https://api.particle.io/v1/devices/events";
    $ch = curl_init($url);
    $header = 'Content-Type: application/x-www-form-urlencoded; charset:utf-8';
    curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $data = array(
        'name' => 'receive_text_event',
        'data' => $data,
        'access_token' => '974763a20335837fb135f10cead6e1d651870247'
    );


    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $contents = curl_exec($ch);
    curl_close($ch);

?>