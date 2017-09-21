<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($subject) || $subject == ""
    || !isset($message_to_user_id) || $message_to_user_id == ""
    || !isset($content) || $content == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, u.user_sub_of, ud.user_firstname FROM user u, user_details ud WHERE MD5(CONCAT(u.user_id,ud.user_nickname))='".$message_to_user_id."' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$message_user = $query->fetch();

if ($user['user_id'] == $message_user['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = MESSAGE_TO_ME;
    die();
}

if($message_user['user_sub_of'] > 0) {
    $message_user['user_email'] = getParentEmail($message_user['user_sub_of']);
}

if (action_blockedlist_exists($user['user_id'], $message_user['user_id']) === true) {
    header(HEADER_SERVERERR);
    $response['code'] = MESSAGE_TO_BLOCKED_USER;
    die();
}

$systemlink = "me/message/inbox/";
if (have_permission("emailcopy_on_message", $message_user['user_id'])) {
    $email_content_array = array
    (
        "NAME" => $message_user['user_firstname'],
        "FROM_USER_NAME" => $user['user_nickname'],
        "FROM_USER_IMAGE" => build_default_image($user['user_id'],"115x115","plain"),
        "FROM_USER_LINK" => $systemlink,
        "FROM_USER_DETAILS" => get_city_name($user['user_city_id'], $user['user_country']) . "<br>" . get_user_main_sport($user['user_id'])
    );

    $email_content_template = email_content_to_template("message-new", $email_content_array,"");
    $alt_content = "";

    if (sending_email($message_user['user_email'], $message_user['user_firstname'],TEXT_MESSAGES_SUBJECT_FROM . " " . $user['user_nickname'] . " : " . substr($subject,0,64), $email_content_template, $alt_content,0) === false) {
        $response['code'] = FAIL_SENDMAIL;
        header(HEADER_SERVERERR);
        die();
    }
}

send_message_online($message_user['user_id'], $user['user_id'], $subject, $content, '', 0);