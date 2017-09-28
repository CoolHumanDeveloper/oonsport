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

$sql = "SELECT u.*, ud.*, uf.friendship_date , uf.friendship_user_id AS friend_id, uf.user_id AS request_id,
(acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE

 FROM 

 user u
 LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
 LEFT JOIN user_friendship AS uf ON u.user_id = uf.user_id OR u.user_id = uf.friendship_user_id

 WHERE 
 uf.friendship_confirmed = 0 AND
u.user_id!='$used_in_profile_id'  AND
(uf.user_id = '$used_in_profile_id' OR uf.friendship_user_id = '$used_in_profile_id') AND
u.user_status=1
";

$query = $DB->prepare($sql);
$query->execute();
$total=$query->rowCount();

$sql.="
  ORDER BY uf.friendship_date DESC LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE."
 ";

$get_search = array();
$sent_invites = array();
$received_invites = array();
if($total > 0) {
    $query = $DB->prepare($sql);
    $query->execute();
    $get_search = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($get_search as $key => $search){
        $get_search[$key]['grid_image'] = api_build_default_image($search['user_id'],"115x100");
        $get_search[$key]['list_image'] = api_build_default_image($search['user_id'],"100x100");
        $search['grid_image'] = api_build_default_image($search['user_id'],"115x100");
        $search['list_image'] = api_build_default_image($search['user_id'],"100x100");
        if ($search['user_id'] == $search['friend_id'])
        {
            $sent_invites[] = $search;
            $get_search[$key]['direct'] = 'sent';
        } else {
            $received_invites[] = $search;
            $get_search[$key]['direct'] = 'received';
        }
    }
}

$response['total'] = $total;
$response['result'] = $get_search;
$response['sent_invites'] = $sent_invites;
$response['received_invites'] = $received_invites;