<?php 
is_user();

if(isset($_GET['content_value']) && strlen($_GET['content_value'])==32) {
	$sql = "SELECT u.*, ud.*, upro.* FROM user u
	 LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
	 LEFT JOIN user_profile AS upro ON u.user_id = upro.user_id
	  WHERE MD5(CONCAT(u.user_id,ud.user_nickname))='".$_GET['content_value']."' LIMIT 1";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $user_profile = $query->fetch();
	
	$total_media = count_total_media($user_profile['user_id']);
}
else {
	header("Location: ".SITE_URL."search/");
    die();
}
//get_user_details($userid)

if(isset($_POST['invite_to_group_' . $_SESSION['user']['secure_spam_key']]) && $_POST['invite_to_group_' . $_SESSION['user']['secure_spam_key']] == 1) {
	$invited_group = $_POST['invite_group_' . $_SESSION['user']['secure_spam_key']];
	
	if(is_group_member($invited_group,$user_profile['user_id']) === true) {
		$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_PROFILE_ALLREADY_GROUP_MEMBER);
		header("Location: ".SITE_URL.'profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'');
        die();
	}
	else
	if(is_group_member($invited_group,$user_profile['user_id']) === true) {
		$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_PROFILE_ALLREADY_GROUP_INVITED);
		header("Location: ".SITE_URL.'profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'');
        die();
	}
	else
	if(is_group_admin($invited_group,$_SESSION['user']['user_id']) === true) {
		
		$sql = "SELECT * FROM `groups` WHERE group_id = '".$invited_group."' LIMIT 1";
        $query = $DB->prepare($sql);
        $query->execute();
        $invited_group_details = $query->fetch();
		
		$sql = "INSERT INTO user_to_groups (user_id, group_id, group_user_status, group_user_invited) VALUES ('".$user_profile['user_id']."', '".$invited_group_details['group_id']."', 0 , NOW())";
		$query = $DB->prepare($sql);
        $query->execute();

		
        $email_content_array = array (
            "FROM_USER_NAME" => $_SESSION['user']['user_nickname'],
            "FROM_USER_IMAGE" => build_default_image($_SESSION['user']['user_id'],"115x115","plain"),
            "NAME" => $user_profile['user_firstname'],
            "GROUP_LINK" => 'group/' . md5($invited_group_details['group_id'].$invited_group_details['group_name'].$invited_group_details['group_created']),
            "GROUP_NAME" => $invited_group_details['group_name'],
            "GROUP_DETAILS" => $invited_group_details['group_description']
            );
		  
        // OON SPORT MESSAGE
        $subject=TEXT_GROUP_SUBJECT_FROM ." ". $_SESSION['user']['user_nickname']." : ". $invited_group_details['group_name'];
        $content = str_replace("#USER#",$_SESSION['user']['user_nickname'],TEXT_PROFILE_GROUP_REQUESTS_MESSAGE_CONTENT);
        $content = str_replace("#GROUP_NAME#",$invited_group_details['group_name'],$content);
        $systemlink="me/friends/groups/";
        send_message_online($user_profile['user_id'], $_SESSION['user']['user_id'], $subject, $content, $systemlink, 1);	
		  
		  // EMAIL
        $email_content_template=email_content_to_template("group-invited",$email_content_array,"");
        $alt_content="";

        if(have_permission("emailcopy_on_group_request",$user_profile['user_id'])) {
            if($user_profile['user_sub_of'] > 0) {
                $user_profile['user_email'] = getParentEmail($user_profile['user_sub_of']);
            }

              if(sending_email($user_profile['user_email'], $user_profile['user_firstname'], TEXT_GROUP_SUBJECT_FROM . " " . $_SESSION['user']['user_nickname']." : ".$invited_group_details['group_name'], $email_content_template,$alt_content,0)) {
                  $_SESSION['system_temp_message'] .= set_system_message("success", TEXT_PROFILE_INVITED_GROUP_SUCCESS);
                    build_history_log($_SESSION['user']['user_id'],"invited_group",'u:' . $user_profile['user_id'].',g:' . $invited_group_details['group_id']);

                    header("Location: ".SITE_URL.'profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'');
                    die();
              }
              else  {
                    $_SESSION['system_message'] .= set_system_message("error","FEHLER");
              }			
        }
        else {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_PROFILE_INVITED_GROUP_SUCCESS);
            build_history_log($_SESSION['user']['user_id'],"invited_group",'u:' . $user_profile['user_id'].',g:' . $invited_group_details['group_id']);

            header("Location: ".SITE_URL.'profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'');
            die();
        }
					
	}
	else {
		$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_PROFILE_INVITED_GROUP_ERROR);
		header("Location: ".SITE_URL.'profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'');

	}
	
}

if($_SESSION['user']['user_id'] == $user_profile['user_id']) {
    if(count_total_media($_SESSION['user']['user_id']) == 0) {
        $_SESSION['system_message'] .= set_system_message("info",TEXT_LOGIN_MISSING_IMAGE);
    }
}


if(isset($_POST['alertprofile']) && $_POST['alertprofile'] == 1) {
	if($_POST['alert_subject'] != '' && $_POST['alert_details'] != '') {
	
        $email_content_array = array(
            "SEND_NAME" => $_SESSION['user']['user_firstname'].' ' . $_SESSION['user']['user_lastname'].' (' . $_SESSION['user']['user_nickname'].')',
            "SEND_ID" => $_SESSION['user']['user_id'],
            "REPORT_NAME" => $user_profile['user_firstname'].' ' . $user_profile['user_lastname'].' (' . $user_profile['user_nickname'].')',
            "REPORT_ID" => $user_profile['user_id'],
            "SUBJECT" => $_POST['alert_subject'],
            "DETAILS" => $_POST['alert_details']
        );

        $email_content_template=email_content_to_template("report-profile",$email_content_array,"");
        $alt_content="";

        if(sending_email(SYSTEM_MAIL,SITE_NAME,'Profil ' . $user_profile['user_nickname'].' (' . $user_profile['user_id'].') wurde gemeldet',$email_content_template,$alt_content,0)) {					
            $_SESSION['system_temp_message'] .= set_system_message("succes",TEXT_PROFILE_ALERT_SUCCESS);
            build_history_log($_SESSION['user']['user_id'],"report_profile",$user_profile['user_id']);

            header("Location: ".SITE_URL.'profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'');
            die();
        }
        else {
                $_SESSION['system_message'] .= set_system_message("error","FEHLER");
        }
	}
	else {	
	   $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_ALERT_ERROR_FORM);
	}
}

$sidebar='<div class="col-md-3 col-sm-12">
<ul class="list-group">
  <li class="list-group-item active start_register_header profile_header" style="background:url('.build_default_image($user_profile['user_id'],"300x300","plain").');">' . $user_profile['user_nickname'].'<br><small>'.get_user_type($user_profile['user_type'],"text").'</small><!--<br><br>
'.build_default_image($user_profile['user_id'],"200x200","img-thumbnail").'-->

</li>
 <li class="list-group-item">' . TEXT_PROFILE_LAST_TIME_UP.': '.last_time_online($user_profile['user_id']).'
</li>
<a class="list-group-item" href="#SITE_URL#profile/gallery/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'"><i class="fa fa-picture-o"></i> ' . TEXT_PROFILE_HEADER_IMAGES_VIDEO.'</a>
  </ul>';
  
if($_SESSION['user']['user_id']!=$user_profile['user_id']) {
    $sidebar.='<div class="list-group">
        <form method="post" action="#SITE_URL#me/messages/create/">
        <button class="list-group-item" type="submit"><i class="fa fa-envelope"></i> ' . TEXT_SUB_NAV_WRITE_MESSAGE.' </button>
        <input type="hidden" name="message_to_user_id_#SPAM_KEY#" value="' . md5($user_profile['user_id'].$user_profile['user_nickname']).'">
        </form>';
  
  if(friendship_exists($_SESSION['user']['user_id'],$user_profile['user_id']) === true) {
		if(friendship_active($_SESSION['user']['user_id'],$user_profile['user_id']) === true) {
	       $sidebar.=' <form method="post" action="#SITE_URL#me/friends/friends/">
          <button class="list-group-item" type="submit"><i class="fa fa-user-plus"></i> <span class="btn btn-sm btn-primary"><i class="fa fa-check"></i>
     ' . TEXT_FRIEND_FRIENDSHIP_ACTIVE.'</span> </button>

          </form>';
		
		$my_groups = build_group_select($_SESSION['user']['user_id'],$user_profile);
	   $sidebar.=$my_groups;
		}
		else {
            $sidebar.='
            <form method="post" action="#SITE_URL#me/friends/request/">
              <button class="list-group-item" type="submit"><i class="fa fa-user-plus"></i> <span class="btn btn-sm btn-danger"><i class="fa fa-hourglass-o"></i>
            ' . TEXT_FRIEND_FRIENDSHIP_PENDING.'</span> </button>

              </form>';
		}
	}
	else {
        $sidebar.='
        <form method="post" action="#SITE_URL#me/friends/request/">
          <button class="list-group-item" type="submit"><i class="fa fa-user-plus"></i> ' . TEXT_SUB_NAV_ADD_FRIEND.' </button>
          <input type="hidden" name="add_user_id_#SPAM_KEY#" value="' . md5($user_profile['user_id'].$user_profile['user_nickname']).'">
          </form>';
	}
  
    if(watchlist_exists($_SESSION['user']['user_id'],$user_profile['user_id']) === true) {
      $sidebar.='<form method="post" action="#SITE_URL#me/friends/watch/">
	  <button class="list-group-item" type="submit"><i class="fa fa-eye"></i> <span class="btn btn-sm btn-primary"><i class="fa fa-check"></i> ' . TEXT_WATCHLIST_WATCHED.'</span> </button> 
	  </form>';
	}
	else {
        $sidebar.='<form method="post" action="#SITE_URL#me/friends/watch/">
        <button class="list-group-item" type="submit"><i class="fa fa-eye"></i> ' . TEXT_SUB_NAV_ADD_WATCH.' </button>
        <input type="hidden" name="watch_user_id_#SPAM_KEY#" value="' . md5($user_profile['user_id'].$user_profile['user_nickname']).'">
        </form>
        ';
	}
	$sidebar.='</div>';
}
else {
    $sidebar.='
            <ul class="list-group">
      <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="' . TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
    </a> ' . TEXT_PROFILE_HEADER.'
    </li>
      </ul>
      <div class="list-group">
       <a href="#SITE_URL#me/profile/default/" class="list-group-item"><i class="fa fa-pencil-square-o"></i> ' . TEXT_PROFILE_EDIT_PROFILE.'</a>
      </div>
      <div class="list-group">
     <a href="#SITE_URL#me/messages" class="list-group-item"><i class="fa fa-envelope"></i> ' . TEXT_MESSAGES_MESSAGES.' </a>
     <a href="#SITE_URL#me/friends" class="list-group-item"><i class="fa fa-user"></i> ' . TEXT_PROFILE_FRIENDS_FRIENDS.' </a>
     <a href="#SITE_URL#me/friends/watch/" class="list-group-item"><i class="fa fa-eye"></i> ' . TEXT_PROFILE_FRIENDS_WATCHLIST.' 
    </a>
    <a href="#SITE_URL#me/friends/groups/" class="list-group-item"><i class="fa fa-users"></i> ' . TEXT_PROFILE_FRIENDS_GROUPS.' 
    </a>
     <a href="#SITE_URL#me/settings" class="list-group-item"><i class="fa fa-cog"></i> ' . TEXT_SETTINGS_HEADER.' 
    </a>

     </div>
    ';
}
 
 // TODO USER SPORT AUSLESEN  = 0
 $sidebar.='
 '.build_banner('profile','xs','',0,'').'
'.build_banner('profile','sm','',0,'').'
'.build_banner('profile','md','',0,'').'
'.build_banner('profile','lg','',0,'').'

	</div>';
	
	
$output = '' . $sidebar.'
<div class="col-md-9 col-sm-9">
        <div class="col-md-6 col-sm-12 content-box-right">
        <div class="row">
		<h4 class="profile">' . TEXT_GLOBAL_PROFILE.':</h4>
        <table class="table">
 <tr><td>' . TEXT_PROFILE_LOCATION.':</td><td>'.get_city_name($user_profile['user_geo_city_id'],$user_profile['user_country']).'</td></tr>
 <tr><td>'.constant('TEXT_PROFILE_PROFILE_AGE_' . $user_profile['user_type']).':</td><td>'.get_age($user_profile['user_dob'],$user_profile['user_type']).'</td></tr>
 <tr class="hidden-md hidden-lg"><td>' . TEXT_PROFILE_SPORTS.':</td><td>'.get_user_main_sport($user_profile['user_id']).'</td></tr>';
 
 if($user_profile['user_type']== 1 || $user_profile['user_type'] == 2) {
	 $output .= '<tr><td>' . TEXT_GLOBAL_GENDER.':</td><td>'.constant('TEXT_GLOBAL_GENDER_'.strtoupper($user_profile['user_gender'])).'</td></tr>';
 }
 
$output .= '</table>
        </div>
        </div>
 <div class="col-md-1 col-sm-12">
        </div>
        <div class="col-md-5 col-sm-12 content-box-right">
		
        <div class="row" style="margin-left:20px">
        ';
		
$sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='".$user_profile['user_id']."' AND um.media_id=m.media_id ORDER BY m.media_id DESC LIMIT ".PROFILE_MAX_IMAGES."";

$query = $DB->prepare($sql);
$query->execute();
$get_galerie_image = $query->fetchAll();

$media_x=0;

foreach ($get_galerie_image as  $galerie_image){
    $media_x++;

    if($total_media > PROFILE_MAX_IMAGES && $media_x == PROFILE_MAX_IMAGES) {
        $output .= '<div class="col-md-4 col-sm-4 col-xs-6"><a href="#SITE_URL#/profile/gallery/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'"> + '.($total_media-5).' ' . TEXT_MORE_IMAGES_VIDEOS.'</a><br><br></div>
            <div class="hidden">
            <a href="#moreImages" rel="gallery" class="fancybox">Open</a>
            </div>
        <div id="moreImages">
        <p>
        <a class="btn btn-primary" href="#SITE_URL#/profile/gallery/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'"> + '.($total_media-5).' ' . TEXT_MORE_IMAGES_VIDEOS.'</a><br><br>
        </p>
        </diV>						


        ';
        break;
    }

    if($galerie_image['media_type']==1) {

        $output .= '<div class="col-md-4 col-sm-4 col-xs-6">
            <a href="'.build_user_image($galerie_image['media_file'],"profil","800x800","plain","").'" class="fancybox" rel="gallery" title="' . $galerie_image['media_title'].'">'.build_user_image($galerie_image['media_file'],"profil","150x150","html","img-thumbnail").'</a><br>
            <br>
            </div>';

    }else if ( $galerie_image[ 'media_type' ] == 3 ) {

        $output .= '<div class="col-md-4 col-sm-4 col-xs-6"><a href="#SITE_URL#tools/webplayer/' . $galerie_image[ 'media_file' ] . '/" class="fancybox fancybox.iframe" rel="gallery" title="' . $galerie_image[ 'media_title' ] . '"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="150" height="150"></a><br><br>
        </div>';
    }
    else  {
        $output .= '<div class="col-md-4 col-sm-4 col-xs-6">
            <a href="' . $galerie_image['media_url'].'"  rel="gallery" target="_blank" class="fancybox  fancybox.iframe" title="' . $galerie_image['media_title'].'"><img src="#SITE_URL#images/default/video_play.jpg" class="img-thumbnail" width="150" height="150">
            </a>
            <br><br>

            </div>';
    }

}
		
        $output .= '
        </div>
        </div>
        <div class="col-md-12 col-sm-12 content-box-right">
			<div class="row">
				<h4 class="profile">' . TEXT_GLOBAL_SPORTTYPES.':</h4>
				<div class="col-md-12 col-sm-12">
					'.get_user_main_sport($user_profile['user_id'], "detail_list").'
				</div>
			
			</div>
		</div>';
		 if($user_profile['user_type']>2) {
             $output .= '<div class="col-md-12 col-sm-12 content-box-right">
				<div class="row">
				<h4 class="profile">' . TEXT_PROFILE_LOCATE.':</h4>
					 <div id="container-fluid">
						<div id="cd-google-map">
							<div id="google-container" style="height:200px; width:100%; background-color:#CCC;">
							</div>
						</div>
					  </div>
				 </div>
			</div>';
        }
		
		$output .= '<div class="col-md-12 col-sm-12 content-box-right">
		<div class="row">
        <h4 class="profile">' . TEXT_GLOBAL_ABOUT.' ' . $user_profile['user_nickname'].':</h4>
        ';
		if($user_profile['user_details'] != '') {
			$output.=nl2br($user_profile['user_details']);
		}
		else {
			$output.=TEXT_GLOBAL_NO_FURTHER_INFORMATION;
		}
		
        $output .= '</div>
		
        </div>
		
		<div class="col-md-5 col-sm-12 content-box-right">
			<div class="row">
				<h4 class="profile">' . TEXT_DASHBOARD_HEADLINE_SHOUTBOX_PROFILE.' ' . TEXT_GLOBAL_OF.' ' . $user_profile['user_nickname'].':</h4>
				<div class="col-md-12 col-sm-12">
					'.build_shoutbox_feed($user_profile['user_id'],'profile').'
				</div>
			
			</div>
		</div>
        
        <div class="col-md-2 col-sm-12">
        </div>
        <div class="col-md-5 col-sm-12 content-box-right">
			<div class="row">
				<h4 class="profile">' . TEXT_DASHBOARD_HEADLINE_MARKETPLACE_PROFILE.' ' . TEXT_GLOBAL_OF.' ' . $user_profile['user_nickname'].':</h4>
				<div class="col-md-12 col-sm-12">
					'.build_shoutbox_feed($user_profile['user_id'],'marketplace_profil').'
				</div>
			
			</div>
		</div>
		';
		
		if($_SESSION['user']['user_id']!=$user_profile['user_id']) {
		$output .= '
		<div class="col-md-12 col-sm-12 content-box-right"><br>
			<div class="row" style="text-align:right">
			<div class="col-md-8 col-sm-12">
			</div>
			<div class="col-md-2 col-xs-6">
			';
			
		if(blockedlist_exists($_SESSION['user']['user_id'],$user_profile['user_id']) === true) {
		$output .= '<a href="#SITE_URL#me/messages/blocked/" class="btn btn-sm btn-danger"><i class="fa fa-ban"></i>
  ' . TEXT_BLOCK_USER_ACTIVE.'</a>
		';
			}
			else {
		$output .= '<form method="post" action="#SITE_URL#me/messages/blocked/">
		  <button  class="btn btn-sm btn-danger" type="submit"><i class="fa fa-ban"></i>
 ' . TEXT_BLOCK_USER.'</button>
		  <input type="hidden" name="blocked_user_id_#SPAM_KEY#" value="' . md5($user_profile['user_id'].$user_profile['user_nickname']).'">
		  </form>
		 ';
			}	
	
    $alertSubject = '';
    $alertDetails = '';
    if(isset($_POST['alert_subject'])) {
       $alertSubject = $_POST['alert_subject']; 
    } 
            
    if(isset($_POST['alert_details'])) {
       $alertDetails = $_POST['alert_details']; 
    } 
			
	$output .= '</div>
				<div class="col-md-2 col-xs-6">
					<a class="btn  btn-sm btn-danger"  data-toggle="modal" data-target="#report_modal"><i class="fa fa-exclamation-triangle"></i> ' . TEXT_PROFILE_ALERT.'</a>
				</div>
		</div>
	</div>


             <div class="modal fade" id="report_modal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                         <header class="list-group-item start_register_header"><strong>' . TEXT_PROFILE_ALERT.'</strong>: </header>

                    </div> 

                    <div class="modal-body">
                    ' . TEXT_PROFILE_ALERT_FORM_INTRO.'<br>
        <br>

                         <form method="post" action="#SITE_URL#profile/' . md5($user_profile['user_id'].$user_profile['user_nickname']).'">
                         <div class="row">
                             <div class="col-md-4"><label>' . TEXT_PROFILE_TO_REPORT.'</label></div>
                             <div class="col-md-8"><strong>' . $user_profile['user_nickname'].'</strong></div>
                         </div>
        <br>

                          <div class="row">
                             <div class="col-md-4"><label>' . TEXT_PROFILE_ALERT_SUBJECT.'</label></div>
                             <div class="col-md-8"><input type="text" class="form-control" value="' . $alertSubject . '" name="alert_subject" required>
                             </div>
                         </div>
        <br>

                          <div class="row">
                            <div class="col-md-12">
        <label>' . TEXT_PROFILE_ALERT_DETAIL.'</label><br>
                            <textarea class="form-control" name="alert_details" required>' . $alertDetails . '</textarea>
                            <input type="hidden" name="alertprofile" value="1">
                            </div>
                         </div>
                  <div class="row">
                        <br>
                        <br>
                        <button  class="btn btn-danger" type="submit"><i class="fa fa-exclamation-triangle"></i> ' . TEXT_PROFILE_ALERT.'
        </button></div></form>

                    </div>

                </div>
           </div>
        </div>';
		
	}
		
		$output .= '</div>';

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

if($user_profile['user_type']>2) {
    
    $geo_place_id= geo_locate_by_input($user_profile['user_address']);
    
    $geo_data = get_place_id_data($geo_place_id);
    //var_dump($geo_place_id);
    $geo_lat = $geo_data['city_lat'];
    $geo_lng = $geo_data['city_lng'];
    
$footer_ext.='

			   <script>
				/*---------------------------------------*/
				/*	GOOGLE MAP
				/*---------------------------------------*/
				jQuery(document).ready(function($) {

    "use strict";

    //google map custom marker icon - .png fallback for IE11
    var is_internetExplorer11 = navigator.userAgent.toLowerCase().indexOf(\'trident\') > -1;
    //var $marker_url = (is_internetExplorer11) ? \'#SITE_URL#images/default/marker.png\' : \'#SITE_URL#images/default/marker.svg\';
	 var $marker_url=[\'#SITE_URL#images/my_position.png\',\'#SITE_URL#images/default/marker_small.png\',\'#SITE_URL#images/default/marker.png\'];

    //we define here the style of the map
    var style = [{
        "stylers": [{
            "hue": "#00aaff"
        }, {
            "saturation": -100
        }, {
            "gamma": 2.15
        }, {
            "lightness": 12
        }]
    }];

    //set google map options
    var map_options = {
        center: new google.maps.LatLng($latitude, $longitude),
        zoom: $map_zoom,
        panControl: true,
        zoomControl: true,
        mapTypeControl: false,
        streetViewControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scrollwheel: false,
        styles: style,
    }
    //inizialize the map
    var map = new google.maps.Map(document.getElementById(\'google-container\'), map_options);
    //add a custom marker to the map	
	
	
	var marker, i, icon2;
	var infowindow = new google.maps.InfoWindow();
				
				for (i = 0; i < locations.length; i++) { 
				if(i == 0) {
				icon2 = new google.maps.MarkerImage($marker_url[locations[i][3]], null, null, new google.maps.Point(50, 50));
				}
				else
				{
				icon2 = $marker_url[locations[i][3]];
				}
				marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map,
        visible: true,
		zIndex:i,
        icon: icon2
    });
	
	/*google.maps.event.addListener(marker, \'click\', (function(marker, i) {
        return function() {
          infowindow.setContent(locations[i][0]);
          infowindow.open(map, marker);
        }
      })(marker, i));*/
    }

  
});

var locations = [[\'<div class="map_box"><strong>'.addslashes($user_profile['user_nickname']).'</strong><br/><a href="#">weitere Informationen</a></div>\',' . $geo_lat.',' . $geo_lng.',2]];
    var $latitude = ' . $geo_lat.', 
        $longitude = ' . $geo_lng.',
        $map_zoom = 13;
        </script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyBmiMufjGEPl3hRAXuGk6TQD2QLJPm7AEU"></script>';
		 }

$content_output = array('TITLE' => SITE_NAME, 'CONTENT' => $output, 'HEADER_EXT' => $header_ext, 'FOOTER_EXT' => $footer_ext);