<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($radius)
    || !isset($sports)
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

$keys = array('radius', 'sports');
foreach ( $keys as $key ) {
    $sql = "UPDATE user_settings SET settings_value='{$$key}' WHERE settings_key='marketplace_display_{$key}' AND user_id = '$used_in_profile_id'";
    $query = $DB->prepare( $sql );
    $query->execute();
}
