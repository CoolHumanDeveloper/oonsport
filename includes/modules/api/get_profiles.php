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
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

$search_lat = $userinfo['user_lat'];
$search_lng = $userinfo['user_lng'];

$page = isset($page) ? $page - 1 : 0;

$sql = "SELECT 
            u.*, 
            ud.*,
            (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE
        FROM 
            user u
            LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
        WHERE 
            u.user_id='$used_in_profile_id' OR
            u.user_sub_of='$used_in_profile_id' OR
            (u.user_sub_of='".$user['user_sub_of']."'AND u.user_sub_of IS NOT NULL) OR
            (u.user_id = '".$user['user_sub_of']."' AND u.user_sub_of IS NULL)
        ";

$query = $DB->prepare($sql);
$query->execute();
$total = $query->rowCount();

$sql.="ORDER BY u.user_id ASC LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE;

$get_search = array();

if($total > 0) {
    $query = $DB->prepare($sql);
    $query->execute();
    $get_search = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($get_search as $key => $search) {
        $get_search[$key]['grid_image'] = api_build_default_image($search['user_id'], "115x100");
        $get_search[$key]['list_image'] = api_build_default_image($search['user_id'], "100x100");
        $get_search[$key]['user_country'] = strtoupper($search['user_country']);
        $get_search[$key]['city_name'] = get_city_name($search['user_geo_city_id'], $search['user_country']);

        $friendship_status = '';

        if ($search['user_sub_of'] > 0 && $search['user_id'] != $used_in_profile_id) {
//            $friendship_status = '<form method="post" action="#SITE_URL#me/settings/profiles/" >
//		                            <input name="profile_user_id_' . $_SESSION['user']['secure_spam_key'] . '" value="' . md5($search['user_id'] . $search['user_nickname']) . '" type="hidden">
//	                                <button name="deleteprofile_' . $_SESSION['user']['secure_spam_key'] . '" class="btn btn-xs btn-danger is-left" type="submit" value="1" ><i class="fa fa-trash"></i></button>
//	                                </form> ';
        } else if ($search['user_id'] == $used_in_profile_id) {
//            $friendship_status = ' <span class="btn btn-xs btn-primary is-right">' . TEXT_PROFILE_IN_USE . '</span>';
        }

        if ($search['user_id'] != $used_in_profile_id) {
//            $friendship_status .= '<form method="post" action="#SITE_URL#me/settings/profiles/" >
//                                    <input name="switchprofile_user_id_' . $_SESSION['user']['secure_spam_key'] . '" value="' . md5($search['user_id'] . $search['user_nickname']) . '" type="hidden">
//                                    <button name="switchprofile_' . $_SESSION['user']['secure_spam_key'] . '" class="btn btn-xs btn-primary  is-right" type="submit" value="1" title="' . TEXT_PROFILE_SWITCH . '" ><i class="fa fa-exchange "></i> ' . TEXT_GLOBAL_SWITCH . '</button>
//                                    </form> ';
        }

        if ($search['user_sub_of'] == 0) {
//            $friendship_status .= '<button class="btn btn-xs btn-danger is-left" type="submit" value="1" disabled title="Standard Profil" ><i class="fa fa-trash"></i></button>';
        }
    }
}

$response['total'] = $total;
$response['result'] = $get_search;