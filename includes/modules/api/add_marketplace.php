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

if ( isset( $_POST['upload_file'] ) ) {
    $image_X = 0;
    foreach($_POST['upload_file'] as $ufile) {

        $tempImage = save_base64_image($ufile, md5($ufile.microtime().rand(10000,99999)), SERVER_IMAGE_PATH . "temp/");

        $is_image = getimagesize( $tempImage ) ? true : false;
        if ( $is_image == true ) {

            $mode = '0777';
            $userfile_tmp = $tempImage;
            // CHECK FILE TYPE

            $md5_file_name = md5( $tempImage . $_SESSION[ 'user' ][ 'user_id' ] );

            $prod_img = SERVER_IMAGE_PATH . "user/" . $md5_file_name . '-' . IMAGE_MAX_DEFAULT_SIZE . ".jpg";
            $temp_img = $tempImage;

            chmod( $temp_img, octdec( $mode ) );

            $new_size = explode( "x", IMAGE_MAX_DEFAULT_SIZE );

            $command = 'convert "' . $temp_img . '" -auto-orient "' . $temp_img . '"';

            exec( $command, $return );

            $command = 'convert "' . $temp_img . '" -resize ' . $new_size[ 0 ] . 'x -resize "x' . $new_size[ 1 ] . '<" -colorspace rgb "' . $prod_img . '"';
            exec( $command, $return );
            $image = imagecreatefromjpeg( $prod_img );

            $bg = imagecreatetruecolor( imagesx( $image ), imagesy( $image ) );
            imagecopyresampled( $bg, $image, 0, 0, 0, 0, imagesx( $image ), imagesy( $image ), imagesx( $image ), imagesy( $image ) );
            imagedestroy( $image );
            $quality = 100; // 0 = worst / smaller file, 100 = better / bigger file
            imagejpeg( $bg, $prod_img, $quality );
            imagedestroy( $bg );


            $sql = "INSERT INTO user_shoutboxmedia (`media_file`, `media_type`, `media_title`) VALUES ('" . $md5_file_name . "', '1', '" . $_POST[ 'add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] . "')";

            $query = $DB->prepare( $sql );
            $query->execute();

            $sql_last_image = $DB->lastInsertId();

            $media_default = 0;
            if ( $image_X == 0 ) {
                $media_default = 1;
            }


            $sql = "INSERT INTO user_to_shoutboxmedia (user_id, media_id, shoutbox_message_id, user_default_media, media_sort_order) VALUES ('" . $_SESSION[ 'user' ][ 'user_id' ] . "', '" . $sql_last_image . "', '" .$lastShoutboxId .  "', '" . $media_default . "','" . $image_X . "')";

            $query = $DB->prepare( $sql );
            $query->execute();

            $image_X++;
        }
    }
}

$query->execute();
