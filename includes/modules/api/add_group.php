<?php
if (!isset($group_name) || $group_name == ""
    || !isset($group_description) || $group_description == ""
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

if(strlen($group_name) > 64) {
    header(HEADER_SERVERERR);
    $response['code'] = MESSAGE_LENGTH;
    die(json_encode($response));
}
if(strlen($group_description) > 255) {
    header(HEADER_SERVERERR);
    $response['code'] = MESSAGE_LENGTH;
    die(json_encode($response));
}

$sql = "INSERT INTO `groups` (`group_name`, `group_description`, `group_created`, `group_admin_user_id`) VALUES ('$group_name', '$group_description', NOW(), '$used_in_profile_id')";
$query = $DB->prepare($sql);
$query->execute();

$last_group_id = $DB->lastInsertId();

$sql = "INSERT INTO user_to_groups (user_id, group_id, group_user_status, group_user_invited) VALUES ('$used_in_profile_id', '".$last_group_id."', 1, NOW())";
$query = $DB->prepare($sql);
$query->execute();
