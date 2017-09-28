<?php
if (!isset($media_type) || $media_type == ""
    || !isset($media_title) || $media_title == ""
    || $media_type == 2
    && (!isset($media_url) || $media_url == ""
    || !isset($media_url_type) || $media_url_type == "")
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

$media_default = isset($media_default) ? $media_default : 0;
$media_sort = isset($media_sort) ? $media_sort : 0;

if($media_type==1) {
    if(isset($_FILES['media_file'])) {

        $is_image = getimagesize($_FILES['media_file']['tmp_name']) ? true : false;
        if($is_image==true) {

            $mode = '0777';
            $userfile_tmp = $_FILES['media_file']['tmp_name'];
            // CHECK FILE TYPE

            $md5_file_name=md5($_FILES['media_file']['tmp_name'].$used_in_profile_id);

            $prod_img = SERVER_IMAGE_PATH . "user/".$md5_file_name.'-'.IMAGE_MAX_DEFAULT_SIZE.".jpg";					//echo $prod_img;
            $temp_img = SERVER_IMAGE_PATH . "temp/".$md5_file_name.".jpg"; // Hier wird das bild nur zwischengespeichert zum konvertieren
            move_uploaded_file($userfile_tmp, $temp_img);
            chmod ($temp_img, octdec($mode));

            $new_size=explode("x",IMAGE_MAX_DEFAULT_SIZE);

            $command='convert "'.$temp_img.'" -auto-orient "'.$temp_img.'"';



            exec($command, $return);

            $command='convert "'.$temp_img.'" -resize '.$new_size[0].'x -resize "x'.$new_size[1].'<" -colorspace rgb "'.$prod_img.'"';
            exec($command, $return);
            $image = imagecreatefromjpeg($prod_img);

            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagecopyresampled($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image),imagesx($image), imagesy($image));
            imagedestroy($image);
            $quality = 100; // 0 = worst / smaller file, 100 = better / bigger file
            imagejpeg($bg, $prod_img, $quality);
            imagedestroy($bg);

            $sql = "INSERT INTO user_media (`media_file`, `media_type`, `media_title`) VALUES ('".$md5_file_name."', '1', '".$media_title."')";

            $query = $DB->prepare($sql);
            $query->execute();

            $sql_last_image = $DB->lastInsertId();

            if($_POST['media_default']==1) {
                $sql = "UPDATE user_to_media SET user_default_media=0 WHERE user_id='$used_in_profile_id'";
                $query = $DB->prepare($sql);
                $query->execute();
            }

            $sql = "INSERT INTO user_to_media (user_id, media_id, user_default_media, media_sort_order) VALUES ('$used_in_profile_id', '".$sql_last_image."','".$media_default."','".$media_sort."')";

            $query = $DB->prepare($sql);
            $query->execute();
        }
    }
} else if($media_type==2) {
    if(strstr($media_url,"http") == false) {
    } else {
        $sql = "INSERT INTO `user_media` (`media_file`, `media_type`, `media_title`, media_url, media_url_type) VALUES ('".md5($media_url.time())."', '2', '".$media_title."', '".$media_url."', '".$media_url_type."')";
        $query = $DB->prepare($sql);
        $query->execute();

        $sql_last_image = $DB->lastInsertId();

        $sql = "INSERT INTO user_to_media (user_id, media_id, user_default_media, media_sort_order) VALUES ('$used_in_profile_id', '".$sql_last_image."', 0 , 0)";
        $query = $DB->prepare($sql);
        $query->execute();
    }
} else if($media_type==3) {
    if(isset($_FILES['media_file'])) {

        if(stristr($_FILES["media_file"]["type"],'video') ==true) {

            $allowedExts = array("mp4", "wma", "mov");
            $extension = pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION);
            $mode = '0777';
            $userfile_tmp = $_FILES['media_file']['tmp_name'];
            // CHECK FILE TYPE

            $md5_file_name=md5($_FILES['media_file']['tmp_name'].$used_in_profile_id);

            $prod_img = SERVER_IMAGE_PATH . "user/".$md5_file_name."." . $extension;
            //echo $prod_img;
            move_uploaded_file($userfile_tmp, $prod_img);
            chmod ($prod_img, octdec($mode));

            $sql = "INSERT INTO user_media (`media_file`, `media_type`, `media_title`,media_url) VALUES ('".$md5_file_name."', '3', '".$media_title."', '".$md5_file_name."." . $extension."')";

            $query = $DB->prepare($sql);
            $query->execute();

            $sql_last_image = $DB->lastInsertId();

            if($_POST['media_default']==1) {
                $sql = "UPDATE user_to_media SET user_default_media=0 WHERE user_id='$used_in_profile_id'";
                $query = $DB->prepare($sql);
                $query->execute();
            }

            $sql = "INSERT INTO user_to_media (user_id, media_id, user_default_media, media_sort_order) VALUES ('$used_in_profile_id', '".$sql_last_image."','".$media_default."','".$media_sort."')";

            $query = $DB->prepare($sql);
            $query->execute();
        }
    }
}