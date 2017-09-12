<?php
if(isset($_POST['register_step'])) {
	if($_POST['register_step'] < $_SESSION['register_step']) {
		$_SESSION['register_latest_step'] = $_SESSION['register_step'];
		$_SESSION['register_step'] = $_POST['register_step'];
	}
	else 	
	$_SESSION['register_step'] = $_POST['register_step'];
	switch ($_SESSION['register_step']) {

		case 1:
		if($_POST['register_type']) {
			$_SESSION['register_type'] = $_POST['register_type'];
			$_SESSION['register_step'] = 2;
		}
		break;


		case 2:
		$_SESSION['system_message'] = '';
		$register_error=0;
		$_SESSION['register_step_2'] = 0;
        if($_POST['register_step_back'] == 2) {
            $register_error=1;
        }
		
		
		if($_POST['register_nickname'] != '') {
			if(check_user_nickname($_POST['register_nickname']) == true) {
					$nick_error=TEXT_REGISTER_ERROR_YOUR_NICKNAME_EXISTS;
					if($_SESSION['register_type'] == 3) $nick_error=TEXT_REGISTER_ERROR_YOUR_CLUBNAME_EXISTS;
					if($_SESSION['register_type'] == 4) $nick_error=TEXT_REGISTER_ERROR_YOUR_LOCATIONNAME_EXISTS;	
						
					$_SESSION['system_message'] .= set_system_message('error', $nick_error);
					$register_error=1;
				}
            if(strlen($_POST['register_nickname']) < 3) {
                $nick_short_error=TEXT_REGISTER_ERROR_YOUR_NICKNAME_SHORT;
                if($_SESSION['register_type'] == 3) $nick_short_error=TEXT_REGISTER_ERROR_YOUR_CLUBNAME_SHORT;
                if($_SESSION['register_type'] == 4) $nick_short_error=TEXT_REGISTER_ERROR_YOUR_LOCATIONNAME_SHORT;	

                $_SESSION['system_message'] .= set_system_message('error', $nick_short_error);
                $register_error=1;
            }
							
			$_SESSION['register_nickname'] = $_POST['register_nickname'];
		}
		else {
            if(!$_SESSION['register_nickname']) {
                $nick_error=TEXT_REGISTER_ERROR_YOUR_NICKNAME_MISSING;
                if($_SESSION['register_type'] == 3) $nick_error=TEXT_REGISTER_ERROR_YOUR_CLUBNAME_MISSING;
                if($_SESSION['register_type'] == 4) $nick_error=TEXT_REGISTER_ERROR_YOUR_LOCATIONNAME_MISSING;

                $_SESSION['system_message'] .= set_system_message('error', $nick_error);
                $register_error=1;
            }
		}
		
		if($_POST['register_password'] != '') {
			if(strlen($_POST['register_password']) < PW_LENGTH) {
				$_SESSION['register_password'] = $_POST['register_password'];
				$_SESSION['register_password_repeat'] = $_POST['register_password_repeat'];
				$_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_YOUR_PASSWORD_LENGHT);
				$register_error=1;
			}
			else if($_POST['register_password_repeat'] == $_POST['register_password']) {
				$_SESSION['register_password'] = $_POST['register_password'];
				$_SESSION['register_password_repeat'] = $_POST['register_password_repeat'];
			}
			else {
				$_SESSION['register_password'] = $_POST['register_password'];
				$_SESSION['register_password_repeat'] = $_POST['register_password_repeat'];
				$_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_YOUR_PASSWORD_MATCH);
				$register_error=1;
			}
		}
		else {
            if(!$_SESSION['register_password'] || !$_SESSION['register_password_repeat']) {
                $_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_YOUR_PASSWORD_MISSING);
                $register_error=1;
            }
		}
		
		if($_POST['register_email'] != '') {
		
            if(check_email($_POST['register_email'])==true) {
                if(check_user_email($_POST['register_email']) == true)
                {
                $_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_YOUR_EMAIL_EXISTS);
                $register_error=1;
                }
                $_SESSION['register_email'] = $_POST['register_email'];
            }
            else {
                $_SESSION['register_email'] = $_POST['register_email'];
                $_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_YOUR_EMAIL_INVALID);
                $register_error=1;
            }
		}
		else {
            if(!$_SESSION['register_email'] || check_email($_SESSION['register_email']) == false)
			$_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_YOUR_EMAIL_INVALID);
			$register_error=1;
		}
		
		if($register_error == 0) {
			$_SESSION['register_step_2'] =1;
			$_SESSION['register_step'] = 3;
		}
		
		break;
		
		
		// PERSÖNLCHE DATEN
		case 3:
		$_SESSION['system_message'] = '';
		$register_error=0;
		$_SESSION['register_step_3'] = 0;
            
        if($_POST['register_step_back'] == 3) {
            $register_error=1;
        }
		
		if($_POST['register_firstname'] != '') {
			$_SESSION['register_firstname'] = $_POST['register_firstname'];
		}
		else {
            if(!$_SESSION['register_firstname']) {
                $_SESSION['system_message'] .= set_system_message('error', "Bitte gib deinen Vornamem ein.");
                $register_error=1;
            }
		}
		
		if($_POST['register_lastname'] != '') {
			$_SESSION['register_lastname'] = $_POST['register_lastname'];
		}
		else {
            if(!$_SESSION['register_lastname']) {
                $_SESSION['system_message'] .= set_system_message('error', "Bitte gib Nachnamen ein.");
                $register_error=1;
            }
		}
		
		if($_POST['register_gender'] != '' ) {
			$_SESSION['register_gender'] = $_POST['register_gender'];
		}
		else if($_SESSION['register_type']!=3 && $_SESSION['register_type'] != 4) {
            if($_SESSION['register_gender'] == "") {
                $_SESSION['system_message'] .= set_system_message('error',"Bitte wähle dein Geschlecht.");
                $register_error=1;
            }
		}      
		
		if($_POST['register_dob'] != '') {
			if(strtotime($_POST['register_dob'])) {
				if(strtotime($_POST['register_dob']) < strtotime("- " .REGISTER_MIN_YEARS . "years")) {
				$_SESSION['register_dob'] = date("d.m.Y",strtotime($_POST['register_dob']));
				}
				else {
                    $_SESSION['register_dob'] = date("d.m.Y",strtotime($_POST['register_dob']));
                    if($_SESSION['register_type'] < 3) {
					$_SESSION['system_message'] .= set_system_message('error', "Du musst mindestens 7 Jahre alt sein. Minderjährige sollten diese Seite zusammen mit dem Erziehungsberechtigten nutzen.");
                    $register_error=1;
                    }
				}

			}
			else {
                    $_SESSION['system_message'] .= set_system_message('error',"Bitte gib dein Geburtstag im richtigen Format ein. TT.MM.JJJJ");
                    $register_error=1;
			}
		}
		else {
            if(!$_SESSION['register_dob']) {
                $_SESSION['system_message'] .= set_system_message('error',"Bitte gib dein Geburtstag ein.");
                $register_error=1;
            }
		}
		

        if($_POST['register_country'] !== "0" && isset($_POST['register_country'])) {
			$_SESSION['register_country'] = $_POST['register_country'];
		}
		else {
            if(!$_SESSION['register_country'] || $_SESSION['register_country'] === "0") {
                $_SESSION['system_message'] .= set_system_message('error', "Bitte wählen Sie Ihr Land. " . $_POST['register_country']);
                $register_error=1;
            }
		}
		
		if($_POST['geo_register'] != '') {
			$_SESSION['geo_register'] = $_POST['geo_register'];
			
			if($_POST['geo_place_id'] != '' && $_POST['geo_place_id'] != 'undefined') {
				$_SESSION['geo_place_id'] = $_POST['geo_place_id'];
                if(geo_locate_check($_SESSION['geo_place_id']) == false) {
                    $_SESSION['system_message'] .= set_system_message('error',"geo_locate_check failed, google service unavailable");
					$register_error=1;
                }
			}
			else {
                $_SESSION['geo_place_id'] = geo_locate_by_input( get_country_name( $_POST['register_country'] ) . ',' . $_POST['geo_register']);

				if(!$_SESSION['geo_place_id']) {
					$_SESSION['system_message'] .= set_system_message('error',"Stadt/Ort Konnte nicht ermittelt werden. Bitte prüfe noch mal dein Land und deine Postleitzahl");
					$register_error=1;
				}
			}
		}
		else {
            if(!$_SESSION['geo_place_id']) {
                $_SESSION['system_message'] .= set_system_message('error',"Bitte gib eine Postleitzahl oder deinen Standort ein.");
                $register_error=1;
            }
		}
		
		if($register_error == 0) {
			$_SESSION['register_step_3'] = 1;
			$_SESSION['register_step'] = 4;
		}
		
		break;
		
		
		// Sportart
		case 4:
		
		
		$_SESSION['system_message'] = '';
		$register_error=0;
		$_SESSION['register_step_4'] = 0;
            
        if($_POST['register_step_back'] == 4){
            $register_error=1;
        }
		
		if($_POST['sport_groups'] != '') {
			$_SESSION['sport_groups'] = $_POST['sport_groups'];
			$_SESSION['sport_groups_1'] = $_POST['sport_groups_1'];
			$_SESSION['sport_groups_2'] = $_POST['sport_groups_2'];
			$_SESSION['sport_groups_3'] = $_POST['sport_groups_3'];
		}
		else {
			$_SESSION['system_message'] .= set_system_message('error',TEXT_REGISTER_ERROR_CHOOSE_SPORTS);
			$register_error=1;
		}
			
		if($_POST['sport_groups_profession'] != '') {
			$_SESSION['sport_groups_profession'] = $_POST['sport_groups_profession'];
		}
		else {
            if(!$_SESSION['sport_groups_profession']) {
			     $_SESSION['system_message'] .= set_system_message('error',constant('TEXT_REGISTER_ERROR_CHOOSE_PROFESSION_'.$_SESSION['register_type']));
				$register_error=1;
            }
		}
			
			
		if($_SESSION['register_type']!=3 && $_SESSION['register_type']!=4) {	
			if($_POST['sport_groups_status'] != '') {
				$_SESSION['sport_groups_status'] = $_POST['sport_groups_status'];
			}
			else {
                if(!$_SESSION['sport_groups_status']) {
				    $_SESSION['system_message'] .= set_system_message('error',TEXT_REGISTER_ERROR_CHOOSE_STATUS);
				    $register_error=1;
                }
			}
		}
		
		$_SESSION['sport_groups_handycap'] = $_POST['sport_groups_handycap'];
		
		if($register_error == 0) {
			$_SESSION['register_step_4'] = 1;
			$_SESSION['register_step'] = 5;
		}
		
		
		break;
		
		
		// Abschluss
		case 5:
		
            $_SESSION['system_message'] = '';
            $register_error=0;
            $_SESSION['register_step_5'] = 0;

            if($_POST['register_terms'] != '') {
                $_SESSION['register_terms'] = $_POST['register_terms'];
            }
            else {
                $_SESSION['system_message'] .= set_system_message('error',TEXT_REGISTER_ERROR_TERMS);
                $register_error=1;
            }


            if($_POST['register_privacy'] != '') {
                $_SESSION['register_privacy'] = $_POST['register_privacy'];
            }
            else {
                $_SESSION['system_message'] .= set_system_message('error',TEXT_REGISTER_ERROR_PRIVACY);
                $register_error=1;
            }

            if($register_error == 0) { 
                $auth_key=md5($_SESSION['register_password'] . $_SESSION['register_email'] . $_SESSION['register_type'].time().rand(0,999));

                $sql = "INSERT INTO `user` (`user_password`, `user_email`, `user_type`, `user_status`, `user_auth_key`, user_register_date) VALUES ('".md5($_SESSION['register_password'])."', '".$_SESSION['register_email']."', '".$_SESSION['register_type']."', 0, '".$auth_key."', NOW())";

                $query = $DB->prepare($sql);
                $query->execute();

                $user_id = $DB->lastInsertId();

                if($user_id > 0) {	
                    $profile_geo=get_city_latlng($_SESSION['register_city_id'],$_SESSION['register_country']);

                    $sql = "INSERT INTO `user_details` (`user_id`, `user_nickname`, `user_firstname`, `user_lastname`, `user_dob`, `user_gender`, user_country, `user_geo_city_id`) VALUES ('".$user_id."', '".$_SESSION['register_nickname']."', '".$_SESSION['register_firstname']."', '".$_SESSION['register_lastname']."', '".date("Y-m-d",strtotime($_SESSION['register_dob']))."', '".$_SESSION['register_gender']."', '".$_SESSION['register_country']."','".$_SESSION['geo_place_id']."');";
                    $query = $DB->prepare($sql);
                    $query->execute();
                    $sport_group_value=0;
                    for($sg_value=3; $sg_value >= 1; $sg_value--) {
                        if($_SESSION['sport_groups_'.$sg_value] != '') {
                            $sport_group_value = $_SESSION['sport_groups_'.$sg_value];
                            break;
                        }
                    }

                    $sql = "INSERT INTO `user_to_sport_group_value` (`user_id`, `sport_group_id`, `sport_group_value_id`, sport_group_profession, sport_group_handycap, sport_group_in_club) VALUES ('".$user_id."', '".$_SESSION['sport_groups']."', '".$sport_group_value."', '".$_SESSION['sport_groups_profession']."', '".$_SESSION['sport_groups_handycap']."', '".$_SESSION['sport_groups_status']."')";
                    $query = $DB->prepare($sql);
                    $query->execute();

                    $sql = "INSERT INTO `user_profile` (user_id) VALUES ('".$user_id."')";
                    $query = $DB->prepare($sql);
                    $query->execute();

                    // SET DEFAULT USER SETTINGS
                    $sql = str_replace("#USERID#", $user_id, DEFAULT_SETTINGS_SQL);
                    $query = $DB->prepare($sql);
                    $query->execute();

                    //require(PHP_MAILER_CLASS);
                    $email_content_array = array(
                        "NAME" => $_SESSION['register_firstname'],
                        "AUTHKEY" => $auth_key
                        );
                    $email_content_template=email_content_to_template("register",$email_content_array,'');
                    $alt_content='';

                    if(sending_email($_SESSION['register_email'],$_SESSION['register_firstname'],"Registrierung auf ".SITE_NAME,$email_content_template,$alt_content,0) == true) {
                        $_SESSION['register_complete'] = 1;
                        build_history_log($user_id,"register");
                    }
                    else {
                        $_SESSION['system_message'] .= set_system_message('error', TEXT_REGISTER_ERROR_SENDING_MAIL);
                        $register_error=1;
                    }

                }
                else {
                    $_SESSION['system_message'] .= set_system_message('error',TEXT_REGISTER_ERROR_CREATING_ACCOUNT);
                    $register_error=1;
                }
            }
		
		break;
    }
}