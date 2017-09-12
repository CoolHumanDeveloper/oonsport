<?php
is_user();

if ( isset( $_GET[ 'content_value' ] ) && strlen( $_GET[ 'content_value' ] ) == 32 ) {
    $sql = "SELECT u.*, ud.*, upro.* FROM user u
	 LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
	 LEFT JOIN user_profile AS upro ON u.user_id = upro.user_id
	  WHERE MD5(CONCAT(u.user_id,ud.user_nickname))='" . $_GET[ 'content_value' ] . "' LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $user_profile = $query->fetch();
} else {
    header( "Location: " . SITE_URL . "search/" );
    die();
}

$sidebar = '<div class="col-md-3 col-sm-12">
<ul class="list-group">
  <li class="list-group-item active start_register_header profile_header" style="background:url(' . build_default_image( $user_profile[ 'user_id' ], "300x300", "plain" ) . ');">' . $user_profile[ 'user_nickname' ] . '<br><small>' . get_user_type( $user_profile[ 'user_type' ], "text" ) . '</small><!--<br><br>
' . build_default_image( $user_profile[ 'user_id' ], "200x200", "img-thumbnail" ) . '-->

</li>
 <li class="list-group-item">' . TEXT_PROFILE_LAST_TIME_UP . ': ' . last_time_online( $user_profile[ 'user_id' ] ) . '
</li>
<a  class="list-group-item" href="#SITE_URL#/profile/' . md5( $user_profile[ 'user_id' ] . $user_profile[ 'user_nickname' ] ) . '"><i class="fa fa-user"></i> ' . TEXT_GLOBAL_PROFILE . ' ' . TEXT_GLOBAL_OF . ' ' . $user_profile[ 'user_nickname' ] . '</a>
  </ul>';

if ( $_SESSION[ 'user' ][ 'user_id' ] != $user_profile[ 'user_id' ] ) {
    $sidebar .= '<div class="list-group">
     <form method="post" action="#SITE_URL#me/messages/create/">
      <button class="list-group-item" type="submit"><i class="fa fa-envelope"></i> ' . TEXT_SUB_NAV_WRITE_MESSAGE . ' </button>
      <input type="hidden" name="message_to_user_id_#SPAM_KEY#" value="' . md5( $user_profile[ 'user_id' ] . $user_profile[ 'user_nickname' ] ) . '">
      </form>';

    if ( friendship_exists( $_SESSION[ 'user' ][ 'user_id' ], $user_profile[ 'user_id' ] ) === true ) {
        if ( friendship_active( $_SESSION[ 'user' ][ 'user_id' ], $user_profile[ 'user_id' ] ) === true ) {
            $sidebar .= '

             <form method="post" action="#SITE_URL#me/friends/friends/">
              <button class="list-group-item" type="submit"><i class="fa fa-user-plus"></i> <span class="btn btn-sm btn-primary"><i class="fa fa-check"></i>
         Ihr seid befreundet</a></span> 

              </form>';
        } else {
            $sidebar .= '
                <form method="post" action="#SITE_URL#me/friends/request/">
                  <button class="list-group-item" type="submit"><i class="fa fa-user-plus"></i> <span class="btn btn-sm btn-danger"><i class="fa fa-hourglass-o"></i>
             Freundschaft unbestätigt</a></span> </button>

                  </form>';
        }
    } else {
        $sidebar .= '
        <form method="post" action="#SITE_URL#me/friends/request/">
          <button class="list-group-item" type="submit"><i class="fa fa-user-plus"></i> ' . TEXT_SUB_NAV_ADD_FRIEND . ' </button>
          <input type="hidden" name="add_user_id_#SPAM_KEY#" value="' . md5( $user_profile[ 'user_id' ] . $user_profile[ 'user_nickname' ] ) . '">
          </form>';
    }

    if ( watchlist_exists( $_SESSION[ 'user' ][ 'user_id' ], $user_profile[ 'user_id' ] ) === true ) {
        $sidebar .= '<form method="post" action="#SITE_URL#me/friends/watch/">
          <button class="list-group-item" type="submit"><i class="fa fa-eye"></i> <span class="btn btn-sm btn-primary"><i class="fa fa-check"></i>
     Auf deiner Merkliste</a></span> </form> ';
    } else {
        $sidebar .= '<form method="post" action="#SITE_URL#me/friends/watch/">
          <button class="list-group-item" type="submit"><i class="fa fa-eye"></i> ' . TEXT_SUB_NAV_ADD_WATCH . ' </button>
          <input type="hidden" name="watch_user_id_#SPAM_KEY#" value="' . md5( $user_profile[ 'user_id' ] . $user_profile[ 'user_nickname' ] ) . '">
          </form>
         ';
    }
    $sidebar .= '</div>';
} else {
    $sidebar .= '
                <ul class="list-group">
          <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="' . TEXT_GLOBAL_BACK . '"><i class="fa fa-chevron-left"></i>
        </a> ' . TEXT_PROFILE_HEADER . '
        </li>
          </ul>
          <div class="list-group">
           <a href="#SITE_URL#me/profile/default/" class="list-group-item"><i class="fa fa-pencil-square-o"></i> ' . TEXT_PROFILE_EDIT_PROFILE . '</a>
          </div>
          <div class="list-group">
         <a href="#SITE_URL#me/messages" class="list-group-item"><i class="fa fa-envelope"></i> ' . TEXT_MESSAGES_MESSAGES . ' </a>
         <a href="#SITE_URL#me/friends" class="list-group-item"><i class="fa fa-user"></i> ' . TEXT_PROFILE_FRIENDS_FRIENDS . ' </a>
         <a href="#SITE_URL#me/friends/watch/" class="list-group-item"><i class="fa fa-eye"></i> ' . TEXT_PROFILE_FRIENDS_WATCHLIST . ' 
        </a>
         <a href="#SITE_URL#me/settings" class="list-group-item"><i class="fa fa-cog"></i> ' . TEXT_SETTINGS_HEADER . ' 
        </a>

         </div>
        ';
}

$sidebar .= '</div>';


$output = '' . $sidebar . '
<div class="col-md-9 col-sm-9  content-box-right">
        <div class="row">
		<h4 class="profile">' . TEXT_PROFILE_HEADER_IMAGES_VIDEO . ' ' . TEXT_GLOBAL_OF . ' ' . $user_profile[ 'user_nickname' ] . ':</h4>
        ';

$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='" . $user_profile[ 'user_id' ] . "' AND um.media_id=m.media_id ORDER BY m.media_id DESC";

$query = $DB->prepare( $sql );
$query->execute();
$get_galerie_image = $query->fetchAll();

foreach ( $get_galerie_image as $galerie_image ) {
    if ( $galerie_image[ 'media_type' ] == 1 ) {

        $output .= '<div class="col-md-2 col-sm-3 col-xs-6"><a href="' . build_user_image( $galerie_image[ 'media_file' ], "profil", "800x800", "plain", "" ) . '" class="fancybox" rel="gallery" title="' . $galerie_image[ 'media_title' ] . '">' . build_user_image( $galerie_image[ 'media_file' ], "profil", "150x150", "html", "img-thumbnail" ) . '</a><br><br>
        </div>';
    } else if ( $galerie_image[ 'media_type' ] == 3 ) {

        $output .= '<div class="col-md-2 col-sm-3 col-xs-6"><a href="#SITE_URL#tools/webplayer/' . $galerie_image[ 'media_file' ] . '/" class="fancybox fancybox.iframe" rel="gallery" title="' . $galerie_image[ 'media_title' ] . '"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="150" height="150"></a><br><br>
        </div>';
    } else {
        $output .= '<div class="col-md-2 col-sm-3 col-xs-6">
        <a href="' . $galerie_image[ 'media_url' ] . '"  rel="gallery" target="_blank" class="fancybox  fancybox.iframe" title="' . $galerie_image[ 'media_title' ] . '"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="150" height="150">
        </a>
        <br><br>

        </div>';
    }
}


$output .= '</div></div>';

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