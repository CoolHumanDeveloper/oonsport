<?php
if (!isset($email) || !isset($password)) {
    header(HEADER_SERVERERR);
    $response['error'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "SELECT * FROM user WHERE user_email='$email' AND user_password=md5('$password') LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch();

if (!$user) {
    $response['error'] = INVALID_PASSWORD;
    header(HEADER_FORBIDDEN);
    die(json_encode($response));
}

if ($user['user_status'] == 0)
{
    $response['error'] = NOTVERIFIED;
    header(HEADER_FORBIDDEN);
    die(json_encode($response));
}

$params = array(
    "email" => $user['user_email'],
    "logintime" => date("Y-m-d H:i:s")
);
$token = $jwt::encode($params, JWT_KEY, JWT_ALG);
$response['token'] = $token;
?>