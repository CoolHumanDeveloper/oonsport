<?php


$page = $_GET['page'] ? $_GET['page']-1 : 0;

if($_GET['page'] > 0) {
	$search_url_page_string="&page=".$_GET['page'];
}

$search_lat = $_SESSION['user']['user_lat'];
$search_lng = $_SESSION['user']['user_lng'];


if(isset($_POST['switchprofile_'.$_SESSION['user']['secure_spam_key']]) && isset($_POST['switchprofile_user_id_'.$_SESSION['user']['secure_spam_key']])) {
	if(isValidUserChild($_POST['switchprofile_user_id_'.$_SESSION['user']['secure_spam_key']]) == true ||
		isValidUserParent($_POST['switchprofile_user_id_'.$_SESSION['user']['secure_spam_key']]) == true) {
		$sql = "SELECT u.*, ud.* FROM 
					user u
					 LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
					 WHERE 
					 MD5(CONCAT(u.user_id,ud.user_nickname)) = '".$_POST['switchprofile_user_id_'.$_SESSION['user']['secure_spam_key']]."' LIMIT 1";
        $query = $DB->prepare($sql);
        $query->execute();
        $user_profile = $query->fetch();
					
        $sql = "DELETE FROM user_sessions WHERE session_id='".$_COOKIE['oon-sid']."' OR session_id='".session_id()."' ";
        $query = $DB->prepare($sql);
        $query->execute();

        setcookie("oon-site", "", time()-3600, '/');
        setcookie("oon-sid", "", time()-3600, '/');
        setcookie("oon-userid", "", time()-3600, '/');

        unset($_SESSION);
        unset($_COOKIE);

        session_destroy();
        session_start();

        $_SESSION['user']=$user_profile;
        $_SESSION['user']['secure_spam_key']=rand(123,999);
        $_SESSION['user']['user_uptime']=time();
        build_user_online($_SESSION['user']['user_id']);
        $_SESSION['logged_in']=1;

        header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
        die();			
	}
}

if(isset($_POST['deleteprofile_'.$_SESSION['user']['secure_spam_key']]) && isset($_POST['profile_user_id_'.$_SESSION['user']['secure_spam_key']])) {
	if(isValidUserChild($_POST['profile_user_id_'.$_SESSION['user']['secure_spam_key']]) == true) {
		if($_POST['deleteprofile_comfirm']==1){
			
			$sql = "SELECT u.*, ud.* FROM 
					user u
					 LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
					 WHERE 
					 MD5(CONCAT(u.user_id,ud.user_nickname)) = '".$_POST['profile_user_id_'.$_SESSION['user']['secure_spam_key']]."' LIMIT 1";
			$query = $DB->prepare($sql);
            $query->execute();
            $user_profile = $query->fetch();
            
			deleteUserFinaly($user_profile['user_id']);
			$_SESSION['system_message'] .= set_system_message("error",  TEXT_PROFILE_DELETE_SUCCESS);
		}
		else{	
			$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_DELETE_CONFIRM.'<br>
			<div class="row"><div class="col-md-6 col-sm-6"><form method="post" action="#SITE_URL#me/settings/profiles/">
			<input type="hidden" name="delete_account" value="1">
			<button type="submit" class="btn btn-sm btn-danger">'.TEXT_ACCOUNT_DELETE_NO.'</button>
			</form></div><div class="col-md-6 col-sm-6">
			<form method="post" action="#SITE_URL#me/settings/profiles/">
			<input type="hidden" name="profile_user_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.$_POST['profile_user_id_'.$_SESSION['user']['secure_spam_key']].'">
			<input type="hidden" name="deleteprofile_'.$_SESSION['user']['secure_spam_key'].'" value="1">
			<input type="hidden" name="deleteprofile_comfirm" value="1">
			<button type="submit" class="btn btn-sm btn-primary">'.TEXT_PROFILE_DELETE_YES.'</button>
			</form></div></div>
		');
		}
	}
	else{
		$_SESSION['system_message'] .= set_system_message("error", "Error - Invalid Action - ");
	}
	
}


if(isset($_POST['add_profile']) && $_POST['add_profile']==2) {
	$profile_nickname = $_POST['profile_nickname'];
	if(check_user_nickname($profile_nickname) == true) {
		$nick_error=TEXT_REGISTER_ERROR_YOUR_NICKNAME_EXISTS;
		if($_POST['user_type']==3) $nick_error=TEXT_REGISTER_ERROR_YOUR_CLUBNAME_EXISTS;
		if($_POST['user_type']==4) $nick_error=TEXT_REGISTER_ERROR_YOUR_LOCATIONNAME_EXISTS;	
			
		$_SESSION['system_message'] .= set_system_message("error",$nick_error);
		$form_error=1;
	}
	if(strlen($profile_nickname) < 3) {
		$nick_short_error=TEXT_REGISTER_ERROR_YOUR_NICKNAME_SHORT;
		if($_POST['user_type']==3) $nick_short_error=TEXT_REGISTER_ERROR_YOUR_CLUBNAME_SHORT;
		if($_POST['user_type']==4) $nick_short_error=TEXT_REGISTER_ERROR_YOUR_LOCATIONNAME_SHORT;	
			
		$_SESSION['system_message'] .= set_system_message("error",$nick_short_error);
		$form_error=1;
	}
	
	$profile_gender = $_POST['profile_gender'];
	if($profile_gender==""  && ($_POST['user_type'] == 2 || $_POST['user_type'] ==1)) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_GENDER);
		$form_error=1;
	}
	
	$profile_dob = $_POST['profile_dob'];
	if($profile_dob=="") {
		$_SESSION['system_message'] .= set_system_message("error",constant('TEXT_PROFILE_PROFILE_ERROR_DOB_'.$_POST['user_type']));
		$form_error=1;
	}
	
	$profile_country = $_POST['profile_country'];
	if($profile_country=="") {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_COUNTRY);
		$form_error=1;
	}
	
	$profile_zipcode = $_POST['profile_zipcode'];
	if($profile_zipcode=="") {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_ZIPCODE);
		$form_error=1;
	}
	
	$profile_city_id = $_POST['profile_city_id'];
	if($profile_city_id=="") {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_NO_CITY_FOUND);
		$form_error=1;
	}
	
	if($form_error == 0) {
		
		$profile_geo=get_city_latlng($profile_city_id,$profile_country);
		
		if($_SESSION['user']['user_sub_of'] > 0) {
			$new_sub_of = $_SESSION['user']['user_sub_of'];
		}
		else {
			$new_sub_of = $_SESSION['user']['user_id'];
		}
		
		$sql = "INSERT INTO `user` (`user_password`, `user_email`, `user_type`, `user_status`, `user_auth_key`, user_register_date, user_sub_of) VALUES ('".md5($_SESSION['user']['user_id'].time())."', '".md5($_SESSION['user']['user_id'].time())."', '".$_POST['user_type']."', 1, '', NOW(), '".$new_sub_of ."')";
        $query = $DB->prepare($sql);
        $query->execute();

		$user_id = $DB->lastInsertId();
		
		if($user_id > 0) {	
			$profile_geo=get_city_latlng($profile_city_id,$profile_country);
			
			$sql = "INSERT INTO `user_details` (`user_id`, `user_nickname`, `user_firstname`, `user_lastname`, `user_dob`, `user_gender`, `user_country`, `user_zipcode`, `user_city_id`,user_lat,user_lng) VALUES ('".$user_id."', '".$profile_nickname."', '".$_SESSION['user']['user_firstname']."', '".$_SESSION['user']['user_lastname']."', '".date("Y-m-d",strtotime($profile_dob))."', '".$profile_gender."', '".$profile_country."', '".$profile_zipcode."', '".$profile_city_id."', '".$profile_geo['lat']."', '".$profile_geo['lng']."');";
            $query = $DB->prepare($sql);
            $query->execute();
			
			$sql = "INSERT INTO `user_profile` (user_id) VALUES ('".$user_id."');";
            $query = $DB->prepare($sql);
            $query->execute();
			
			$sql = str_replace('#USERID#',$user_id, DEFAULT_SETTINGS_SQL);
            $query = $DB->prepare($sql);
            $query->execute();
			
			$sql = "	SELECT * 
					FROM 
						user_to_sport_group_value uts
					WHERE 
						uts.user_id ='".$_SESSION['user']['user_id']."'
					LIMIT 1";
            
            $query = $DB->prepare($sql);
            $query->execute();
            $user_sport_group = $query->fetch();
			
			$sql = "INSERT INTO 
                `user_to_sport_group_value` (`user_id`, `sport_group_id`,    `sport_group_value_id`, sport_group_profession, sport_group_handycap, sport_group_in_club) VALUES ('".$user_id."', '".$user_sport_group['sport_group_id']."', '".$user_sport_group['sport_group_value_id']."', '".$user_sport_group['sport_group_profession']."', '".$user_sport_group['sport_group_handycap']."', '".$user_sport_group['sport_group_in_club']."')";
			$query = $DB->prepare($sql);
            $query->execute();
			
			$_SESSION['system_temp_message'] .= set_system_message("success", TEXT_PROFILE_PROFILE_UPDATE_SUCCESS);
			header("Location: ".SITE_URL."me/settings/profiles/");
            die();
		}
	}
}

// CONTENT STARTS HERE
$output = '<div class="col-md-9 col-sm-12 content-box-right main_content">
	<h4 class="profile">'.TEXT_PROFILE_SETTING_PROFILES.' </h4><br>';

if(isset($_POST['add_profile'])) {
	$output .= '<form method="post" action="#SITE_URL#me/settings/profiles/" id="add_profile"  class="form">
        <div class="text-center">
		<div class="row form_line">
			<div class="col-md-6">
				<label>'.constant('TEXT_PROFILE_PROFILE_NICKNAME_'.$_POST['user_type']).':</label>
			</div>
			<div class="col-md-6">
				<input type="text" name="profile_nickname" value="'.$profile_nickname.'" class="form-control">
			</div>
		</div>';
		
    if($_POST['user_type'] == 2 || $_POST['user_type'] ==1) {

    $output .= '
    <div class="row form_line">
        <div class="col-md-6">
            <label>'.TEXT_PROFILE_PROFILE_GENDER.':</label>
        </div>
        <div class="col-md-6">
            <select name="profile_gender" class="form-control">
            <option value="">'.TEXT_GLOBAL_PLEASE_CHOOSE.'</option>
            <option value="f"';
            if($profile_gender=="f") {
                $output .= ' selected="selected"';
            }

            $output .= '>'.TEXT_GLOBAL_GENDER_FEMALE.'</option>
            <option value="m"';
            if($profile_gender=="m") {
                $output .= ' selected="selected"';
            }

            $output .= '>'.TEXT_GLOBAL_GENDER_MALE.'</option>
            </select>
        </div>
    </div>';

    }

    $output .= '
        <div class="row form_line">
                <div class="col-md-6">
                    <label>'.constant('TEXT_PROFILE_PROFILE_DOB_'.$_POST['user_type']).' ('.TEXT_REGISTER_DATE_FORMAT.'):</label>
                </div>
            <div class="col-md-6">
                <div class="input-group date">
                  <input type="text" name="profile_dob" value="'.date("d.m.Y",strtotime($profile_dob)).'" class="form-control" required><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
        </div>

        <div class="row form_line">

        <div class="col-md-6">
            <label>'.TEXT_GLOBAL_COUNTRY.':</label>
        </div>
        <div class="col-md-6">
            <select name="profile_country" id="profile_country" class="form-control" onChange="document.getElementById(\'profile_zipcode\').value=\'\',document.getElementById(\'profile_city_id\').value=\'\'">
            <option value="">'.TEXT_GLOBAL_PLEASE_CHOOSE.'</option>
            ';

$sql = "SELECT 
            c.*, 
            t.country_name AS translatedName
        FROM geo_countries c
            LEFT JOIN geo_countries_translation t ON c.country_code = t.country_code AND t.language_code = '" . $_SESSION['language_code'] . "'
        ORDER BY 
        `country_sort` DESC, 
        t.country_name  IS NULL ASC, 
        c.country_name ASC";

$query = $DB->prepare($sql);
$query->execute();
$get_countries = $query->fetchAll();

foreach ($get_countries as $countries){
    $select_hr="";
    if($sort!=$countries['country_sort']) { 
        $select_hr='class="select-hr"'; 
    }
    $output .= '<option value="' . $countries['country_code'] . '" ' . $select_hr . ' ';

    if($profile_country == $countries['country_code']) {
        $output .= 'selected="selected"';
    }

    if($countries['translatedName']) {
        $countries['country_name'] = $countries['translatedName'];
    }

    $output .= '>' . $countries['country_name'] . '</option>';
        $sort=$countries['country_sort'];
    }	
            $output .= '
            </select>
                </div>
            </div>

            <div class="row form_line">
                    <div class="col-md-6">
                        <label>'.TEXT_PROFILE_PROFILE_ZIPCODE.':</label>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="profile_zipcode" id="profile_zipcode" value="'.$profile_zipcode.'" required  class="form-control">
                        <br>
                        <br>
                        <input name="user_type" type="hidden" value="'.$_POST['user_type'].'"  />
                        <input id="profile_city_preview" type="hidden"  />
                        <input id="profile_city_id" name="profile_city_id" value="'.$profile_city_id.'" type="hidden"  />
                    </div>
                </div>
          </div>

          <div class="row form_line">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <input type="hidden" name="add_profile" value="2">
                        <input type="submit" value="'.TEXT_GLOBAL_ADD.'" class="btn btn-sm btn-primary form-control">
                    </div>
            </div>

            </form>';
}
else {
	if(isset($_GET['view'])) {
		$_SESSION['user']['default_view'] = $_GET['view'];
		switch ($_GET['view']) {
          case ("grid"):
              $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
              $result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");
              $_SESSION['user']['default_view']="grid";
          break;

          case ("list"):
              $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/list.html");
              $result_item=file_get_contents(SERVER_PATH . "template/modules/search/list-item.html");
              $_SESSION['user']['default_view']="list";
          break;

          default:
            $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
            $result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");
          break;
		}
	}
	else {
			if($_SESSION['user']['default_view'] == 'list') {
				$result_grid=file_get_contents(SERVER_PATH . "template/modules/search/list.html");
				$result_item=file_get_contents(SERVER_PATH . "template/modules/search/list-item.html");
			 }
			 else {
				$result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
				$result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");
			 }
	}
	
    $sql = "	
        SELECT 
            u.*, 
            ud.*,
            (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE
         FROM 
            user u
            LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
         WHERE 
            u.user_id='".$_SESSION['user']['user_id']."' OR
            u.user_sub_of='".$_SESSION['user']['user_id']."' OR
            (u.user_sub_of='".$_SESSION['user']['user_sub_of']."'AND u.user_sub_of IS NOT NULL) OR
            (u.user_id = '".$_SESSION['user']['user_sub_of']."' AND u.user_sub_of IS NULL)
        ";
	
    $query = $DB->prepare($sql);
    $query->execute();
    $total = $query->rowCount();
	
	$sql.="
	  ORDER BY u.user_id ASC LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE."
	 ";
	
	if($total > 0) {
	 $view_box='
	<ul class="pagination view_box"><li><a href="?view=grid'.$search_url_page_string.'" aria-label="Grid">
	<span class="glyphicon glyphicon-th" aria-hidden="true"></span></a></li><li><a href="?view=list'.$search_url_page_string.'" aria-label="Grid">
	<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span></a></li></ul>
	';
	$pagination=build_site_pagination("me/settings/profiles/",$total,$_GET['page'], VIEW_PER_PAGE,$search_url_terms_string,$view_box);
	$pagination_footer=build_site_pagination("me/settings/profiles/",$total,$_GET['page'], VIEW_PER_PAGE,$search_url_terms_string,"");
	
	
	$result="";
	$result.=$pagination.'
	<div class="row">';
	
    $query = $DB->prepare($sql);
    $query->execute();
    $get_search = $query->fetchAll();

	foreach ($get_search as $search) {
		
		$search_item=$result_item; // TEMPLATE SETZEN
		$search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
		$search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
		$search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
		$search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);
		$search_item = str_replace("#SEARCH_COUNTRY#",strtoupper($search['user_country'])." - ",$search_item);
		//$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
		$search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
		//$search_item = str_replace("#SPORTS#",get_user_main_sport($search['user_id']),$search_item);
		//$search_item = str_replace("#DISTANCE#","(".round($search['DISTANCE'],2)."km)",$search_item);
	   $search_item = str_replace("#DISTANCE#","",$search_item);
		
		$friendship_status = '';
		
		if($search['user_sub_of'] > 0 && $search['user_id'] != $_SESSION['user']['user_id']) {
		$friendship_status='<form method="post" action="#SITE_URL#me/settings/profiles/" >		
		<input name="profile_user_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.md5($search['user_id'].$search['user_nickname']).'" type="hidden">
	<button name="deleteprofile_'.$_SESSION['user']['secure_spam_key'].'" class="btn btn-xs btn-danger is-left" type="submit" value="1" ><i class="fa fa-trash"></i></button>
	</form> ';
		}
		else if($search['user_id'] == $_SESSION['user']['user_id']) {
			$friendship_status = ' <span class="btn btn-xs btn-primary is-right">'.TEXT_PROFILE_IN_USE.'</span>';
		}
		
		if($search['user_id'] != $_SESSION['user']['user_id']) {
			$friendship_status .= '<form method="post" action="#SITE_URL#me/settings/profiles/" >		
                <input name="switchprofile_user_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.md5($search['user_id'].$search['user_nickname']).'" type="hidden">
            <button name="switchprofile_'.$_SESSION['user']['secure_spam_key'].'" class="btn btn-xs btn-primary  is-right" type="submit" value="1" title="'.TEXT_PROFILE_SWITCH.'" ><i class="fa fa-exchange "></i> '.TEXT_GLOBAL_SWITCH.'</button>
            </form> ';
		}
		
		if($search['user_sub_of'] == 0) {
			$friendship_status .= ' <button class="btn btn-xs btn-danger is-left" type="submit" value="1" disabled title="Standard Profil" ><i class="fa fa-trash"></i></button>';
		}
		
		$search_item = str_replace("#SPORTS#",$friendship_status,$search_item);

		$result.=$search_item;
		
	}
	
	$result.='</div>'.$pagination_footer;
	
	
	$output.=$result.'
	<div class="row">
		<div class="col-md-6 col-sm-6 main_content">
		<h4 class="profile">'.TEXT_PROFILE_SETTING_PROFILES_ADD.' </h4><br>
			<form method="post" action="#SITE_URL#me/settings/profiles/">
				<select name="user_type" class="form-control">';
				
    $sql = "SELECT * FROM user_types ut, user_types_details utd WHERE ut.user_type_id=utd.user_type_id AND utd.language_code='".$_SESSION['language_code']."' ORDER BY ut.user_type_id ASC";

    $query = $DB->prepare($sql);
    $query->execute();
    $get_user_types = $query->fetchAll();

    $choice_x=0;
    foreach ($get_user_types as $user_types) {
        $output .= '<option value="'.$user_types['user_type_id'].'">'.constant('TEXT_REGISTER_USER_TYPE_'.$user_types['user_type_id']).'</option>';
    }
				
    $output .= '
				</select>
				<input type="hidden" name="add_profile" value="1">
				<button type="submit" class="btn btn-primary">'.TEXT_PROFILES_ADD_NEW.'</button>
			</form>
		</div>
	</div>
	
	';
	}

}

$output .= '
	</div>';

$header_ext = '<link rel="stylesheet" type="text/css" href="#SITE_URL#css/bootstrap-datepicker3.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">';

$footer_ext = ' 

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="#SITE_URL#js/bootstrap-datepicker.js"></script>
				
  <script> $(\'#add_profile .input-group.date\').datepicker({
        format: "dd.mm.yyyy",
        endDate: "-7y",
        language: "de"
    });
	
	jQuery(document).ready(function(){
		
	$(function () {
    var select = $( "#profile_country" ),
        options = select.find( "option" ),
        address = $( "#profile_zipcode" );

    var selectType = options.filter( ":selected" ).attr( "value" );
	
    address.autocomplete({
        source: \'#SITE_URL#js/ajax_source/zipformquery/\' + selectType + \'/\',
        minLength: 3,
		select:function(evt, ui) {
			// when a zipcode is selected, populate related fields in this form
			this.form.profile_city_preview.value = ui.item.label;
			this.form.profile_city_id.value = ui.item.city_id;
			
		}
    });

    select.change(function () {
        selectType = options.filter( ":selected" ).attr( "value" );
        address.autocomplete( "option", "source", \'#SITE_URL#js/ajax_source/zipformquery/\' + selectType + \'/\');
    });
});
	
	
	});
	
	</script>';


$content_output = array('TITLE' => SITE_NAME, 'CONTENT' => $sidebar.$output, 'FOOTER_EXT' => $footer_ext,  'HEADER_EXT' => $header_ext);