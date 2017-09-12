<?php
$page = isset($_GET['page']) ? $_GET['page']-1 : 0;

$search_url_terms_string="";
$search_url_types_string="";
$search_url_page_string="";
$search_sql="";
// Kriterien für die Suche und Sub-Suchen
$search_criteria_string="";

$_SESSION['search_load_more']=0;

if(isset($_GET['page'])){
    if($_GET['page'] > 0) {
        $search_url_page_string="&page=".$_GET['page'];
    }
} else {
    $_GET['page'] = 0;
}

//pre selection
if(!isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']])) {
    $_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']] = $_SESSION['user']['user_zipcode'];
    $_GET['search_country_'.$_SESSION['user']['secure_spam_key']] = $_SESSION['user']['user_country'];
    $_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']] = $_SESSION['user']['user_city_id'];
}


// Prüfe Country ID auf eine Valide Postleitzahl.
if(isset($_GET['search_country_'.$_SESSION['user']['secure_spam_key']]) && isset($_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']])) {
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
                if($getkey == "search_type_".$_SESSION['user']['secure_spam_key']) {
                // FÜR ALLE TYPES, WIRD AN DAS ALLEGEMINE ERGEBNIS ANGEHÄNGT FÜR SEPARATE TYPEN AUSWAHL WIR ES WEGGELASSEN
                $search_url_types_string.='&'.$getkey.'[]='.$subvalue;
                }
                else  {
                $search_url_terms_string.='&'.$getkey.'['.$subkey.']='.$subvalue;
                }
            }
        }
        else {
            if($getkey != "search_type_all_".$_SESSION['user']['secure_spam_key']) {
                $search_url_terms_string.='&'.$getkey.'='.$getvalue;
            }
        }
		
	}
}

$selected_sport_group_value=0;
$selected_sport_group_id=0;
$sg_x=0;
$rebuild_sport_groups = '';
$build_subgroup_form = '';

if(isset($_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']])) {
	$search_criteria_string .= $_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']].", ";
	$search_sql.=" AND utsgv.sport_group_id = '".$_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']]."'";
	$selected_sport_group_id = $_GET['search_single_sport_group_'.$_SESSION['user']['secure_spam_key']];
}
else {
	for($sg_x = 3; $sg_x >=0; $sg_x--) {
		if(isset($_GET['search_sport_groups_'.$sg_x])) {
			$selected_sport_group_value = $_GET['search_sport_groups_'.$sg_x];
			break;
		}
	}
	
	// Wenn nur die Hauptgruppe gewählt wurde, die z.B. auch keine Unterpunkte hat:
	if($_GET['search_sport_groups_0'] != 0) $selected_sport_group_id = $_GET['search_sport_groups_0'];
	
	if($sg_x == 0 && $_GET['search_sport_groups_0'] != 0) {
	 	$search_sql.=" AND utsgv.sport_group_id = '".$_GET['search_sport_groups_0']."' ";
	}
	else {
		$search_sport_group_value_ids=check_for_all_value_ids($selected_sport_group_value, $_GET['search_sport_groups_0'], $sg_x);
		
		if(count($search_sport_group_value_ids) > 0) {
			$sg_or="";
			$search_sql.=" AND ( ";
			foreach($search_sport_group_value_ids as $search_value_id) {
				$search_sql.=$sg_or."utsgv.sport_group_value_id = '".$search_value_id."'";
				$sg_or=" OR ";
			}
			$search_sql.=" ) ";

            // Hier sollen die ID's zurückgegeben werden, die betroffen sind. auch unter kategorien
            if($sg_x > 0) {
                $start_build_subgroup_form=0;
				for($sg_y = 3; $sg_y > 0 && $sg_y <= $sg_x ; $sg_y--) {
					if(isset($_GET['search_sport_groups_'.$sg_y])) {
						//$build_subgroup_form .= $_GET['search_sport_groups_'.$sg_y]."->";
						$start_build_subgroup_form = 1;
						$temp_subgroup_parent = get_sport_parent($_GET['search_sport_groups_'.$sg_y]);
						
						$build_subgroup_form = '<div id="search_sport_sub_group_div_'.($sg_y-1).'">'
						.build_sport_select_line($selected_sport_group_id,$temp_subgroup_parent['sport_group_sub_of'],($sg_y-1),$_GET['search_sport_groups_'.$sg_y]).
						'</div>'.$build_subgroup_form;
					}
					else 
					if(!isset($_GET['search_sport_groups_'.$sg_y])) {
                        // Wenn bereits höhere Gruppen gefunden wurden.
					
						if($start_build_subgroup_form === 1) {
							$temp_subgroup_id = get_sport_parent($_GET['search_sport_groups_'.($sg_y+1)]);
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
}

$search_sidebar='
     <ul class="list-group  sub_nav">
      <li class="list-group-item">
      <h4 class="profile">'.TEXT_INDEX_QUICKSEARCH.'</h4><br>
      <form method="get" action="#SITE_URL#search/">
      <strong>'.TEXT_SEARCH_NICK_HEADER.':</strong><br>';

if(isset($_GET['search_name_'.$_SESSION['user']['secure_spam_key']]))  {
    $search_criteria_string.="\"".$_GET['search_name_'.$_SESSION['user']['secure_spam_key']]."\", ";
    $search_sidebar.='<input type="text" class="form-control" name="search_name_'.$_SESSION['user']['secure_spam_key'].'" value="'.$_GET['search_name_'.$_SESSION['user']['secure_spam_key']].'">';
} else {
    $search_sidebar.='<input type="text" class="form-control" name="search_name_'.$_SESSION['user']['secure_spam_key'].'" value="">';
}
      
$search_sidebar.='<hr>
      <strong>'.TEXT_GLOBAL_SPORTTYPE.':</strong><br>
      '.build_search_sports_select($selected_sport_group_id,$build_subgroup_form).'
    <hr>';



  
$selected_type = array();
$selected_type_all='';
$search_type_query = array();
$search_type_sql='';

if($_GET['search_type_all_'.$_SESSION['user']['secure_spam_key']]==1 || count($_GET['search_type_'.$_SESSION['user']['secure_spam_key']]) == 0) {
    $selected_type_all=' checked="checked"';
    $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][0]=1;
    $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][1]=2;
    $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][2]=3;
    $_GET['search_type_'.$_SESSION['user']['secure_spam_key']][3]=4;
}
 
if (is_array($_GET['search_type_'.$_SESSION['user']['secure_spam_key']])) {
    $search_type_sql.=" AND ( ";
    $search_sql_type_opperator="";
    foreach($_GET['search_type_'.$_SESSION['user']['secure_spam_key']] as $st => $st_value) {
        $selected_type[$_GET['search_type_'.$_SESSION['user']['secure_spam_key']][$st]]=' checked="checked"';
        $search_type_sql.=$search_sql_type_opperator."u.user_type='".$st_value."'";
        $search_sql_type_opperator=" OR ";
    }

    $search_type_sql.=" )";
}


$search_sidebar.='<strong>'.TEXT_GLOBAL_TYPE.':</strong><br>
        <label><input type="checkbox" name="search_type_all_'.$_SESSION['user']['secure_spam_key'].'" id="search_type_all" value="1" '.$selected_type_all.'> '.TEXT_GLOBAL_ALL.'</label><br>
        <label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="1" '.$selected_type[1].'> '.TEXT_SEARCH_USER_TYPE_1.'</label><br>
        <label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="2" '.$selected_type[2].'> '.TEXT_SEARCH_USER_TYPE_2.'</label><br>
        <label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="3" '.$selected_type[3].'> '.TEXT_SEARCH_USER_TYPE_3.'</label><br>
        <label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="4" '.$selected_type[4].'> '.TEXT_SEARCH_USER_TYPE_4.'</label>
        <hr>
          <strong>'.TEXT_GLOBAL_COUNTRY.':</strong> <select name="search_country_'.$_SESSION['user']['secure_spam_key'].'" id="search_country" class="form-control">
                <option value="">'.TEXT_GLOBAL_PLEASE_CHOOSE.'</option>';


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
    $search_sidebar .= '<option value="' . $countries['country_code'] . '" ' . $select_hr . ' ';

    if($_GET['search_country_' . $_SESSION['user']['secure_spam_key']] == $countries['country_code']) {
        $search_sidebar .= 'selected="selected"';
    }

    if($countries['translatedName']) {
        $countries['country_name'] = $countries['translatedName'];
    }

    $search_sidebar .= '>' . $countries['country_name'] . '</option>';
        $sort=$countries['country_sort'];
}	

$search_sidebar.='</select><br>

<strong>'.TEXT_GLOBAL_ZIPCODE.':</strong> <input type="text" id="search_zipcode" name="search_zipcode_'.$_SESSION['user']['secure_spam_key'].'" value="'.$_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']].'"  class="form-control">';
if(isset($_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']])) {			
    if($_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']] != '') {
        $search_criteria_string .= $_GET['search_zipcode_'.$_SESSION['user']['secure_spam_key']].", ";
    }
}
$search_sidebar.='<br>

<strong>'.TEXT_GLOBAL_RADIUS.':</strong> <select  class="form-control" name="search_radius_'.$_SESSION['user']['secure_spam_key'].'" id="search_radius_'.$_SESSION['user']['secure_spam_key'].'">';
			

$form_distances = array(0,10,25,50,100,200);
for($x_rad=0;$x_rad < count($form_distances); $x_rad++) {
	$form_distances_select="";
	if($form_distances[$x_rad] == 0)  {
		$form_distances_text=TEXT_GLOBAL_WITHOUT;
	}
	else {
		$form_distances_text=$form_distances[$x_rad]." ".TEXT_GLOBAL_KM;
	}
	
	if($_GET['search_radius_'.$_SESSION['user']['secure_spam_key']]==$form_distances[$x_rad] || (!isset($_GET['search_radius_'.$_SESSION['user']['secure_spam_key']]) && DEFAULT_DISTANCE_SEARCH==$form_distances[$x_rad])) {
		 $form_distances_select=" selected";
		 if($form_distances[$x_rad]!=0) $search_criteria_string.=TEXT_GLOBAL_RADIUS.': '.$form_distances[$x_rad]." ".TEXT_GLOBAL_KM;
		 
	}
	
	$search_sidebar.='<option'.$form_distances_select.' value="'.$form_distances[$x_rad].'">'.$form_distances_text.'</option>';
}

$button_detailsearch='<a class="btn btn-sm btn-default form-control" href="#SITE_URL#advancedsearch/">'.TEXT_SEARCH_DETAIL_HEADER.'</a>';

if($_SESSION['logged_in'] == 0) { 
    $button_detailsearch='<a data-toggle="modal" data-target="#register_modal" class="btn btn-sm btn-default  form-control" >'.TEXT_SEARCH_DETAIL_HEADER.'</a>';
}

$search_sidebar.='</select>
    <hr>
    <br>
    <!--<input id="search_city_preview" name="search_city_preview_'.$_SESSION['user']['secure_spam_key'].'" type="hidden"  />-->
        <input id="search_city_id" name="search_city_id_'.$_SESSION['user']['secure_spam_key'].'" type="hidden" value="'.$_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']].'"  />
        <input id="search_action" name="search_action_'.$_SESSION['user']['secure_spam_key'].'" type="hidden" value="search.do"  />
        <input type="submit" value="'.TEXT_GLOBAL_SEARCH.'..." class="btn btn-sm btn-primary form-control">
        </form><br><br>
        '.$button_detailsearch.'
    </li>
      </ul>
    ';

if($_GET['search_action_'.$_SESSION['user']['secure_spam_key']]==="search.do" 
   || isset($_GET['page'])) {
	
    $header_button_detailsearch='<a href="#SITE_URL#advancedsearch/">['.TEXT_SEARCH_DETAIL_HEADER.']</a>';

    if($_SESSION['logged_in'] == 0) { 
        $header_button_detailsearch='<a data-toggle="modal" data-target="#register_modal">['.TEXT_SEARCH_DETAIL_HEADER.']</a>';	
     }
	
    $sidebar='
        <div class="col-md-3 col-sm-12">

        <div class="side_bar">
        <ul class="list-group">
          <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
        </a> '.TEXT_SEARCH_HEADER.' <br>
        <p class="text-right small">'.$header_button_detailsearch.'</p>
        </li>
          </ul>
          '.$search_sidebar.'
        </div>
            '.build_banner('search','lg','',$selected_sport_group_id,'').'
            '.build_banner('search','md','',$selected_sport_group_id,'').'
            '.build_banner('search','sm','',$selected_sport_group_id,'').'
            '.build_banner('search','xs','',$selected_sport_group_id,'').'
        </div>
    ';

    $sub_sidebar='<div class="col-md-3 col-sm-12">
        <div class="sub_side_bar">
        '.$search_sidebar.'
        </div>
        </div>
        ';
}
else {
	$header_button_detailsearch='<a href="#SITE_URL#advancedsearch/">['.TEXT_SEARCH_DETAIL_HEADER.']</a>';

if($_SESSION['logged_in'] == 0)$header_button_detailsearch='<a data-toggle="modal" data-target="#register_modal">['.TEXT_SEARCH_DETAIL_HEADER.']</a>';
	
	
	$sidebar='
<div class="col-md-3 col-sm-12">
<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> '.TEXT_SEARCH_HEADER.' <br>
<p class="text-right small">'.$header_button_detailsearch.'</p>
</li>
  </ul>
  '.$search_sidebar.'
  
  	'.build_banner('search','sm','',$selected_sport_group_id,'').'

</div>
';
	$sub_sidebar='';
}

$offline_item="";

if($_SESSION['logged_in'] == 0) $offline_item="-offline";

if(isset($_GET['view'])) {
	$_SESSION['user']['default_view'] = $_GET['view'];
	switch ($_GET['view']) {
		  
        case ("grid"):
            $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
            $result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item".$offline_item.".html");
            $_SESSION['user']['default_view']="grid";
        break;

        case ("list"):
            $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/list.html");
            $result_item=file_get_contents(SERVER_PATH . "template/modules/search/list-item".$offline_item.".html");
            $_SESSION['user']['default_view']="list";
        break;

        default:
            $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
            $result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item".$offline_item.".html");
        break;
	
	}
}
else {
    if($_SESSION['user']['default_view'] == 'list') {
        $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/list.html");
        $result_item=file_get_contents(SERVER_PATH . "template/modules/search/list-item".$offline_item.".html");  
    }
    else {
        $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
        $result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item".$offline_item.".html");
    }
}


// TO DO:
// HIER DIE LAT LNG AUS DER SUCHE (CITY_ID) ÜBERNHEMEN

if(isset($_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']] && isset($_GET['search_country_'.$_SESSION['user']['secure_spam_key']])) {
	$geo=get_city_latlng($_GET['search_city_id_'.$_SESSION['user']['secure_spam_key']], $_GET['search_country_'.$_SESSION['user']['secure_spam_key']] );
	
	if($geo) {
        $search_lat=$geo['lat'];
        $search_lng=$geo['lng'];
	}
	else {
        $search_lat = $_SESSION['user']['user_lat'];
        $search_lng = $_SESSION['user']['user_lng'];
	}
	
}
else {
    $search_lat = $_SESSION['user']['user_lat'];
    $search_lng = $_SESSION['user']['user_lng'];
}
$search_exclude_user = $_SESSION['user']['user_id'];
$result="";

	// NAME FILTER
	if(isset($_GET['search_name_'.$_SESSION['user']['secure_spam_key']])) {
		$search_sql.=" AND ud.user_nickname LIKE '%".$_GET['search_name_'.$_SESSION['user']['secure_spam_key']]."%'";
		
	}
	
	$search_by_values = array("distance","newest","online");
	$search_by_values_to_db = array("distance" => 'DISTANCE ASC', "newest" => "u.user_id DESC", "online" => "u.user_online DESC");


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
	
	
	
if(isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_action_'.$_SESSION['user']['secure_spam_key']] == "search.do") {
	// Wenn Radius gesetzt
		if($_GET['search_radius_'.$_SESSION['user']['secure_spam_key']] > 0 && $search_lat != 0) {
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
    $total = $query->rowCount();

    //Limit ausgabe der aktuellen Seite
	$sql = $base_search_sql.$search_type_sql." ORDER BY ".$search_by." LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE;

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
    $total = $query->rowCount();

    //Limit ausgabe der aktuellen Seite
    $sql = $base_search_sql.$search_type_sql."
      ORDER BY ".$search_by." LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE."
     ";

}
 
if($total < 1) {
    $output = '<div class="col-md-9 col-sm-12 content-box-right">'.set_system_message("error", TEXT_SEARCH_NO_RESULTS);
}
else {

     $view_box='
    <ul class="pagination view_box">
    <li>
    <span><form method="get" action="'.$search_url_terms_string.$search_url_types_string.$search_url_page_string.'">
    '.TEXT_SEARCH_SORT_BY.': <select name="search_sort_'.$_SESSION['user']['secure_spam_key'].'" onChange="this.form.submit();">
    <option value="distance" '.$search_by_selected['distance'].'>'.TEXT_SEARCH_SORT_BY_DISTANCE.'</option>
    <option value="newest" '.$search_by_selected['newest'].'>'.TEXT_SEARCH_SORT_BY_NEWEST.'</option>
    <option value="online" '.$search_by_selected['online'].'>'.TEXT_SEARCH_SORT_BY_ONLINE.'</option>
    </select>
    </form></span>
    </li>
    <li><a href="?view=grid'.$search_url_terms_string.$search_url_types_string.$search_url_page_string.'" aria-label="Grid">
    <span class="glyphicon glyphicon-th" aria-hidden="true"></span></a></li><li><a href="?view=list'.$search_url_terms_string.$search_url_types_string.$search_url_page_string.'" aria-label="Grid">
    <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span></a></li></ul>
    ';

    $pagination=build_search_pagination($total,$_GET['page'],VIEW_PER_PAGE,$search_url_terms_string.$search_url_types_string,$view_box);
    $pagination_footer=build_search_pagination($total,$_GET['page'],VIEW_PER_PAGE,$search_url_terms_string.$search_url_types_string,"");

    $result.=$pagination.'
    <div class="row">';

    // Länderübergreifen suchen
    // daher ohne:
    //AND
    //ud.user_country='".$_SESSION['user']['user_country']."'
    
    $query = $DB->prepare($sql);
    $query->execute();
    $get_search = $query->fetchAll();

    foreach ($get_search as $search) {
        $search_item=$result_item; // TEMPLATE SETZEN
        $search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
        $search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
        $search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
        $search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);

        // Nur Anzeigen wenn es ungleich dem Eigenen ist.
        $search_country_prefix="";
        if($search['user_country'] != $_SESSION['user']['user_country']) {
            $search_country_prefix = strtoupper($search['user_country'])." - ";
        }
        $search_item = str_replace("#SEARCH_COUNTRY#",$search_country_prefix,$search_item);

        //$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
        $search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
        $search_item = str_replace("#SPORTS#",get_user_main_sport($search['user_id']),$search_item);

        //$search_item = str_replace("#DISTANCE#","(".round($search['DISTANCE'],2)."km)",$search_item);
        $search_item = str_replace("#DISTANCE#","",$search_item);

        $result.=$search_item;

    }

    $result.='</div>'.$pagination_footer;


    $output = '<div class="col-md-9 col-sm-12 content-box-right">
    ';

    if(!isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']])) {
        $output .= '
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12"><h4 class="search">'.TEXT_SEARCH_RESULT_STANDARD.'</h4><br>
                        </div>
                    </div>';
    }

    if(isset($_GET['search_action_'.$_SESSION['user']['secure_spam_key']]) && $_GET['search_action_'.$_SESSION['user']['secure_spam_key']]==="search.do") {

        $result='
                <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12"><h4 class="search">'.TEXT_SEARCH_RESULT_UNSORTED.'</h4><br>
                </div>
                </div>'.$result;

        if(((is_array($_GET['search_type_'.$_SESSION['user']['secure_spam_key']]) 
                && count($_GET['search_type_'.$_SESSION['user']['secure_spam_key']]) > 1)
            || ($_GET['search_type_all_'.$_SESSION['user']['secure_spam_key']]==1))
        ){
//&& $_GET['page'] == 0
            $output .= '
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12"><h4 class="search">'.TEXT_SEARCH_RESULT_BY_GROUPS.'</h4><br>
                            </div>
                        </div>';

            foreach ($_GET['search_type_'.$_SESSION['user']['secure_spam_key']] as $search_type) {

                $sub_query_type_sql=" AND u.user_type='".$search_type."'";
                $total_subquery=get_total_subquery($base_search_sql.$sub_query_type_sql);

                if($total_subquery > 0) {

                    $subquery_preview=get_subquery_preview($base_search_sql.$sub_query_type_sql, SEARCH_MAX_GROUPS_PREVIEW);


                    $output .= '
                    <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                            <a href="#SITE_URL#search/?search_type_'.$_SESSION['user']['secure_spam_key'].'[]='.$search_type.$search_url_terms_string.'" class="thumbnail thumbnail-line"><div class="row"><div class="grid_line col-xs-10 col-sm-10 col-md-5">
                            <strong>'.$total_subquery.' '.constant('TEXT_SEARCH_USER_TYPE_'.$search_type).' </strong><br>
                    <small>'.$search_criteria_string.'</small>
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

                    <div class="col-xs-12 col-sm-12 col-md-12">';

                    $output .= '</div> </div>';

                }

            }

        }
            else {

            // return an array of sportgroups ordered by maximum user in group
            $sub_query_type_sql=" AND u.user_type='".$_GET['search_type_'.$_SESSION['user']['secure_spam_key']][0]."'";

            $sub_sports=get_subquery_sportgroups($base_search_sql.$sub_query_type_sql,0,SEARCH_MAX_GROUPS);

            if(count($sub_sports) > 1) {

                $output .= '
                        <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12"><h4 class="search">'.TEXT_SEARCH_RESULT_BY_GROUPS.'</h4><br>
        </div>
                        </div>';

                foreach($sub_sports as $sub_sport) {
                    $sub_query_sport_sql=" AND utsgv.sport_group_id = '".$sub_sport['sport_group_id']."' ";
                    $subquery_preview=get_subquery_preview($base_search_sql.$sub_query_type_sql.$sub_query_sport_sql, 5);


                    $output .= '
                        <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                                <a href="#SITE_URL#search/?search_single_sport_group_'.$_SESSION['user']['secure_spam_key'].'='.$sub_sport['sport_group_id'].$search_url_terms_string.$search_url_types_string.'" class="thumbnail thumbnail-line"><div class="row"><div class="grid_line col-xs-10 col-sm-10 col-md-5">
                                <strong>'.$sub_sport['ANZAHL'].' '.$sub_sport['sport_group_name'].' </strong><br>
                        <small>'.$search_criteria_string.'</small>
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
                        </div>

                        </div>';
                }
                $output .= '<div class="row" id="showMoreDiv'.$_SESSION['search_load_more'].'">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                    <a class="moreGroupsLoader thumbnail thumbnail-line-more">
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
                        <div id="moreGroupsContent"></div>';
            }		
        }
    }
}

$output.=$result.'</div><div class="clearfix"></div>';

// FOOTER JS ...
		
$header_ext = '
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">';

$footer_ext = ' 

    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="#SITE_URL#js/search_sport_subgroup.js"></script>

      <script>
        jQuery(document).ready(function(){

        var update_geo = function () {
            if ($("#search_country").find( "option" ).filter( ":selected" ).attr( "value" ) != "") {
                $("#search_radius_'.$_SESSION['user']['secure_spam_key'].'").prop("disabled", false);
                $("#search_zipcode").prop("disabled", false);
            }
            else {
                $("#search_radius_'.$_SESSION['user']['secure_spam_key'].'").prop("disabled", "disabled");
                $("#search_zipcode").prop("disabled", "disabled");
            }
        };


        $(".moreGroupsLoader").click(function(){
            $("#moreGroupsContent").load("#SITE_URL#js/ajax_source/search_group_after.php", '.json_encode($_GET).');
            $("#showMoreDiv'.$_SESSION['search_load_more'].'").hide();
        });

        $(update_geo);
        $("#search_country").change(update_geo);

        $(function () {
        var select = $( "#search_country" ),
            options = select.find( "option" ),
            address = $( "#search_zipcode" );

        var selectType = options.filter( ":selected" ).attr( "value" );

        address.autocomplete({
            source: \'#SITE_URL#js/ajax_source/zipformquery/\' + selectType + \'/\',
            minLength: 3,
            select:function(evt, ui)
            {
                // when a zipcode is selected, populate related fields in this form
                /*this.form.search_city_preview.value = ui.item.label;*/
                this.form.search_city_id.value = ui.item.city_id;

            }
        });

        select.change(function () {

            selectType = options.filter( ":selected" ).attr( "value" );
            address.autocomplete( "option", "source", \'#SITE_URL#js/ajax_source/zipformquery/\' + selectType + \'/\');
        });
    });


        });

        $("#search_type_all").change(function () {
        $(".search_type_checkbox").prop(\'checked\', $(this).prop("checked"));
    });

    $(".search_type_checkbox").change(function () {
        $("#search_type_all").removeAttr("checked");
    });

	</script>';


$content_output = array('TITLE' => 'Suche - '.SITE_NAME, 'META_DESCRIPTION' => '', 'CONTENT' => $sidebar.$output.$sub_sidebar, 'HEADER_EXT' => $header_ext, 'FOOTER_EXT' => $footer_ext);