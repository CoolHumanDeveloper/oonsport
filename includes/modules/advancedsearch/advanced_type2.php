<?php

$advanced_type = 2;
$search_type_advanced_sql[$advanced_type]='';

  $selected_type_profession[$advanced_type] = array();
  $selected_type_profession_all[$advanced_type]='';
  $search_type_profession_query[$advanced_type] = array();
  $search_type_profession_sql[$advanced_type]='';

if($_GET['search_type_'.$advanced_type.'_profession_all_'.$_SESSION['user']['secure_spam_key']]==1 || count($_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']]) == 0) {
	  $selected_type_profession_all[$advanced_type]=' checked="checked"';
	  $_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][0]=1;
	  $_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][1]=2;
	  $_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][2]=3;
	  $_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][3]=4;
	  $_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][4]=5;
  }
 
  if (is_array($_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']])) {
	   $advanced_profession_array = array('ALL', 'BEGINNER', 'ROOKIE', 'AMATEUR',  'PROFI', 'OTHER');
	    $search_criteria_advanced_string[$advanced_type].=', '.constant('TEXT_GLOBAL_PROFESSION_'.$advanced_type).':';
	  
	  $search_type_profession_sql[$advanced_type].=" AND ( ";
	  $search_sql_type_profession_opperator[$advanced_type]="";
	  foreach($_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']] as $st => $st_value)
	  {
	 	 $selected_type_profession[$advanced_type][$_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][$st]]=' checked="checked"';
		 
		 $search_type_profession_sql[$advanced_type].=$search_sql_type_profession_opperator[$advanced_type]."utsgv.sport_group_profession='".$st_value."'";
		 
		 $search_sql_type_profession_opperator[$advanced_type]=" OR ";
		 
		 
		 $search_criteria_advanced_string[$advanced_type].=$search_criteria_advanced_string_separator.constant('TEXT_GLOBAL_PROFESSION_'.$advanced_profession_array[$st_value].'_'.$advanced_type);
		 $search_criteria_advanced_string_separator=", ";
	  }
	  
	   $search_type_profession_sql[$advanced_type].=" )";
	   
	   $search_type_advanced_sql[$advanced_type].=$search_type_profession_sql[$advanced_type];
	 
  }


if($_GET['search_type_'.$advanced_type.'_handycap_'.$_SESSION['user']['secure_spam_key']]==1) {
	$selected_handycap[$advanced_type]=' checked="checked"';
	$search_type_advanced_sql[$advanced_type].=" AND utsgv.sport_group_handycap = '1' ";
	$search_criteria_advanced_string[$advanced_type].=', '.TEXT_GLOBAL_HANDYCAP.': '.TEXT_GLOBAL_YES;
}

if(isset($_GET['search_type_'.$advanced_type.'_gender_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_type_'.$advanced_type.'_gender_'.$_SESSION['user']['secure_spam_key']] != "0" ) {
	$selected_gender[$advanced_type][$_GET['search_type_'.$advanced_type.'_gender_'.$_SESSION['user']['secure_spam_key']]]=' selected="selected"';
	
	$search_type_advanced_sql[$advanced_type].=" AND ud.user_gender = '".$_GET['search_type_'.$advanced_type.'_gender_'.$_SESSION['user']['secure_spam_key']]."' ";
	
	$search_criteria_advanced_string[$advanced_type].=', '.TEXT_GLOBAL_GENDER.': '.constant('TEXT_GLOBAL_GENDER_'.strtoupper($_GET['search_type_'.$advanced_type.'_gender_'.$_SESSION['user']['secure_spam_key']]));

}



if(isset($_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']]) && is_numeric($_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']])) {
	// To-Date muss größer sein als From Daten
	if($_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']] < $_GET['search_type_'.$advanced_type.'_age_from_'.$_SESSION['user']['secure_spam_key']]) {
		$_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']] = $_GET['search_type_'.$advanced_type.'_age_from_'.$_SESSION['user']['secure_spam_key']] + 1;
		
	}	
	
	$search_criteria_advanced_string[$advanced_type].=', '.TEXT_GLOBAL_AGE.': '.$_GET['search_type_'.$advanced_type.'_age_from_'.$_SESSION['user']['secure_spam_key']].' - '.$_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']].' Jahre';
	
	$search_type_advanced_sql[$advanced_type].=" 
				AND ud.user_dob < (DATE_SUB(CURDATE(), INTERVAL ".$_GET['search_type_'.$advanced_type.'_age_from_'.$_SESSION['user']['secure_spam_key']]." YEAR)) 
				AND ud.user_dob > (DATE_SUB(CURDATE(), INTERVAL ".$_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']]." YEAR))";

}

$advanced_type_content[$advanced_type] ='

<!-- Type '.$advanced_type.'-->
	
	<div class="col-md-4 col-sm-12">
		<div class="advanced_search_inner_type_'.$advanced_type.'">
			<strong>'.constant('TEXT_GLOBAL_PROFESSION_'.$advanced_type).':</strong><br>
				<label>
					<input type="checkbox" name="search_type_'.$advanced_type.'_profession_all_'.$_SESSION['user']['secure_spam_key'].'" id="search_type_'.$advanced_type.'_profession_all" value="1" '.$selected_type_profession_all[$advanced_type].'> '.TEXT_GLOBAL_ALL.'</label><br>
				<label>
					<input type="checkbox" name="search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_'.$advanced_type.'_profession_checkbox" value="1" '.$selected_type_profession[$advanced_type][1].'> '.constant('TEXT_GLOBAL_PROFESSION_BEGINNER_'.$advanced_type).'</label><br>
				<label>
					<input type="checkbox" name="search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_'.$advanced_type.'_profession_checkbox" value="2" '.$selected_type_profession[$advanced_type][2].'> '.constant('TEXT_GLOBAL_PROFESSION_ROOKIE_'.$advanced_type).'</label><br>
				<label>
					<input type="checkbox" name="search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_'.$advanced_type.'_profession_checkbox" value="3" '.$selected_type_profession[$advanced_type][3].'> '.constant('TEXT_GLOBAL_PROFESSION_AMATEUR_'.$advanced_type).'</label><br>
				<label>
					<input type="checkbox" name="search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_'.$advanced_type.'_profession_checkbox" value="4" '.$selected_type_profession[$advanced_type][4].'> '.constant('TEXT_GLOBAL_PROFESSION_PROFI_'.$advanced_type).'</label>
				<br>
				<label>
					<input type="checkbox" name="search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_'.$advanced_type.'_profession_checkbox" value="5" '.$selected_type_profession[$advanced_type][5].'> '.TEXT_GLOBAL_OTHER.'</label>
		</div>
	</div>
	
	<div class="col-md-4 col-sm-12">
			<div class="advanced_search_inner_type_'.$advanced_type.'">
<strong>'.TEXT_GLOBAL_AGE.':</strong><br>
	<label>
		'.build_age_range('search_type_'.$advanced_type.'_age_from_'.$_SESSION['user']['secure_spam_key'],$_GET['search_type_'.$advanced_type.'_age_from_'.$_SESSION['user']['secure_spam_key']],REGISTER_MIN_YEARS).'
	- 
	'.build_age_range('search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key'],$_GET['search_type_'.$advanced_type.'_age_to_'.$_SESSION['user']['secure_spam_key']],99).'
	</label>
	 <br>
	 <br>
	<strong>'.TEXT_GLOBAL_GENDER.':</strong><br>
	<select name="search_type_'.$advanced_type.'_gender_'.$_SESSION['user']['secure_spam_key'].'" class="form-control">
	<option value="0" '.$selected_gender[$advanced_type][0].'>'.TEXT_GLOBAL_ALL.'</option>
	<option value="f" '.$selected_gender[$advanced_type]['f'].'>'.TEXT_GLOBAL_GENDER_FEMALE.'</option>
	<option value="m" '.$selected_gender[$advanced_type]['m'].'>'.TEXT_GLOBAL_GENDER_MALE.'</option>
	</select>
	<br>
	<strong>'.TEXT_GLOBAL_SPECIAL.':</strong><br>
	<label><input type="checkbox" name="search_type_'.$advanced_type.'_handycap_'.$_SESSION['user']['secure_spam_key'].'" class="search_handycap_checkbox" value="1" '.$selected_handycap[$advanced_type].' class="form-control"> '.constant('TEXT_SEARCH_YOUR_HANDYCAP_'.$advanced_type).'</label><br>
	
	</div>
	</div>
	
';

