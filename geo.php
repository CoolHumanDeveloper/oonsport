<?php

// Creates a News Setting for all Users
include ("includes/config.db.php");
include ("includes/functions/default.php");
$_SESSION['language_code'] = 'de';
$sql = "SELECT * FROM user_details WHERE user_geo_city_id = '' AND user_city_idX > 0 LIMIT 10";
$query = $DB->prepare($sql);
$query->execute();

$get_basic = $query->fetchAll();
foreach ($get_basic as $data) {
    echo $data['user_id'].'---'.$data['user_city_id'];
    $sql = "SELECT CONCAT(place,' ',IFNULL(place4,'')) AS city FROM geodb_world." . strtolower( $data['user_country'] ) . " WHERE city_id='" . $data['user_city_id'] . "' LIMIT 1";
//die($sql);

    $query = $DB->prepare( $sql );
    $query->execute();
    $city_name = $query->fetch();
    
    $place_id = geo_locate_by_input( get_country_name($data['user_country']) .','. $city_name['city'] . ',' . $data['user_zipcode'] . ',' . $data['user_address'].'');
    
    
	echo $data['user_id']."<br>";
 	$sql = "UPDATE user_details SET user_geo_city_id = '" .$place_id."' WHERE user_id = '".$data['user_id']."'";
	$query = $DB->prepare($sql);
    $query->execute();
}
