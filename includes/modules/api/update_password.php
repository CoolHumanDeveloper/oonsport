<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($password) || $password == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$used_in_profile_id = $user['user_id'];
if (isset($infos->used_in_profile))
    $used_in_profile_id = $infos->used_in_profile;

if (strlen($password) < PW_LENGTH) {
    header(HEADER_SERVERERR);
    $response['code'] = SHORT_PASSWORD;
    die(json_encode($response));
}

$sql = "UPDATE user SET user_password = '".md5($password)."' WHERE user_id = '$used_in_profile_id'";
$query = $DB->prepare($sql);
$query->execute();
