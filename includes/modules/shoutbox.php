<?php 
is_user();

// DELETE SHOUTBOX POST 
if(isset($_POST['shoutbox_key_' . $_SESSION['user']['secure_spam_key']]) && strlen($_POST['shoutbox_key_' . $_SESSION['user']['secure_spam_key']])==32) {
	
	if($_POST['delete_shoutbox_key_' . $_SESSION['user']['secure_spam_key']]==1) {
		$sql = "DELETE FROM `user_shoutbox` WHERE MD5(CONCAT(shoutbox_message_id, shoutbox_date, user_id))='".$_POST['shoutbox_key_' . $_SESSION['user']['secure_spam_key']]."' AND user_id = '".$_SESSION['user']['user_id']."' ";
		
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SHOUT_BOX_SUCCESS_REMOVED);		
            build_history_log($_SESSION['user']['user_id'],"removed_shoutbox",'');
            header("Location: ".SITE_URL."shoutbox/");
            die();
		
		}
		else {
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SHOUT_BOX_SUCCESS_REMOVED_ERROR);
            header("Location: ".SITE_URL."shoutbox/");
            die();
		}
	}
    
    if($_POST['reactivate_shoutbox_key_' . $_SESSION['user']['secure_spam_key']]==1) {
		$sql = "UPDATE `user_shoutbox` SET shoutbox_status = 0, shoutbox_durration = '".date("Y-m-d H:i:s",strtotime("+7 days"))."' WHERE MD5(CONCAT(shoutbox_message_id, shoutbox_date, user_id))='".$_POST['shoutbox_key_' . $_SESSION['user']['secure_spam_key']]."' AND user_id = '".$_SESSION['user']['user_id']."' ";
		
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SHOUT_BOX_SUCCESS_REACTIVATED);		
            build_history_log($_SESSION['user']['user_id'],"removed_shoutbox",'');
            header("Location: ".SITE_URL."shoutbox/");
            die();
		
		}
		else {
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SHOUT_BOX_SUCCESS_REACTIVATED_ERROR);
            header("Location: ".SITE_URL."shoutbox/");
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
		if($_POST['add_shoutbox_duration_' . $_SESSION['user']['secure_spam_key']] > 0 && $_POST['add_shoutbox_duration_' . $_SESSION['user']['secure_spam_key']] <= 30) {
			$duration = $_POST['add_shoutbox_duration_' . $_SESSION['user']['secure_spam_key']];
		}
		else {
			$duration=30;
		}
		

        $sql = "INSERT INTO `user_shoutbox` (user_id, `shoutbox_date`, `shoutbox_durration`, `shoutbox_text`,`shoutbox_title`,`shoutbox_type`, shoutbox_sport_group_id) VALUES ('".$_SESSION['user']['user_id']."', NOW(), '".date("Y-m-d H:i:s", strtotime("+ ".$duration." days"))."', '".$_POST['add_shoutbox_text_' . $_SESSION['user']['secure_spam_key']]."', '".$_POST['add_shoutbox_title_' . $_SESSION['user']['secure_spam_key']]."', '".$_POST['add_shoutbox_type_' . $_SESSION['user']['secure_spam_key']][ 0 ]."', '".$_POST['shoutbox_group']."');";
		
        $query = $DB->prepare($sql);
        $query->execute();
		
		$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SHOUT_BOX_SUCCESS_ADD);
		header("Location: ".SITE_URL."shoutbox/");
        die();
	}
}

// Settings
if(isset($_POST['update_settings'])) {
	$sql = "UPDATE user_settings SET settings_value='0' WHERE  user_id = '".$_SESSION['user']['user_id']."'";
    $query = $DB->prepare($sql);
    $query->execute();
	
	foreach($_POST as $getkey => $getvalue) {
        if(stristr($getkey,"MySet_") == true ) {

            $sql = "UPDATE user_settings SET settings_value='".$getvalue."' WHERE settings_key='".str_replace("MySet_","",$getkey)."' AND user_id = '".$_SESSION['user']['user_id']."'";

            $query = $DB->prepare($sql);
            $query->execute();
        }
		
	}
	$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SETTINGS_UPDATED);
	header("Location: ".SITE_URL."shoutbox/");
    die();
	
}

$settings_form='
<ul class="list-group  sub_nav">
<li class="list-group-item">
<form method="post" action="#SITE_URL#shoutbox/">
';

$settings_types= array(	'shoutbox_display_radius' => 'radius', 
						'shoutbox_display_friends' => 'checkbox', 
						'shoutbox_display_sports' => 'checkbox',
                        'shoutbox_display_marketplace' => 'checkbox');

$radius_distances = array(0,10,25,50,100,200);

$setting_headlines = array();

$sql = "SELECT * FROM user_settings WHERE user_id = '".$_SESSION['user']['user_id']."' AND settings_type = 'shoutbox' ORDER BY settings_type ASC, settings_key ASC";
$query = $DB->prepare($sql);
$query->execute();
$get_settings = $query->fetchAll();
foreach($get_settings as $settings){

    if(!in_array($settings['settings_type'],$setting_headlines)) {
        array_push($setting_headlines,$settings['settings_type']);
        $settings_form.='  <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <h4 class="profile">' . constant('TEXT_SETTING_HEADER_' . strtoupper($settings['settings_type'])).'</h4></div>

                </div>';	
    }	


    $settings_form.='	<div class="row"><br>

                    <div class="col-md-7 col-sm-7">'.constant('TEXT_SETTING_'.strtoupper($settings['settings_key'])).'</div>
                    <div class="col-md-5 col-sm-5">';

    if($settings_types[$settings['settings_key']] == 'checkbox') {
            $checked_value='';
            if($settings['settings_value']== 1)	$checked_value=' checked="checked"';
            $settings_form.='<input type="checkbox" name="MySet_' . $settings['settings_key'].'" ' . $checked_value.' value="1">';
    }

    if($settings_types[$settings['settings_key']] == 'radius')  {
        $settings_form.='<select name="MySet_' . $settings['settings_key'].'" class="form-control">';
        for($x_rad=0;$x_rad < count($radius_distances); $x_rad++) {
            $radius_distances_select="";

            if($radius_distances[$x_rad] == 0)  {
                $radius_distances_text = TEXT_GLOBAL_NONE;
            }
            else {
            $radius_distances_text=$radius_distances[$x_rad]." ".TEXT_GLOBAL_KM;
            }

            if($settings['settings_value'] == $radius_distances[$x_rad]) {
                 $radius_distances_select=" selected";

            }


            $settings_form.='<option' . $radius_distances_select.' value="' . $radius_distances[$x_rad].'" >' . $radius_distances_text.'</option>';
        }

        $settings_form.='</select>';
    }
    $settings_form.='</div>
        </div>';	
}


$settings_form.='<br>
<br>
<input type="hidden" name="update_settings" value="1">
<button type="submit" class="btn btn-primary form-control">'.TEXT_GLOBAL_SAVE.'</button></form>
</li>
</ul>';


// End Settings

$subnav=' ' . $settings_form.' ';


$sidebar='

<div class="col-md-3 col-sm-12">
<div class="side_bar">
<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> ' . TEXT_SHOUTBOX_HEADER . '
</li>
  </ul>
' . $subnav.'
</div>
'.build_banner('shoutbox','xs','',0,'').'
'.build_banner('shoutbox','sm','',0,'').'
'.build_banner('shoutbox','md','',0,'').'
'.build_banner('shoutbox','lg','',0,'').'
	</div>';
	
	$sub_sidebar='

<div class="col-md-3 col-sm-12">
<div class="sub_side_bar">
' . $subnav.'
</div>
	</div>';

$output = '	<div class="col-md-9 col-sm-12">';
if(!isset($_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]])) {
$output.='
    <div class="col-md-12 content-box-right" id="shoutBoxBox">
<form method="post" action="#SITE_URL#shoutbox/"  class="collapse" id="collapseShoutBox" data-parent="#marketPlaceBox">
    <div class="col-md-8">
      <h4 class="profile">'.TEXT_SHOUTBOX_HEADER_CREATE.':</h4>
      <br>
      <label>
        <input name="add_shoutbox_type_' . $_SESSION['user']['secure_spam_key'].'[]" value="standard" type="radio" checked>
        '.TEXT_SHOUTBOX_TYPE_GLOBAL.'</label>
      <label>
        <input name="add_shoutbox_type_' . $_SESSION['user']['secure_spam_key'].'[]" value="date" type="radio">
        '.TEXT_SHOUTBOX_TYPE_DATE.'</label>
      <input type="text" name="add_shoutbox_title_' . $_SESSION['user']['secure_spam_key'].'" placeholder="'.TEXT_SHOUTBOX_TITLE_PLACEHOLDER.'" class="form-control">
      <br>
      <textarea class="form-control" name="add_shoutbox_text_' . $_SESSION['user']['secure_spam_key'].'" rows="4" maxlength="255" placeholder="'.TEXT_SHOUTBOX_PLACEHOLDER.'"></textarea>
      <br>
    </div>
    <div class="col-md-4">
      <label for="add_shoutbox_duration_' . $_SESSION['user']['secure_spam_key'].'">'.TEXT_SHOUTBOX_DURATION.'</label>
      <select name="add_shoutbox_duration_' . $_SESSION['user']['secure_spam_key'].'"  class="form-control">
        <option value="1">1 '.TEXT_GLOBAL_DAY.'</option>
        <option value="2">2 '.TEXT_GLOBAL_DAYS.'</option>
        <option value="7">7 '.TEXT_GLOBAL_DAYS.'</option>
        <option value="14">14 '.TEXT_GLOBAL_DAYS.'</option>
        <option value="30">30 '.TEXT_GLOBAL_DAYS.'</option>
      </select>
      <br>
      <label for="shoutbox_group">' . TEXT_SHOUTBOX_SPORT.'</label>
      ' . build_search_sports_select($selected = $_POST['shoutbox_group'], $search_lines = '', $first_level = true) . '<br>
      <input type="submit" value="'.TEXT_SHOUTBOX_WRITE.'" class="btn btn-sm btn-primary form-control">
    </div>

</form>
<div class="col-md-3">
    <a class="btn btn-sm btn-primary form-control" data-toggle="collapse" href="#collapseShoutBox" aria-expanded="false" aria-controls="collapseShoutBox" data-parent="#ShoutBoxBox" onClick="this.style.display=\'none\'">
    ' . TEXT_SHOUTBOX_WRITE . '
  </a>
  </div>
</div>';
}
$output .='
<div class="col-md-12 content-box-right">
<h4 class= "search">' . TEXT_SHOUTBOX_SEARCH_HEADER . ':</h4>
<div class="row">
    <form method="get" action="#SITE_URL#shoutbox/"  enctype="multipart/form-data">
        <div class="col-md-6"> 
        <label>
            <input name="shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="shoutbox_all" type="radio" ';
    if(!isset($_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0]) || $_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] == 'shoutbox_all' ) {
            $output .= ' checked ';
        }

    $output .='>
            ' . TEXT_SHOUTBOX_TYPE_ALL . '</label>
          <label>
            <input name="shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="standard" type="radio"';
    if($_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] == 'standard') {
            $output .= ' checked ';
        }

    $output .='>
            ' . TEXT_SHOUTBOX_TYPE_GLOBAL . '</label>
          <label>
            <input name="shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]" value="date" type="radio"';
    if($_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] == 'date') {
            $output .= ' checked ';
        }

    $output .='>
            ' . TEXT_SHOUTBOX_TYPE_DATE . '</label>
          <br>
          <input type="text" name="shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" placeholder="' . TEXT_SHOUTBOX_SEARCH_PLACEHOLDER . '" value="';
        
        if(isset($_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . ''])) {
            $output .= $_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . ''];
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
        <input type="submit" value="' . TEXT_SHOUTBOX_SEARCH . '" class="btn btn-sm btn-primary form-control">
      </div>
    </form>
    </div>
</div>

<div class="col-md-12 content-box-right">
 <h4 class="profile">'.TEXT_SHOUTBOX_HEADLINE_RESULT.':</h4>
      <br>
'.build_shoutbox_feed($_SESSION['user']['user_id'],'shoutbox').'
</div>
</div>';

$content_output = array('TITLE' => TEXT_SHOUTBOX_HEADER,
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');