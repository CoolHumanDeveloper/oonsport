<?php


if(isset($_POST['delete_sports_'.$_SESSION['user']['secure_spam_key']])) {
		
    $sql = "SELECT COUNT(*) AS qty FROM user_to_sport_group_value WHERE user_id = '".$_SESSION['user']['user_id']."'";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $count_sports = $query->fetch();
    
    if($count_sports['qty'] > 1) {


        $sql = "DELETE FROM user_to_sport_group_value WHERE MD5(CONCAT(sport_group_id,sport_group_value_id,user_id)) = '".$_POST['sports_id_'.$_SESSION['user']['secure_spam_key']]."'  AND user_id = '".$_SESSION['user']['user_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();

        if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_SPORTS_SUCCESS_DELETE);
            header("Location: ".SITE_URL."me/profile/sports/");
            die();
        }
        else  { 
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SPORTS_ERROR_DELETE);
            header("Location: ".SITE_URL."me/profile/sports/");
            die();
        }

    }
    else {
        $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SPORTS_ERROR_DELETE_ONE_LEFT);
        header("Location: ".SITE_URL."me/profile/sports/");
        die();
    }
		

}

if(isset($_POST['update_sports'])) {
	
	if($_POST['search_sport_groups_0'] == 0) {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_SPORT_CHOOSE_ERROR);
    }
    else
    {
	
	
		for($sg_x = 3; $sg_x >=0; $sg_x--) {
            if(isset($_POST['search_sport_groups_'.$sg_x]))
            {
                $selected_sport_group_value = $_POST['search_sport_groups_'.$sg_x];

                if($selected_sport_group_value!=0) {

                    if($selected_sport_group_value == $_POST['search_sport_groups_0'])
                    {
                        $selected_sport_group_value=0;
                    }

                    $sql = "	UPDATE 
                            `user_to_sport_group_value` 
                        SET 
                            sport_group_id ='".$_POST['search_sport_groups_0']."', 
                            sport_group_value_id ='".$selected_sport_group_value."',
                            sport_group_profession ='".$_POST['add_sports_profession']."', 
                            sport_group_handycap ='".$_POST['add_sports_handycap']."'
                        WHERE 
                            MD5(CONCAT(sport_group_id,sport_group_value_id,user_id)) = '".md5($_POST['sgID'].$_POST['sgvID'].$_SESSION['user']['user_id'])."'  
                            AND user_id = '".$_SESSION['user']['user_id']."'";
                    $query = $DB->prepare($sql);
                    $query->execute();

                    if($query->rowCount() != 1) {
                        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_SPORT_UPDATE_ERROR);
                    }
                    else {
                        $_SESSION['system_temp_message'] .= set_system_message("success", TEXT_PROFILE_SPORT_UPDATE_SUCCESS);
                        header("Location: ".SITE_URL."me/profile/sports/");
                        die();
                    }
                }
                else {
                    $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_SPORT_UPDATE_ERROR);
                }
                break;
            }
        }
    }
}


if(isset($_POST['add_sports'])) {
    if($_POST['search_sport_groups_0'] == 0) {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_SPORT_CHOOSE_ERROR);
    }
    else {
        for($sg_x = 3; $sg_x >=0; $sg_x--) {
            if(isset($_POST['search_sport_groups_'.$sg_x])) {
                $selected_sport_group_value = $_POST['search_sport_groups_'.$sg_x];

                if($selected_sport_group_value!=0) {

                    if($selected_sport_group_value == $_POST['search_sport_groups_0']) {
                        $selected_sport_group_value=0;
                    }

                    $sql = "INSERT INTO `user_to_sport_group_value` (`user_id`, `sport_group_id`, `sport_group_value_id`, sport_group_profession, sport_group_handycap) VALUES ('".$_SESSION['user']['user_id']."', '".$_POST['search_sport_groups_0']."', '".$selected_sport_group_value."', '".$_POST['add_sports_profession']."', '".$_POST['add_sports_handycap']."')";
                    $query = $DB->prepare($sql);
                    $query->execute();

                    if($query->rowCount() != 1) {
                        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_SPORT_ADD_DUBLICATE);
                    }
                    else {
                        $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_PROFILE_SPORT_ADD_SUCCESS);
                        header("Location: ".SITE_URL."me/profile/sports/");
                        die();
                    }
                }
                else {
                    $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_SPORT_ADD_ERROR);
                }
                break;
            }
        }
    }
}


$selected_sport_group_id="";
$build_subgroup_form="";
$selected_sport_group_value=0;
$selected_sport_group_id=0;
$sg_x=0;
$rebuild_sport_groups="";

if(isset($_POST['reset_update_sports_'.$_SESSION['user']['secure_spam_key']])) {
	$parent_array = array();
	$parent_array[] = $_POST['sport_group'];
	
	$sub_parent_array = array();
	
	if($_POST['sport_group_value'] > 0) {
		$sub_parent_array[] = $_POST['sport_group_value'];
		$parent = $_POST['sport_group_parent'];
		while($parent!=0) {
			$sub_parent_array[]=$parent;
			$get_parent=get_sport_parent($parent);
			$parent=$get_parent['sport_group_sub_of'];
		}
	}
	
	// Array zusammen führen, Sub Array umkehren
	$parent_array = array_merge($parent_array, array_reverse($sub_parent_array));		
	$selected_sport_group_value=0;
	for($sg_x = 3; $sg_x > 0; $sg_x--) {
		if(isset($parent_array[$sg_x])) {
			$selected_sport_group_value=$parent_array[$sg_x];
			break;
		}
	}
	
	// Wenn nur die Hauptgruppe gewählt wurde, die z.B. auch keine Unterpunkte hat:
	if($_POST['sport_group'] != 0) $selected_sport_group_id = $_POST['sport_group'];
	
	if($sg_x == 0 && $_POST['sport_group'] != 0) {

	}
	else {
        if($sg_x > 0) {
            $start_build_subgroup_form=0;
            for($sg_y = $sg_x; $sg_y > 0 && $sg_y <= $sg_x ; $sg_y--) {
                if(isset($parent_array[$sg_y])) {
                    //$build_subgroup_form .= $_GET['search_sport_groups_'.$sg_y]."->";
                    $start_build_subgroup_form = 1;
                    $temp_subgroup_parent = get_sport_parent($parent_array[$sg_y]);

                    $build_subgroup_form = '<div id="search_sport_sub_group_div_'.($sg_y-1).'">'
                    .build_sport_select_line($selected_sport_group_id,$temp_subgroup_parent['sport_group_sub_of'],($sg_y-1),$parent_array[$sg_y]).
                    '</div>'.$build_subgroup_form;
                }
                else 
                if(!isset($parent_array[$sg_y])) { // Wenn bereits höhere Gruppen gefunden wurden.
                    if($start_build_subgroup_form === 1) {
                        $temp_subgroup_id = get_sport_parent($parent_array[$sg_y+1]);
                        //echo "Fehler[".$_GET['search_sport_groups_'.($sg_y+1)]."]";
                        $temp_subgroup_parent = get_sport_parent($temp_subgroup_id['sport_group_sub_of']);

                        $build_subgroup_form = '<div id="search_sport_sub_group_div_'.($sg_y-1).'">'
                        .build_sport_select_line($selected_sport_group_id,$temp_subgroup_parent['sport_group_sub_of'],($sg_y-1),$temp_subgroup_id).
                        '</div>'.$build_subgroup_form;


                    }
                    else { 
                    $build_subgroup_form = '<div id="search_sport_sub_group_div_'.($sg_y-1).'"></div>'.$build_subgroup_form;
                    }
                }
            }
		}
	}

	
}


if(!isset($_POST['reset_update_sports_'.$_SESSION['user']['secure_spam_key']])) {
$output = '<div class="col-md-9 col-sm-12 content-box-right">
			<div class="row">
				<h4 class="profile">'.TEXT_PROFILE_SPORT_HEADER_MY_SPORTS.':</h4>
				<div class="col-md-12 col-sm-12">
					'.get_profile_user_main_sport($_SESSION['user']['user_id']).'
				</div>

		</div><br>
<br>
<br>
<h4 class="profile">'.TEXT_PROFILE_SPORT_HEADER_ADD_SPORTS.':</h4>
<form action="#SITE_URL#me/profile/sports/" method="post">
<input type="hidden" name="add_sports" value="1">
		';
		
		$sports_form_button = '<input type="submit" value="'.TEXT_GLOBAL_ADD.'"   class="btn btn-sm btn-primary form-control">';
		
}
else {
		
    $output = '<div class="col-md-9 col-sm-12 content-box-right">
		<h4 class="profile">'.TEXT_PROFILE_SPORT_HEADER_UPDATE_SPORTS.':</h4>
<form action="#SITE_URL#me/profile/sports/" method="post">
<input type="hidden" name="sgID" value="'.$selected_sport_group_id.'">
<input type="hidden" name="sgvID" value="'.$selected_sport_group_value.'">
<input type="hidden" name="update_sports" value="1">
';

$sports_form_button = '
<div class="col-md-8 col-sm-12"><input type="submit" value="'.TEXT_GLOBAL_UPDATE.'"   class="btn btn-sm btn-primary form-control"></div><div class="col-md-1 col-sm-12"></div><div class="col-md-3 col-sm-12">
<a href="#SITE_URL#me/profile/sports/" class="btn btn-sm btn-default form-control">'.TEXT_GLOBAL_CANCEL.'</a></div>';
}


$output .= ''.build_search_sports_select($selected_sport_group_id,$build_subgroup_form).'
<div class="row">
	<div class="col-md-4 col-xs-12"><br>

	<label for="add_sports_profession">';
	$output.=constant('TEXT_GLOBAL_PROFESSION_'.$_SESSION['user']['user_type']).': </label>
	<select name="add_sports_profession" id="add_sports_profession" class="form-control">
	<option value="1">'.constant('TEXT_GLOBAL_PROFESSION_BEGINNER_'.$_SESSION['user']['user_type']).'</option>
	<option value="2">'.constant('TEXT_GLOBAL_PROFESSION_ROOKIE_'.$_SESSION['user']['user_type']).'</option>
	<option value="3">'.constant('TEXT_GLOBAL_PROFESSION_AMATEUR_'.$_SESSION['user']['user_type']).'</option>
	<option value="4">'.constant('TEXT_GLOBAL_PROFESSION_PROFI_'.$_SESSION['user']['user_type']).'</option>
	<option value="5" selected>'.constant('TEXT_GLOBAL_PROFESSION_OTHER').'</option>
	</select>
</div>
	<div class="col-md-2 col-xs-12">
	&nbsp;
	</div>
	<!--<div class="col-md-3 col-xs-12">';
	
	$output .= '<br>
<label for="add_sports_group">';
	if($_SESSION['user']['user_type'] == 1 || $_SESSION['user']['user_type'] == 2) {
	$output.=TEXT_REGISTER_YOUR_STATUS.': </label>
	<select name="add_sports_group" id="add_sports_group" class="form-control">
	<option name="1">'.TEXT_REGISTER_YOUR_STATUS_FREE.'</option>
	<option name="2">'.TEXT_REGISTER_YOUR_STATUS_MEMBER.'</option>
	<option name="3">'.TEXT_REGISTER_YOUR_STATUS_CONTRACT.'</option>
	<option name="4" selected>'.TEXT_GLOBAL_OTHER.'</option>
	</select>';
	}
	$output .= '
	</div>-->

<div class="col-md-6 col-xs-12">';
	$output .= '<br>
<label for="add_sports_handycap">';
	if($_SESSION['user']['user_type'] == 1 || $_SESSION['user']['user_type'] == 2) {
	$output.=TEXT_PROFILE_SPORTS_HANDYCAP.': </label><br>


	<input type="checkbox" name="add_sports_handycap" id="add_sports_handycap" value="1">
	</select>'.constant('TEXT_PROFILE_SPORTS_HANDYCAP_INFO_'.$_SESSION['user']['user_type']).'';
	}
	
	$output .= '
	</div>
</div><br>
'.$sports_form_button.'
</form>

</div>';

 
  $footer_ext = '
<script src="#SITE_URL#js/search_sport_subgroup.js"></script>';


$content_output = array('TITLE' => 'Profil -> '.TEXT_PROFILE_SPORT_HEADER_MY_SPORTS, 
                        'CONTENT' => $sidebar.$output, 
                        'FOOTER_EXT' => $footer_ext,  
                        'HEADER_EXT' => $header_ext);