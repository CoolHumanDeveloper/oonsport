<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($user_id) || $user_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM user_details WHERE user_id=" . $user['user_id'];
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

$sql = "SELECT u.*, ud.*, upro.* FROM user u
	    LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
	    LEFT JOIN user_profile AS upro ON u.user_id = upro.user_id
	    WHERE u.user_id='".$user_id."' LIMIT 1";

$query = $DB->prepare($sql);
$query->execute();
$user_profile = $query->fetch(PDO::FETCH_ASSOC);

$result = array();
foreach($user_profile as $key => $up)
    $result[$key] = $up;

$result['total_media'] = count_total_media($user_profile['user_id']);
$result['city_name'] = get_city_name($user_profile['user_geo_city_id'],$user_profile['user_country']);
$result['user_age'] = get_age($user_profile['user_dob'],$user_profile['user_type']);
$result['main_sport'] = get_user_main_sport($user_profile['user_id']);

$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$user_profile['user_id']."' AND um.media_id=m.media_id ORDER BY m.media_id DESC LIMIT ".PROFILE_MAX_IMAGES."";

$query = $DB->prepare($sql);
$query->execute();
$result['galerie_image'] = $query->fetchAll(PDO::FETCH_ASSOC);

//build_shoutbox_feed($user_profile['user_id'],'profile')
//build_shoutbox_feed($user_profile['user_id'],'marketplace_profil')

$result['blocked'] = blockedlist_exists($user['user_id'], $user_profile['user_id']);

if($user_profile['user_type']>2) {
    $geo_place_id= geo_locate_by_input($user_profile['user_address']);
    $result['geo_data'] = get_place_id_data($geo_place_id);
}

$response['result'] = $result;