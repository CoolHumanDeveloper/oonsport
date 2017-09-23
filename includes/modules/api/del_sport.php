<?php
if (!isset($sport_groups_0) || $sport_groups_0 == ""
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

$sql = "DELETE FROM user_to_sport_group_value WHERE sport_group_id = '$sport_groups_0'  AND user_id = '".$user['user_id']."'";
$query = $DB->prepare($sql);
$query->execute();
