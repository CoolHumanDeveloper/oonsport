<?php
if (!isset($email) || !isset($password)) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "SELECT * FROM user WHERE user_email='$email' AND user_password=md5('$password') LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $response['code'] = INVALID_PASSWORD;
    header(HEADER_FORBIDDEN);
    die(json_encode($response));
}

if ($user['user_status'] == 0)
{
    $response['code'] = NOTVERIFIED;
    header(HEADER_FORBIDDEN);
    die(json_encode($response));
}

$params = array(
    "email" => $user['user_email'],
    "logintime" => date("Y-m-d H:i:s")
);
$token = $jwt::encode($params, JWT_KEY, JWT_ALG);
$response['token'] = $token;

$userinfo = array();
$userinfo['email'] = $email;

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

$sql = "SELECT * FROM user_profile WHERE user_id=" . $user['user_id'];
$query = $DB->prepare($sql);
$query->execute();
$user_profile = $query->fetch(PDO::FETCH_ASSOC);
unset($user_profile['user_id']);
foreach($user_profile as $key => $ud)
    $userinfo[$key] = $ud;

$userinfo['user_image'] = api_build_default_image($user['user_id'], "50x50");
$userinfo['user_type'] = $user['user_type'];

$response['userinfo'] = $userinfo;
?>