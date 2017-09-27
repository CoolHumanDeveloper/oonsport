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
foreach ($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

$lang = isset($lang) ? $lang : 'en';
$page = isset($page) ? $page - 1 : 0;
$name = isset($name) ? $name : '';
$placeid = isset($placeid) ? $placeid : $userinfo['user_geo_city_id'];
$country = isset($country) ? $country : $userinfo['user_country'];
$orderby = isset($orderby) ? $orderby : "distance";
$radius = isset($radius) ? $radius : 0;
$hide = isset($hide) ? $hide : 0;
$groups0 = isset($groups0) ? $groups0 : 0;

$search_sql = "";

if ($country == '')
    $radius = 0;

$selected_sport_group_value = 0;

$search_sport_operator = "AND";
if ($hide == 1) $search_sport_operator = "AND NOT";

if (isset($single_sport_group)) {
    if (isset($more_sport)) {
        $search_sql .= " AND  utsgv.sport_group_id = '" . $single_sport_group . "' AND (";
        $selected_sport_group_id = $single_sport_group;

        $sg_or = "";

        foreach ($more_sport as $sms) {
            if (is_group_child($sms, $selected_sport_group_id) == true) {
                $search_sport_more_value_ids = check_for_all_value_ids($sms, false, false);

                foreach ($search_sport_more_value_ids as $search_more_value_id) {
                    $search_sql .= $sg_or . " utsgv.sport_group_value_id = '" . $search_more_value_id . "' ";
                    $sg_or = " OR ";
                }
            }
        }
        $search_sql .= ")";
    } else {
        $search_sql .= " AND utsgv.sport_group_id = '" . $single_sport_group . "'";
    }
} else {
    for ($sg_x = 3; $sg_x >= 0; $sg_x--) {
        if (isset(${"groups$sg_x"})) {
            $selected_sport_group_value = ${"groups$sg_x"};
            break;
        }
    }
    if ($sg_x == 0 && $groups0 != 0) {
        $search_sql .= " " . $search_sport_operator . " utsgv.sport_group_id = '$groups0' ";
    } else {
        $search_sport_group_value_ids = check_for_all_value_ids($selected_sport_group_value, $groups0, $sg_x);
        if (count($search_sport_group_value_ids) > 0) {
            $sg_or = "";
            $search_sql .= " " . $search_sport_operator . " ( ";
            foreach ($search_sport_group_value_ids as $search_value_id) {
                $search_sql .= $sg_or . "utsgv.sport_group_value_id = '" . $search_value_id . "'";
                $sg_or = " OR ";
            }

            if (isset($more_sport)) {
                foreach ($more_sport as $sms) {
                    $search_sport_more_value_ids = check_for_all_value_ids($sms, false, false);
                    foreach ($search_sport_more_value_ids as $search_more_value_id) {
                        $search_sql .= $sg_or . "utsgv.sport_group_value_id = '" . $search_more_value_id . "'";
                    }
                }
            }
            $search_sql .= " ) ";
        }
    }
}

$selected_type = array();
$search_type_sql = '';
$type_all = isset($type_all) ? $type_all : '';
$type = isset($type) ? $type : array();

if ($type_all == 1 || count($type) == 0) {
    $type[0] = 1;
    $type[1] = 2;
    $type[2] = 3;
    $type[3] = 4;
}

for ($i = 0; $i < 4; $i++) {
    $advanced_type = $i + 1;
    $search_type_advanced_sql[$advanced_type] = '';
    $search_criteria_advanced_string[$advanced_type] = '';

    $selected_type_profession[$advanced_type] = array();
    $selected_type_profession_all[$advanced_type] = '';
    $search_type_profession_query[$advanced_type] = array();
    $search_type_profession_sql[$advanced_type] = '';

    ${"type_{$advanced_type}_profession_all"} = isset(${"type_{$advanced_type}_profession_all"}) ? ${"type_{$advanced_type}_profession_all"} : 1;
    ${"type_{$advanced_type}_profession"} = isset(${"type_{$advanced_type}_profession"}) ? ${"type_{$advanced_type}_profession"} : [];

    if (${"type_{$advanced_type}_profession_all"} == 1 || count(${"type_{$advanced_type}_profession"}) == 0) {
        $selected_type_profession_all[$advanced_type] = ' checked="checked"';
        ${"type_{$advanced_type}_profession"}[0] = 1;
        ${"type_{$advanced_type}_profession"}[1] = 2;
        ${"type_{$advanced_type}_profession"}[2] = 3;
        ${"type_{$advanced_type}_profession"}[3] = 4;
        ${"type_{$advanced_type}_profession"}[4] = 5;
    }

    if (is_array(${"type_{$advanced_type}_profession"})) {
        $advanced_profession_array = array('ALL', 'BEGINNER', 'ROOKIE', 'AMATEUR', 'PROFI', 'OTHER');
        $search_criteria_advanced_string[$advanced_type] .= ', ' . constant('TEXT_GLOBAL_PROFESSION_' . $advanced_type) . ':';

        $search_type_profession_sql[$advanced_type] .= " AND ( ";
        $search_sql_type_profession_opperator[$advanced_type] = "";
        $search_criteria_advanced_string_separator = '';
        foreach (${"type_{$advanced_type}_profession"} as $st => $st_value) {
            $search_type_profession_sql[$advanced_type] .= $search_sql_type_profession_opperator[$advanced_type] . "utsgv.sport_group_profession='" . $st_value . "'";

            $search_sql_type_profession_opperator[$advanced_type] = " OR ";

            if ($i > 1 && defined('TEXT_GLOBAL_PROFESSION_' . $advanced_profession_array[$st_value] . '_' . $advanced_type))
                $search_criteria_advanced_string[$advanced_type] .= $search_criteria_advanced_string_separator . constant('TEXT_GLOBAL_PROFESSION_' . $advanced_profession_array[$st_value] . '_' . $advanced_type);
            $search_criteria_advanced_string_separator = ", ";
        }

        $search_type_profession_sql[$advanced_type] .= " )";

        $search_type_advanced_sql[$advanced_type] .= $search_type_profession_sql[$advanced_type];
    }

    ${"type_{$advanced_type}_handycap"} = isset(${"type_{$advanced_type}_handycap"}) ? ${"type_{$advanced_type}_handycap"} : 0;

    if (${"type_{$advanced_type}_handycap"} == 1) {
        $search_type_advanced_sql[$advanced_type] .= " AND utsgv.sport_group_handycap = '1' ";
        $search_criteria_advanced_string[$advanced_type] .= ', ' . TEXT_GLOBAL_HANDYCAP . ': ' . TEXT_GLOBAL_YES;
    }

    if ($i > 1)
        continue;

    if (isset(${"type_{$advanced_type}_gender"}) && ${"type_{$advanced_type}_gender"} != "0") {
        $search_type_advanced_sql[$advanced_type] .= " AND ud.user_gender = '" . ${"type_{$advanced_type}_gender"} . "' ";
        $search_criteria_advanced_string[$advanced_type] .= ', ' . TEXT_GLOBAL_GENDER . ': ' . constant('TEXT_GLOBAL_GENDER_' . strtoupper(${"type_{$advanced_type}_gender"}));
    }

    ${"type_{$advanced_type}_age_to"} = isset(${"type_{$advanced_type}_age_to"}) ? ${"type_{$advanced_type}_age_to"} : 0;
    ${"type_{$advanced_type}_age_from"} = isset(${"type_{$advanced_type}_age_from"}) ? ${"type_{$advanced_type}_age_from"} : 0;

    if (${"type_{$advanced_type}_age_to"} && is_numeric(${"type_{$advanced_type}_age_to"})
        && ${"type_{$advanced_type}_age_from"} && is_numeric(${"type_{$advanced_type}_age_from"})
    ) {
        if (${"type_{$advanced_type}_age_to"} < ${"type_{$advanced_type}_age_from"}) {
            ${"type_{$advanced_type}_age_to"} = ${"type_{$advanced_type}_age_from"} + 1;
        }

        $search_type_advanced_sql[$advanced_type] .= " 
				AND ud.user_dob < (DATE_SUB(CURDATE(), INTERVAL " . ${"type_{$advanced_type}_age_from"} . " YEAR)) 
				AND ud.user_dob > (DATE_SUB(CURDATE(), INTERVAL " . ${"type_{$advanced_type}_age_to"} . " YEAR))";
    }
}

if (is_array($type)) {
    $search_type_sql .= " AND ( ";
    $search_sql_type_opperator = "";
    foreach ($type as $st => $st_value) {
        $selected_type[$type[$st]] = ' checked="checked"';
        $search_type_sql .= $search_sql_type_opperator . "( u.user_type='" . $st_value . "' " . $search_type_advanced_sql[$st_value] . ")";
        $search_sql_type_opperator = " OR ";
    }
    $search_type_sql .= " )";
}

$sql = "SELECT 
            c.*, 
            t.country_name AS translatedName
        FROM geo_countries c
            LEFT JOIN geo_countries_translation t ON c.country_code = t.country_code AND t.language_code = '" . $lang . "'
        GROUP BY c.country_code
        ORDER BY 
        `country_sort` DESC, 
        t.country_name  IS NULL ASC, 
        c.country_name ASC";

$query = $DB->prepare($sql);
$query->execute();
$get_countries = $query->fetchAll();

foreach ($get_countries as $countries) {
    if ($countries['translatedName']) {
        $countries['country_name'] = $countries['translatedName'];
    }
}

if ($placeid) {
    $geo = get_place_id_data($placeid);

    if ($geo) {
        $search_lat = $geo['city_lat'];
        $search_lng = $geo['city_lng'];
    } else {
        $search_lat = $userinfo['user_lat'];
        $search_lng = $userinfo['user_lng'];
    }
} else {
    $search_lat = $userinfo['user_lat'];
    $search_lng = $userinfo['user_lng'];
}
$search_exclude_user = $user['user_id'];

if ($name != '') {
    $search_sql .= " AND ud.user_nickname LIKE '%$name%'";
}

$search_by_values = array("distance", "newest", "online");
$search_by_values_to_db = array("distance" => 'DISTANCE ASC', "newest" => "u.user_id DESC", "online" => "u.user_online DESC");

if (in_array($orderby, $search_by_values)) {
    $search_by = $search_by_values_to_db[$orderby];
} else {
    $search_by = $search_by_values_to_db['distance'];
}

if ($radius > 0 && $search_lat != 0) {
    $search_sql .= " AND (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $search_lat . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $search_lat . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $search_lng . ")))*" . RADIUS . ") < " . $radius;
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
    " . $search_sql;

    $query = $DB->prepare($base_search_sql . $search_type_sql);
    $query->execute();
    $total = $query->rowCount();

    $sql = $base_search_sql . $search_type_sql . " ORDER BY " . $search_by . " DESC LIMIT " . $page * VIEW_PER_PAGE . "," . VIEW_PER_PAGE . "
     ";
} else {
    $base_search_sql = "SELECT DISTINCT(u.user_id), u.*, ud.*, 
					(acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $search_lat . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $search_lat . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $search_lng . ")))*" . RADIUS . ") AS DISTANCE
					 
					  FROM 
                         user u
                        INNER JOIN user_details AS ud ON u.user_id=ud.user_id
                        INNER JOIN geo_cities AS geo ON geo.city_google_code = ud.user_geo_city_id
                        LEFT JOIN user_to_sport_group_value AS utsgv ON utsgv.user_id = u.user_id
                        LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = utsgv.sport_group_id
                        WHERE   
					
					u.user_id!='" . $search_exclude_user . "' AND
					u.user_status=1 
					" . $search_sql;


    $query = $DB->prepare($base_search_sql . $search_type_sql);
    $query->execute();
    $total = $query->rowCount();

    $sql = $base_search_sql . $search_type_sql . "
  ORDER BY " . $search_by . " LIMIT " . $page * VIEW_PER_PAGE . "," . VIEW_PER_PAGE . "
 ";
}

$response['total'] = $total;

if ($total > 0) {
    $query = $DB->prepare($sql);
    $query->execute();
    $get_search = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($get_search as $key => $search) {
        $get_search[$key]['grid_image'] = api_build_default_image($search['user_id'], "115x100", "grid_image");
        $get_search[$key]['list_image'] = api_build_default_image($search['user_id'], "100x100", "grid_image");
        $get_search[$key]['city_name'] = get_city_name($search['user_geo_city_id'], $search['user_country']);
        $get_search[$key]['main_sport'] = get_user_main_sport($search['user_id']);
    }
}

$response['result'] = $get_search;
?>