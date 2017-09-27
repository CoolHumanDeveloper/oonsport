<?php
if (
    !isset($user_type) || $user_type == ""
    || !isset($profile_nickname) || $profile_nickname == ""
    || (!isset($profile_gender) || $profile_gender == "") && ($user_type == 1 || $user_type == 2)
    || !isset($profile_dob) || $profile_dob == ""
    || !isset($profile_country) || $profile_country == ""
    || !isset($profile_zipcode) || $profile_zipcode == ""
    || !isset($profile_city_id) || $profile_city_id == ""
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

if(check_user_nickname($profile_nickname) == true) {
    header(HEADER_SERVERERR);
    $response['code'] = DUPLICATED_NICKNAME;
    die(json_encode($response));
}

if(strlen($profile_nickname) < 3) {
    header(HEADER_SERVERERR);
    $response['code'] = SHORT_NICKNAME;
    die(json_encode($response));
}

if ($user['user_sub_of'] > 0) {
    $new_sub_of = $user['user_sub_of'];
} else {
    $new_sub_of = $user['user_id'];
}

$sql = "INSERT INTO `user` (`user_password`, `user_email`, `user_type`, `user_status`, `user_auth_key`, user_register_date, user_sub_of) VALUES ('".md5($user['user_id'].time())."', '".md5($user['user_id'].time())."', '".$user_type."', 1, '', NOW(), '".$new_sub_of ."')";
$query = $DB->prepare($sql);
$query->execute();

$user_id = $DB->lastInsertId();

if ($user_id > 0) {
    $profile_geo = get_city_latlng($profile_city_id, $profile_country);
    $place_id = geo_locate_by_input( get_country_name( $profile_country ) . ',' . $profile_city_id);

    $sql = "INSERT INTO `user_details` (`user_id`, `user_nickname`, `user_firstname`, `user_lastname`, `user_dob`, `user_gender`, `user_country`, `user_zipcode`, `user_city_idX`,user_lat,user_lng, user_geo_city_id) VALUES ('" . $user_id . "', '" . $profile_nickname . "', '" . $userinfo['user_firstname'] . "', '" . $userinfo['user_lastname'] . "', '" . date("Y-m-d", strtotime($profile_dob)) . "', '" . $profile_gender . "', '" . $profile_country . "', '" . $profile_zipcode . "', '" . $profile_city_id . "', '" . $profile_geo['lat'] . "', '" . $profile_geo['lng'] . "', '$place_id');";
    $query = $DB->prepare($sql);
    $query->execute();

    $sql = "INSERT INTO `user_profile` (user_id) VALUES ('" . $user_id . "');";
    $query = $DB->prepare($sql);
    $query->execute();

    $sql = str_replace('#USERID#', $user_id, DEFAULT_SETTINGS_SQL);
    $query = $DB->prepare($sql);
    $query->execute();

    $sql = "SELECT * 
            FROM 
                user_to_sport_group_value uts
            WHERE 
                uts.user_id ='" . $user['user_id'] . "'
            LIMIT 1";

    $query = $DB->prepare($sql);
    $query->execute();
    $user_sport_group = $query->fetch();

    $sql = "INSERT INTO 
            `user_to_sport_group_value` (`user_id`, `sport_group_id`, `sport_group_value_id`, sport_group_profession, sport_group_handycap, sport_group_in_club) VALUES ('" . $user_id . "', '" . $user_sport_group['sport_group_id'] . "', '" . $user_sport_group['sport_group_value_id'] . "', '" . $user_sport_group['sport_group_profession'] . "', '" . $user_sport_group['sport_group_handycap'] . "', '" . $user_sport_group['sport_group_in_club'] . "')";
    $query = $DB->prepare($sql);
    $query->execute();
}