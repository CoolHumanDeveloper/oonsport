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

$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, u.user_sub_of, ud.user_firstname FROM user u, user_details ud WHERE u.user_id='$invite_user_id' AND u.user_id = ud.user_id LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$friendship_user = $query->fetch();

if($friendship_user['user_sub_of'] > 0) {
    $friendship_user['user_email'] = getParentEmail($friendship_user['user_sub_of']);
}

if ($user['user_id'] === $friendship_user['user_id']) {
    header(HEADER_SERVERERR);
    $response['code'] = PROCESS_ME;
    die();
}

$sql = "UPDATE `user_friendship` uf SET uf.friendship_confirmed = 2 WHERE uf.user_id='$invite_user_id' and uf.friendship_user_id={$user['user_id']} ";
$query = $DB->prepare($sql);
$query->execute();

if($query->rowCount() == 1) {
    build_history_log($_SESSION['user']['user_id'],"friendship_decline",$friendship_user['user_id']);
}