<?php

is_user();

$sidebar='<div class="side_bar">

<div class="col-md-3 col-sm-12">
<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> '.TEXT_SETTINGS_HEADER.' 
</li>
  </ul>
 <div class="list-group">
  <a href="#SITE_URL#me/settings/profiles/" class="list-group-item"><i class="fa fa-cog"></i> '.TEXT_SETTINGS_PROFILES.'</a>
  <a href="#SITE_URL#me/settings/messages/" class="list-group-item"><i class="fa fa-cog"></i> '.TEXT_MESSAGES_SETTINGS.'</a>
  <a href="#SITE_URL#me/settings/blocked/" class="list-group-item"><i class="fa fa-cog"></i> '.TEXT_MESSAGES_BLOCKED.'</a>
  <a href="#SITE_URL#me/settings/shoutbox/" class="list-group-item"><i class="fa fa-cog"></i> '.TEXT_MESSAGES_SETTINGS_SHOUTBOX.'</a>
  <a href="#SITE_URL#me/settings/marketplace/" class="list-group-item"><i class="fa fa-cog"></i> '.TEXT_MESSAGES_SETTINGS_MARKETPLACE.'</a>
  <a href="#SITE_URL#me/settings/newsletter/" class="list-group-item"><i class="fa fa-cog"></i> '.TEXT_MESSAGES_SETTINGS_NEWSLETTER.'</a>
  
  <a href="#SITE_URL#me/settings/quit/" class="list-group-item"><i class="fa fa-power-off"></i>

 '.TEXT_SETTINGS_DELETE_ACCOUNT.'</a>
 
  </div>
   '.build_banner('profile','xs','',0,'').'
'.build_banner('profile','sm','',0,'').'
'.build_banner('profile','md','',0,'').'
'.build_banner('profile','lg','',0,'').'

	</div>
';

$sidebar = str_replace('settings/'.$_GET['sub_content_value'].'/" class="', 'settings/'.$_GET['sub_content_value'].'/" class="active ',$sidebar);


switch($_GET['sub_content_value']){
		
      case ("messages"):
      require(MODULE_PATH . "me/settings/messages.php");
      break;
	  
	  case ("newsletter"):
      require(MODULE_PATH . "me/settings/newsletter.php");
      break;
	  
	  case ("shoutbox"):
      require(MODULE_PATH . "me/settings/shoutbox.php");
      break;
        
      case ("marketplace"):
      require(MODULE_PATH . "me/settings/marketplace.php");
      break;
	  
	  case ("blocked"):
      require(MODULE_PATH . "me/settings/blocked.php");
      break;
        
      case ("profiles"):
      require(MODULE_PATH . "me/settings/profiles.php");
      break;
	  
	  case ("quit"):
      require(MODULE_PATH . "me/settings/quit.php");
      break;

	  default:
      header("location: ".SITE_URL."me/settings/messages/");
      break;
}
?>
