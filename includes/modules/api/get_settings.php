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

$used_in_profile_id = $user['user_id'];
if (isset($infos->used_in_profile))
    $used_in_profile_id = $infos->used_in_profile;

$sql = "SELECT * FROM user_settings WHERE user_id=$used_in_profile_id";
$query = $DB->prepare($sql);
$query->execute();
$user_settings = $query->fetchAll(PDO::FETCH_ASSOC);

$settings = array();
foreach($user_settings as $setting){
    $settings[$setting['settings_key']] = $setting['settings_value'];
}

$response['result'] = $settings;