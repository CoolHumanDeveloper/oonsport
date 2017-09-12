<?php
is_user();

// DELETE SHOUTBOX POST 
if ( isset( $_POST[ 'shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) && strlen( $_POST[ 'shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) == 32 ) {

    if ( $_POST[ 'delete_shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] == 1 ) {
        $sql = "DELETE FROM `user_shoutbox` WHERE MD5(CONCAT(shoutbox_message_id, shoutbox_date, user_id))='" . $_POST[ 'shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] . "' AND user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "' ";

        $query = $DB->prepare( $sql );
        $query->execute();
        if ( $query->rowCount() == 1 ) {
            $_SESSION[ 'system_temp_message' ] .= set_system_message( "success", TEXT_MARKETPLACE_SUCCESS_REMOVED );
            build_history_log( $_SESSION[ 'user' ][ 'user_id' ], "removed_shoutbox", '' );
            header( "Location: " . SITE_URL . "marketplace/" );
            die();

        } else {
            $_SESSION[ 'system_temp_message' ] .= set_system_message( "error", TEXT_MARKETPLACE_SUCCESS_REMOVED_ERROR );
            header( "Location: " . SITE_URL . "marketplace/" );
            die();
        }
    }
    
    if($_POST['reactivate_shoutbox_key_' . $_SESSION['user']['secure_spam_key']]==1) {
		$sql = "UPDATE `user_shoutbox` SET shoutbox_status = 0, shoutbox_durration = '".date("Y-m-d H:i:s",strtotime("+7 days"))."' WHERE MD5(CONCAT(shoutbox_message_id, shoutbox_date, user_id))='".$_POST['shoutbox_key_' . $_SESSION['user']['secure_spam_key']]."' AND user_id = '".$_SESSION['user']['user_id']."' ";
		
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_MARKETPLACE_SUCCESS_REACTIVATED);		
            build_history_log($_SESSION['user']['user_id'],"removed_shoutbox",'');
            header("Location: ".SITE_URL."marketplace/");
            die();
		
		}
		else {
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_MARKETPLACE_SUCCESS_REACTIVATED_ERROR);
            header("Location: ".SITE_URL."marketplace/");
            die();
		}
	}
}

if ( isset( $_POST[ 'add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) ) {
    
    
    if ( strlen( $_POST[ 'add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) > 255 ) {
        $_SESSION[ 'system_message' ] .= set_system_message( "error", TEXT_MARKETPLACE_ERROR_TEXT_LENGTH );
        $shoutbox_renew_message = $_POST[ 'add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ];
    } else
    if ( strlen( $_POST[ 'add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) === 0 ) {
        $_SESSION[ 'system_message' ] .= set_system_message( "error", TEXT_MARKETPLACE_ERROR_TEXT_LENGTH_SHORT );
        $shoutbox_renew_message = $_POST[ 'add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ];
    } else if ( strlen( $_POST[ 'add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) > 64 ) {
        $_SESSION[ 'system_message' ] .= set_system_message( "error", TEXT_MARKETPLACE_ERROR_TITLE_LENGTH );
        $shoutbox_renew_message = $_POST[ 'add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ];
    } else
    if ( strlen( $_POST[ 'add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] ) === 0 ) {
        $_SESSION[ 'system_message' ] .= set_system_message( "error", TEXT_MARKETPLACE_ERROR_TITLE_LENGTH_SHORT );
        $shoutbox_renew_message = $_POST[ 'add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ];
    } else {
        if ( $_POST[ 'add_shoutbox_duration_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] > 0 && $_POST[ 'add_shoutbox_duration_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] <= 30 ) {
            $duration = $_POST[ 'add_shoutbox_duration_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ];
        } else {
            $duration = 30;
        }


        $sql = "INSERT INTO 
        `user_shoutbox` 
        (user_id, `shoutbox_date`, `shoutbox_durration`, `shoutbox_text`,`shoutbox_title`,`shoutbox_type`, shoutbox_sport_group_id) 
        VALUES 
        ('" . $_SESSION[ 'user' ][ 'user_id' ] . "', NOW(), '" . date( "Y-m-d H:i:s", strtotime( "+ " . $duration . " days" ) ) . "', '" . $_POST[ 'add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] . "', '" . $_POST[ 'add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ] . "', '" . $_POST[ 'add_shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] ][ 0 ] . "', '".$_POST['shoutbox_group']."');";

        $query = $DB->prepare( $sql );
        $query->execute();
        
        $lastShoutboxId = $DB->lastInsertId();

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

        // ENDE BILDER HINZUFÜGEN

        $_SESSION[ 'system_temp_message' ] .= set_system_message( "success", TEXT_MARKETPLACE_SUCCESS_ADD );
        header( "Location: " . SITE_URL . "marketplace/" );
        die();
    }
}

// Settings
if ( isset( $_POST[ 'update_settings' ] ) ) {
    $sql = "UPDATE user_settings SET settings_value='0' WHERE  user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "'";
    $query = $DB->prepare( $sql );
    $query->execute();

    foreach ( $_POST as $getkey => $getvalue ) {
        if ( stristr( $getkey, "MySet_" ) == true ) {

            $sql = "UPDATE user_settings SET settings_value='" . $getvalue . "' WHERE settings_key='" . str_replace( "MySet_", "", $getkey ) . "' AND user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "'";

            $query = $DB->prepare( $sql );
            $query->execute();
        }

    }
    $_SESSION[ 'system_temp_message' ] .= set_system_message( "success", TEXT_SETTINGS_UPDATED );
    header( "Location: " . SITE_URL . "marketplace/" );
    die();

}

$settings_form = '
<ul class="list-group  sub_nav">
<li class="list-group-item">
<form method="post" action="#SITE_URL#marketplace/">
';

$settings_types = array( 'marketplace_display_radius' => 'radius',
    'marketplace_display_sports' => 'checkbox' );

$radius_distances = array( 0, 10, 25, 50, 100, 200 );

$setting_headlines = array();

$sql = "SELECT * FROM user_settings WHERE user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "' AND settings_type = 'marketplace' ORDER BY settings_type ASC, settings_key ASC";
$query = $DB->prepare( $sql );
$query->execute();
$get_settings = $query->fetchAll();
foreach ( $get_settings as $settings ) {
    $settings[ 'settings_type' ] = 'marketplace'; // Überschreiben für Marketplace Variablen
    if ( !in_array( $settings[ 'settings_type' ], $setting_headlines ) ) {
        array_push( $setting_headlines, $settings[ 'settings_type' ] );
        $settings_form .= '  <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <h4 class="profile">' . constant( 'TEXT_SETTING_HEADER_' . strtoupper( $settings[ 'settings_type' ] ) ) . '</h4></div>

                </div>';
    }


    $settings_form .= '	<div class="row"><br>

                    <div class="col-md-7 col-sm7">' . constant( 'TEXT_SETTING_' . strtoupper( $settings[ 'settings_key' ] ) ) . '</div>
                    <div class="col-md-5 col-sm-5">';

    if ( $settings_types[ $settings[ 'settings_key' ] ] == 'checkbox' ) {
        $checked_value = '';
        if ( $settings[ 'settings_value' ] == 1 )$checked_value = ' checked="checked"';
        $settings_form .= '<input type="checkbox" name="MySet_' . $settings[ 'settings_key' ] . '" ' . $checked_value . ' value="1">';
    }

    if ( $settings_types[ $settings[ 'settings_key' ] ] == 'radius' ) {
        $settings_form .= '<select name="MySet_' . $settings[ 'settings_key' ] . '" class="form-control">';
        for ( $x_rad = 0; $x_rad < count( $radius_distances ); $x_rad++ ) {
            $radius_distances_select = "";

            if ( $radius_distances[ $x_rad ] == 0 ) {
                $radius_distances_text = TEXT_GLOBAL_NONE;
            } else {
                $radius_distances_text = $radius_distances[ $x_rad ] . " " . TEXT_GLOBAL_KM;
            }

            if ( $settings[ 'settings_value' ] == $radius_distances[ $x_rad ] ) {
                $radius_distances_select = " selected";

            }


            $settings_form .= '<option' . $radius_distances_select . ' value="' . $radius_distances[ $x_rad ] . '" >' . $radius_distances_text . '</option>';
        }

        $settings_form .= '</select>';
    }
    $settings_form .= '</div>
        </div>';
}


$settings_form .= '<br>
<br>
<input type="hidden" name="update_settings" value="1">
<button type="submit" class="btn btn-primary form-control">' . TEXT_GLOBAL_SAVE . '</button></form>
</li>
</ul>';


// End Settings

$subnav = ' ' . $settings_form . ' ';


$sidebar = '

<div class="col-md-3 col-sm-12">
<div class="side_bar">
<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="' . TEXT_GLOBAL_BACK . '"><i class="fa fa-chevron-left"></i>
</a> ' . TEXT_MARKETPLACE_HEADER . '
</li>
  </ul>
' . $subnav . '
</div>
'.build_banner('marketplace','xs','',0,'').'
'.build_banner('marketplace','sm','',0,'').'
'.build_banner('marketplace','md','',0,'').'
'.build_banner('marketplace','lg','',0,'').'
	</div>';

$sub_sidebar = '

<div class="col-md-3 col-sm-12">
<div class="sub_side_bar">
' . $subnav . '
</div>
	</div>';

$result_item = file_get_contents( SERVER_PATH . "template/modules/search/list-item.html" );

$output = '	<div class="col-md-9 col-sm-12">';
if(!isset($_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]])) {
$output.=
    '<div class="col-md-12 content-box-right" id="marketPlaceBox">

   
    <form method="post" action="#SITE_URL#marketplace/"  enctype="multipart/form-data"  class="collapse" id="collapseMarketplace" data-parent="#marketPlaceBox">
        
        <div class="col-md-8">
                 <h4 class="search">' . TEXT_MARKETPLACE_HEADER_CREATE . ':</h4>
                <br>
              <label>
                <input name="add_shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="marketplace_offer" type="radio" checked>
                ' . TEXT_SHOUTBOX_TYPE_OFFER . '</label>
              <label>
                <input name="add_shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="marketplace_search" type="radio">
                ' . TEXT_SHOUTBOX_TYPE_SEARCH . '</label>
              <br>
              <input type="text" name="add_shoutbox_title_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" placeholder="' . TEXT_MARKETPLACE_TITLE_PLACEHOLDER . '" class="form-control">
              <br>
              <textarea class="form-control" name="add_shoutbox_text_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" rows="4" maxlength="255" placeholder="' . TEXT_MARKETPLACE_PLACEHOLDER . '"></textarea>
              <br>
        </div>
        <div class="col-md-4">
            <br>
              <br>
              <input type="file" id="fileinput" name="markteplaceImages" multiple accept="application/pdf,image/*" style="display: none;" />
              <button type="button" onclick="document.getElementById(\'fileinput\').click();"  class="form-control"><i class="fa fa-file"></i> ' . TEXT_MARKETPLACE_ADD_IMAGE . ' </button>
              <div id="gallery"> </div>
              <div style="clear:both"></div>
              <label for="add_shoutbox_duration_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '">' . TEXT_SHOUTBOX_DURATION . '</label>
              <select name="add_shoutbox_duration_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '"  class="form-control">
                <option value="1">1 ' . TEXT_GLOBAL_DAY . '</option>
                <option value="2">2 ' . TEXT_GLOBAL_DAYS . '</option>
                <option value="7">7 ' . TEXT_GLOBAL_DAYS . '</option>
                <option value="14">14 ' . TEXT_GLOBAL_DAYS . '</option>
                <option value="30">30 ' . TEXT_GLOBAL_DAYS . '</option>
              </select>
              <br>
              <label for="shoutbox_group">' . TEXT_SHOUTBOX_SPORT.'</label>
              ' . build_search_sports_select($selected = $_POST['shoutbox_group'], $search_lines = '', $first_level = true) . '
            <br>
            <input type="submit" value="' . TEXT_MARKETPLACE_WRITE . '" class="btn btn-sm btn-primary form-control">
        </div>

    </form>
    <div class="col-md-3">
    <a class="btn btn-sm btn-primary form-control" data-toggle="collapse" href="#collapseMarketplace" aria-expanded="false" aria-controls="collapseMarketplace" data-parent="#marketPlaceBox" onClick="this.style.display=\'none\'">
    ' . TEXT_MARKETPLACE_WRITE . '
  </a>
  </div>
</div>';
}
$output .='
<div class="col-md-12 content-box-right">
<h4 class= "search">' . TEXT_MARKETPLACE_SEARCH_HEADER . ':</h4>
<div class="row">
    <form method="get" action="#SITE_URL#marketplace/"  enctype="multipart/form-data">
        <div class="col-md-6"> 
        <label>
            <input name="marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="marketplace_all" type="radio" ';
    if(!isset($_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0]) || $_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] == 'marketplace_all' ) {
            $output .= ' checked ';
        }

    $output .='>
            ' . TEXT_SHOUTBOX_TYPE_ALL . '</label>
          <label>
            <input name="marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="marketplace_offer" type="radio"';
    if($_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] == 'marketplace_offer') {
            $output .= ' checked ';
        }

    $output .='>
            ' . TEXT_SHOUTBOX_TYPE_OFFER . '</label>
          <label>
            <input name="marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="marketplace_search" type="radio"';
    if($_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] == 'marketplace_search') {
            $output .= ' checked ';
        }

    $output .='>
            ' . TEXT_SHOUTBOX_TYPE_SEARCH . '</label>
          <br>
          <input type="text" name="marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" placeholder="' . TEXT_MARKETPLACE_SEARCH_PLACEHOLDER . '" value="';
        
        if(isset($_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . ''])) {
            $output .= $_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . ''];
        }

        $output .= '" class="form-control">
          <br>
        </div>
        <div class="col-md-4">
          <label for="shoutbox_group">' . TEXT_SHOUTBOX_SPORT.'</label>
          ' . build_search_sports_select($selected = $_GET['shoutbox_group'], $search_lines = '', $first_level = true) . '
      </div>
      <div class="col-md-2">
          <label for="shoutbox_group">&nbsp;</label>
        <input type="submit" value="' . TEXT_MARKETPLACE_SEARCH . '" class="btn btn-sm btn-primary form-control">
      </div>
    </form>
    </div>
</div>
    <div class="col-md-12 content-box-right">
    <h4 class="search">' . TEXT_MARKETPLACE_HEADER_RESULTS . ':</h4>
            <br>
    ' . build_shoutbox_feed( $_SESSION[ 'user' ][ 'user_id' ], 'marketplace' ) . '<br><br>
    </div>
</div>';

$footer_ext = '<script src="/js/image/gallery.js"></script><script>
jQuery(document).ready(function(){
    $(document).on(\'click\', \'.deleteButton\', function(){
    $(this).parent().remove();
        console.log("test2");
        });
});
</script>
';
$header_ext = '<style>
        #gallery .thumbnail{
            width:70px;
            height: 70px;
            padding:5px;
            float:left;
            margin:2px;
        }
        #gallery .thumbnail img{
            
           width: 50px;
            height: 50px;
            
            position: relative;
            top: -20px;
            left: 0px;
            z-index: 0;
        }
        
        .deleteButton
        {
            float: right;
            z-index: 100;
            right: 0;
            top: 0;
            position: relative;
        }
    </style>';

$content_output = array( 'TITLE' => TEXT_MARKETPLACE_HEADER,
    'CONTENT' => $sidebar . $output . $sub_sidebar,
    'HEADER_EXT' => $header_ext,
    'FOOTER_EXT' => $footer_ext );