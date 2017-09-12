<?php

include ("../../includes/config.php");
is_user();

$_GET=$_POST;
$page=$_GET['page'] ? $_GET['page']-1 : 0;

$_SESSION['search_load_more']+=SEARCH_MAX_GROUPS;
$search_url_terms_string="";
$search_url_types_string="";
$search_url_page_string="";
$search_sql="";
// Kriterien für die Suche und Sub-Suchen
$search_criteria_string="";

$_SESSION['search_load_more'];

if($_GET['page'] > 0) {
	$search_url_page_string="&page=".$_GET['page'];
}


//pre selection
if(!isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']])) {
$_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']]=$_SESSION['user']['user_zipcode'];
$_GET['search_country_'.$_SESSION['user']['secure_spam_key']]=$_SESSION['user']['user_country'];
$_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']]=$_SESSION['user']['user_city_id'];
}


// Prüfe Country ID auf eine Valide Postleitzahl.
if($_GET['search_country_'.$_SESSION['user']['secure_spam_key']] != "" && $_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']]!="") {
	$mycityid = get_city_id_by_zipcode($_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']],$_GET['search_country_'.$_SESSION['user']['secure_spam_key']]);
	if(!$mycityid) {
		// Suche abbrechen
		unset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']]);
		$_SESSION['system_message'] = set_system_message("error", TEXT_SEARCH_WRONG_POSTCODE);
	}
	else {
		$_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']]=$mycityid;
	}
}
else {
	$_GET['search_radius_'.$_SESSION['user']['secure_spam_key']]=0;
}



foreach($_GET as $getkey => $getvalue) {
	if(stristr($getkey,"search_") == true ) {
		if(is_array($getvalue)) {
				foreach ($getvalue as $subkey => $subvalue) {
					if($getkey == "search_type_".$_SESSION['user']['secure_spam_key'])
					{
					// FÜR ALLE TYPES, WIRD AN DAS ALLEGEMINE ERGEBNIS ANGEHÄNGT FÜR SEPARATE TYPEN AUSWAHL WIR ES WEGGELASSEN
					$search_url_types_string.='&'.$getkey.'[]='.$subvalue;
					}
					else {
					$search_url_terms_string.='&'.$getkey.'['.$subkey.']='.$subvalue;
					}
					/*echo "---".$getkey.'['.$subkey.']='.$subvalue."<br>";*/
				}
			}
			else {
				if($getkey != "search_type_all_".$_SESSION['user']['secure_spam_key']) {
				$search_url_terms_string.='&'.$getkey.'='.$getvalue;
				}
				/*echo $getkey.'='.$getvalue."<br>";*/
			}
		
	}
}

$selected_sport_group_value=0;
$selected_sport_group_id=0;
$sg_x=0;
$rebuild_sport_groups="";

$search_sport_operator = "AND";
if($_GET['search_hide_'.$_SESSION['user']['secure_spam_key']] == 1) $search_sport_operator = "AND NOT";

if(isset($_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']])) {

	
		// Weitere Untersportarten in die SQL Abfrage includieren und Form-generierung
;

		if(isset($_GET['search_more_sport'])) {
			$search_criteria_string.=$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']].", ";
		$search_sql.=" AND  utsgv.sport_group_id = '".$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']]."' AND (";
		$selected_sport_group_id=$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']];
			
			$sg_or="";
			
			foreach($_GET['search_more_sport'] as $sms) {
				if(is_group_child($sms,$selected_sport_group_id) == true) {
					$search_sport_more_value_ids=check_for_all_value_ids($sms, false, false);
					
					foreach($search_sport_more_value_ids as $search_more_value_id)
					{
						$search_sql.=$sg_or." utsgv.sport_group_value_id = '".$search_more_value_id."' ";
						$sg_or=" OR ";
					}
					

				}
			}
			
			$search_sql.=")";
			
		}
		else {
				
		$search_criteria_string.=$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']].", ";
		$search_sql.=" AND utsgv.sport_group_id = '".$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']]."'";
		$selected_sport_group_id=$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']];
		}
	
}
else {
	for($sg_x = 3; $sg_x >=0; $sg_x--) {
		if(isset($_GET['search_sport_groups_'.$sg_x])) {
			$selected_sport_group_value=$_GET['search_sport_groups_'.$sg_x];
			break;
		}
	}
	
	//$selected_sport_group_value.":".$_GET['search_sport_groups_0'].":".$sg_x.":"."?";
	
	
	// Wenn nur die Hauptgruppe gewählt wurde, die z.B. auch keine Unterpunkte hat:
	if($_GET['search_sport_groups_0'] != 0) $selected_sport_group_id=$_GET['search_sport_groups_0'];
	
	if($sg_x == 0 && $_GET['search_sport_groups_0'] != 0) {
	 	$search_sql.=" ".$search_sport_operator." utsgv.sport_group_id = '".$_GET['search_sport_groups_0']."' ";
	}
	else {
	
		
		
	
		// 2. 
		// Sub Values
		$search_sport_group_value_ids=check_for_all_value_ids($selected_sport_group_value, $_GET['search_sport_groups_0'], $sg_x);
		
		
		
		if(count($search_sport_group_value_ids) > 0) {
			$sg_or="";
			$search_sql.=" ".$search_sport_operator." ( ";
			foreach($search_sport_group_value_ids as $search_value_id) {
				$search_sql.=$sg_or."utsgv.sport_group_value_id = '".$search_value_id."'";
				$sg_or=" OR ";
				
				
			}
			
			
			// Weitere Untersportarten in die SQL Abfrage includieren und Form-generierung
			
				if(isset($_GET['search_more_sport'])) {
					foreach($_GET['search_more_sport'] as $sms)
					{
						$search_sport_more_value_ids=check_for_all_value_ids($sms, false, false);
						foreach($search_sport_more_value_ids as $search_more_value_id)
						{
							$search_sql.=$sg_or."utsgv.sport_group_value_id = '".$search_more_value_id."'";
						}
						

					}
				}
			
			
			
			
			$search_sql.=" ) ";
			

			
			
			
		}
	}
	
}



  if($_GET['search_name_'.$_SESSION['user']['secure_spam_key']]!="") {
  $search_criteria_string.="\"".$_GET['search_name_'.$_SESSION['user']['secure_spam_key']]."\", ";
  }
  
  $selected_type=array();
  $selected_type_all='';
  $search_type_query=array();
  $search_type_sql='';
  
  if($_GET['search_type_all_'.$_SESSION['user']['secure_spam_key']]==1 || count($_GET['search_type_'.$_SESSION['user']['secure_spam_key']]) == 0) {
	  $selected_type_all=' checked="checked"';
	  $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][0]=1;
	  $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][1]=2;
	  $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][2]=3;
	  $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][3]=4;
  }
 
include ("../../includes/modules/advancedsearch/advanced_type1.php");
include ("../../includes/modules/advancedsearch/advanced_type2.php");
include ("../../includes/modules/advancedsearch/advanced_type3.php");
include ("../../includes/modules/advancedsearch/advanced_type4.php"); 


if (is_array($_GET['search_type_'.$_SESSION['user']['secure_spam_key']])) {
	  
	  $search_type_sql.=" AND ( ";
	  $search_sql_type_opperator="";
	  foreach($_GET['search_type_'.$_SESSION['user']['secure_spam_key']] as $st => $st_value) {
	 	 $selected_type[$_GET['search_type_'.$_SESSION['user']['secure_spam_key']][$st]]=' checked="checked"';
		 $search_type_sql.=$search_sql_type_opperator."( u.user_type='".$st_value."' ".$search_type_advanced_sql[$st_value].")";
		 $search_sql_type_opperator=" OR ";
	  }
	  
	   $search_type_sql.=" )";
	 
}

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
            if($_GET['search_country_'.$_SESSION['user']['secure_spam_key']]==$countries['country_code']) {
                if($countries['translatedName']) {
                    $countries['country_name'] = $countries['translatedName'];
                }
                
                $search_criteria_string.=$countries['country_name'].", ";
            }
		}
			
		if($_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']]!="") {
		$search_criteria_string.=$_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']].", ";
		}

			

$form_distances=array(0,10,25,50,100,200);
for($x_rad=0;$x_rad < count($form_distances); $x_rad++) {
	if($_GET['search_radius_'.$_SESSION['user']['secure_spam_key']]==$form_distances[$x_rad] || (!isset($_GET['search_radius_'.$_SESSION['user']['secure_spam_key']]) && DEFAULT_DISTANCE_SEARCH==$form_distances[$x_rad])) {
		 $form_distances_select=" selected";
		 if($form_distances[$x_rad]!=0) $search_criteria_string.=TEXT_GLOBAL_RADIUS.': '.$form_distances[$x_rad]." ".TEXT_GLOBAL_KM;
		 
	}

}





if(isset($_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']] && isset($_GET['search_country_'.$_SESSION['user']['secure_spam_key']])) {
	$geo=get_city_latlng($_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']], $_GET['search_country_'.$_SESSION['user']['secure_spam_key']] );
	
	if($geo) {
	$search_lat=$geo['lat'];
	$search_lng=$geo['lng'];
	}
	else {
	$search_lat=$_SESSION['user']['user_lat'];
	$search_lng=$_SESSION['user']['user_lng'];
	}
	
}
else {
$search_lat=$_SESSION['user']['user_lat'];
$search_lng=$_SESSION['user']['user_lng'];
}
$search_exclude_user=$_SESSION['user']['user_id'];
$result="";

	// NAME FILTER
	if($_GET['search_name_'.$_SESSION['user']['secure_spam_key']]!="") {
		$search_sql.=" AND ud.user_nickname LIKE '%".$_GET['search_name_'.$_SESSION['user']['secure_spam_key']]."%'";
		
	}
	
	
	$search_by_values=array("distance","newest","user_online");
	$search_by_values_to_db=array("distance" => 'DISTANCE ASC', "newest" => "u.user_id DESC", "online" => "u.user_online DESC");


	if(isset($_GET['search_sort_'.$_SESSION['user']['secure_spam_key']])) {
		if(in_array($_GET['search_sort_'.$_SESSION['user']['secure_spam_key']],$search_by_values)) {
			$search_by_selected[$_GET['search_sort_'.$_SESSION['user']['secure_spam_key']]]=' selected="selected"';		
			$search_by=$search_by_values_to_db[$_GET['search_sort_'.$_SESSION['user']['secure_spam_key']]];
			
		}
		else {
			$search_by_selected['distance']=' selected="selected"';
			$search_by=$search_by_values_to_db['distance'];
		}
	}
	else {
		
		if($search_lat == 0) {
		$search_by_selected['newest']=' selected="selected"';	
		$search_by=$search_by_values_to_db['newest'];
		}
		else {
		$search_by_selected['distance']=' selected="selected"';	
		$search_by=$search_by_values_to_db['distance'];
		}
	}
	
	
	
if(isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_action_'.$_SESSION['user']['secure_spam_key']]==="search.do") {

	// Wenn Radius gesetzt
		if($_GET['search_radius_'.$_SESSION['user']['secure_spam_key']] > 0 && $search_lat > 0) {
		$search_sql.=" AND (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") < ".$_GET['search_radius_'.$_SESSION['user']['secure_spam_key']];
		
	}

}



if($search_lat == 0) {
	$base_search_sql="SELECT DISTINCT(u.user_id), u.*, ud.*,  
	'0' AS DISTANCE
 
 FROM user u
LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
LEFT JOIN user_to_sport_group_value AS utsgv ON utsgv.user_id = u.user_id
LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = utsgv.sport_group_id
WHERE  
u.user_status=1 
".$search_sql;

$query = $DB->prepare($base_search_sql.$search_type_sql);
$query->execute();

$total=$query->rowCount();
	
	
//$sql = $base_search_sql.$search_type_sql." ORDER BY ".$search_by." DESC LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE."
// ";

 }
 else {

$base_search_sql="SELECT DISTINCT(u.user_id), u.*, ud.*, 
					(acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE
					 
					 FROM 
					 user u
					LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
					LEFT JOIN user_to_sport_group_value AS utsgv ON utsgv.user_id = u.user_id
					LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = utsgv.sport_group_id
					WHERE  
					
					u.user_id!='".$search_exclude_user."' AND
					u.user_status=1 
					".$search_sql;
 
 
$query = $DB->prepare($base_search_sql.$search_type_sql);
$query->execute();

$total=$query->rowCount();

//$sql = $base_search_sql.$search_type_sql."
//  ORDER BY ".$search_by." LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE."
// ";

 }
 
if($total < 1){
	 $output='<div class="col-md-9 col-sm-12 content-box-right">'.set_system_message("error", TEXT_SEARCH_NO_RESULTS);
}
else {

    if(isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_action_'.$_SESSION['user']['secure_spam_key']]==="search.do") {

        if( ( (is_array($_GET['search_type_'.$_SESSION['user']['secure_spam_key']]) 
        && count($_GET['search_type_'.$_SESSION['user']['secure_spam_key']]) > 1)
        ||
         ($_GET['search_type_all_'.$_SESSION['user']['secure_spam_key']]==1)
         )
         && $_GET['page'] == 0
         ) {
            // Nothing ?

        }
        else {

            // Filter aus der Advanced Search
            // return an array of sportgroups ordered by maximum user in group
            $sub_query_type_sql=" AND u.user_type='".$_GET['search_type_'.$_SESSION['user']['secure_spam_key']][0] . "'" . $search_type_advanced_sql[$_GET['search_type_'.$_SESSION['user']['secure_spam_key']][0]];
            $sub_sports=get_subquery_sportgroups($base_search_sql.$sub_query_type_sql,$_SESSION['search_load_more'],SEARCH_MAX_GROUPS);

            if(count($sub_sports) > 1) {

                foreach($sub_sports as $sub_sport) {

                    $sub_query_sport_sql=" AND utsgv.sport_group_id = '".$sub_sport['sport_group_id']."' ";

                    $subquery_preview=get_subquery_preview($base_search_sql.$sub_query_type_sql.$sub_query_sport_sql, SEARCH_MAX_GROUPS_PREVIEW);


                    $output.='
                        <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                                <a href="#SITE_URL#advancedsearch/?search_single_sport_group_'.$_SESSION['user']['secure_spam_key'].'='.$sub_sport['sport_group_id'].$search_url_terms_string.$search_url_types_string.'" class="thumbnail thumbnail-line"><div class="row"><div class="grid_line col-xs-10 col-sm-10 col-md-5">
                                <strong>'.$sub_sport['ANZAHL'].' '.$sub_sport['sport_group_name'].' </strong><br>
                        <small>'.$search_criteria_string.$search_criteria_advanced_string[$_GET['search_type_'.$_SESSION['user']['secure_spam_key']][0]].'</small>
                                </div>

                                <div class="grid_line col-xs-10 col-sm-10 col-md-5">
                                  '.$subquery_preview.'
                                  </div>

                                <div class="grid_line grid_button-right col-xs-2 col-sm-2 col-md-2">
                                <i class="fa fa-angle-double-right "></i>
                            </div>

                        </div>
                        </a>

                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12"> 
                        ';	

                    $output.='</div></div>';

                }

                $output.='<div class="row" id="showMoreDiv'.$_SESSION['search_load_more'].'">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                        <a onClick="showMoreGroups'.$_SESSION['search_load_more'].'('.$_SESSION['search_load_more'].');" class="thumbnail thumbnail-line-more">
                                        <div class="row">
                                            <div class="grid_line col-xs-10 col-sm-10 col-md-5">

                                            </div>

                                            <div class="grid_line col-xs-10 col-sm-10 col-md-5">
                                            <i class="fa fa-angle-double-down "></i> '.TEXT_SEARCH_GROUPS_MORE.'<i class="fa fa-angle-double-down "></i>
                                              </div>

                                            <div class="grid_line grid_button-right col-xs-2 col-sm-2 col-md-2">

                                            </div>

                                        </div>
                                    </a>

                                </div>
                            </div>
                            <div id="moreGroupsContent'.$_SESSION['search_load_more'].'"></div>


                            <script>
                            function showMoreGroups'.$_SESSION['search_load_more'].'(page)
                                {
                                $("#moreGroupsContent'.$_SESSION['search_load_more'].			'").load("'.SITE_URL.'js/ajax_source/advanced_search_group_after.php", '.json_encode($_GET).');

                                $("#showMoreDiv'.$_SESSION['search_load_more'].'").hide();
                                }
                            </script>
                            ';
            }
            else {
                die(""); // Error Messages?
            }
        }		

    }
}
echo $output;