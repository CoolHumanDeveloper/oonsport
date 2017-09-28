<?php
if (!isset($shoutbox_text) || $shoutbox_text == ""
    || !isset($shoutbox_title) || $shoutbox_title == ""
    || !isset($shoutbox_type)
    || !isset($shoutbox_group)
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

$duration = isset($duration) ? $duration : 30;
if ($duration < 0 || $duration > 30) $duration = 30;

$sql = "INSERT INTO `user_shoutbox` (user_id, `shoutbox_date`, `shoutbox_durration`, `shoutbox_text`,`shoutbox_title`,`shoutbox_type`, shoutbox_sport_group_id)
 VALUES ('$used_in_profile_id', NOW(), '".date("Y-m-d H:i:s", strtotime("+ ".$duration." days"))."', '$shoutbox_text', '$shoutbox_title', '$shoutbox_type', '$shoutbox_group');";
$query = $DB->prepare($sql);
$query->execute();
