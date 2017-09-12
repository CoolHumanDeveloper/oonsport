<?php
if (!isset($email) || !isset($password)) {
    header(HEADER_SERVERERR);
    $response['error'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "SELECT * FROM user u, user_details ud WHERE u.user_id=ud.user_id AND u.user_email='$email' AND u.user_password='".md5($password)."' AND u.user_status=1  LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch();

if ($user) {
    $params = array(
        "email" => $user['user_email'],
        "logintime" => date("Y-m-d H:i:s")
    );
    $token = $jwt::encode($params, JWT_KEY, JWT_ALG);
    $response['token'] = $token;
} else {
    $response['error'] = INVALID_PASSWORD;
    header(HEADER_FORBIDDEN);
}
die(json_encode($response));
?>