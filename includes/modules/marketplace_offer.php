<?php
is_user();

if ( isset( $_GET[ 'content_value' ] ) && strlen( $_GET[ 'content_value' ] ) == 32 ) {

    $sql = "SELECT u.*, ud.*, ubox.*, upro.*
 
             FROM 

             user u
             INNER JOIN user_details ud ON u.user_id=ud.user_id
             INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
             INNER JOIN user_profile AS upro ON u.user_id = upro.user_id
             WHERE 
            u.user_status=1 AND
            ubox.shoutbox_type LIKE 'marketplace_%' AND
            MD5(CONCAT(ubox.shoutbox_message_id,ubox.shoutbox_date,ubox.user_id))='" . $_GET[ 'content_value' ] . "'
            GROUP BY ubox.shoutbox_message_id LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $marketplaceOffer = $query->fetch();

    if ( !$marketplaceOffer ) {
        header( "Location: " . SITE_URL . "marketplace/" );
        die();
    }
} else {
    header( "Location: " . SITE_URL . "marketplace/" );
    die();
}

$sidebar = '
<div class="col-md-3 col-sm-12">

<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="' . TEXT_GLOBAL_BACK . '"><i class="fa fa-chevron-left"></i>
</a> ' . TEXT_MARKETPLACE_HEADER . '
</li>
 
</li>
 <li class="list-group-item">' . TEXT_PROFILE_LAST_TIME_UP . ': ' . last_time_online( $marketplaceOffer[ 'user_id' ] ) . '
</li>
<a  class="list-group-item" href="#SITE_URL#/profile/' . md5( $marketplaceOffer[ 'user_id' ] . $marketplaceOffer[ 'user_nickname' ] ) . '"><i class="fa fa-user"></i> ' . TEXT_GLOBAL_PROFILE . ' ' . TEXT_GLOBAL_OF . ' ' . $marketplaceOffer[ 'user_nickname' ] . '</a>
  </ul>';

if ( $_SESSION[ 'user' ][ 'user_id' ] != $marketplaceOffer[ 'user_id' ] ) {
    $sidebar .= '<div class="list-group">
     <form method="post" action="#SITE_URL#me/messages/create/">
      <button class="list-group-item" type="submit"><i class="fa fa-envelope"></i> ' . TEXT_SUB_NAV_WRITE_MESSAGE . ' </button>
      <input type="hidden" name = "message_subject_#SPAM_KEY#" value="' . TEXT_MARKETPLACE_REQUEST_SUBJECT . ': ' . $marketplaceOffer[ 'shoutbox_title' ] . '">
      <input type="hidden" name="message_to_user_id_#SPAM_KEY#" value="' . md5( $marketplaceOffer[ 'user_id' ] . $marketplaceOffer[ 'user_nickname' ] ) . '">
      </form>';

    $sidebar .= '</div>'.build_banner('marketplace_offer','xs','',0,'').'
'.build_banner('marketplace_offer','sm','',0,'').'
'.build_banner('marketplace_offer','md','',0,'').'
'.build_banner('marketplace_offer','lg','',0,'').'';
}

$sidebar .= '</div>';


$output = '' . $sidebar . '
<div class="col-md-9 col-sm-9 content-box-right">
        <div class="row">
            <div class="col-md-8 col-sm-12">
                <h4 class="search"><span class="light_green">' . constant( 'TEXT_SHOUTBOX_TYPE_' . strtoupper( $marketplaceOffer[ 'shoutbox_type' ] ) ) . '</span><br>' . $marketplaceOffer[ 'shoutbox_title' ] . '</h4>        <small>' . date( "d.m.Y H:i", strtotime( $marketplaceOffer[ 'shoutbox_date' ] ) ) . ' von ' . $marketplaceOffer[ 'user_nickname' ] . ' aus ' . get_city_name( $marketplaceOffer[ 'user_city_id' ], $marketplaceOffer[ 'user_country' ] ) . '</small>

                <br><br>
                ' . nl2br( $marketplaceOffer[ 'shoutbox_text' ] );

if ( $marketplaceOffer[ 'user_id' ] == $_SESSION[ 'user' ][ 'user_id' ]) {
    $output.= '
                
                <form method="post" action="#SITE_URL#marketplace/" >
                            <input name="shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="' . md5( $marketplaceOffer[ 'shoutbox_message_id' ] . $marketplaceOffer[ 'shoutbox_date' ] . $marketplaceOffer[ 'user_id' ] ) . '" type="hidden">
                            <button name="delete_shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" class="btn btn-xs btn-danger" type="submit" value="1" >
                            <i class="fa fa-trash"></i> '.TEXT_GLOBAL_DELETE.'
                            </button>
                        </form>';
}
$output.='</div>
            <div class="col-md-4 col-sm-12">
            ';

$sql = "SELECT * FROM user_shoutboxmedia m, user_to_shoutboxmedia um WHERE um.user_id='" . $marketplaceOffer[ 'user_id' ] . "' AND um.shoutbox_message_id = '".$marketplaceOffer['shoutbox_message_id']. "' AND um.media_id=m.media_id ORDER BY um.user_default_media DESC";

$query = $DB->prepare( $sql );
$query->execute();
$get_galerie_image = $query->fetchAll();
$galerieX = 0;
foreach ( $get_galerie_image as $galerie_image ) {
    if ( $galerie_image[ 'media_type' ] == 1 ) {
        if($galerieX == 0)  {
            $output .= '<div class="col-md-12 col-sm-12 col-xs-12"><a href="' . build_user_image( $galerie_image[ 'media_file' ], "profil", "800x800", "plain", "" ) . '" class="fancybox" rel="gallery" title="' . $galerie_image[ 'media_title' ] . '">' . build_user_image( $galerie_image[ 'media_file' ], "profil", "600x600", "html", "img-thumbnail" ) . '</a><br><br></div>';
        }
        else {
            $output .= '<div class="col-md-6 col-sm-6 col-xs-6"><a href="' . build_user_image( $galerie_image[ 'media_file' ], "profil", "800x800", "plain", "" ) . '" class="fancybox" rel="gallery" title="' . $galerie_image[ 'media_title' ] . '">' . build_user_image( $galerie_image[ 'media_file' ], "profil", "150x150", "html", "img-thumbnail" ) . '</a><br><br></div>';
        }

    } else {
        $output .= '<div class="col-md-6 col-sm-6 col-xs-6">
        <a href="' . $galerie_image[ 'media_url' ] . '"  rel="gallery" target="_blank" class="fancybox  fancybox.iframe" title="' . $galerie_image[ 'media_title' ] . '"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="150" height="150">
        </a>
        <br><br>

        </div>';
    }
    
    $galerieX ++;
}


$output .= '</div></div></div>';

$header_ext = '';
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


$content_output = array( 'TITLE' => SITE_NAME, 'CONTENT' => $output, 'HEADER_EXT' => $header_ext, 'FOOTER_EXT' => $footer_ext );