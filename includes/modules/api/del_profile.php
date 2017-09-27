<?php
if (!isset($profile_id) || $profile_id == "") {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user['user_id'] != $profile_id)
    deleteUserFinaly($profile_id);