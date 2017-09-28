<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($profile_id) || $profile_id == ""
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

$lang = isset($lang) ? $lang : 'en';

$sql = "SELECT u.*, ud.*, upro.* FROM user u
        LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
        LEFT JOIN user_profile AS upro ON u.user_id = upro.user_id
        WHERE u.user_id='$profile_id' LIMIT 1";

$query = $DB->prepare($sql);
$query->execute();
$user_profile = $query->fetch(PDO::FETCH_ASSOC);

$profile_info = $user_profile;

$total_media = count_total_media($user_profile['user_id']);
$profile_info['total_media'] = $total_media;
$profile_info['user_image_300'] = api_build_default_image($user_profile['user_id'],"300x300");
$profile_info['user_image_200'] = api_build_default_image($user_profile['user_id'],"200x200");
$profile_info['last_online_time'] = last_time_online($user_profile['user_id']);
$profile_info['is_friend'] = friendship_exists($used_in_profile_id, $user_profile['user_id']) === true;

$my_groups = array();

if ($used_in_profile_id != $user_profile['user_id']) {
    if ($profile_info['is_friend']) {
        $profile_info['is_active'] = friendship_active($used_in_profile_id, $user_profile['user_id']) === true;

        if ($profile_info['is_active']) {
            $my_groups = api_build_group_select($used_in_profile_id, $user_profile);
            $profile_info['is_group_connected'] = is_group_connected($groups['group_id'], $profile['user_id']) === true;
        }
    }

    $profile_info['watchlist_exists'] = watchlist_exists($used_in_profile_id,$user_profile['user_id']) === true;
}

$profile_info['city_name'] = get_city_name($user_profile['user_geo_city_id'], $user_profile['user_country']);
$profile_info['age'] = get_age($user_profile['user_dob'],$user_profile['user_type']);
$profile_info['main_sport'] = api_get_user_main_sport($user_profile['user_id'], $lang, "detail_list");

$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$user_profile['user_id']."' AND um.media_id=m.media_id ORDER BY m.media_id DESC LIMIT ".PROFILE_MAX_IMAGES;
$query = $DB->prepare($sql);
$query->execute();
$get_galerie_image = $query->fetchAll(PDO::FETCH_ASSOC);

$media_x = 0;

foreach ($get_galerie_image as $k => $galerie_image){
    $media_x++;

    if($galerie_image['media_type']==1) {
        $get_galerie_image[$k]['image'] = api_build_user_image($galerie_image['media_file'],"profil","800x800");
    } else if ( $galerie_image[ 'media_type' ] == 3 ) {
        $get_galerie_image[$k]['image'] = SITE_URL . 'images/default/video_play.jpg';
    } else {
        $get_galerie_image[$k]['image'] = SITE_URL . 'images/default/video_play.jpg';
    }
}
$profile_info['medias'] = $get_galerie_image;

$actpage = isset($page) ? $page - 1 : 0;
$profile_info['shoutbox'] = api_build_shoutbox_feed($user_profile['user_id'],'profile');
$profile_info['marketplace'] = api_build_shoutbox_feed($user_profile['user_id'],'marketplace_profil');

if ($used_in_profile_id != $user_profile['user_id']) {
    $profile_info['is_blocked'] = blockedlist_exists($used_in_profile_id, $user_profile['user_id']) === true;
}

$response['profile_info'] = $profile_info;