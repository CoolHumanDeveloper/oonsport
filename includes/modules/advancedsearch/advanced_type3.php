<?php

$advanced_type = 3;
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
	  $search_type_profession_sql[$advanced_type].=" AND ( ";
	  $search_sql_type_profession_opperator[$advanced_type]="";
	  foreach($_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']] as $st => $st_value)
	  {
	 	 $selected_type_profession[$advanced_type][$_GET['search_type_'.$advanced_type.'_profession_'.$_SESSION['user']['secure_spam_key']][$st]]=' checked="checked"';
		 
		 $search_type_profession_sql[$advanced_type].=$search_sql_type_profession_opperator[$advanced_type]."utsgv.sport_group_profession='".$st_value."'";
		 
		 $search_sql_type_profession_opperator[$advanced_type]=" OR ";
	  }
	  
	   $search_type_profession_sql[$advanced_type].=" )";
	 
  }
  
  
if($_GET['search_type_'.$advanced_type.'_handycap_'.$_SESSION['user']['secure_spam_key']]==1) {
	$selected_handycap[$advanced_type]=' checked="checked"';
	$search_type_advanced_sql[$advanced_type].=" AND utsgv.sport_group_handycap = '1' ";
	$search_criteria_advanced_string[$advanced_type].=', '.TEXT_GLOBAL_HANDYCAP.': '.TEXT_GLOBAL_YES;
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
	<strong>'.TEXT_GLOBAL_SPECIAL.':</strong><br>
	<label><input type="checkbox" name="search_type_'.$advanced_type.'_handycap_'.$_SESSION['user']['secure_spam_key'].'" class="search_handycap_checkbox" value="1" '.$selected_handycap[$advanced_type].' class="form-control"> '.constant('TEXT_SEARCH_YOUR_HANDYCAP_'.$advanced_type).'</label><br>
	</div>
	</div>
	
';

