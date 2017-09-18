<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

//if (!isset($groupid) || !isset($groupid)) {
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
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

$lang = isset($lang) ? $lang : 'en';
$page = isset($page) ? $page - 1 : 0;
$action = isset($action) ? $action : "search.do";
$name = isset($name) ? $name : '';
$placeid = isset($placeid) ? $placeid : $userinfo['user_geo_city_id'];
$country = isset($country) ? $country : $userinfo['user_country'];
$orderby = isset($orderby) ? $orderby : "distance";

$search_url_terms_string="";
$search_url_types_string="";
$search_url_page_string="";
$search_sql="";

if(isset($_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']])) {
    $search_criteria_string .= $_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']].", ";
    $search_sql.=" AND utsgv.sport_group_id = '".$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']]."'";
    $selected_sport_group_id = $_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']];
}
else {
    for($sg_x = 3; $sg_x >=0; $sg_x--) {
        if (isset(${"groups$sg_x"})) {
            $selected_sport_group_value = ${"groups$sg_x"};
            break;
        }
    }

    // Wenn nur die Hauptgruppe gewählt wurde, die z.B. auch keine Unterpunkte hat:
    if($groups0 != 0) $selected_sport_group_id = $groups0;

    if($sg_x == 0 && $groups0 != 0) {
        $search_sql.=" AND utsgv.sport_group_id = '$groups0' ";
    }
    else {
        $search_sport_group_value_ids=check_for_all_value_ids($selected_sport_group_value, $groups0, $sg_x);

        if(count($search_sport_group_value_ids) > 0) {
            $sg_or="";
            $search_sql.=" AND ( ";
            foreach($search_sport_group_value_ids as $search_value_id) {
                $search_sql.=$sg_or."utsgv.sport_group_value_id = '".$search_value_id."'";
                $sg_or=" OR ";
            }
            $search_sql.=" ) ";
        }
    }
}

$selected_type_all='';
$search_type_sql='';

$type_all = isset($type_all) ? $type_all : 0;
$type1 = isset($type1) ? $type1 : 0;
$type2 = isset($type2) ? $type2 : 0;
$type3 = isset($type3) ? $type3 : 0;
$type4 = isset($type4) ? $type4 : 0;

if (!$type_all && ($type1 | $type2 | $type3 | $type4) > 0)
{
    $search_type_sql = " AND ( ";
    for($i = 1; $i < 5; $i++){
        $t = "type$i";
        if ($$t)
            $search_type_sql .= "u.user_type='$i' OR ";
    }
    $search_type_sql = preg_replace("/ OR $/", '', $search_type_sql) . " )";
}

if($placeid) {
    $geo = get_place_id_data($placeid);

    if($geo) {
        $search_lat=$geo['city_lat'];
        $search_lng=$geo['city_lng'];
    } else {
        $search_lat = $userinfo['user_lat'];
        $search_lng = $userinfo['user_lng'];
    }
} else {
    $search_lat = $userinfo['user_lat'];
    $search_lng = $userinfo['user_lng'];
}
$search_exclude_user = $user['user_id'];

// NAME FILTER
if ($name)
    $search_sql .= " AND ud.user_nickname LIKE '%$name%'";

$search_by_values = array("distance", "newest", "online");
$search_by_values_to_db = array("distance" => 'DISTANCE ASC', "newest" => "u.user_id DESC", "online" => "u.user_online DESC");


if(isset($_GET['search_sort_' . $_SESSION['user']['secure_spam_key']])) {
    if(in_array($orderby, $search_by_values)) {
        $search_by = $search_by_values_to_db[$orderby];
    }
    else {
        $search_by = $search_by_values_to_db['distance'];
    }
} else {
    if($search_lat == 0) {
        $search_by = $search_by_values_to_db['newest'];
    }
    else {
        $search_by = $search_by_values_to_db['distance'];
    }
}

$radius = isset($radius) ? $radius : 0;

if($radius > 0 && $search_lat != 0) {
    $search_sql .= " AND (acos(sin(RADIANS(geo.city_lat))*sin(RADIANS($search_lat))+cos(RADIANS(geo.city_lat))*cos(RADIANS($search_lat))*cos(RADIANS(geo.city_lng) - RADIANS($search_lng)))*".RADIUS.") < $radius";
}

if ($search_lat == 0) {
    $base_search_sql = "SELECT DISTINCT(u.user_id), u.*, ud.*,  
                    '0' AS DISTANCE
                    FROM user u
                    INNER JOIN user_details AS ud ON u.user_id = ud.user_id
                    INNER JOIN geo_cities AS geo ON geo.city_google_code = ud.user_geo_city_id
                    LEFT JOIN user_to_sport_group_value AS utsgv ON utsgv.user_id = u.user_id
                    LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = utsgv.sport_group_id
                    WHERE  
                    u.user_status=1 
                    ".$search_sql;

    $query = $DB->prepare($base_search_sql . $search_type_sql);
    $query->execute();
    $total = $query->rowCount();

    //Limit ausgabe der aktuellen Seite
    $sql = $base_search_sql.$search_type_sql." ORDER BY ".$search_by." LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE;

} else {
    $base_search_sql="SELECT DISTINCT(u.user_id), u.*, ud.*, 
                        (acos(sin(RADIANS(geo.city_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(geo.city_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(geo.city_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE
                         FROM 
                         user u
                        INNER JOIN user_details AS ud ON u.user_id=ud.user_id
                        INNER JOIN geo_cities AS geo ON geo.city_google_code = ud.user_geo_city_id
                        LEFT JOIN user_to_sport_group_value AS utsgv ON utsgv.user_id = u.user_id
                        LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = utsgv.sport_group_id
                        WHERE  
                        u.user_id!='".$search_exclude_user."' AND
                        u.user_status=1 
                        ".$search_sql;
    $query = $DB->prepare($base_search_sql.$search_type_sql);
    $query->execute();
    $total = $query->rowCount();

    //Limit ausgabe der aktuellen Seite
    $sql = $base_search_sql . $search_type_sql . "ORDER BY " . $search_by . " LIMIT " . $page * VIEW_PER_PAGE . ", " . VIEW_PER_PAGE;

}
//die($sql);
$query = $DB->prepare($sql);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

$response['total'] = $total;
$response['result'] = $result;