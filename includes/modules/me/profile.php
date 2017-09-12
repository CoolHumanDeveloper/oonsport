<?php 
is_user();

$sidebar='<div class="col-md-3 col-sm-12">
<div class="side_bar">


<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> '.TEXT_PROFILE_EDIT_PROFILE.'
</li>
  </ul>
 <div class="list-group">
  <a href="#SITE_URL#profile/'.md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']).'"  class="list-group-item"><i class="fa fa-home"></i>
 '.TEXT_PROFILE_HEADER.'</a>
  <a href="#SITE_URL#me/profile/default/" class="list-group-item"><i class="fa fa-pencil-square-o"></i>
 '.TEXT_PROFILE_EDIT_PROFILE.'</a>
  
  <a href="#SITE_URL#me/profile/sports/" class="list-group-item"><i class="fa fa-dribbble"></i>
 
 '.TEXT_PROFILE_SPORT_HEADER_MY_SPORTS.'</a>
  <a href="#SITE_URL#me/profile/galerie/" class="list-group-item"><i class="fa fa-picture-o"></i>
 '.TEXT_PROFILE_SPORT_HEADER_IMAGES_VIDEOS.'</a>
  <a href="#SITE_URL#me/profile/chronik/" class="list-group-item"><i class="fa fa-file-text-o"></i>
 '.TEXT_PROFILE_CHRONIK_HEADER.'</a>
</div>

	</div>
	 '.build_banner('profile','xs','',0,'').'
'.build_banner('profile','sm','',0,'').'
'.build_banner('profile','md','',0,'').'
'.build_banner('profile','lg','',0,'').'

	</div>
';

$sidebar = str_replace('profile/'.$_GET['sub_content_value'].'/" class="', 'profile/'.$_GET['sub_content_value'].'/" class="active ',$sidebar);

switch($_GET['sub_content_value']){
		
      case ("default"):
      require(MODULE_PATH . "me/profile/profile.php");
      break;
      
      case ("sports"):
      require(MODULE_PATH . "me/profile/sports.php");
      break;
	  
	  case ("galerie"):
      require(MODULE_PATH . "me/profile/galerie.php");
      break;
        
        case ("galerie-edit"):
      require(MODULE_PATH . "me/profile/galerie-edit.php");
      break;
	  
	   case ("chronik"):
      require(MODULE_PATH . "me/profile/chronik.php");
      break;
	  
	  default:
      header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
      break;
}
?>