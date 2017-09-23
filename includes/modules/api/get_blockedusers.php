<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

//if (!isset($shoutbox_message_id) || $shoutbox_message_id == ""
//) {
//    header(HEADER_SERVERERR);
//    $response['code'] = MISSING_PARAMETER;
//    die(json_encode($response));
//}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM user_details WHERE user_id=" . $user['user_id'];
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);

$search_lat = $user_detail['user_lat'];
$search_lng = $user_detail['user_lng'];

$sql = "SELECT u.*, ud.*, uw.blocked_date , uw.blocked_user_id,
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE

     FROM 

     user u
     LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
     LEFT JOIN user_blocked AS uw ON u.user_id = uw.blocked_user_id

     WHERE 
    u.user_id!='".$user['user_id']."'  AND
    uw.user_id = '".$user['user_id']."' AND
    u.user_status=1
    ";

$query = $DB->prepare($sql);
$query->execute();
$get_search = $query->fetchAll(PDO::FETCH_ASSOC);

foreach($get_search as $key => $item)
{
    $get_search[$key]['user_image'] = api_build_default_image( $item[ 'user_id' ], "50x50");

    $search_country_prefix="";
    if($item['user_country'] != $userinfo['user_country']) {
        $search_country_prefix = strtoupper($item['user_country']) . " - ";
    }
    $get_search[$key]['country'] = $search_country_prefix;
    $get_search[$key]['city'] = get_city_name($item['user_geo_city_id'],$item['user_country']);
    $get_search[$key]['main_sport'] = get_user_main_sport($item['user_id']);
}

$response['result'] = $get_search;