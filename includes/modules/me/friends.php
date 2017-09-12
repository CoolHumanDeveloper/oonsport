<?php

is_user();

$subnav=' <div class="list-group sub_nav">
  <a href="#SITE_URL#me/friends/friends/" class="list-group-item"><i class="fa fa-user"></i> '.TEXT_PROFILE_FRIENDS_FRIENDS.' 
</a>
  <a href="#SITE_URL#me/friends/groups/" class="list-group-item"><i class="fa fa-users"></i> '.TEXT_PROFILE_FRIENDS_GROUPS.' 
</a>
  <a href="#SITE_URL#me/friends/request/" class="list-group-item"><i class="fa fa-user-plus"></i> '.TEXT_PROFILE_FRIENDS_REQUESTS.' 
</a>
<a href="#SITE_URL#me/friends/watch/" class="list-group-item"><i class="fa fa-eye"></i> '.TEXT_PROFILE_FRIENDS_WATCHLIST.' 
</a>
  </div>';


$sidebar='<div class="col-md-3 col-sm-12">
<div class="side_bar">


<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> '.TEXT_PROFILE_FRIENDS_FRIENDS.'
</li>
  </ul>
 '.$subnav.'
	</div>
	
	 '.build_banner('profile','xs','',0,'').'
'.build_banner('profile','sm','',0,'').'
'.build_banner('profile','md','',0,'').'
'.build_banner('profile','lg','',0,'').'

	</div>
';

$sub_sidebar='<div class="col-md-3 col-sm-12">
<div class="sub_side_bar">
 '.$subnav.'
	</div>
	</div>
';


if(isset($_GET['sub_content_value'])) {
$sidebar = str_replace('friends/'.$_GET['sub_content_value'].'/" class="', 'friends/'.$_GET['sub_content_value'].'/" class="active ',$sidebar);

$sub_sidebar = str_replace('friends/'.$_GET['sub_content_value'].'/" class="', 'friends/'.$_GET['sub_content_value'].'/" class="active ',$sub_sidebar);
}
else {
	$_GET['sub_content_value']="default";
}

switch($_GET['sub_content_value']){
		
      case ("friends"):
      require(MODULE_PATH . "me/friends/friends.php");
      break;
      
	  case ("groups"):
      require(MODULE_PATH . "me/friends/groups.php");
      break;
	  
	  case ("request"):
      require(MODULE_PATH . "me/friends/request.php");
      break;
	  
	  case ("watch"):
      require(MODULE_PATH . "me/friends/watch.php");
      break;
	  
 
	  default:
      header("location: ".SITE_URL."me/friends/friends/");
      break;
}
?>
