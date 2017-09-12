<?php

// Creates a News Setting for all Users
include ("includes/config.db.php");
die('create settings');
$sql = "SELECT user_id FROM user WHERE 1";
$query = $DB->prepare($sql);
        $query->execute();

$get_basic=$query->fetchAll();

foreach ($get_basic as $data) {
	echo $data['user_id']."<br>";
	$sql = "INSERT INTO `user_settings` (`user_id`, `settings_key`, `settings_value`, `settings_type`) VALUES
	(#USERID#, 'marketplace_display_sports', '1', 'marketplace')";
	$sql = str_replace("#USERID#",$data['user_id'],$sql);
	$query = $DB->prepare($sql);
        $query->execute();
}
