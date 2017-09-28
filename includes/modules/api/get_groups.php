<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

//if (!isset($shoutbox_message_id) || $shoutbox_message_id == ""
//) {
//    header(HEADER_SERVERERR);
//    $response['code'] = MISSING_PARAMETER;
//    die(json_encode($response));
//}

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

$sql = "SELECT 
			* 
		FROM 
			`groups` g,
			user_to_groups utg
		WHERE 
			utg.user_id='$used_in_profile_id' AND
			utg.group_id = g.group_id AND 
			utg.group_user_status < 2 
		GROUP BY 
			g.group_id
		ORDER BY
			g.group_id DESC";

$query = $DB->prepare($sql);
$query->execute();
$get_groups = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($get_groups as $key => $groups) {
    $sub_query_sql = "	SELECT 
                            * 
                        FROM 
                            user u,
                            user_to_groups utg
                        WHERE 
                            utg.group_id='" . $groups['group_id'] . "' AND
                            utg.user_id = u.user_id AND 
                            utg.group_user_status = 1
                        GROUP BY 
                            u.user_id
                        ORDER BY
                            u.user_id DESC";

    $query = $DB->prepare($sub_query_sql . " LIMIT " . 5);
    $query->execute();
    $get_preview = $query->fetchAll(PDO::FETCH_ASSOC);

    $get_groups[$key]['members'] = array();
    foreach ($get_preview as $preview) {
        $get_groups[$key]['members'][] = api_build_default_image($preview['user_id'], "50x50");
    }
}

$response['result'] = $get_groups;