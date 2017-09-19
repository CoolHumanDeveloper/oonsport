<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($shoutbox_message_id) || $shoutbox_message_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "UPDATE `user_shoutbox` SET shoutbox_status = 0, shoutbox_durration = '".date("Y-m-d H:i:s",strtotime("+7 days"))."' WHERE shoutbox_message_id='".$shoutbox_message_id."' AND user_id = '".$user['user_id']."' ";

$query = $DB->prepare($sql);
$query->execute();
