<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($friendship_accept)
    || !isset($friendship_request)
    || !isset($group_message)
    || !isset($group_request)
    || !isset($message)
    || !isset($marketplace)
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$keys = array('friendship_accept', 'friendship_request', 'group_message', 'group_request', 'message', 'marketplace');
foreach ( $keys as $key ) {
    $sql = "UPDATE user_settings SET settings_value='{$$key}' WHERE settings_key='emailcopy_on_{$key}' AND user_id = '" . $user[ 'user_id' ] . "'";
    $query = $DB->prepare( $sql );
    $query->execute();
}