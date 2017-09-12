<?php 
is_user();

header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
die();
// DELETE SHOUTBOX POST 

if(isset($_POST['shoutbox_key_'.$_SESSION['user']['secure_spam_key']]) && strlen($_POST['shoutbox_key_'.$_SESSION['user']['secure_spam_key']])==32) {
	
    if($_POST['delete_shoutbox_key_'.$_SESSION['user']['secure_spam_key']]==1) {
		$sql = "DELETE FROM `user_shoutbox` WHERE MD5(CONCAT(shoutbox_message_id, shoutbox_date, user_id))='" . $_POST['shoutbox_key_'.$_SESSION['user']['secure_spam_key']] . "' AND user_id = '" . $_SESSION['user']['user_id'] . "' ";
        
        $query = $DB->prepare($sql);
        $query->execute();

		if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SHOUT_BOX_SUCCESS_REMOVED);		
		
            build_history_log($_SESSION['user']['user_id'],"removed_shoutbox",'');

            header("Location: ".SITE_URL."dashboard/");
            die();
		
		}
		else {
		$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SHOUT_BOX_SUCCESS_REMOVED_ERROR);
		header("Location: ".SITE_URL."dashboard/");
		die();
		
		}
		
	}
    
        if($_POST['reactivate_shoutbox_key_' . $_SESSION['user']['secure_spam_key']]==1) {
		$sql = "UPDATE `user_shoutbox` SET  shoutbox_status = 0, shoutbox_durration = '".date("Y-m-d H:i:s",strtotime("+7 days"))."' WHERE MD5(CONCAT(shoutbox_message_id, shoutbox_date, user_id))='".$_POST['shoutbox_key_' . $_SESSION['user']['secure_spam_key']]."' AND user_id = '".$_SESSION['user']['user_id']."' ";
		
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SHOUT_BOX_SUCCESS_REACTIVATED);		
            build_history_log($_SESSION['user']['user_id'],"removed_shoutbox",'');
            header("Location: ".SITE_URL."dashboard/");
            die();
		
		}
		else {
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SHOUT_BOX_SUCCESS_REACTIVATED_ERROR);
            header("Location: ".SITE_URL."dashboard/");
            die();
		}
	}
}

if(isset($_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']])) {
	if(strlen($_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']]) > 255) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_SHOUT_BOX_ERROR_TEXT_LENGTH);
		$shoutbox_renew_message = $_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']];
	}
	else 
	if(strlen($_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']]) === 0) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_SHOUT_BOX_ERROR_TEXT_LENGTH_SHORT);
		$shoutbox_renew_message = $_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']];
	}
    else if(strlen($_POST['add_shoutbox_title_' . $_SESSION['user']['secure_spam_key']]) > 64) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_SHOUT_BOX_ERROR_TITLE_LENGTH);
		$shoutbox_renew_message = $_POST['add_shoutbox_title_' . $_SESSION['user']['secure_spam_key']];
	}
    else 
	if(strlen($_POST['add_shoutbox_title_' . $_SESSION['user']['secure_spam_key']]) === 0) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_SHOUT_BOX_ERROR_TITLE_LENGTH_SHORT);
		$shoutbox_renew_message = $_POST['add_shoutbox_title_' . $_SESSION['user']['secure_spam_key']];
	}
	else {
		if($_POST['add_shoutbox_duration_'.$_SESSION['user']['secure_spam_key']] > 0 && $_POST['add_shoutbox_duration_'.$_SESSION['user']['secure_spam_key']] <= 30) {
			$duration = $_POST['add_shoutbox_duration_'.$_SESSION['user']['secure_spam_key']];
		}
		else {
			$duration=30;
		}

        $sql = "INSERT INTO `user_shoutbox` (user_id, `shoutbox_date`, `shoutbox_durration`, `shoutbox_text`,`shoutbox_title`,`shoutbox_type`, shoutbox_sport_group_id) VALUES ('".$_SESSION['user']['user_id']."', NOW(), '".date("Y-m-d H:i:s", strtotime("+ ".$duration." days"))."', '".$_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']]."', '".$_POST['add_shoutbox_title_' . $_SESSION['user']['secure_spam_key']]."', '".$_POST['add_shoutbox_type_' . $_SESSION['user']['secure_spam_key']][0]."', '".$_POST['shoutbox_group']."');";
		
        $query = $DB->prepare($sql);
        $query->execute();
		
		$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SHOUT_BOX_SUCCESS_ADD);
		header("Location: ".SITE_URL."dashboard/");
        die();
	}
}


$subnav='<div class="list-group sub_nav">
   <a href="#SITE_URL#me/profile" class="list-group-item"><i class="fa fa-home"></i> ' . TEXT_PROFILE_HEADER.'
</a>
   <a href="#SITE_URL#me/settings" class="list-group-item"><i class="fa fa-cog"></i> ' . TEXT_SETTINGS_HEADER.'</a>
</div>

<div class="list-group sub_nav">
  <a href="#SITE_URL#me/messages" class="list-group-item"><i class="fa fa-envelope"></i> ' . TEXT_MESSAGES_MESSAGES.'</a>
  <a href="#SITE_URL#me/friends"  class="list-group-item"><i class="fa fa-user"></i> ' . TEXT_PROFILE_FRIENDS_FRIENDS.'</a>
  <a href="#SITE_URL#shoutbox/"  class="list-group-item"><i class="fa fa-bullhorn"></i> ' . TEXT_SHOUTBOX_HEADER.'

</a>
  <!--<a href="#SITE_URL#me/search" class="list-group-item">gespeicherte Suchen</a>-->
  </div>
   ';


$sidebar='

<div class="col-md-3 col-sm-12">
<div class="side_bar">
<ul class="list-group">
  <li class="list-group-item start_register_header">' . TEXT_GLOBAL_DASHBOARD.'
</li>
  </ul>
'.$subnav.'
</div>
 '.build_banner('profile','xs','',0,'').'
'.build_banner('profile','sm','',0,'').'
'.build_banner('profile','md','',0,'').'
'.build_banner('profile','lg','',0,'').'

	</div>';
	
	$sub_sidebar='

<div class="col-md-3 col-sm-12">
<div class="sub_side_bar">
'.$subnav.'
</div>

	</div>';

$result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");

$output = '
	
	<div class="col-md-9 col-sm-12">
    <div class="col-md-6 content-box-right"><h4 class="profile">' . TEXT_DASHBOARD_HEADLINE_SHOUTBOX.':</h4>
    '.build_shoutbox_feed($_SESSION['user']['user_id'],'dashboard').'<br>


    <a href="#SITE_URL#shoutbox/" class="btn btn-default">' . TEXT_GLOBAL_SHOW_ALL.'</a>
    <br>
    </div>
    <div class="col-md-1 col-sm-12">
    </div>
     <div class="col-md-5 content-box-right"><strong>' . TEXT_SHOUTBOX_HEADER.':</strong><br>
    <form method="post" action="#SITE_URL#dashboard/">
    <label><input name="add_shoutbox_type_' . $_SESSION['user']['secure_spam_key'].'[]" value="standard" type="radio" checked>'.TEXT_SHOUTBOX_TYPE_GLOBAL.'</label>
<label><input name="add_shoutbox_type_' . $_SESSION['user']['secure_spam_key'].'[]" value="date" type="radio">'.TEXT_SHOUTBOX_TYPE_DATE.'</label>

<input type="text" name="add_shoutbox_title_' . $_SESSION['user']['secure_spam_key'].'" placeholder="'.TEXT_SHOUTBOX_TITLE_PLACEHOLDER.'" class="form-control"> <br>
    <textarea class="form-control" name="add_shoutbox_text_'.$_SESSION['user']['secure_spam_key'] . '" rows="4" maxlength="255" placeholder="' . TEXT_SHOUTBOX_PLACEHOLDER.'"></textarea><br>


    <div class="col-md-5">
    <label for="add_shoutbox_duration_'.$_SESSION['user']['secure_spam_key'] . '">' . TEXT_SHOUTBOX_DURATION.'</label>
    <select name="add_shoutbox_duration_'.$_SESSION['user']['secure_spam_key'] . '"  class="form-control">
    <option value="1">1 ' . TEXT_GLOBAL_DAY.'</option>
    <option value="2">2 ' . TEXT_GLOBAL_DAYS.'</option>
    <option value="7">7 ' . TEXT_GLOBAL_DAYS.'</option>
    <option value="14">14 ' . TEXT_GLOBAL_DAYS.'</option>
    <option value="30">30 ' . TEXT_GLOBAL_DAYS.'</option>
    </select>
    </div>
    <div class="col-md-7">
    <label for="shoutbox_group">' . TEXT_SHOUTBOX_SPORT.'</label>
    ' . build_search_sports_select($selected = $_POST['shoutbox_group'], $search_lines = '', $first_level = true) . '
    </div>
    <div class="col-md-12"><input type="submit" value="' . TEXT_SHOUTBOX_WRITE.'" class="btn btn-sm btn-primary form-control"></div>

    </form>
    </div>

    <div class="row">
    <div class="col-md-12 content-box-right"><h4 class="profile">' . TEXT_DASHBOARD_HEADER_ATHLETES.':</h4>
    ';

$sql = "SELECT *,
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") AS DISTANCE

     FROM 

     user u, 
     user_details ud 

     WHERE 

    u.user_id=ud.user_id AND 
    u.user_id!='" . $_SESSION['user']['user_id'] . "' AND
    u.user_status=1 AND
    u.user_type=1 AND
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") < 50 AND
    ud.user_country='" . $_SESSION['user']['user_country'] . "' ORDER BY u.user_id DESC, DISTANCE ASC LIMIT 4
     ";

$query = $DB->prepare($sql);
$query->execute();
$get_search = $query->fetchAll();
$result="";
foreach ($get_search as $search){
	$search_item=$result_item; // TEMPLATE SETZEN
	$search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
		$search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
	$search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
	$search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);
	$search_item = str_replace("#SEARCH_COUNTRY#",strtoupper($search['user_country'])." - ",$search_item);
	//$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
	$search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
	$search_item = str_replace("#SPORTS#",get_user_main_sport($search['user_id']),$search_item);
	//$search_item = str_replace("#DISTANCE#","(".round($search['DISTANCE'],2)."km)",$search_item);
    $search_item = str_replace("#DISTANCE#","",$search_item);
    
    $result.=$search_item;
}

if($result == '') {
    $result=TEXT_DASHBOARD_ERROR_NO_RESULTS;
}

$output .= $result . '



		<a href="#SITE_URL#search/?search_action_'.$_SESSION['user']['secure_spam_key'] . '=search.do&search_type_'.$_SESSION['user']['secure_spam_key'] . '[]=1&search_country_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_country'] . '&search_zipcode_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_zipcode'] . '&search_radius_'.$_SESSION['user']['secure_spam_key'] . '='.DEFAULT_DISTANCE_SEARCH.'&search_city_id_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_city_id'] . '" class="btn btn-default">' . TEXT_GLOBAL_SHOW_ALL.'</a><br><br><br><br>
        <div style="clear:both"></div>
</div></div>

<div class="row  ">
	<div class="col-md-12 content-box-right">
		<h4 class="profile">' . TEXT_DASHBOARD_HEADER_TRAINER.':</h4>
	';

$sql = "SELECT *,
(acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") AS DISTANCE
 
 FROM 
 
 user u, 
 user_details ud 
 
 WHERE 
 
u.user_id=ud.user_id AND 
u.user_id!='" . $_SESSION['user']['user_id'] . "' AND
u.user_status=1 AND
u.user_type=2 AND
(acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") < 50 AND
ud.user_country='" . $_SESSION['user']['user_country'] . "' ORDER BY u.user_id DESC, DISTANCE ASC LIMIT 4
 ";
 


$query = $DB->prepare($sql);
$query->execute();
$get_search = $query->fetchAll();
$result="";
foreach ($get_search as $search){
	$search_item=$result_item; // TEMPLATE SETZEN
	$search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
		$search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
	$search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
	$search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);
	$search_item = str_replace("#SEARCH_COUNTRY#",strtoupper($search['user_country'])." - ",$search_item);
	//$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
	$search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
	$search_item = str_replace("#SPORTS#",get_user_main_sport($search['user_id']),$search_item);
	//$search_item = str_replace("#DISTANCE#","(".round($search['DISTANCE'],2)."km)",$search_item);
$search_item = str_replace("#DISTANCE#","",$search_item);
	
	
	$result.=$search_item;
	
}

if($result=='') {
    $result=TEXT_DASHBOARD_ERROR_NO_RESULTS_TRAINER;
}


$output.=$result.'

            <a href="#SITE_URL#search/?search_action_'.$_SESSION['user']['secure_spam_key'] . '=search.do&search_type_'.$_SESSION['user']['secure_spam_key'] . '[]=2&search_country_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_country'] . '&search_zipcode_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_zipcode'] . '&search_radius_'.$_SESSION['user']['secure_spam_key'] . '='.DEFAULT_DISTANCE_SEARCH.'&search_city_id_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_city_id'] . '" class="btn btn-default">' . TEXT_GLOBAL_SHOW_ALL.'</a><br><br><br><br><div style="clear:both"></div>


        </div>
    </div>
    <div class="row">

	<div class="col-md-12 content-box-right">
		<h4 class="profile">' . TEXT_DASHBOARD_HEADER_CLUBS.':</h4>	
	
		';

$sql = "SELECT *,
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") AS DISTANCE

     FROM 

     user u, 
     user_details ud 

     WHERE 

    u.user_id=ud.user_id AND 
    u.user_id!='" . $_SESSION['user']['user_id'] . "' AND
    u.user_status=1 AND
    u.user_type=3 AND
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") < 50 AND
    ud.user_country='" . $_SESSION['user']['user_country'] . "' ORDER BY u.user_id DESC, DISTANCE ASC LIMIT 4
     ";
 


$query = $DB->prepare($sql);
$query->execute();
$get_search = $query->fetchAll();
$result="";
foreach ($get_search as $search){
    
	$search_item=$result_item; // TEMPLATE SETZEN
	$search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
		$search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
	$search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
	$search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);
	$search_item = str_replace("#SEARCH_COUNTRY#",strtoupper($search['user_country'])." - ",$search_item);
	//$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
	$search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
	$search_item = str_replace("#SPORTS#",get_user_main_sport($search['user_id']),$search_item);
	//$search_item = str_replace("#DISTANCE#","(".round($search['DISTANCE'],2)."km)",$search_item);
$search_item = str_replace("#DISTANCE#","",$search_item);
	
	
	$result.=$search_item;
	
}

if($result=="") { 
    $result=TEXT_DASHBOARD_ERROR_NO_RESULTS_CLUB;
}

$output.=$result.'<br>

	<a href="#SITE_URL#search/?search_action_'.$_SESSION['user']['secure_spam_key'] . '=search.do&search_type_'.$_SESSION['user']['secure_spam_key'] . '[]=3&search_country_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_country'] . '&search_zipcode_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_zipcode'] . '&search_radius_'.$_SESSION['user']['secure_spam_key'] . '='.DEFAULT_DISTANCE_SEARCH.'&search_city_id_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_city_id'] . '" class="btn btn-default">' . TEXT_GLOBAL_SHOW_ALL.'</a><br><br><br><br><div style="clear:both"></div>


	</div>
</div>
<div class="row">
	<div class="col-md-12 content-box-right">
		<h4 class="profile">' . TEXT_DASHBOARD_HEADER_FACILITIES.':</h4>';

$sql = "SELECT *,
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") AS DISTANCE

     FROM 

     user u, 
     user_details ud 

     WHERE 

    u.user_id=ud.user_id AND 
    u.user_id!='" . $_SESSION['user']['user_id'] . "' AND
    u.user_status=1 AND
    u.user_type=4 AND
    (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION['user']['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION['user']['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION['user']['user_lng'] . ")))*".RADIUS.") < 50 AND
    ud.user_country='" . $_SESSION['user']['user_country'] . "' ORDER BY u.user_id DESC, DISTANCE ASC LIMIT 4
     ";
 


$query = $DB->prepare($sql);
$query->execute();
$get_search = $query->fetchAll();
$result = '';
foreach ($get_search as $search){
	$search_item=$result_item; // TEMPLATE SETZEN
	$search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
		$search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
	$search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
	$search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);
	$search_item = str_replace("#SEARCH_COUNTRY#",strtoupper($search['user_country'])." - ",$search_item);
	$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
	$search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
	$search_item = str_replace("#SPORTS#",get_user_main_sport($search['user_id']),$search_item);
	//$search_item = str_replace("#DISTANCE#","(".round($search['DISTANCE'],2)."km)",$search_item);
    $search_item = str_replace("#DISTANCE#","",$search_item);
	
	$result.=$search_item;
	
}
if($result == '') {
    $result=TEXT_DASHBOARD_ERROR_NO_RESULTS_LOCATION;
}

$output.=$result.'<br>

		<a href="#SITE_URL#search/?search_action_'.$_SESSION['user']['secure_spam_key'] . '=search.do&search_type_'.$_SESSION['user']['secure_spam_key'] . '[]=4&search_country_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_country'] . '&search_zipcode_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_zipcode'] . '&search_radius_'.$_SESSION['user']['secure_spam_key'] . '='.DEFAULT_DISTANCE_SEARCH.'&search_city_id_'.$_SESSION['user']['secure_spam_key'] . '='.$_SESSION['user']['user_city_id'] . '" class="btn btn-default">' . TEXT_GLOBAL_SHOW_ALL.'</a><br><br><br><br><div style="clear:both"></div>
</div>
</div>';

$content_output = array('TITLE' => 'Dashboard',
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');