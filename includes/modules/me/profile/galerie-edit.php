<?php


if(isset($_POST['edit_media'])) {
    $sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$_SESSION['user']['user_id']."' AND um.media_id=m.media_id AND m.media_id = '".$_POST['galerie_media_id']."' LIMIT 1";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $del_image = $query->fetch(); 

    if($del_image['media_id'] 
       && isset($_POST['edit_action']) 
       && file_exists(SERVER_IMAGE_PATH . "user/".$del_image['media_file'] . "-" . IMAGE_MAX_DEFAULT_SIZE.".jpg")
      ) {
        $modifyImageCommand = '';
        $md5_file_name = md5($del_image['media_file'].time().rand(111,999));
        
        $orignal_image = SERVER_IMAGE_PATH . "user/".$del_image['media_file']."-".IMAGE_MAX_DEFAULT_SIZE.".jpg";
        $prod_img = SERVER_IMAGE_PATH . "user/".$md5_file_name.'-'.IMAGE_MAX_DEFAULT_SIZE.".jpg";
    
        
        
        
        switch ($_POST['edit_action']){
            case "rotate_l":
                $modifyImageCommand = 'convert "'.$orignal_image.'" -rotate "-90" "'.$prod_img.'"';
            break;
                
            case "rotate_r":
                $modifyImageCommand = 'convert "'.$orignal_image.'" -rotate "90" "'.$prod_img.'"';
            break;
                
            case "rotate_rr":
                $modifyImageCommand = 'convert "'.$orignal_image.'" -rotate "180" "'.$prod_img.'"';
            break;
                
            case "flip":
                $modifyImageCommand = 'convert "'.$orignal_image.'" -flop "'.$prod_img.'"';
            break;
                
            default:
            break;
        }
        
        
        if($modifyImageCommand != '') {
            deleteImageForUpdate($del_image['media_id']);

            exec($modifyImageCommand);
            
            $sql = "UPDATE user_media SET media_file='" . $md5_file_name . "' WHERE media_id='".$del_image['media_id']."'";
            $query = $DB->prepare($sql);
            $query->execute();


            if(file_exists(SERVER_IMAGE_PATH . "user/".$del_image['media_file']."-".IMAGE_MAX_DEFAULT_SIZE.".jpg")) {
                unlink(SERVER_IMAGE_PATH . "user/".$del_image['media_file']."-".IMAGE_MAX_DEFAULT_SIZE.".jpg");
            }
           
            if(file_exists(SERVER_IMAGE_PATH . "temp/".$del_image['media_file'].".jpg")){
                unlink(SERVER_IMAGE_PATH . "temp/".$del_image['media_file'].".jpg");
            }

            $_SESSION['system_message'] .= set_system_message("success", TEXT_PROFILE_GALERIE_CHANGE_IMAGE_SUCCESS);
        }
        
    }
    else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_GALERIE_CHANGE_IMAGE_ERROR);
    }
}
		

$output = '<div class="col-md-6 col-sm-12">
<h4 class="profile">'.TEXT_PROFILE_SPORT_HEADER_IMAGES_EDIT.' </h4><br>';

$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$_SESSION['user']['user_id']."' AND um.media_id=m.media_id AND m.media_id = '".$_POST['galerie_media_id']."' LIMIT 1";

$query = $DB->prepare($sql);
$query->execute();
$galerie_image = $query->fetch();

if ($galerie_image) {
    if($galerie_image['media_type']==1){
        $galerie_default='';
        $galerie_disabled='';
        if($galerie_image['user_default_media']==1) {
            $galerie_default=' galerie_default';
            $galerie_disabled=' disabled';
        }
        $output .= '<div class="col-md-12 col-sm-12 col-xs-12">
        <form method="post" action="#SITE_URL#me/profile/galerie-edit/" >
        <input name="galerie_media_id" value="'.$galerie_image['media_id'].'" type="hidden">
        <input name="edit_media" value="1" type="hidden">
        <br> <button name="edit_action" value="rotate_l" class="btn btn-sm btn-primary" type="submit"><i class="fa fa-undo"></i> 90°</button> 
        <button  name="edit_action" value="rotate_r" class="btn btn-sm btn-primary" type="submit"><i class="fa fa-repeat"></i> 90°</button> 
        <button  name="edit_action" value="rotate_rr" class="btn btn-sm btn-primary" type="submit"><i class="fa fa-repeat"></i> 180°</button>
        <button  name="edit_action" value="flip" class="btn btn-sm btn-primary" type="submit"><i class="fa fa-exchange"></i> ' . TEXT_FLIP_IMAGE . '</button>
        
        </form><br>'.build_user_image($galerie_image['media_file'],"profil","800x800","html","img-thumbnail".$galerie_default).'<br>'.$galerie_image['media_title'].'<br>

        <br>
        <br>
        </div>';

    }
}


$output .= '<div class="clearfix"></div> </div>';

$content_output = array('TITLE' => 'Profile',
 'CONTENT' => $sidebar.$output,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');