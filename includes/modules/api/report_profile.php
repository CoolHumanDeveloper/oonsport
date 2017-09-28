<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($alert_subject) || $alert_subject == ""
    || !isset($alert_details) || $alert_details == ""
    || !isset($profile_id) || $profile_id == ""
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

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, u.user_sub_of,  ud.user_firstname FROM user u, user_details ud WHERE u.user_id='$profile_id' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$user_profile = $query->fetch();

$email_content_array = array(
    "SEND_NAME" => $userinfo['user_firstname'].' ' . $userinfo['user_lastname'].' (' . $userinfo['user_nickname'].')',
    "SEND_ID" => $used_in_profile_id,
    "REPORT_NAME" => $user_profile['user_firstname'].' ' . $user_profile['user_lastname'].' (' . $user_profile['user_nickname'].')',
    "REPORT_ID" => $profile_id,
    "SUBJECT" => $alert_subject,
    "DETAILS" => $alert_details
);

$email_content_template=email_content_to_template("report-profile",$email_content_array,"");
$alt_content="";

if(sending_email(SYSTEM_MAIL,SITE_NAME,'Profil ' . $user_profile['user_nickname'].' (' . $user_profile['user_id'].') wurde gemeldet',$email_content_template,$alt_content,0)) {
    build_history_log($used_in_profile_id,"report_profile", $user_profile['user_id']);
}
