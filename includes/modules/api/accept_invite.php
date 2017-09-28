<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

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

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, u.user_sub_of, ud.user_firstname FROM user u, user_details ud WHERE u.user_id='$invite_user_id' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$friendship_user = $query->fetch();

if($friendship_user['user_sub_of'] > 0) {
    $friendship_user['user_email'] = getParentEmail($friendship_user['user_sub_of']);
}

if ($used_in_profile_id === $friendship_user['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = PROCESS_ME;
    die(json_encode($response));
}

$sql = "UPDATE `user_friendship` uf SET uf.friendship_confirmed = 1 WHERE uf.friendship_user_id='{$used_in_profile_id}' and uf.user_id='$invite_user_id' ";
$query = $DB->prepare($sql);
$query->execute();
if($query->rowCount() == 1) {
    $subject = $userinfo['user_nickname']. " ". TEXT_PROFILE_FRIENDS_REQUESTS_MESSAGE_ACCEPT_SUBJECT;
    $content = str_replace("#USER#",$user['user_nickname'],TEXT_PROFILE_FRIENDS_REQUESTS_MESSAGE_ACCEPT_CONTENT);
    $systemlink="profile/".md5($used_in_profile_id.$userinfo['user_nickname']);

    send_message_online($friendship_user['user_id'],$used_in_profile_id,$subject,$content,$systemlink,1);

    if(have_permission("emailcopy_on_friendship_accept", $friendship_user['user_id']))
    {
        $email_content_array = array
        (
            "NAME" => $friendship_user['user_firstname'],
            "FRIEND_USER_NAME" => $user['user_nickname'],
            "FRIEND_USER_IMAGE" => build_default_image($used_in_profile_id,"115x115","plain"),
            "FRIEND_USER_LINK" => $systemlink,
            "FRIEND_USER_DETAILS" => get_city_name($userinfo['user_city_id'],$userinfo['user_country']).
                "<br>".get_user_main_sport($used_in_profile_id)
        );

        $email_content_template=email_content_to_template("friendship-accept",$email_content_array,"");
        $alt_content="";

        if(sending_email($friendship_user['user_email'],$friendship_user['user_firstname'],
                "Deine Freunschaftsanfrage hat ".$userinfo['user_nickname']." akzeptiert",$email_content_template,$alt_content,0) === false)
        {
            error_log("Fehler: >Emailversand< Template=friendship-accept, To-User: ".$friendship_user['user_email']." ");
        }

    }

    build_history_log($used_in_profile_id,"friendship_accept",$friendship_user['user_id']);
    build_history_log($friendship_user['user_id'],"friendship_accept",$used_in_profile_id);
}