<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

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

$output = '';
$user_id = $user['user_id'];
$page = isset($page) ? $page - 1 : 0;
$filter = array();
$lang = isset($lang) ? $lang : 'en';
$shoutbox_group = isset($shoutbox_group) ? $shoutbox_group : 0;
$shoutbox_search = isset($shoutbox_search) ? $shoutbox_search : '';
$shoutbox_type = isset($shoutbox_type) ? $shoutbox_type : '';

$show_marketplace = false;
$limit = 20;

$distance = get_setting( "shoutbox_display_radius", $user_id );
$show_sports = get_setting( "shoutbox_display_sports", $user_id );
$show_friends = get_setting( "shoutbox_display_friends", $user_id );
$show_marketplace = get_setting( "shoutbox_display_marketplace", $user_id );

$friends_sql = "";
$friends_sql_join = "";

if ( $show_friends == 1 ) {
    $friends_sql_join = " LEFT JOIN user_friendship AS uf ON uf.user_id = u.user_id OR uf.friendship_user_id=u.user_id ";
    $friends_sql = " (uf.user_id = '" . $user_id . "' OR uf.friendship_user_id = '" . $user_id . "') AND ";
}


$marketplace_sql = "";
if ( $show_marketplace == 0) {
    $marketplace_sql = " ubox.shoutbox_type NOT LIKE 'marketplace%' AND ";
}

if ( $distance === false ) {
    $distance = 50;
}

$distance_sql = "";
if ( $distance > 0 ) {
    $distance_sql = " (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo[ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo[ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo[ 'user_lng' ] . ")))*" . RADIUS . ") < '" . $distance . "' AND ";
}

$sports_sql = "";
$sports_sql_join = "";

if ( $show_sports == 1 ) {
    $sql = "SELECT * 
			FROM 
				user_to_sport_group_value uts
				LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
				LEFT JOIN user AS u ON uts.user_id = u.user_id
			WHERE 
				uts.user_id ='" . $user_id . "' AND 
				sgd.language_code='" . $lang . "' 
			GROUP BY 
				sgd.sport_group_id";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_main_sport = $query->fetchAll();

    $sports_sql = " (";
    $or_sports_sql = "";
    foreach ( $get_main_sport as $main_sport ) {
        $sports_sql .= $or_sports_sql . "ubox.shoutbox_sport_group_id = '" . $main_sport[ 'sport_group_id' ] . "'";
        $or_sports_sql = " OR ";
    }
    $sports_sql .= ") AND ";
    $sports_sql_join = "";
}

$view_per_page = VIEW_PER_PAGE;
$site = 'shoutbox/';
//Suche
$search = '';
$search_sql = '';


if($shoutbox_group) {
    $search .= '&shoutbox_group=' . $shoutbox_group;
    $sports_sql = " ubox.shoutbox_sport_group_id = '" . $shoutbox_group . "' AND ";
}

if($shoutbox_search) {
    $search_sql = "(ubox.shoutbox_title LIKE '%$shoutbox_search%' OR ubox.shoutbox_text LIKE '%$shoutbox_search%' ) AND ";
}
if($shoutbox_type) {
    if ($shoutbox_type[0] != 'shoutbox_all' ){
        $search_sql = "shoutbox_type = '" . $shoutbox_type[0] . "' AND ";
    }
}

$sql = "SELECT 
        u.*, 
        ud.*, 
        ubox.* ,
        (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo[ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo[ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo[ 'user_lng' ] . ")))*" . RADIUS . ") AS DISTANCE
    FROM 
        user u
        INNER JOIN user_details ud ON u.user_id=ud.user_id
        INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
        " . $friends_sql_join . "
        " . $sports_sql_join . "
    WHERE 
        u.user_status=1 AND
        (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $user_id . "')) AND
        " . $distance_sql . "
        " . $friends_sql . "
        " . $sports_sql . "
        " . $marketplace_sql . "
        " . $search_sql . "
        ud.user_country='" . $userinfo[ 'user_country' ] . "' 
    GROUP BY ubox.shoutbox_message_id
    ";

$query = $DB->prepare( $sql );
$query->execute();
$total = $query->rowCount();

$sql .= "ORDER BY 
        ubox.shoutbox_date DESC
        LIMIT " . $page*$view_per_page.",".$view_per_page;

$query = $DB->prepare( $sql );
$query->execute();
$result = $query->fetchAll();

foreach($result as $key => $item)
{
    $result[$key]['user_image'] = api_build_default_image( $item[ 'user_id' ], "50x50");
}

$response['total'] = $total;
$response['result'] = $result;