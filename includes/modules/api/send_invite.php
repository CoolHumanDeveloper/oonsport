<?php
if (!isset($invite_user_id) || $invite_user_id == ""
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

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, u.user_sub_of,  ud.user_firstname FROM user u, user_details ud WHERE u.user_id='$invite_user_id' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$friendship_user = $query->fetch();

if($friendship_user['user_sub_of'] > 0) {
    $friendship_user['user_email'] = getParentEmail($friendship_user['user_sub_of']);
}

if($user['user_id'] === $friendship_user['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = PROCESS_ME;
    die();
}

if(friendship_exists($user['user_id'], $friendship_user['user_id']) === true) {
    header(HEADER_SERVERERR);
    $response['code'] = ALREADY_REGISTERED;
    die();
}

$sql = "INSERT INTO `user_friendship` (`user_id`, `friendship_user_id`, `friendship_confirmed`, `friendship_date`) VALUES ('".$user['user_id']."', '$invite_user_id', 0, NOW());";
$query = $DB->prepare($sql);
$query->execute();

$subject = TEXT_PROFILE_FRIENDS_REQUESTS_MESSAGE_SUBJECT . " " . $userinfo['user_nickname'];
$content = str_replace("#USER#", $userinfo['user_nickname'],TEXT_PROFILE_FRIENDS_REQUESTS_MESSAGE_CONTENT);
$systemlink="me/friends/request/";
send_message_online($friendship_user['user_id'], $user['user_id'], $subject, $content, $systemlink,1);

if (have_permission("emailcopy_on_friendship_request", $friendship_user['user_id'])) {

    $email_content_array = array
    (
        "NAME" => $friendship_user['user_firstname'],
        "FRIEND_USER_NAME" => $user['user_nickname'],
        "FRIEND_USER_IMAGE" => build_default_image($user['user_id'],"115x115","plain"),
        "FRIEND_USER_LINK" => $systemlink,
        "FRIEND_USER_DETAILS" => get_city_name($userinfo['user_city_id'], $userinfo['user_country']).
            "<br>" . get_user_main_sport($user['user_id'])
    );

    $email_content_template = email_content_to_template("friendship-request", $email_content_array,"");
    $alt_content="";

    if(sending_email($friendship_user['user_email'],$friendship_user['user_firstname'],TEXT_PROFILE_FRIENDS_REQUESTS_MESSAGE_SUBJECT." ".$userinfo['user_nickname'],$email_content_template,$alt_content,0) === false)
    {
        error_log("Fehler: >Emailversand< Template=friendship-request, To-User: ".$friendship_user['user_email']." ");
    }
}

build_history_log($user['user_id'],"friendship_requeset", $friendship_user['user_id']);
