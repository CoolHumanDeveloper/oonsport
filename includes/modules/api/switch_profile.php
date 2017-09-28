<?php
if (
    !isset($switchprofile_user_id) || $switchprofile_user_id == ""
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

if(!api_isValidUserProfile($switchprofile_user_id, $used_in_profile_id) == true) {
    header(HEADER_FORBIDDEN);
    $response['code'] = FAIL_USER;
    die(json_encode($response));
}
$params = array(
    "email" => $user['user_email'],
    "logintime" => date("Y-m-d H:i:s"),
    "used_in_profile" => $switchprofile_user_id
);
$token = $jwt::encode($params, JWT_KEY, JWT_ALG);
$response['token'] = $token;