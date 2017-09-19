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

$sql = "DELETE FROM `user_shoutbox` WHERE shoutbox_message_id='" . $shoutbox_message_id . "' AND user_id = '" . $user['user_id'] . "'";

$query = $DB->prepare( $sql );
$query->execute();
