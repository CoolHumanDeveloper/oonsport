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

$lang = isset($lang) ? $lang : "en";

$sql = "SELECT *, uts.sport_group_id AS sgID, uts.sport_group_value_id AS vID
        FROM 
            user_to_sport_group_value uts
            LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
            LEFT JOIN sport_group_value AS sgv ON uts.sport_group_value_id = sgv.sport_group_value_id
            LEFT JOIN user AS u ON uts.user_id = u.user_id
        WHERE 
            uts.user_id ='" . $user['user_id'] . "' AND 
            sgd.language_code='" . $lang . "' 
        ORDER BY sgd.sport_group_name ASC";

$query = $DB->prepare($sql);
$query->execute();
$get_main_sport = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($get_main_sport as $key => $main_sport) {
    $get_main_sport[$key]['subgroup'] = get_user_sport_list($user['user_id'], $main_sport['vID']);
}

$response['result'] = $get_main_sport;
