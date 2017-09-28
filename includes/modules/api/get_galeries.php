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

$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='$used_in_profile_id' AND um.media_id=m.media_id ORDER BY m.media_id DESC";
$query = $DB->prepare($sql);
$query->execute();

$get_galerie_image = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($get_galerie_image as $key => $galerie_image) {
    if ($galerie_image['media_type'] == 1) {
        $get_galerie_image[$key]['user_image'] = api_build_user_image($galerie_image['media_file'], "profil", "115x115");
    } else if ($galerie_image['media_type'] == 3) {
        $get_galerie_image[$key]['media_url'] = SITE_IMAGE_URL . 'user/' . $galerie_image['media_url'];
    }
}

$response['result'] = $get_galerie_image;
