<?php 
is_user();
switch($_GET['content_value']){
		
      case ("messages"):
      require(MODULE_PATH . "me/messages.php");
      break;
      
      case ("profile"):
      require(MODULE_PATH . "me/profile.php");
      break;
	  
	  case ("friends"):
      require(MODULE_PATH . "me/friends.php");
      break;
	  
	   case ("search"):
      require(MODULE_PATH . "me/search.php");
      break;
	  
	   case ("settings"):
      require(MODULE_PATH . "me/settings.php");
      break;
	  
	  default:
        header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
        die();
      break;
}