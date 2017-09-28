<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($firstname) || $firstname == ""
    || !isset($lastname) || $lastname == ""
    || !isset($dob) || $dob == ""
    || !isset($country) || $country == ""
    || !isset($geo) || $geo == ""
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
$sql = "SELECT * FROM user_details WHERE user_id=" . $used_in_profile_id;
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

if (($user['user_type'] == 1 || $user['user_type'] == 2) && (!isset($gender) || $gender == "")) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$gender = isset($gender) ? $gender : "";

$place_id = geo_locate_by_input( get_country_name( $country ) . ',' . $geo);

$sql = "UPDATE `user_details` SET `user_firstname`='$firstname', `user_lastname`='$lastname', `user_dob`='".date("Y-m-d",strtotime($dob))."', `user_gender`='$gender', `user_country`='$country', user_geo_city_id='$place_id'  WHERE  `user_id`='$used_in_profile_id'";
$query = $DB->prepare($sql);
$query->execute();
