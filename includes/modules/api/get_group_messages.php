<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($group_id) || $group_id == ""
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

$search_lat = $user_detail['user_lat'];
$search_lng = $user_detail['user_lng'];

$page = isset($page) ? $page - 1 : 0;

$sql = "	SELECT 
			g.*,
			u.user_id 
		FROM 
			groups g
			LEFT JOIN user_to_groups utg ON g.group_id = utg.group_id
			LEFT JOIN user u ON u.user_id = g.group_admin_user_id
		WHERE
			g.group_id = '$group_id' AND
			u.user_status = 1			
		LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$group = $query->fetch(PDO::FETCH_ASSOC);

if (is_group_member($group['group_id'], $used_in_profile_id) === false && is_group_invited($group['group_id'], $used_in_profile_id) === false) {
    header(HEADER_FORBIDDEN);
    $response['code'] = FAIL_USER;
    die(json_encode($response));
}

$group_messages = api_build_group_feed($group_id);

$response['result'] = $group_messages;