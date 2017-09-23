<?php

if(isset($_POST['update_address']) && $_POST['update_address']==1) {
	
	$geo_place_id=geo_locate_by_input($_POST['profile_address']);
	//$geo_city=get_city_id_by_lat_lng($geo['lat'] , $geo['lng'] , $_SESSION['user']['user_country']);

	if($geo_place_id) {
        $sql = "UPDATE user_details SET user_address='" . $_POST['profile_address']."', user_geo_city_id='" . $geo_place_id ."' WHERE user_id = '" . $_SESSION['user']['user_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
        $_SESSION['user']['user_geo_city_id'] = $geo_place_id;
        $_SESSION['user']['user_address'] = $_POST['profile_address'];
        /*$_SESSION['user']['user_zipcode'] = $geo_city['postalcode'];
        $_SESSION['user']['user_city_id'] = $geo_city['city_id'];
        $_SESSION['user']['user_lng'] = $geo['lng'];
        $_SESSION['user']['user_lat'] = $geo['lat'];*/
        $_SESSION['system_temp_message'] .= set_system_message("success", TEXT_PROFILE_GEO_RELOCATE_SUCCESS);
        header("Location: ".SITE_URL."me/profile/default/");
        die();
	}
	else {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_GEO_RELOCATE_ERROR);
	}
}

if(isset($_POST['update_password']) && $_POST['update_password']==1) {
	
    if($_POST['profile_password'] != '') {

        if(strlen($_POST['profile_password']) < PW_LENGTH) {
            $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_PASSWORD_LENGHT);
        }

        else if($_POST['profile_password_repeat'] == $_POST['profile_password']) {

            $sql = "UPDATE user SET user_password = '".md5($_POST['profile_password'])."' WHERE user_id = '" . $_SESSION['user']['user_id']."'";
            $query = $DB->prepare($sql);
            $query->execute();

            $_SESSION['system_temp_message'] .= set_system_message("succes", TEXT_PROFILE_CHANGE_PASSWORD_SUCCESS);
            header("Location: ".SITE_URL."me/profile/default/");
            die();

        }
        else {
            $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_PASSWORD_MATCH);
        }
    }
    else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_PASSWORD_MISSING);
    }
}

if(isset($_POST['update_email']) && $_POST['update_email']==1) {
	if($_POST['profile_email'] != '') {
		
        if(check_email($_POST['profile_email'])==true) {
            if(check_user_email($_POST['profile_email']) == true) {
            $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_EMAIL_EXISTS);
            }
            else {
                $sql = "UPDATE user SET user_email = '" . $_POST['profile_email']."' WHERE user_id = '" . $_SESSION['user']['user_id']."'";
                $query = $DB->prepare($sql);
                $query->execute();

                $_SESSION['system_temp_message'] .= set_system_message("succes", TEXT_PROFILE_CHANGE_EMAIL_SUCCESS);
                header("Location: ".SITE_URL."me/profile/default/");
                die();
            }
        }
        else {
            $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_EMAIL_INVALID);
        }
    }
    else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_EMAIL_INVALID);
    }
}


if(isset($_POST['update_profile']) && $_POST['update_profile']==1) {
	$profile_firstname = $_POST['profile_firstname'];
	if($profile_firstname=="") {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_FIRSTNAME);
        $form_error=1;
	}
	
	$profile_lastname = $_POST['profile_lastname'];
	if($profile_lastname=="") {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_NAME);
        $form_error=1;
	}

	$profile_gender = $_POST['profile_gender'];
	if($profile_gender==""  && ($_SESSION['user']['user_type'] == 2 || $_SESSION['user']['user_type'] ==1)) {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_GENDER);
        $form_error=1;
	}
	
	
	
	$profile_dob = $_POST['profile_dob'];
	if($profile_dob=="") {
        $_SESSION['system_message'] .= set_system_message("error", constant('TEXT_PROFILE_PROFILE_ERROR_DOB_' . $_SESSION['user']['user_type']));
        $form_error=1;
	}
	
	
	$profile_country = $_POST['profile_country'];
	if($profile_country=="") {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_COUNTRY);
        $form_error=1;
	}
	
	$profile_geo = $_POST['profile_geo'];
	if($profile_geo=="") {
	$_SESSION['system_message'] .= set_system_message("error", TEXT_PROFILE_PROFILE_ERROR_ZIPCODE);
	$form_error=1;
	}
	
    
	
	if($form_error == 0) {
		
        $profile_place_id = geo_locate_by_input( get_country_name( $profile_country ) . ',' . $profile_geo);
		
		$sql = "UPDATE `user_details` SET `user_firstname`='" . $profile_firstname."', `user_lastname`='" . $profile_lastname."', `user_dob`='".date("Y-m-d",strtotime($profile_dob))."', `user_gender`='" . $profile_gender."', `user_country`='" . $profile_country."', user_geo_city_id='" . $profile_place_id ."'  WHERE  `user_id`='" . $_SESSION['user']['user_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
		
		$_SESSION['system_message'] .= set_system_message("success",TEXT_PROFILE_PROFILE_UPDATE_SUCCESS);
		
		$_SESSION['user']['user_firstname'] = $profile_firstname;
		$_SESSION['user']['user_lastname'] = $profile_lastname;
		$_SESSION['user']['user_gender'] = $profile_gender;
		$_SESSION['user']['user_dob'] = $profile_dob;
		$_SESSION['user']['user_country'] = $profile_country;
        $_SESSION['user']['user_geo_city_id'] = $profile_place_id;
	}
}
else {
	$profile_firstname = $_SESSION['user']['user_firstname'];
	$profile_lastname = $_SESSION['user']['user_lastname'];
	$profile_gender = $_SESSION['user']['user_gender'];
	$profile_dob = $_SESSION['user']['user_dob'];
	$profile_country = $_SESSION['user']['user_country'];
}


$output = '<div class="col-md-9 col-sm-12 content-box-right">
<h4 class="profile">' . TEXT_PROFILE_EDIT_PROFILE . ' </h4><br>
		<form method="post" action="#SITE_URL#me/profile/default/" id="my_profile"  class="form">
        <div class="text-center">

		
		
		<div class="row form_line">
	
		<div class="col-md-6">
        	<label>' . TEXT_PROFILE_PROFILE_FIRSTNAME . ':</label>
		</div>
		<div class="col-md-6">
			<input type="text" name="profile_firstname" value="' . $profile_firstname . '" required class="form-control">
		</div>
		</div>
		
		<div class="row form_line">
	
			<div class="col-md-6">
				<label>' . TEXT_PROFILE_PROFILE_LASTNAME . ':</label></div>
			<div class="col-md-6">
				<input type="text" name="profile_lastname" value="' . $profile_lastname . '" required class="form-control">
			</div>
		</div>
		
		<div class="row form_line">
	
		<div class="col-md-12"><small>' . TEXT_PROFILE_PROFILE_NAME_HIDE . '</small></div>
		</div>
	
				<div class="row form_line">
	
		<div class="col-md-6">
        	<label>'.constant('TEXT_PROFILE_PROFILE_NICKNAME_' . $_SESSION['user']['user_type']) . ':</label>
		</div>
		<div class="col-md-6">
			<input type="text" value="' . $_SESSION['user']['user_nickname'] . '" disabled class="form-control">
		</div>
		</div>';
		
		if($_SESSION['user']['user_type'] == 2 || $_SESSION['user']['user_type'] ==1) {
			
            $output .= '<div class="row form_line">

            <div class="col-md-6">
    <label>' . TEXT_PROFILE_PROFILE_GENDER . ':</label>
    </div>
    <div class="col-md-6"><select name="profile_gender" class="form-control">
            <option value="">' . TEXT_GLOBAL_PLEASE_CHOOSE . '</option>
            <option value="f"';
            if($profile_gender=="f") {
                $output .= ' selected="selected"';
            }

            $output .= '>' . TEXT_GLOBAL_GENDER_FEMALE . '</option>
            <option value="m"';
            if($profile_gender=="m") {
                $output .= ' selected="selected"';
            }

            $output .= '>' . TEXT_GLOBAL_GENDER_MALE . '</option>
            </select></div>
            </div>';
		
		}
		
$output .= '<div class="row form_line">
	
			<div class="col-md-6">
				<label>'.constant('TEXT_PROFILE_PROFILE_DOB_' . $_SESSION['user']['user_type']) . ' (' . TEXT_REGISTER_DATE_FORMAT . '):</label>
			</div>
			<div class="col-md-6">
				<div class="input-group date">
				  <input type="text" name="profile_dob" value="'.date("d.m.Y",strtotime($profile_dob)) . '" class="form-control" required><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
				</div>
			</div>
		</div>
		
		<div class="row form_line">
	
		<div class="col-md-6">
<label>' . TEXT_GLOBAL_COUNTRY . ':</label>
</div>
<div class="col-md-6"><select name="profile_country" id="profile_country" class="form-control">
        <option value="">' . TEXT_GLOBAL_PLEASE_CHOOSE . '</option>
		';
		
$sql = "SELECT 
                    c.*, 
                    t.country_name AS translatedName
                FROM geo_countries c
                    LEFT JOIN geo_countries_translation t ON c.country_code = t.country_code AND t.language_code = '" . $_SESSION['language_code'] . "'
                    GROUP BY c.country_code
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

$output .= '</select></div>
</div>

		
		<div class="row form_line">
	
				<div class="col-md-6">
					<label>' . TEXT_SEARCH_LOCATION . ':</label>
				</div>
				<div class="col-md-6">
                
                
                
				<input type="text" id="profile_geo" name="profile_geo" value="'.$profile_geo.'"  class="form-control">
                
                <input type="hidden" id="profile_place_id" name="profile_place_id" value="'.$profile_place_id.'">

				</div>
			</div>
			
		</div>
       
      <div class="row form_line">
				<div class="col-md-6">
				</div>
				<div class="col-md-6">
				<input type="hidden" name="update_profile" value="1">
				<input type="submit" value="' . TEXT_GLOBAL_SAVE . '" class="btn btn-sm btn-primary form-control">
				</div>
		</div>
		
        </form>
		';
		
		
		$output .= '<hr><br>
		<h4 class="profile">' . TEXT_PROFILE_MANAGE_PROFILES_HEADER . '</h4><br>
		<a href="#SITE_URL#me/settings/profiles/" class="btn btn-sm btn-default">' . TEXT_PROFILES_ADD_NEW . '</a>
		<a href="#SITE_URL#me/settings/profiles/" class="btn btn-sm btn-default">' . TEXT_PROFILES_SWITCH . '</a>
		<a href="#SITE_URL#me/settings/profiles/" class="btn btn-sm btn-default">' . TEXT_PROFILES_MANAGE . '</a>';
		
		
if($_SESSION['user']['user_type'] == 3 || $_SESSION['user']['user_type'] ==4) {
    // GEO GOOGLE
    $profil_address = $_SESSION['user']['user_address'];
    if(isset($_POST['profile_address'])) $profil_address = $_POST['profile_address'];
				
		
    $output .= '<br>
<hr><br>

		<h4 class="profile">' . TEXT_PROFILE_GEO_RELOCATE_HEADER . '</h4>
		<form method="post" action="#SITE_URL#me/profile/default/" id="my_profile"  class="form">
<div class="row form_line">
	
			<div class="col-md-12">
		' . TEXT_PROFILE_GEO_RELOCATE_INFO . '
</div>
</div>
		<div class="row form_line">
	
			<div class="col-md-8">
		
				<label>' . TEXT_PROFILE_GEO_RELOCATE_ADDRESS . ':</label>

				<input type="text" name="profile_address" value="' . $profil_address . '" required class="form-control">
			</div>
			
			<div class="col-md-4"><br>

			<input type="hidden" name="update_address" value="1">
				<input type="submit" value="' . TEXT_GLOBAL_UPDATE . '" class="btn btn-sm btn-primary form-control">
			</div>
		</div>';
		

 $output .= '
		 		<div class="row form_line">
		 			<div class="col-md-12 col-sm-12">
						 <div id="container-fluid">
							<div id="cd-google-map">
								<div id="google-container" style="height:200px; width:100%; background-color:#CCC;">
								</div>
							</div>
						  </div>
					 </div>
				</div>
			
        </form>
		';
		
}
		
		// Passwort
if($_SESSION['user']['user_sub_of'] > 0) {
	$output .= '<hr><br>
		<h4 class="profile">' . TEXT_PROFILE_CHANGE_PASSWORD_HEADER . '</h4><br>
'.set_system_message("error", TEXT_SWITCH_TO_USE) . '<br>';
}
else {		
    $output .= '<br>
        <hr><br>
		<h4 class="profile">' . TEXT_PROFILE_CHANGE_PASSWORD_HEADER . '</h4>
		<form method="post" action="#SITE_URL#me/profile/default/" id="my_profile"  class="form">
		<div class="row form_line">
	
			<div class="col-md-6">
				<label>' . TEXT_PROFILE_CHANGE_PASSWORD_NEW . ':</label>
			</div>
			<div class="col-md-6">
				<input type="password" name="profile_password" value="' . $_POST['profile_password'] . '" required class="form-control">
			</div>
		</div>
		
		<div class="row form_line">
	
			<div class="col-md-6">
				<label>' . TEXT_PROFILE_CHANGE_PASSWORD_REPEAT . ':</label></div>
			<div class="col-md-6">
				<input type="password" name="profile_password_repeat" value="' . $_POST['profile_password_repeat'] . '" required class="form-control">
			</div>
		</div>
		
		      
      <div class="row form_line">
				<div class="col-md-6">
				</div>
				<div class="col-md-6">
				<input type="hidden" name="update_password" value="1">
				<input type="submit" value="' . TEXT_GLOBAL_UPDATE . '" class="btn btn-sm btn-primary form-control">
				</div>
		</div>
		
        </form>
		';
}
		
		// Emailadresse
if($_SESSION['user']['user_sub_of'] > 0) {
	$output .= '<hr><br>
		<h4 class="profile">' . TEXT_PROFILE_CHANGE_EMAIL_HEADER . '</h4><br>
'.set_system_message("error", TEXT_SWITCH_TO_USE) . '<br>';
}
else {
		$output .= '<br>
<hr><br>
		<h4 class="profile">' . TEXT_PROFILE_CHANGE_EMAIL_HEADER . '</h4>
		<form method="post" action="#SITE_URL#me/profile/default/" id="my_profile"  class="form">
		<div class="row form_line">
	
			<div class="col-md-6">
				<label>' . TEXT_PROFILE_CHANGE_EMAIL_NEW . ':</label><br>
	' . TEXT_PROFILE_CHANGE_EMAIL_INFO . '
			</div>
			<div class="col-md-6">
				<input type="text" name="profile_email" value="' . $_SESSION['user']['user_email'] . '" required class="form-control">
			</div>
		</div>
		      
			<div class="row form_line">
					<div class="col-md-6">
					</div>
					<div class="col-md-6">
						<input type="hidden" name="update_email" value="1">
						<input type="submit" value="' . TEXT_GLOBAL_UPDATE . '" class="btn btn-sm btn-primary form-control">
					</div>
			</div>
		
        </form>
		';
}

$output .= '</div>';
		
		
		// FOOTER JS ...
		
$header_ext = '<link rel="stylesheet" type="text/css" href="#SITE_URL#css/bootstrap-datepicker3.css">
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">';

$footer_ext = ' 

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="#SITE_URL#js/bootstrap-datepicker.js"></script>
				
  <script> $(\'#my_profile .input-group.date\').datepicker({
        format: "dd.mm.yyyy",
        endDate: "-7y",
        language: "de"
    });
	

        jQuery(document).ready(function(){

            var update_geo = function () {
                if ($("#profile_country").find( "option" ).filter( ":selected" ).attr( "value" ) != "") {
                    $("#profile_geo").prop("disabled", false);
                }
                else {
                    $("#profile_geo").prop("disabled", "disabled");
                }
            };


            $(update_geo);
            $("#profile_country").change(update_geo);

        });
	
	</script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAAUCGinXBvsdx8WHD_PdVvoXA42lakEd4"></script>
    <script src="#SITE_URL#js/geo_profile.js"></script>';

if($_SESSION['user']['user_type']>2) {
    
    $geo_place_id= geo_locate_by_input($_SESSION['user']['user_address']);
    
    $geo_data = get_place_id_data($geo_place_id);
    //var_dump($geo_data);
    $geo_lat = $geo_data['city_lat'];
    $geo_lng = $geo_data['city_lng'];
    
    $footer_ext.='

			   <script>
				/*---------------------------------------*/
				/*	GOOGLE MAP
				/*---------------------------------------*/
				jQuery(document).ready(function($) {

    "use strict";

    //google map custom marker icon - .png fallback for IE11
    var is_internetExplorer11 = navigator.userAgent.toLowerCase().indexOf(\'trident\') > -1;
    //var $marker_url = (is_internetExplorer11) ? \'#SITE_URL#images/default/marker.png\' : \'#SITE_URL#images/default/marker.svg\';
	 var $marker_url=[\'#SITE_URL#images/my_position.png\',\'#SITE_URL#images/default/marker_small.png\',\'#SITE_URL#images/default/marker.png\'];

    //we define here the style of the map
    var style = [{
        "stylers": [{
            "hue": "#00aaff"
        }, {
            "saturation": -100
        }, {
            "gamma": 2.15
        }, {
            "lightness": 12
        }]
    }];

    //set google map options
    var map_options = {
        center: new google.maps.LatLng($latitude, $longitude),
        zoom: $map_zoom,
        panControl: true,
        zoomControl: true,
        mapTypeControl: false,
        streetViewControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scrollwheel: false,
        styles: style,
    }
    //inizialize the map
    var map = new google.maps.Map(document.getElementById(\'google-container\'), map_options);
    //add a custom marker to the map	
	
	
	var marker, i, icon2;
	var infowindow = new google.maps.InfoWindow();
				
				for (i = 0; i < locations.length; i++) { 
				if(i == 0) {
				icon2 = new google.maps.MarkerImage($marker_url[locations[i][3]], null, null, new google.maps.Point(50, 50));
				}
				else
				{
				icon2 = $marker_url[locations[i][3]];
				}
				marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map,
        visible: true,
		zIndex:i,
        icon: icon2
    });
	
	/*google.maps.event.addListener(marker, \'click\', (function(marker, i) {
        return function() {
          infowindow.setContent(locations[i][0]);
          infowindow.open(map, marker);
        }
      })(marker, i));*/
    }

  
});

var locations = [[\'<div class="map_box"><strong>'.addslashes($_SESSION['user']['user_nickname']) . '</strong><br/>in 0.08km<br/><a href="#">weitere Informationen</a></div>\',' . $geo_lat . ',' . $geo_lng . ',2]];
    var $latitude = ' . $geo_lat . ', 
        $longitude = ' . $geo_lng . ',
        $map_zoom = 13;
        </script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAAUCGinXBvsdx8WHD_PdVvoXA42lakEd4"></script>';
}



$content_output = array('TITLE' => 'Profil -> ' . TEXT_PROFILE_PROFILE_DATA_HEADER, 'META_DESCRIPTION' =>'',
 'CONTENT' => $sidebar.$output,
 'HEADER_EXT' => $header_ext,
  'FOOTER_EXT' => $footer_ext);