<?php

if(isset($_POST['set_default_media'])) {
	// ALTES DEFAULT ZURÜCKSETZEN
	$sql = "UPDATE user_to_media SET user_default_media=0 WHERE user_id='".$_SESSION['user']['user_id']."'";
	$query = $DB->prepare($sql);
    $query->execute();
					
	// NEUES DEFAULT BILD
	$sql = "UPDATE user_to_media SET user_default_media=1 WHERE user_id='".$_SESSION['user']['user_id']."' AND media_id='".$_POST['galerie_media_id']."'";
	$query = $DB->prepare($sql);
    $query->execute();
	$_SESSION['system_message'] .= set_system_message("success",TEXT_PROFILE_GALERIE_SET_PROFILE_IMAGE_SUCCESS);
}

if(isset($_POST['unlink_media'])) {
    $sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$_SESSION['user']['user_id']."' AND um.media_id=m.media_id AND m.media_id = '".$_POST['galerie_media_id']."' LIMIT 1";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $del_image = $query->fetch(); 

    if($del_image['media_id']) {
        $sql = "DELETE FROM user_media WHERE media_id='".$del_image['media_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();

        $sql = "DELETE FROM user_to_media WHERE user_id='".$_SESSION['user']['user_id']."' AND media_id='".$_POST['galerie_media_id']."'";
        
        $query = $DB->prepare($sql);
        $query->execute();
        
        if($del_image['media_type'] == 3) {
            unlink(SERVER_IMAGE_PATH . "user/".$del_image['media_url']);
        } else if($del_image['media_type'] == 1) {
            unlink(SERVER_IMAGE_PATH . "user/".$del_image['media_file']."-".IMAGE_MAX_DEFAULT_SIZE.".jpg");
        }
        
        $_SESSION['system_message'] .= set_system_message("success", TEXT_PROFILE_GALERIE_DELETE_IMAGE_SUCCESS);
    }
    else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_GALERIE_DELETE_IMAGE_ERROR);
    }
}
		
if($_POST['submit_upload']==1) {

    if(isset($_FILES['media_upload'])) {

        $is_image = getimagesize($_FILES['media_upload']['tmp_name']) ? true : false;
        if($is_image==true) {

            $mode = '0777';
            $userfile_tmp = $_FILES['media_upload']['tmp_name']; 
            // CHECK FILE TYPE

            $md5_file_name=md5($_FILES['media_upload']['tmp_name'].$_SESSION['user']['user_id']);

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


            $sql = "INSERT INTO user_media (`media_file`, `media_type`, `media_title`) VALUES ('".$md5_file_name."', '1', '".$_POST['media_title']."')";
            
            $query = $DB->prepare($sql);
            $query->execute();
            
            $sql_last_image = $DB->lastInsertId();
            
            if($_POST['media_default']==1) {
                $sql = "UPDATE user_to_media SET user_default_media=0 WHERE user_id='".$_SESSION['user']['user_id']."'";
                $query = $DB->prepare($sql);
                $query->execute();
            }

            $sql = "INSERT INTO user_to_media (user_id, media_id, user_default_media, media_sort_order) VALUES ('".$_SESSION['user']['user_id']."', '".$sql_last_image."','".$_POST['media_default']."','".$_POST['media_sort']."')";
                        
            $query = $DB->prepare($sql);
            $query->execute();
        }
    }
}

if($_POST['submit_upload']==2) {
    if(strstr($_POST['media_upload_url'],"http") == false) {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_GALERIE_VIDEO_HTTP_ERROR);
    } else {

        $sql = "INSERT INTO `user_media` (`media_file`, `media_type`, `media_title`, media_url, media_url_type) VALUES ('".md5($_POST['media_upload_url'].time())."', '2', '".$_POST['media_title']."', '".$_POST['media_upload_url']."', '".$_POST['media_url_type']."')";
        $query = $DB->prepare($sql);
        $query->execute();

        $sql_last_image = $DB->lastInsertId();

        $sql = "INSERT INTO user_to_media (user_id, media_id, user_default_media, media_sort_order) VALUES ('".$_SESSION['user']['user_id']."', '".$sql_last_image."', 0 , 0)";
        $query = $DB->prepare($sql);
        $query->execute();
    }
}
	
//Video

if($_POST['submit_upload']==3) {

    if(isset($_FILES['media_upload_video'])) {

        if(stristr($_FILES["media_upload_video"]["type"],'video') ==true) {
            
            $allowedExts = array("mp4", "wma", "mov");
            $extension = pathinfo($_FILES['media_upload_video']['name'], PATHINFO_EXTENSION);
            $mode = '0777';
            $userfile_tmp = $_FILES['media_upload_video']['tmp_name']; 
            // CHECK FILE TYPE

            $md5_file_name=md5($_FILES['media_upload_video']['tmp_name'].$_SESSION['user']['user_id']);

            $prod_img = SERVER_IMAGE_PATH . "user/".$md5_file_name."." . $extension;					
            //echo $prod_img;
            move_uploaded_file($userfile_tmp, $prod_img); 
            chmod ($prod_img, octdec($mode));

            $sql = "INSERT INTO user_media (`media_file`, `media_type`, `media_title`,media_url) VALUES ('".$md5_file_name."', '3', '".$_POST['media_title']."', '".$md5_file_name."." . $extension."')";
            
            $query = $DB->prepare($sql);
            $query->execute();
            
            $sql_last_image = $DB->lastInsertId();
            
            if($_POST['media_default']==1) {
                $sql = "UPDATE user_to_media SET user_default_media=0 WHERE user_id='".$_SESSION['user']['user_id']."'";
                $query = $DB->prepare($sql);
                $query->execute();
            }

            $sql = "INSERT INTO user_to_media (user_id, media_id, user_default_media, media_sort_order) VALUES ('".$_SESSION['user']['user_id']."', '".$sql_last_image."','".$_POST['media_default']."','".$_POST['media_sort']."')";
                        
            $query = $DB->prepare($sql);
            $query->execute();
        }
    }
}
		
$output = '<div class="col-md-6 col-sm-12">
<h4 class="profile">'.TEXT_PROFILE_SPORT_HEADER_IMAGES_VIDEOS.' </h4><br>';

$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$_SESSION['user']['user_id']."' AND um.media_id=m.media_id ORDER BY m.media_id DESC";

$query = $DB->prepare($sql);
$query->execute();

$get_galerie_image = $query->fetchAll();
foreach ($get_galerie_image as $galerie_image) {
    if($galerie_image['media_type']==1){
        $galerie_default="";
        $galerie_disabled="";
        if($galerie_image['user_default_media']==1) {
            $galerie_default=' galerie_default';
            $galerie_disabled=' disabled';
        }
        $output .= '<div class="col-md-4 col-sm-4 col-xs-6">'.build_user_image($galerie_image['media_file'],"profil","115x115","html","img-thumbnail".$galerie_default).'<br>'.$galerie_image['media_title'].'<br>
        <form method="post" action="#SITE_URL#me/profile/galerie/" >
        <input name="galerie_media_id" value="'.$galerie_image['media_id'].'" type="hidden"><input name="set_default_media" value="'.TEXT_PROFILE_SET_AS_PROFILE_IMAGE.'" class="btn btn-sm btn-primary" type="submit" '.$galerie_disabled.'> <input name="unlink_media" value="X" class="btn btn-sm btn-danger" type="submit"></form> <form method="post" action="#SITE_URL#me/profile/galerie-edit/" ><input name="galerie_media_id" value="'.$galerie_image['media_id'].'" type="hidden"><button name="rotate_media" class="btn btn-sm btn-primary" type="submit"><i class="fa fa-pencil"></i>&nbsp;</button> 
        </form>
        <br>
        <br>
        </div>';

    } else if($galerie_image['media_type']==2) {
        $output .= '<div class="col-md-4 col-sm-4 col-xs-6">
                    <a href="'.$galerie_image['media_url'].'"  rel="gallery" target="_blank" class="fancybox  fancybox.iframe" title="'.$galerie_image['media_title'].'"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="115" height="115">
                    </a>
                    <br>
                    '.$galerie_image['media_title'].'<br>
                                            <form method="post" action="#SITE_URL#me/profile/galerie/" >
                    <input name="galerie_media_id" value="'.$galerie_image['media_id'].'" type="hidden">
                    <input name="unlink_media" value="X" class="btn btn-sm btn-danger" type="submit"></form><br>
                    <br>
                    <br></div>';
    } else {
        $output .= '<div class="col-md-4 col-sm-4 col-xs-6">
                    <a href="#SITE_URL#tools/webplayer/'.$galerie_image['media_file'].'/" rel="gallery" target="_blank" class="fancybox fancybox.iframe" title="'.$galerie_image['media_title'].'"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="115" height="115">
                    </a>
                    <br>
                    '.$galerie_image['media_title'].'<br>
                                            <form method="post" action="#SITE_URL#me/profile/galerie/" >
                    <input name="galerie_media_id" value="'.$galerie_image['media_id'].'" type="hidden">
                    <input name="unlink_media" value="X" class="btn btn-sm btn-danger" type="submit"></form><br>
                    <br>
                    <br></div>';
    }
}


$output .= '
    <div class="clearfix"></div>
    </div>

    <div class="col-md-3 col-sm-12">
    <div class="clearfix"></div>
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#images" aria-controls="images" role="tab" data-toggle="tab">'.TEXT_GLOBAL_IMAGES.'</a></li>
        <li role="presentation"><a href="#video" aria-controls="video" role="tab" data-toggle="tab">'.TEXT_GLOBAL_VIDEOS.'</a></li>
         <li role="presentation"><a href="#videoupload" aria-controls="videoupload" role="tab" data-toggle="tab">'.TEXT_GLOBAL_VIDEOS.' Upload</a></li>
    </ul>


    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="images">
            <form method="post" action="#SITE_URL#me/profile/galerie/" enctype="multipart/form-data"><br>
        <br>

            <input type="file" id="fileinput" name="media_upload" style="display: none;">
            <button type="button" onclick="document.getElementById(\'fileinput\').click();"  class="form-control"><i class="fa fa-file"></i>
                 '.TEXT_CHOOSE_FILE.'</button><br>

            <input type="text" name="media_title" class="form-control" placeholder="'.TEXT_PROFILE_GALERIE_DESCRIPTION_IMAGE.'"><br>
            <label for="media_default">'.TEXT_PROFILE_GALERIE_DEFAULT_IMAGE.'</label><input type="checkbox" name="media_default" value="1"> <br>
            <input type="hidden" name="media_sort"  class="form-control"><!-- Sortierung des Bildes in der Galerie<br>-->
            <input type="hidden" name="submit_upload" value="1">
             <button type="submit" class="btn btn-default">'.TEXT_PROFILE_GALERIE_UPLOAD_IMAGE.'</button>
            </form>
        </div>

        <div role="tabpanel" class="tab-pane" id="video">
            <form method="post" action="#SITE_URL#me/profile/galerie/" enctype="multipart/form-data"><br>
    <br>

        '.TEXT_PROFILE_GALERIE_VIDEO_URL.': <input type="text" name="media_upload_url" class="form-control"><br>'.TEXT_GLOBAL_FOR_EXAMPLE.' https://www.youtube.com/watch?v=m71435pKD0Q<br>
    <br>

        '.TEXT_PROFILE_GALERIE_VIDEO_SOURCE.':
        <select name="media_url_type"  class="form-control">
        <option value="youtube">YouTube</option>
        <option value="vimeo">Vimeo</option>
        <option value="twitter">Twitter</option>
        <option value="instagram">Instagram</option>
        </select><br>

        <input type="text" name="media_title" class="form-control" placeholder="'.TEXT_PROFILE_GALERIE_DESCRIPTION_VIDEO.'"><br>
        <input type="hidden" name="media_sort"  class="form-control"><!-- Sortierung des Bildes in der Galerie<br>-->
        <input type="hidden" name="submit_upload" value="2">
         <button type="submit" class="btn btn-default">'.TEXT_PROFILE_GALERIE_ADD_VIDEO.'</button>
        </form>

        </div>
        
        <div role="tabpanel" class="tab-pane" id="videoupload">
            <form method="post" action="#SITE_URL#me/profile/galerie/" enctype="multipart/form-data"><br>
        <br>

            <input type="file" id="fileinput_video" name="media_upload_video" style="display: none;">
            <button type="button" onclick="document.getElementById(\'fileinput_video\').click();"  class="form-control"><i class="fa fa-file"></i>
                 '.TEXT_CHOOSE_FILE.'</button><br>

            <input type="text" name="media_title" class="form-control" placeholder="'.TEXT_PROFILE_GALERIE_DESCRIPTION_VIDEO.' Video"><br>
            <input type="hidden" name="media_sort"  class="form-control"><!-- Sortierung des Video in der Galerie<br>-->
            <input type="hidden" name="submit_upload" value="3">
             <button type="submit" class="btn btn-default">'.TEXT_PROFILE_GALERIE_ADD_VIDEO.'</button>
            </form>
        </div>
    </div>



    </div>
';

$footer_ext = '


<!-- Add mousewheel plugin (this is optional) -->
<script type="text/javascript" src="#SITE_URL#js/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add fancyBox -->
<link rel="stylesheet" href="#SITE_URL#js/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="#SITE_URL#js/jquery.fancybox.pack.js?v=2.1.5"></script>

<!-- Optionally add helpers - button, thumbnail and/or media -->
<link rel="stylesheet" href="#SITE_URL#js/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />

<script type="text/javascript" src="#SITE_URL#js/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$(".fancybox")

		.fancybox({
		openEffect  : "none",
        closeEffect : "none",
        nextEffect  : "none",
        prevEffect  : "none",
        padding     : 0,
		helpers 	: {
						media : {}
					  }
		});
	});
</script>

';

$content_output = array('TITLE' => 'Profile',
 'CONTENT' => $sidebar.$output,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => $footer_ext);