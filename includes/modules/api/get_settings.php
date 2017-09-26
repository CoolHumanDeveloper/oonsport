<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM user_settings WHERE user_id=" . $user['user_id'];
$query = $DB->prepare($sql);
$query->execute();
$user_settings = $query->fetch(PDO::FETCH_ASSOC);

$response['result'] = $user_settings;