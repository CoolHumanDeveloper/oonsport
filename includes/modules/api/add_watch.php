<?php
if (!isset($watch_user_id) || $watch_user_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$user_id = $user['user_id'];

$sql = "SELECT * FROM user_details WHERE user_id=" . $user['user_id'];
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, ud.user_firstname FROM user u, user_details ud WHERE u.user_id='".$watch_user_id."' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$friendship_user = $query->fetch();

if($user['user_id'] === $friendship_user['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = PROCESS_ME;
    die();
}

if(watchlist_exists($user['user_id'],$friendship_user['user_id']) === true) {
    header(HEADER_SERVERERR);
    $response['code'] = ALREADY_REGISTERED;
    die();
}

$sql = "INSERT INTO `user_watchlist` (`user_id`, `watchlist_user_id`, `watchlist_date`) VALUES ('".$user['user_id']."', '".$friendship_user['user_id']."', NOW());";
$query = $DB->prepare($sql);
$query->execute();

build_history_log($user['user_id'],"watchlist",$friendship_user['user_id']);
