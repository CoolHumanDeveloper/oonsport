<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($radius)
    || !isset($friends)
    || !isset($sports)
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

$keys = array('radius', 'friends', 'sports', 'marketplace');
foreach ( $keys as $key ) {
    $sql = "UPDATE user_settings SET settings_value='{$$key}' WHERE settings_key='shoutbox_display_{$key}' AND user_id = '" . $user[ 'user_id' ] . "'";
    $query = $DB->prepare( $sql );
    $query->execute();
}