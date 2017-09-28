<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($email) || $email == ""
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

if (check_email($email) == true) {
    if (check_user_email($email)){
        header(HEADER_SERVERERR);
        $response['code'] = DUPLICATED_EMAIL;
        die(json_encode($response));
    }
} else {
    header(HEADER_SERVERERR);
    $response['code'] = INVALID_EMAIL;
    die(json_encode($response));
}

$sql = "UPDATE user SET user_email = '$email' WHERE user_id = '$used_in_profile_id'";
$query = $DB->prepare($sql);
$query->execute();