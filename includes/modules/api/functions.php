<?php
function debug($obj){
    if (!DEBUG_MODE) return;
    $fp = fopen("debug.txt", "a");
    fputs($fp, print_r($obj, true) . "\n");
    fclose($fp);
}
function api_build_default_image ($user_id, $size) {
    global $DB;
    $sql = "SELECT 
            m.*,
            u.user_type,
            ud.user_gender
        FROM 
        user_media m
        INNER JOIN user_to_media um ON um.media_id=m.media_id
        INNER JOIN user u ON um.user_id = u.user_id
        INNER JOIN user_details ud ON ud.user_id = u.user_id 
        WHERE 
        u.user_id = '".$user_id."' AND
        um.user_default_media = 1 AND 
        m.media_type = 1 
        LIMIT 1";

    $query = $DB->prepare($sql);
    $query->execute();
    $image = $query->fetch();

    if($image['media_id']) {
        $output = api_build_user_image($image['media_file'],"profil", $size);
    } else {
        $sql = "SELECT 
            u.user_type,
            ud.user_gender
        FROM 
        user u 
        INNER JOIN user_details ud ON ud.user_id = u.user_id 
        WHERE 
        u.user_id = '".$user_id."'
        LIMIT 1";

        $query = $DB->prepare($sql);
        $query->execute();
        $image = $query->fetch();

        if ($image['user_type'] <= 2 && $image != NULL) {
            if($image['user_gender'] == 'f') {
                $profileImage = 'user_blank_female.jpg';
            } else {
                $profileImage = 'user_blank_male.jpg';
            }
        } else if($image['user_type'] == 3) {
            $profileImage = 'user_blank_club.jpg';
        } else if($image['user_type'] == 4) {
            $profileImage = 'user_blank_location.jpg';
        } else {
            $profileImage = 'user_blank.jpg';
        }
        $output = api_build_user_image('#SITE_URL#images/default/user/'.$profileImage,"profil", $size);
    }

    return $output;
}

function api_build_user_image($image, $type, $size) {
    $img = $type."/".$size."/".$image.".jpg";

    // Fallback, falls eine URL Angeben ist für z.B. default image
    if(stristr($image, "#SITE_URL#") == true) {
        $output = str_replace("#SITE_URL#",SITE_URL, $image);
        return $output;
    }

    if(!file_exists(SERVER_IMAGE_PATH . "_static/".$img))
        $output = SITE_IMAGE_URL."temp/".$img;
    else
        $output = SITE_IMAGE_URL."_static/".$img;

    return $output;
}