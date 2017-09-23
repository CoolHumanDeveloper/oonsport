<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($group_message_id) || $group_message_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "DELETE FROM `groups_message` WHERE group_message_id='$group_message_id' AND group_message_user_id = '".$user['user_id']."' ";
$query = $DB->prepare($sql);
$query->execute();

if($query->rowCount() == 1) {
    build_history_log($user['user_id'],"removed_group_message",'');
}
