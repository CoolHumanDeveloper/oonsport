<?php

function load_template() {
    global $DB;
    global $globalLanguage;
    global $selectLanguageValue;
    $template = file_get_contents( SERVER_PATH . "template/default.html" );

    if ( !isset( $_GET[ 'content' ] ) )$_GET[ 'content' ] = "index";
    $content = load_content( $_GET[ 'content' ] );

    foreach ( $content as $key => $value ) {
        $template = str_replace( "#" . $key . "#", $value, $template );
    }

    $template_intern_header = "";
    if ( $_SESSION[ 'logged_in' ] == 1 ) {
        $template_intern_header = '<link rel="stylesheet" type="text/css" href="#SITE_URL#css/custom_user.css">';
    }

    $template = str_replace( "#INTERN_HEADER#", $template_intern_header, $template );
    $template = str_replace( "#LOGIN_LOGOUT_NAV#", build_login_box(), $template );

    //Navigation setzten
    $navigation = build_navigation();

    foreach ( $navigation as $key => $value ) {
        $template = str_replace( "#NAVIGATION_" . $key . "#", $value, $template );
    }

    //Setztd den Spam Key in allen Forms, Wichtig : #SPAM_KEY# muss an die Namen angehängt werden z.B. <input name="loginname#SPAM_KEY#"....
    if ( !isset( $_SESSION[ 'system_message' ] ) ) {
        $_SESSION[ 'system_message' ] = '';
    }
    $template = str_replace( "#SPAM_KEY#", $_SESSION[ 'user' ][ 'secure_spam_key' ], $template );
    $template = str_replace( "#SYSTEM_MESSAGE#", $_SESSION[ 'system_message' ], $template );
    unset( $_SESSION[ 'system_message' ] );

    $cookie_template = SERVER_PATH . "template/cookie/" . $_SESSION[ 'language_code' ] . "/cookie.html";
    if ( file_exists( $cookie_template ) ) {
        $cookie_template = file_get_contents( $cookie_template );
    } else  {
        $cookie_template = SERVER_PATH . "template/cookie/en/cookie.html";
        if ( file_exists( $cookie_template ) ) {
            $cookie_template = file_get_contents( $cookie_template );
        } else{
            $cookie_template = "";
        }
    }

    if ( $_SESSION[ 'logged_in' ] == 1 ) {
        $template = str_replace( "#COUNT_MSG#", get_unreaded_messages(), $template );
        $template = str_replace( "#USER_IMAGE#", build_default_image( $_SESSION[ 'user' ][ 'user_id' ], "25x25", "img-thumbnail nav_user_image" ), $template );
        $template = str_replace( "#USER_NAME#", $_SESSION[ 'user' ][ 'user_nickname' ], $template );
        $template = str_replace( "#REGISTER_MODEL#", "", $template );
        $template = str_replace( "#GLOBAL_COOKIE#", "", $template );
        $template = str_replace( "#SUB_PROFILES#", getProfilList(), $template );
    } else {
        $template = str_replace( "#REGISTER_MODEL#", build_register_modal(), $template );
        $template = str_replace( "#GLOBAL_COOKIE#", $cookie_template, $template );
    }

    if ( DEFAULT_LANGUAGE == 'de' ) {
        $template = str_replace( "#HEADER_LANGUAGE_META#", '
        <meta http-equiv="Content-Language" content="de_DE" />
        <meta name="language" content="de" />
        <link rel="alternate" hreflang="en" href="' . EN_SITE_URL . '" /> ', $template );
    } else {
        $template = str_replace( "#HEADER_LANGUAGE_META#", '<meta http-equiv="Content-Language" content="en_US" />
		<meta name="language" content="en" />
		<link rel="alternate" hreflang="de" href="' . DE_SITE_URL . '" /> ', $template );
    }

    
    
    //Sprach Navigation
    $languageNavEntry = '';
    foreach($selectLanguageValue as $sLanguage => $sLanguageValue) {
        $languageNavEntry .= '<li><a href="#' . strtoupper($sLanguage) . '_SITE_URL#">'.$sLanguageValue.'</a></li>';
    }
    $template = str_replace( '#LANGUAGE_NAVIGATION#',$languageNavEntry, $template);
    
    
    $template = str_replace( '#ACTIVE_LANGUAGE#', $selectLanguageValue[strtolower(DEFAULT_LANGUAGE)], $template);
    
    $template = str_replace( "#SITE_URL#", SITE_URL, $template );
    
    foreach($globalLanguage as $gLanguage) {
        $template = str_replace( '#' . strtoupper($gLanguage) . '_SITE_URL#', constant(strtoupper($gLanguage) . '_SITE_URL'), $template );
    }
    
    
    
    
     
    $template = str_replace( "#DE_SITE_URL#", DE_SITE_URL, $template );
    $template = str_replace( "#SITE_INDEX_TITLE#", TEXT_SITE_INDEX_TITLE, $template );
    $template = str_replace( "#TEXT_CHOOSE_LANGUAGE#", TEXT_CHOOSE_LANGUAGE, $template );
    $template = str_replace( "#TEXT_LANGUAGE#", TEXT_LANGUAGE, $template );
    $template = str_replace( "#GOOGLE_ANALYTICS_ID#", GOOGLE_ANALYTICS_ID, $template );
    


    if ( $_GET[ 'content' ] != "index" && $_GET[ 'content' ] != "dashboard" ) {
        $template = str_replace( "#HISTORY_BACK#", TEXT_HISTORY_BACK, $template );
    } else {
        $template = str_replace( "#HISTORY_BACK#", "", $template );
    }

    return $template;
}

function load_content( $content ) {
    global $DB;
    switch ( $content ) {

        case ( "content" ):
            require( MODULE_PATH . "content.php" );
            break;

        case ( "profile_gallery" ):
            require( MODULE_PATH . "profile_gallery.php" );
            break;

        case ( "profile" ):
            require( MODULE_PATH . "profile.php" );
            break;

        case ( "dashboard" ):
            require( MODULE_PATH . "dashboard.php" );
            break;

        case ( "group" ):
            require( MODULE_PATH . "group.php" );
            break;

        case ( "shoutbox" ):
            require( MODULE_PATH . "shoutbox.php" );
            break;

        case ( "marketplace" ):
            require( MODULE_PATH . "marketplace.php" );
            break;

        case ( "marketplace_offer" ):
            require( MODULE_PATH . "marketplace_offer.php" );
            break;

        case ( "error" ):
            require( MODULE_PATH . "error.php" );
            break;

        case ( "register" ):
            require( MODULE_PATH . "register.php" );
            break;

        case ( "search" ):
            require( MODULE_PATH . "search.php" );
            break;

        case ( "advancedsearch" ):
            require( MODULE_PATH . "advancedsearch.php" );
            break;

        case ( "login" ):
            require( MODULE_PATH . "login.php" );
            break;

        case ( "reset-password" ):
            require( MODULE_PATH . "reset-password.php" );
            break;

        case ( "index" ):
            require( MODULE_PATH . "index.php" );
            break;

        case ( "me" ):
            require( MODULE_PATH . "me.php" );
            break;

        case ( "api" ):
            require( MODULE_PATH . "api.php" );
            break;

        default:
            require( MODULE_PATH . "index.php" );
            break;

    }

    return $content_output;
}

function build_navigation() {
    global $DB;
    $navigation = array();
    $sql = "SELECT * FROM navigation WHERE 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_nav_class = $query->fetchAll();

    foreach ( $get_nav_class as $nav_class ) {
        $nav = "<ul class=\"" . $nav_class[ 'navigation_class' ] . "\">";

        $sql = "SELECT content_url,content_title,content_type FROM navigation n, content c, content_details cd WHERE c.content_id = cd.content_id AND c.content_navigation=n.navigation_id AND cd.language_code='" . $_SESSION[ 'language_code' ] . "' AND cd.content_status=1 ORDER BY content_sort ASC";
        $query = $DB->prepare( $sql );
        $query->execute();
        $get_nav_item = $query->fetchAll();

        foreach ( $get_nav_item as $nav_item ) {
            $active_nav = "";
            if ( $_GET[ 'content' ] == "content" && $_GET[ 'content_value' ] == $nav_item[ 'content_url' ] ) {
                $active_nav = ' class="active"';
            }
            $nav .= "<li " . $active_nav . "><a href=\"" . SITE_URL . $nav_item[ 'content_type' ] . "/" . $nav_item[ 'content_url' ] . "\">" . $nav_item[ 'content_title' ] . "</a></li>";
        }

        $nav .= "</ul>";
        $navigation[ strtoupper( $nav_class[ 'navigation_name' ] ) ] = $nav;
    }

    return $navigation;
}

function build_content_url( $name ) {
    global $DB;
    $sql = "SELECT 
			content_url,
			content_title,
			content_type 
		FROM 
			content c, 
			content_details cd 
		WHERE 
			c.content_id = cd.content_id AND 
			cd.language_code='" . $_SESSION[ 'language_code' ] . "' AND 
			c.content_name='" . $name . "' AND 
			cd.content_status=1  
		LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $nav_item = $query->fetch();

    $output = $nav_item[ 'content_type' ] . "/" . $nav_item[ 'content_url' ];

    return $output;
}

function build_login_box() {
    if ( ( $_SESSION[ 'logged_in' ] ) == 0 ) {
        $modul_template = SERVER_PATH . "template/modules/boxes/" . $_SESSION[ 'language_code' ] . "/login.html";
        if ( file_exists( $modul_template ) ) {
            $output = file_get_contents( $modul_template );
        }
    } else {
        $modul_template = SERVER_PATH . "template/modules/boxes/" . $_SESSION[ 'language_code' ] . "/user.html";
        if ( file_exists( $modul_template ) ) {
            $output = file_get_contents( $modul_template );
            // Active Navigation in Main Nav
            $output = str_replace( "navPoint-" . $_GET[ 'content' ], "active", $output );
        }
    }

    return $output;
}


//// EMAIL FUNKITONEN

function check_email( $email ) {

    //check for all the non-printable codes in the standard ASCII set,
    //including null bytes and newlines, and exit immediately if any are found.
    if ( preg_match( "/[\\000-\\037]/", $email ) ) {
        return false;
    }
    $pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
    if ( !preg_match( $pattern, $email ) ) {
        return false;
    }
    // Validate the domain exists with a DNS check
    // if the checks cannot be made (soft fail over to true)
    list( $user, $domain ) = explode( '@', $email );
    if ( function_exists( 'checkdnsrr' ) ) {
        if ( !checkdnsrr( $domain, "MX" ) ) { // Linux: PHP 4.3.0 and higher & Windows: PHP 5.3.0 and higher
            return false;
        }
    } else if ( function_exists( "getmxrr" ) ) {
        if ( !getmxrr( $domain, $mxhosts ) ) {
            return false;
        }
    }
    return true;
} // end function validate_email


function sending_email( $email_to, $name_to, $subject, $content, $alt_content, $attachment_array ) {
    require( PHP_MAILER_CLASS );
    $mail = new phpmailer();

    $mail->IsSendmail(); // per SMTP verschicken
    $mail->Host = SYSTEM_MAIL_SERVER; // SMTP-Server
    $mail->SMTPAuth = true; // SMTP mit Authentifizierung benutzen
    $mail->Username = SYSTEM_MAIL; // SMTP-Benutzername
    $mail->Password = SYSTEM_MAIL_PW; // SMTP-Passwort

    $mail->From = SYSTEM_MAIL;
    $mail->FromName = "OON-Sport";
    $mail->AddAddress( $email_to, utf8_decode( $name_to ) );
    $mail->AddReplyTo( SYSTEM_MAIL, "OON-Sport" );

    $mail->WordWrap = 50; // Zeilenumbruch einstellen
    // $mail->AddAttachment("/var/tmp/file.tar.gz");      // Attachment
    // $mail->AddAttachment("/tmp/image.jpg", "new.jpg");
    $mail->IsHTML( true ); // als HTML-E-Mail senden

    $mail->Subject = utf8_decode( $subject );
    $mail->Body = utf8_decode( $content );
    $mail->AltBody = utf8_decode( $alt_content );
    $result = $mail->Send();


    if ( !$result ) {
        //var_dump($mail->ErrorInfo);
        $output = false;
    } else {
        $output = true;
    }

    return $output;
}

function email_content_to_template( $template, $content_array, $image_types ) {
    //$template="register"; // TEST
    //TO DO: Language Switch in Path
     
    
    if (  file_exists(  SERVER_PATH . "template/email/" . $_SESSION[ 'language_code' ] . "/" . $template . ".html" ) ) {
        $output = file_get_contents( SERVER_PATH . "template/email/" . $_SESSION[ 'language_code' ] . "/" . $template . ".html" );
        
    } else  {
        if ( file_exists( SERVER_PATH . "template/email/en/" . $template . ".html" ) ) {
            $output = file_get_contents( SERVER_PATH . "template/email/en/" . $template . ".html");
        } else{
            
            sending_email( 'ahlers@comfort-design.de', 'carsten', 'oon error: email_content_to_template', 'Template nicht erreibar: ' . $template . '(' . $output . ')',0 );
            $output = "";
        }
    }
    

    //$image_types -> STATIC OR REFRESH
    //$image_types="refresh"; // TEST
    if ( $image_types == "refresh" ) {
        $image_view = "temp";
    } else {
        $image_view = "_static";
    }

    $output = str_replace( "#SITE_IMAGE_URL#", SITE_IMAGE_URL, $output );
    $output = str_replace( "#IMAGE_ROOT#", "", $output );
    $output = str_replace( "#MAIN_URL#", SITE_URL, $output );

    //$content_array = array("NAME","tremonti","STADT" => "Oldenburg","AUTHKEY" => "AKFKASFKSAKFSA"); // TEST
    foreach ( $content_array as $item => $value ) {
        $keyname = $item;
        $output = str_replace( "#" . $keyname . "#", $content_array[ $keyname ], $output );
    }

    return $output;
}


// Benutzerberechtigung
function have_permission( $setting, $user_id ) {
    global $DB;

    $sql = "SELECT * FROM user_settings WHERE user_id='" . $user_id . "' AND settings_key = '" . $setting . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $setting = $query->fetch();

    if ( $setting[ 'settings_value' ] == 1 ) {
        return true;
    }

    return false;
}

function get_setting( $setting, $user_id ) {
    global $DB;

    $sql = "SELECT * FROM user_settings WHERE user_id='" . $user_id . "' AND settings_key = '" . $setting . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $setting = $query->fetch();

    if ( $setting ) {
        return $setting[ 'settings_value' ];
    }

    return false;
}

// SYSTEM MELDUNGEN
function set_system_message( $type, $message ) {
    if ( $type == "error" ) {
        $msg = '<div class="alert alert-danger" role="alert"><strong>' . TEXT_GLOBAL_NOTE . ':</strong> ' . $message . '</div>';
    } else if ( $type == "success" ) {
        $msg = '<div class="alert alert-success" role="alert"><strong>' . TEXT_GLOBAL_NOTE . ':</strong> ' . $message . '</div>';
    } else $msg = '<div class="alert alert-warning" role="info"><strong>' . TEXT_GLOBAL_NOTE . ':</strong> ' . $message . '</div>';

    return ( $msg );
}

//
function get_user_details( $userid ) {
    global $DB;

    $sql = "SELECT * FROM `user` u, user_details ud WHERE u.`user_id` = '" . $userid . "' AND u.user_id = ud.user_id LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $user = $query->fetch();

    if ( $user[ 'user_id' ] ) {
        return $user;
    } else {
        return false;
    }
}


function check_user_nickname( $username ) {
    global $DB;
    $sql = "SELECT * FROM `user` u, user_details ud WHERE ud.`user_nickname` = '" . $username . "' AND u.user_id = ud.user_id LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $user = $query->fetch();

    if ( $user[ 'user_id' ] ) {
        return true;
    } else {
        return false;
    }
}

function check_user_email( $email ) {
    global $DB;
    $sql = "SELECT * FROM `user` u, user_details ud WHERE u.`user_email` = '" . $email . "' AND u.user_id = ud.user_id LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $user = $query->fetch();

    if ( $user[ 'user_id' ] ) {
        return true;
    } else {
        return false;
    }
}

function is_group_child( $value, $group ) {
    global $DB;
    $sql = "SELECT 
                sgd.sport_group_id 
            FROM 
                sport_group_details AS sgd
                LEFT JOIN sport_group_value AS sgv ON sgd.sport_group_id = sgv.sport_group_id
            WHERE 
                sgv.sport_group_value_id = '" . $value . "' AND
                sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
            LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $group_id = $query->fetch();
    if ( $group_id[ 'sport_group_id' ] == $group ) {
        return true;
    }

    return false;
}

function get_sport_group_name( $group_id ) {
    global $DB;
    if($group_id != 0) {
        $sql = "SELECT 
                    sgd.sport_group_name 
                FROM 
                    sport_group sg 
                    LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = sg.sport_group_id
                WHERE 
                    sg.sport_group_id = '" . $group_id . "' AND
                    sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
                LIMIT 1";

        $query = $DB->prepare( $sql );
        $query->execute();
        $group_name = $query->fetch();

        return ' / '.TEXT_GLOBAL_SPORTTYPE . ': ' . $group_name[ 'sport_group_name' ];
    }
    
    return '';
}


function get_value_group_name( $value_id ) {
    global $DB;
    $sql = "SELECT 
                sgd.sport_group_name 
            FROM 
                sport_group_details AS sgd
                LEFT JOIN sport_group_value AS sgv ON sgd.sport_group_id = sgv.sport_group_id
            WHERE 
                sgv.sport_group_value_id = '" . $value_id . "' AND
                sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
            LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $group_name = $query->fetch();

    return $group_name[ 'sport_group_name' ];
}

function get_user_main_sport( $user_id, $type = "plain" ) {
    global $DB;
    if ( $user_id === "register" ) {
        $sql = "SELECT * 
			FROM 
				sport_group_details AS sgd
			WHERE 
				sgd.sport_group_id = '" . $type . "' AND
				sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
			LIMIT 1";

        $type = "plain";
    } else if ( $type == 'detail_list' ) {
        $sql = "SELECT * 
			FROM 
				user_to_sport_group_value uts
				LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
				LEFT JOIN user AS u ON uts.user_id = u.user_id
			WHERE 
				uts.user_id ='" . $user_id . "' AND 
				sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
			ORDER BY sgd.sport_group_name ASC";
    } else {
        $sql = "SELECT * 
			FROM 
				user_to_sport_group_value uts
				LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
				LEFT JOIN user AS u ON uts.user_id = u.user_id
			WHERE 
				uts.user_id ='" . $user_id . "' AND 
				sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
			GROUP BY 
				sgd.sport_group_id";
    }

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_main_sport = $query->fetchAll();

    $seperator = "";
    $output = '';

    $total = $query->rowCount();
    $x = 1;
    $more = 0;
    $add_more = 0;

    foreach ( $get_main_sport as $main_sport ) {
        if ( $type == "detail_list" ) {
            $subgroup = get_user_sport_list( $user_id, $main_sport[ 'sport_group_value_id' ] );
            $handycap = "";
            if ( $main_sport[ 'sport_group_handycap' ] == '1' ) {
                $handycap = ' <i class="fa fa-wheelchair" style="cursor:help" title="' . constant( 'TEXT_PROFILE_SPORTS_HANDYCAP_INFO_PROFILE_' . $main_sport[ 'user_type' ] ) . '"></i>';
            }
            $output .= "<tr><td>" . $seperator . $main_sport[ 'sport_group_name' ] . $handycap . "</td><td>" . $subgroup . "</td><td>" . get_profession_name( $main_sport[ 'sport_group_profession' ], $main_sport[ 'user_type' ] ) . "</td></tr>";
            $user_type = $main_sport[ 'user_type' ];
        } else {
            if ( $x > 1 && $total > 2 ) {
                $more++;
                $add_more = 1;
            } else {
                $output .= $seperator . $main_sport[ 'sport_group_name' ];
                $seperator = ", ";
                $x++;
            }
        }
    }

    if ( $add_more == 1 ) {
        $output .= " + " . $more . " weitere";
    }



    if ( $type == "detail_list" ) {
        $output = '
        <div class="row">
        <table class="table scroll-table"><thead> <tr> <th>' . TEXT_GLOBAL_SPORTTYPE . '</th> <th>' . TEXT_GLOBAL_DETAIL . '</th> <th>' . constant( 'TEXT_GLOBAL_PROFESSION_' . $user_type ) . '</th></tr> </thead><tbody>' . $output . '</tbody></table>
        </div>';
    }

    return $output;
}


// PROFIL ANSICHT ZUM BEARBEITEN
function get_profile_user_main_sport( $user_id ) {
    global $DB;
    $sql = "SELECT *, uts.sport_group_id AS sgID, uts.sport_group_value_id AS vID
			FROM 
				user_to_sport_group_value uts
				LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
				LEFT JOIN sport_group_value AS sgv ON uts.sport_group_value_id = sgv.sport_group_value_id
				LEFT JOIN user AS u ON uts.user_id = u.user_id
			WHERE 
				uts.user_id ='" . $user_id . "' AND 
				sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
			ORDER BY sgd.sport_group_name ASC";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_main_sport = $query->fetchAll();

    $seperator = '';
    $output = '';

    $total = $query->rowCount();
    $x = 1;
    $more = 0;
    $add_more = 0;

    foreach ( $get_main_sport as $main_sport ) {
        $subgroup = get_user_sport_list( $user_id, $main_sport[ 'vID' ] );
        $handycap = '';
        if ( $main_sport[ 'sport_group_handycap' ] == '1' )$handycap = ' <i class="fa fa-wheelchair" style="cursor:help" title="' . constant( 'TEXT_PROFILE_SPORTS_HANDYCAP_INFO_PROFILE_' . $main_sport[ 'user_type' ] ) . '"></i>';

        $output .= '<tr><td>' . $seperator . $main_sport[ 'sport_group_name' ] . $handycap . '</td><td>' . $subgroup . '</td><td>' . get_profession_name( $main_sport[ 'sport_group_profession' ], $main_sport[ 'user_type' ] ) . '</td><td>';


        $output .= '<form method="post" action="#SITE_URL#me/profile/sports/">
            <button  class="btn btn-xs btn-primary" title="' . TEXT_GLOBAL_CHANGE . '"><i class="fa fa-edit"></i></button>
            <input type="hidden" name="sport_group_parent" value="' . $main_sport[ 'sport_group_sub_of' ] . '">
            <input type="hidden" name="sport_group" value="' . $main_sport[ 'sgID' ] . '">
            <input type="hidden" name="sport_group_value" value="' . $main_sport[ 'vID' ] . '">
             <input type="hidden" name="sports_id_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="' . md5( $main_sport[ 'sgID' ] . $main_sport[ 'vID' ] . $_SESSION[ 'user' ][ 'user_id' ] ) . '"><input type="hidden" name="reset_update_sports_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="1"></form></td><td>
             <form method="post" action="#SITE_URL#me/profile/sports/">';

        if ( $total == 1 ) {
            $output .= '
             <button  class="btn btn-xs btn-danger" title="' . TEXT_SPORTS_ERROR_DELETE_ONE_LEFT . '" disabled><i class="fa fa-trash"></i></button>';
        } else {
            $output .= '
             <button  class="btn btn-xs btn-danger" title="' . TEXT_GLOBAL_DELETE . '"><i class="fa fa-trash"></i></button>';
        }

        $output .= '
         <input type="hidden" name="sports_id_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="' . md5( $main_sport[ 'sgID' ] . $main_sport[ 'vID' ] . $_SESSION[ 'user' ][ 'user_id' ] ) . '"><input type="hidden" name="delete_sports_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="1"></form>		

                </td></tr>';
        $user_type = $main_sport[ 'user_type' ];
    }

    $output = '
			<div class="row">
			<table class="table scroll-table"><thead> <tr> <th>' . TEXT_GLOBAL_SPORTTYPE . '</th> <th>' . TEXT_GLOBAL_DETAIL . '</th> <th>' . constant( 'TEXT_GLOBAL_PROFESSION_' . $user_type ) . '</th><th> </th> </tr> </thead><tbody>' . $output . '</tbody></table>
			</div>';

    return $output;
}


function get_user_sport_list( $user_id, $value_id ) {
    global $DB;
    if ( $user_id === 0 ) {
        $sql = "SELECT * 
			FROM 
				sport_group_value sgv, 
				sport_group_value_details sgvd 
			WHERE 
				sgv.sport_group_value_id = '" . $value_id . "' AND
				sgv.sport_group_value_id = sgvd.sport_group_value_id AND
				sgvd.language_code='" . $_SESSION[ 'language_code' ] . "'
			LIMIT 1";
    } else {
        $sql = "SELECT * 
			FROM 
				sport_group_value sgv, 
				user_to_sport_group_value uts, 
				sport_group_value_details sgvd 
			WHERE 
				uts.sport_group_value_id = '" . $value_id . "' AND
				sgv.sport_group_value_id = sgvd.sport_group_value_id AND
				uts.sport_group_value_id = sgvd.sport_group_value_id AND 
				uts.user_id ='" . $user_id . "' AND 
				sgvd.language_code='" . $_SESSION[ 'language_code' ] . "'
			LIMIT 1";
    }

    $query = $DB->prepare( $sql );
    $query->execute();
    $main_sport = $query->fetch();

    $output = '';

    $sub_of = $main_sport[ 'sport_group_sub_of' ];
    $x = 0;
    while ( $sub_of > 0 ) {

        $parent = get_sport_parent( $sub_of );

        $sub_of = $parent[ 'sport_group_sub_of' ];
        $output = $parent[ 'sport_group_value_name' ] . " &gt; " . $output;
        $x++;

        // Sicherung für den Fall einer Falschen Zuordnung
        if ( $x == 5 ) {
            $output = "error";
            break;
        }
    }

    $output .= $main_sport[ 'sport_group_value_name' ];

    return $output;
}

function check_for_all_value_ids( $value_id, $group_ip, $level ) {
    global $DB;
    $group_ids = array();

    $sql = "SELECT * FROM 
        sport_group_value sgv, 
        sport_group_value_details sgvd 
        WHERE 
        sgvd.sport_group_value_id = '" . $value_id . "' AND
        sgv.sport_group_value_id = sgvd.sport_group_value_id LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $sport = $query->fetch();

    if ( $query->rowCount() < 1 ) {
        return $group_ids;
    }

    if ( $sport[ 'sport_group_value_name' ] == "Alle" || $sport[ 'sport_group_value_name' ] == "All" ) {
        $group_ids[] = $sport[ 'sport_group_value_id' ];

        if ( $sport[ 'sport_group_sub_of' ] != 0 ) {
            $sql = "SELECT sgv.sport_group_value_id FROM 
                    sport_group_value sgv
                    LEFT JOIN sport_group_value_details AS sgvd ON sgv.sport_group_value_id = sgvd.sport_group_value_id
                    WHERE 
                    (sgv.sport_group_sub_of = '" . $sport[ 'sport_group_sub_of' ] . "' OR 
                    sgv.sport_group_value_id = '" . $sport[ 'sport_group_sub_of' ] . "' OR
                    sgv.sport_group_sub_of = '" . $sport[ 'sport_group_value_id' ] . "'

                    )
                    ";
        } else {
            $sql = "SELECT sgv.sport_group_value_id FROM 
				sport_group_value sgv, 
				sport_group_value_details sgvd 
				WHERE 
				sgv.sport_group_id = '" . $sport[ 'sport_group_id' ] . "' AND
				sgv.sport_group_value_id = sgvd.sport_group_value_id";
        }

        $query = $DB->prepare( $sql );
        $query->execute();
        $get_sport = $query->fetchAll();

        foreach ( $get_sport as $sport ) {
            $group_ids[] = $sport[ 'sport_group_value_id' ];
        }

    } else {
        $group_ids[] = $sport[ 'sport_group_value_id' ];
    }

    return $group_ids;
}

function get_sport_parent( $parent_id ) {
    global $DB;
    $sql = "SELECT * FROM 
	sport_group_value sgv, 
	sport_group_value_details sgvd 
	WHERE 
	sgv.sport_group_value_id = '" . $parent_id . "' AND
	sgv.sport_group_value_id = sgvd.sport_group_value_id AND 
	sgvd.language_code='" . $_SESSION[ 'language_code' ] . "' LIMIT 1";


    $query = $DB->prepare( $sql );
    $query->execute();
    $sport_name = $query->fetch();

    return $sport_name;
}




function get_profession_name( $prof_id, $type ) {
    $profession_array = array( 0 => TEXT_PROFIL_PROFESSION_UNKNOWN,
        1 => constant( 'TEXT_GLOBAL_PROFESSION_BEGINNER_' . $type ),
        2 => constant( 'TEXT_GLOBAL_PROFESSION_ROOKIE_' . $type ),
        3 => constant( 'TEXT_GLOBAL_PROFESSION_AMATEUR_' . $type ),
        4 => constant( 'TEXT_GLOBAL_PROFESSION_PROFI_' . $type ),
        5 => TEXT_GLOBAL_OTHER );
    return ( $profession_array[ $prof_id ] );
}


function get_user_type( $user_type_id, $type ) {
    // TO DO $type = icon oder text
    global $DB;
    $sql = "SELECT * FROM user_types ut, user_types_details utd WHERE ut.user_type_id=utd.user_type_id AND utd.language_code='" . $_SESSION[ 'language_code' ] . "' AND ut.user_type_id='" . $user_type_id . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $user_types = $query->fetch();

    return $user_types[ 'user_type_name' ];
}

function get_age( $birthday, $type ) {
    if ( $type == 3 || $type == 4 ) {
        $alter = date( "Y", strtotime( $birthday ) );
    } else {
        $alter = floor( ( date( "Ymd" ) - date( "Ymd", strtotime( $birthday ) ) ) / 10000 );
    }
    return $alter;
}


/*
GOOGLE MAPS FUNCTIONS*/

function geo_locate_by_input($geo_input) {
    global $DB;
    $geo_input= str_replace(array(',','-','.'),' ',$geo_input);
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($geo_input).'&key=AIzaSyAAUCGinXBvsdx8WHD_PdVvoXA42lakEd4&language='.$_SESSION['language_code'];
    //echo $geo_input.'<br>';
    $result = file_get_contents($url);
    
    //echo $url;//.'<br><pre>';
    //var_dump($_SESSION);
    $result = json_decode($result);
    
    //var_dump($result);
    
    if($result->status == 'ZERO_RESULTS') {
        return false;
    }
    //var_dump($result->results);
    //var_dump($result->results[0]->address_components);
    $place_id = $result->results[0]->place_id;
    
    $sql = "SELECT 
            *
        FROM geo_cities c
        WHERE c.city_google_code = :placeId LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->bindParam(':placeId', $place_id);
    $query->execute();
    $city = $query->fetch();
    
    if(!$city) {
        $lat = $result->results[0]->geometry->location->lat;
        $lng = $result->results[0]->geometry->location->lng;

        $city = '';
        $district = ''; 
        $zipcode = '';
        $state = '';
        $country_code = '';
        $country_name = '';

        foreach($result->results[0]->address_components as $addrCom) {
            //echo $addrCom->long_name.'<br>';

            if(in_array('postal_code',$addrCom->types)) {
                $zipcode = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('sublocality',$addrCom->types)) {
                $district = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('locality',$addrCom->types)) {
                $city = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('administrative_area_level_1',$addrCom->types)) {
                $state = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('country',$addrCom->types)) {
                $country_code = $addrCom->short_name;
                $country_name = $addrCom->long_name;
            }

        }

        //echo '<hr>' . $city . ' = $city<br>';
        //echo $district . ' = $district<br>'; 
        //echo $zipcode . ' = $zipcode<br>';
        //echo $state . ' = $state<br>';
        //echo $country_code . ' = $country_code<br>';
        //var_dump($result->results[0]->formatted_address);
        //var_dump($lat);
        //var_dump($lng);
        //var_dump($place_id);
        //echo "</pre>";
        
        $sql = "INSERT INTO 
                geo_cities (
                `city_google_code`, 
                `city_name`,
                `city_district`, 
                `city_state`, 
                `city_country`,
                `city_country_code`, 
                `city_zipcode`,
                `city_lat`, 
                `city_lng`) VALUES (
                :placeId, 
                :cName, 
                :cDistrict, 
                :cState, 
                :cCountry,
                :cCode,
                :cZipcode,
                :cLat, 
                :cLng);";

        $query = $DB->prepare( $sql );
        
        $query->bindParam(':placeId', $place_id);
        $query->bindParam(':cName', $city);
        $query->bindParam(':cDistrict', $district);
        $query->bindParam(':cState', $state);
        $query->bindParam(':cCountry', $country_name);
        $query->bindParam(':cCode', $country_code);
        $query->bindParam(':cZipcode', $zipcode);
        $query->bindParam(':cLat', $lat);
        $query->bindParam(':cLng', $lng);
        $query->execute();
        
        //die();
    }
    
    return $place_id;
    
}


function get_place_id_data($place_id) {
    
    global $DB;
    if($place_id == '') {
        return false;
    }
    
    $sql = "SELECT 
            *
        FROM geo_cities c
        WHERE c.city_google_code = :placeId LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->bindParam(':placeId', $place_id);
    $query->execute();
    $city = $query->fetch();
    
    if(!$city) {

        if(geo_locate_check()) {
            $data = get_place_id_data($place_id);   
        } else {
            return false;
        }
    
    }
    
    return $city;
    
}

function geo_locate_check($place_id) {
    global $DB;
                   
    if($place_id == '') {
        return false;
    }
    
    $sql = "SELECT 
            *
        FROM geo_cities c
        WHERE c.city_google_code = :placeId LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->bindParam(':placeId', $place_id);
    $query->execute();
    $city = $query->fetch();


    if(!$city) {

    
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?place_id='.$place_id.'&key=AIzaSyAAUCGinXBvsdx8WHD_PdVvoXA42lakEd4&language='.$_SESSION['language_code'];
    
        $result = file_get_contents($url);
        $result = json_decode($result);
        if($result->status == 'ZERO_RESULTS' || !$result) {
            return false;
        }

        $place_id = $result->results[0]->place_id;
        
        $lat = $result->results[0]->geometry->location->lat;
        $lng = $result->results[0]->geometry->location->lng;

        $city = '';
        $district = ''; 
        $zipcode = '';
        $state = '';
        $country_code = '';
        $country_name = '';

        foreach($result->results[0]->address_components as $addrCom) {
            //echo $addrCom->long_name.'<br>';

            if(in_array('postal_code',$addrCom->types)) {
                $zipcode = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('sublocality',$addrCom->types)) {
                $district = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('locality',$addrCom->types)) {
                $city = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('administrative_area_level_1',$addrCom->types)) {
                $state = $addrCom->long_name;
            }

            if(in_array('political',$addrCom->types) &&  in_array('country',$addrCom->types)) {
                $country_code = $addrCom->short_name;
                $country_name = $addrCom->long_name;
            }

        }

        $sql = "INSERT INTO 
                geo_cities (
                `city_google_code`, 
                `city_name`,
                `city_district`, 
                `city_state`, 
                `city_country`,
                `city_country_code`, 
                `city_zipcode`,
                `city_lat`, 
                `city_lng`) VALUES (
                :placeId, 
                :cName, 
                :cDistrict, 
                :cState, 
                :cCountry,
                :cCode,
                :cZipcode,
                :cLat, 
                :cLng);";

        $query = $DB->prepare( $sql );
        
        $query->bindParam(':placeId', $place_id);
        $query->bindParam(':cName', $city);
        $query->bindParam(':cDistrict', $district);
        $query->bindParam(':cState', $state);
        $query->bindParam(':cCountry', $country_name);
        $query->bindParam(':cCode', $country_code);
        $query->bindParam(':cZipcode', $zipcode);
        $query->bindParam(':cLat', $lat);
        $query->bindParam(':cLng', $lng);
        $query->execute();
        
        return true;
    }
    else {
        return true;
    }
    
    return false;
}


function get_city_name( $city_id ) {
    global $DB;
    if ( $city_id == "" ) return "unknown";

    $sql = "SELECT CONCAT(city_name,' ',IFNULL(city_district,'')) as cName FROM geo_cities WHERE city_google_code='" . $city_id . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $city_name = $query->fetch();

    if ( $city_name[ 'cName' ] ) {
        return $city_name[ 'cName' ];
    }

    return "unknown";
}

function get_country_name( $country_code ) {
    global $DB;
    if ( $country_code == "" ) return "unknown2";
    
    $sql = "SELECT 
                c.*, 
                t.country_name AS translatedName
            FROM geo_countries c
                LEFT JOIN geo_countries_translation t ON c.country_code = t.country_code AND t.language_code = '" . $_SESSION['language_code'] . "'
                WHERE 
                c.country_code = '" . $country_code . "' ORDER BY 
            `country_sort` DESC, 
            t.country_name  IS NULL ASC, 
            c.country_name ASC LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $country = $query->fetch();
    
    if($country['translatedName']) {
        return $country['translatedName'];
    }
    
    return $country['country_name'];
}


function get_city_latlng( $city_id, $country ) {
    global $DB;
    if ( $country == "" ) return false;

    $sql = "SELECT latitude AS lat,longitude AS lng FROM geodb_world." . strtolower( $country ) . " WHERE city_id='" . $city_id . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $city_name = $query->fetch();

    if ( $city_name[ 'lat' ] ) {
        return $city_name;
    }

    return false;
}

function get_city_id_by_zipcode( $zipcode, $country ) {
    global $DB;
    if ( $country == "" ) return false;

    $sql = "SELECT city_id FROM geodb_world." . strtolower( $country ) . " WHERE postalcode='" . $zipcode . "' ORDER BY place ASC, place2 ASC, place3 ASC, place4 ASC LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $city_id = $query->fetch();

    if ( $city_id ) {
        return $city_id[ 'city_id' ];
    }

    return false;
}

function get_city_id_by_lat_lng( $lat, $lng, $country ) {
    global $DB;
    if ( $country == "" ) return false;

    $sql = "SELECT city_id, postalcode, 
					(acos(sin(RADIANS(latitude))*sin(RADIANS(" . $lat . "))+cos(RADIANS(latitude))*cos(RADIANS(" . $lat . "))*cos(RADIANS(longitude) - RADIANS(" . $lng . ")))*" . RADIUS . ") AS DISTANCE
					 FROM geodb_world." . strtolower( $country ) . " WHERE 1 ORDER BY DISTANCE ASC LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $city = $query->fetch();

    if ( $city ) {
        return $city;
    }

    return false;
}


function build_advanced_search_pagination( $total, $page, $view_per_page, $search, $view_box ) {

    $view_pref_next = 5;
    if ( $page < 1 )$page = 1;
    $total_pages = ceil( $total / $view_per_page );

    // Keine SeitenNavi wenn es nur eine Seite gibt.
    if ( $total_pages <= 1 ) {
        if ( $view_box != '' ) {
            return '<div class="row"><nav>' . $view_box . '</nav></div>';
        } else {
            return '';
        }
    }


    $output = '';
    $output .= '
    <div class="row">
    <nav>
      <ul class="pagination">';

    // PREV
    if ( $page == 1 ) {
        if($_SESSION['logged_in'] == 0) {
                    $output .= '<li class="disabled"><a href="#" aria-label="Previous" data-toggle="modal"   data-target="#register_modal"><span aria-hidden="true">«</span></a></li>';
                        } 
        else {
                   $output .= '<li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">«</span></a></li>'; 
        }
    } else {
        $output .= '<li><a href="#SITE_URL#advancedsearch/?page=' . ( $page - 1 ) . $search . '" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
    }

    // PAGES
    for ( $x = 1; $x <= $total_pages; $x++ ) {
        if ( $page == $x ) {
            if($_SESSION['logged_in'] == 0) {
                $output .= '<li class="active"><a href="#" data-toggle="modal"   data-target="#register_modal">' . ( $x ) . '<span class="sr-only">(current)</span></a></li>';
                        } 
            else {
                $output .= '<li class="active"><a href="#">' . ( $x ) . '<span class="sr-only">(current)</span></a></li>';
            }

        } else {
            if ( ($x < $page && $page - $x < $view_pref_next) || ( $x > $page && $x - $page < $view_pref_next )) {
                if($_SESSION['logged_in'] == 0) {
                    $output .= '<li><a href="#" data-toggle="modal"   data-target="#register_modal">' . ( $x ) . '</a></li>';
                } 
                else {
                    $output .= '<li><a href="#SITE_URL#advancedsearch/?page=' . $x . $search . '">' . ( $x ) . '</a></li>';
                }
            }
        }
    }

    // NEXT
    if ( $page == $total_pages ) {
        if($_SESSION['logged_in'] == 0) {
            $output .= '<li  class="disabled"><a href="#" aria-label="Next" data-target="#register_modal"><span aria-hidden="true">»</span></a></li>';
        } 
        else {
            $output .= '<li class="disabled"><a href="#" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
        }
    } else {
        if($_SESSION['logged_in'] == 0) {
            $output .= '<li><a href="#" aria-label="Next" data-target="#register_modal"><span aria-hidden="true">»</span></a></li>';
        } 
        else {
            $output .= '<li><a href="#SITE_URL#advancedsearch/?page=' . ( $page + 1 ) . $search . '" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
        }
    }

    $output .= '</ul>' . $view_box . '
           </nav>
         </div>';

    return $output;

}


function build_search_pagination( $total, $page, $view_per_page, $search, $view_box ) {

        $view_pref_next = 5;
    if ( $page < 1 )$page = 1;
    $total_pages = ceil( $total / $view_per_page );

    // Keine SeitenNavi wenn es nur eine Seite gibt.
    if ( $total_pages <= 1 ) {
        if ( $view_box != '' ) {
            return '<div class="row"><nav>' . $view_box . '</nav></div>';
        } else {
            return '';
        }
    }


    $output = '';
    $output .= '
    <div class="row">
    <nav>
      <ul class="pagination">';

    // PREV
    if ( $page == 1 ) {
        if($_SESSION['logged_in'] == 0) {
                    $output .= '<li class="disabled"><a href="#" aria-label="Previous" data-toggle="modal"   data-target="#register_modal"><span aria-hidden="true">«</span></a></li>';
                        } 
        else {
                   $output .= '<li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">«</span></a></li>'; 
        }
    } else {
        $output .= '<li><a href="#SITE_URL#search/?page=' . ( $page - 1 ) . $search . '" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
    }

    // PAGES
    for ( $x = 1; $x <= $total_pages; $x++ ) {
        if ( $page == $x ) {
            if($_SESSION['logged_in'] == 0) {
                $output .= '<li class="active"><a href="#" data-toggle="modal"   data-target="#register_modal">' . ( $x ) . '<span class="sr-only">(current)</span></a></li>';
                        } 
            else {
                $output .= '<li class="active"><a href="#">' . ( $x ) . '<span class="sr-only">(current)</span></a></li>';
            }

        } else {
            if ( ($x < $page && $page - $x < $view_pref_next) || ( $x > $page && $x - $page < $view_pref_next )) {
                if($_SESSION['logged_in'] == 0) {
                    $output .= '<li><a href="#" data-toggle="modal"   data-target="#register_modal">' . ( $x ) . '</a></li>';
                } 
                else {
                    $output .= '<li><a href="#SITE_URL#search/?page=' . $x . $search . '">' . ( $x ) . '</a></li>';
                }
            }
        }
    }

    // NEXT
    if ( $page == $total_pages ) {
        if($_SESSION['logged_in'] == 0) {
            $output .= '<li  class="disabled"><a href="#" aria-label="Next" data-target="#register_modal"><span aria-hidden="true">»</span></a></li>';
        } 
        else {
            $output .= '<li class="disabled"><a href="#" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
        }
    } else {
        if($_SESSION['logged_in'] == 0) {
            $output .= '<li><a href="#" aria-label="Next" data-target="#register_modal"><span aria-hidden="true">»</span></a></li>';
        } 
        else {
            $output .= '<li><a href="#SITE_URL#search/?page=' . ( $page + 1 ) . $search . '" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
        }
    }

    $output .= '</ul>' . $view_box . '
           </nav>
         </div>';

    return $output;
}

function build_site_pagination( $site, $total, $page, $view_per_page, $search, $view_box, $forceDisplay = false ) {

    $view_pref_next = 5;
    if ( $page < 1 )$page = 1;
    $total_pages = ceil( $total / $view_per_page );

    // Keine SeitenNavi wenn es nur eine Seite gibt.
    if ( $total_pages <= 1 && $forceDisplay == false) {
        if ( $view_box != '' ) {
            return '<div class="row">
                <nav>' . $view_box . '</nav>
                 </div>';
        } else {
            return "";
        }
    }

    $output = '';
    $output .= '
    <div class="row">
    <nav>
      <ul class="pagination">';

    // PREV
    if ( $page == 1 ) {
        $output .= '<li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
    } else {
        $output .= '<li><a href="#SITE_URL#' . $site . '?page=' . ( $page - 1 ) . $search . '" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
    }

    // PAGES
    for ( $x = 1; $x <= $total_pages; $x++ ) {
        if ( $page == $x ) {
            $output .= '<li class="active"><a href="#">' . ( $x ) . '<span class="sr-only">(current)</span></a></li>';
        } else {
            if ( $x < $page && $page - $x < $view_pref_next )$output .= '<li><a href="#SITE_URL#' . $site . '?page=' . $x . $search . '">' . ( $x ) . '</a></li>';
            if ( $x > $page && $x - $page < $view_pref_next )$output .= '<li><a href="#SITE_URL#' . $site . '?page=' . $x . $search . '">' . ( $x ) . '</a></li>';
        }
    }

    // NEXT
    if ( $page == $total_pages ) {
        $output .= '<li  class="disabled"><a href="#" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
    } else {
        $output .= '<li><a href="#SITE_URL#' . $site . '?page=' . ( $page + 1 ) . $search . '" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
    }

    $output .= '</ul>' . $view_box . '
       </nav>
     </div>';

    return $output;

}


function random_password() {
    $pw_letters = "23456789abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    $pw = "";
    for ( $x = 0; $x < 8; $x++ ) {
        $pw .= $pw_letters[ rand( 0, strlen( $pw_letters ) - 1 ) ];
    }
    return $pw;
}


function build_history_log( $user_id, $action, $info = '' ) {
    global $DB;
    $sql = "INSERT INTO `user_history` (`user_id`, `history_action`, `history_ip`, `history_date`, `history_info`) VALUES ('" . $user_id . "', '" . $action . "', '" . substr( md5( $_SERVER[ 'REMOTE_ADDR' ] ), 0, 15 ) . "', NOW(), '" . $info . "')";
    $query = $DB->prepare( $sql );
    $query->execute();
    return true;
}

function build_user_online( $user_id ) {
    global $DB;
    $sql = "UPDATE `user` SET user_online = NOW() WHERE user_id = '" . $user_id . "' LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    return true;
}

function build_register_modal() {
    global $DB;
    $output = '<div class="modal fade" id="register_modal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				 <header class="list-group-item start_register_header">' . TEXT_INDEX_REGISTER_HEADER . '</header>
			</div> 
       
			<div class="modal-body">
				
				
				 <div class="list-group-item  start_register_box choice-start_register_box"><div class="row choice_form_line">
       ';

    $sql = "SELECT * FROM user_types ut, user_types_details utd WHERE ut.user_type_id=utd.user_type_id AND utd.language_code='" . $_SESSION[ 'language_code' ] . "' ORDER BY ut.user_type_id ASC";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_user_types = $query->fetchAll();

    $choice_x = 0;
    foreach ( $get_user_types as $user_types ) {

        $output .= '<div class="col-md-6 col-sm-3 choice-element-' . $user_types[ 'user_type_index' ] . '">
                    <form method="post" action="#SITE_URL#register/"  class="form"><input type="submit" name="rs1" value="' . constant( 'TEXT_GLOBAL_REGISTER_AS_' . $user_types[ 'user_type_id' ] ) . '" class="btn btn-sm btn-primary choice-button">
                        <input type="hidden" name="register_type" value="' . $user_types[ 'user_type_id' ] . '">
                        <input type="hidden" name="register_step" value="1">

                     </form>
                 </div>';
    }

    $output .= '
			</div>
		</div>
		<p class="text-center">' . TEXT_GLOBAL_OR_REGISTER . ':</p>
			
		<div class="list-group-item  start_register_box">
			<div class="row form_line">
				<div class="text-center">
					<div class="col-md-12">
					
					<form method="post" action="#SITE_URL#login/">
					<div class="col-sm-6 col-xs-12 login_box">
						<label>' . TEXT_LOGIN_EMAILADRESS . ':<br>
						</label>
						<input class="form-control" type="text" name="login_name' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" required>
					</div>
					<div class="col-sm-6 col-xs-12 login_box">
					<label>' . TEXT_LOGIN_PASSWORD . ':<br>
					</label>
					<input  class="form-control" type="password" name="login_password' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" required><br>
					<a href="#SITE_URL#reset-password/">' . TEXT_LOGIN_PASSWORD_FORGOTTON . '</a></div>
					<div class="col-sm-12 col-xs-12 login_box"><br>
						<input type="submit" value="' . TEXT_LOGIN_LOGIN_BUTTON . '"  class="btn btn-sm btn-primary form-control"><br>
						<input type="checkbox" name="login_remember' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="1"> ' . TEXT_LOGIN_STAY_ONLINE . '
					</div>
					</form>
					<br>
					</div>
				</div>
			</div>
		</div>
	
	
		</div>
	</div>
</div>	';

    return $output;
}


function friendship_exists( $user, $user2 ) {
    global $DB;
    $sql = "SELECT * FROM 
	`user_friendship` 
	WHERE
	(user_id = '" . $user . "' AND friendship_user_id = '" . $user2 . "') OR
	(user_id = '" . $user2 . "' AND friendship_user_id = '" . $user . "') 
	LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    if ( $query->rowCount() == 1 ) {
        return true;
    }

    return false;
}

function friendship_active( $user, $user2 ) {
    global $DB;
    $sql = "SELECT * FROM 
        `user_friendship` 
        WHERE
        ((user_id = '" . $user . "' AND friendship_user_id = '" . $user2 . "') OR
        (user_id = '" . $user2 . "' AND friendship_user_id = '" . $user . "')) AND 
        friendship_confirmed = 1
        LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    if ( $query->rowCount() == 1 ) {
        return true;
    }

    return false;
}

function watchlist_exists( $user, $user2 ) {
    global $DB;
    $sql = "SELECT * FROM 
	`user_watchlist` 
	WHERE
	(user_id = '" . $user . "' AND watchlist_user_id = '" . $user2 . "') 
	LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    if ( $query->rowCount() == 1 ) {
        return true;
    }

    return false;
}

function blockedlist_exists( $user, $user2 ) {
    global $DB;
    $sql = "SELECT * FROM 
	`user_blocked` 
	WHERE
	(user_id = '" . $user . "' AND blocked_user_id = '" . $user2 . "') 
	LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    if ( $query->rowCount() == 1 ) {
        return true;
    }

    return false;
}

function action_blockedlist_exists( $user, $user2 ) {
    global $DB;
    $sql = "SELECT * FROM 
	`user_blocked` 
	WHERE
	(user_id = '" . $user . "' AND blocked_user_id = '" . $user2 . "') OR
	(blocked_user_id = '" . $user . "' AND user_id = '" . $user2 . "') 
	LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    if ( $query->rowCount() == 1 ) {
        return true;
    }

    return false;
}



function last_time_online( $user ) {
    global $DB;
    $sql = "SELECT history_date FROM 
	`user_history` 
	WHERE
	user_id = '" . $user . "'
	ORDER BY history_date DESC 
	LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $last_time = $query->fetch();

    if ( $last_time ) {
        return date( "d.m.Y", strtotime( $last_time[ 'history_date' ] ) );
    }

    return TEXT_GLOBAL_UNKOWN;

}

function get_total_subquery( $sql ) {
    global $DB;

    $query = $DB->prepare( $sql );
    $query->execute();
    $total = $query->rowCount();

    return $total;
}

function get_subquery_preview( $sql, $limit ) {
    global $DB;
    $result = "";

    $query = $DB->prepare( $sql . " LIMIT " . $limit );
    $query->execute();
    $get_preview = $query->fetchAll();

    foreach ( $get_preview as $preview ) {
        $result .= build_default_image( $preview[ 'user_id' ], "50x50", "grid_line_image" );
    }

    return $result;

}

function get_subquery_sportgroups( $sql, $start = 0, $limit ) {
    global $DB;
    $result = array();

    // $SQL Modifikation

    $sql = str_replace( "SELECT DISTINCT(u.user_id),", "SELECT sgd.sport_group_name, utsgv.sport_group_id, COUNT(DISTINCT(u.user_id)) AS ANZAHL, ", $sql );

    $sql .= " AND sgd.language_code='" . $_SESSION[ 'language_code' ] . "'
                GROUP BY utsgv.sport_group_id 
                ORDER BY ANZAHL DESC
                LIMIT " . $start . ", " . $limit;
    $query = $DB->prepare( $sql );
    $query->execute();
    $get_sportgroups = $query->fetchAll();

    foreach ( $get_sportgroups as $sportgroups ) {
        $result[] = $sportgroups;
    }

    return $result;
}


function get_geo_by_address( $address, $country ) {
    if ( $address != '' ) {

        $cord = array();
        $cord[ 'url' ] = "http://maps.googleapis.com/maps/api/geocode/json?address=" . seo_geo_name( $address ) . "+" . $country . "&sensor=true";
        //echo $cord['url'];
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $cord[ 'url' ] );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        $temp = curl_exec( $ch );
        $output = json_decode( $temp );

        //print_r($output);
        $cord[ 'lat' ] = $output->results[ 0 ]->geometry->location->lat;
        $cord[ 'lng' ] = $output->results[ 0 ]->geometry->location->lng;
        if ( !is_numeric( $cord[ lat ] ) || !is_numeric( $cord[ lng ] ) ) {
            return false;
        } else {
            return $cord;
        }
    } else {
        return false;
    }


}

function seo_geo_name( $name ) {
    $name = strtolower( $name );
    $name = str_replace( " in ", "-", $name );
    $name = str_replace( ".", "", $name );
    $name = str_replace( "ä", "ae", $name );
    $name = str_replace( "ü", "ue", $name );
    $name = str_replace( "ö", "oe", $name );
    $name = str_replace( "Ä", "Ae", $name );
    $name = str_replace( "Ü", "Ue", $name );
    $name = str_replace( "Ö", "Oe", $name );
    $name = str_replace( "ß", "ss", $name );
    $name = str_replace( " ", "+", $name );
    $name = str_replace( ",", "", $name );
    return $name;
}



function cut_string( $string, $length ) {

    if ( strlen( $string ) > $length ) {
        return substr( $string, 0, 17 ) . "...";
    }
    return $string;
}

//BANNER
function build_banner( $placement, $size, $geo, $sport, $user_attributes ) {

    global $DB;
    if ( $sport > 0 ) {
        $sql = "	SELECT 
					* 
				FROM 
					affiliate_group ag
					LEFT JOIN affiliate_banner ab ON ab.affiliate_group_id = ag.affiliate_group_id
					LEFT JOIN affiliate_to_reference atr ON atr.affiliate_group_id = ag.affiliate_group_id
				WHERE
				 atr.reference_type = 'sport' AND
				 atr.reference_value = '" . $sport . "' AND
				 ab.affiliate_size = '" . $size . "' 
				LIMIT 1";
        $query = $DB->prepare( $sql );
        $query->execute();
        $banner = $query->fetch();
    } else {
        $banner = false;
    }

    if ( $banner ) {
        $banner[ 'affiliate_code' ] = str_replace( "http://", "https://", $banner[ 'affiliate_code' ] );


        return '
            <div class="affiliate affiliate-' . $size . ' visible-' . $size . '-block">
            <div class="affiliate-header">' . TEXT_AFFILIATE_HEADER . '</div>
            ' . $banner[ 'affiliate_code' ] . '
            </div>';
    } else {
        $sql = "	SELECT 
					* 
				FROM 
					affiliate_group ag
					LEFT JOIN affiliate_banner ab ON ab.affiliate_group_id = ag.affiliate_group_id
					LEFT JOIN affiliate_to_reference atr ON atr.affiliate_group_id = ag.affiliate_group_id
				WHERE
				 atr.reference_type = 'global' AND
				 ab.affiliate_size = '" . $size . "' 
				ORDER BY RAND()
				LIMIT 1";
        $query = $DB->prepare( $sql );
        $query->execute();
        $banner = $query->fetch();

    }

    if ( $banner ) {

        $banner[ 'affiliate_code' ] = str_replace( "http://", "https://", $banner[ 'affiliate_code' ] );
        return '
            <div class="affiliate affiliate-' . $size . ' visible-' . $size . '-block">
            <div class="affiliate-header">' . TEXT_AFFILIATE_HEADER . '</div>
            ' . $banner[ 'affiliate_code' ] . '
            </div>';
    } else {
        return '
        <div class="affiliate affiliate-' . $size . ' visible-' . $size . '-block">
        <div class="affiliate-header">' . TEXT_AFFILIATE_HEADER . '</div>
        <img src="#SITE_URL#images/banner/oon/oon-trainer-160x300.jpg" width="160" height="300" alt=""/>
        </div>';
    }
}

// User Language

function load_language_key( $key, $language ) {
    // Texte werden geladen
    $sql = "SELECT * FROM text WHERE language_code='" . $language . "' AND text_key='" . $key . "' LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $text_language = $query->fetch();

    return $text_language[ 'text_value' ];

}

// Child User
function isValidUserChild( $childID ) {
    global $DB;
    $sql = "SELECT 
			u.user_id, u.user_sub_of
		 FROM 
			user u
			LEFT JOIN user_details ud ON u.user_id = ud.user_id
		 WHERE 
			MD5(CONCAT(u.user_id,ud.user_nickname)) = '" . $childID . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $child = $query->fetch();

    // Child
    if ( $_SESSION[ 'user' ][ 'user_id' ] == $child[ 'user_sub_of' ] && $_SESSION[ 'user' ][ 'user_sub_of' ] == 0 ) {
        return true;
    }

    // Aktuell im anderen Child / Child
    if ( $_SESSION[ 'user' ][ 'user_sub_of' ] == $child[ 'user_sub_of' ] && $_SESSION[ 'user' ][ 'user_sub_of' ] > 0 ) {
        return true;
    }

    return false;
}

function isValidUserParent( $parentID ) {
    global $DB;
    $sql = "SELECT 
			u.user_id, u.user_sub_of
		 FROM 
			user u
			LEFT JOIN user_details ud ON u.user_id = ud.user_id
		 WHERE 
			MD5(CONCAT(u.user_id,ud.user_nickname)) = '" . $parentID . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $parent = $query->fetch();

    if ( $parent[ 'user_id' ] == $_SESSION[ 'user' ][ 'user_sub_of' ] && $_SESSION[ 'user' ][ 'user_sub_of' ] > 0 ) {
        return true;
    }

    return false;
}

function getParentEmail( $parentID ) {
    global $DB;
    if ( $parentID == 0 ) return false;

    $sql = "SELECT 
			u.user_email
		 FROM 
			user u
		 WHERE 
			u.user_id = '" . $parentID . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $parent = $query->fetch();

    return $parent[ 'user_email' ];
}

function getProfilList() {
    global $DB;
    $sql = "SELECT 
				u.*, 
				ud.*
			 FROM 
				user u
				LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
			 WHERE 
				u.user_id != '" . $_SESSION[ 'user' ][ 'user_id' ] . "' AND
				(
				u.user_sub_of = '" . $_SESSION[ 'user' ][ 'user_id' ] . "' OR
				(u.user_sub_of = '" . $_SESSION[ 'user' ][ 'user_sub_of' ] . "'AND u.user_sub_of IS NOT NULL) OR
				(u.user_id = '" . $_SESSION[ 'user' ][ 'user_sub_of' ] . "' AND u.user_sub_of IS NULL)
				)
			";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_search = $query->fetchAll();
    $total = $query->rowCount();

    if ( $total == 0 ) {
        return '';
    }
    $output = '<li class="dropdown-header">' . TEXT_MORE_PROFILES . '</li>';
    foreach ( $get_search as $search ) {
        $output .= '<li><a href="#SITE_URL#me/settings/profiles/">' . $search[ 'user_nickname' ] . '</a></li>';
    }
    $output .= '<li role="separator" class="divider"></li>';

    return $output;

}

// DELETE PROFILE
function deleteUserFinaly( $userId ) {
    global $DB;
    $sql = "DELETE FROM user WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_details WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_to_sport_group_value WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_watchlist WHERE user_id = '" . $userId . "' OR watchlist_user_id = '" . $userId . "'; " .
    "DELETE FROM user_shoutbox WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_settings WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_sessions WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_profile WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_to_messages WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_history WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_friendship WHERE user_id = '" . $userId . "' OR friendship_user_id= '" . $userId . "'; " .
    "DELETE FROM user_history WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM user_blocked WHERE user_id = '" . $userId . "' OR blocked_user_id = '" . $userId . "'; " .
    "DELETE FROM user_to_groups WHERE user_id = '" . $userId . "'; " .
    "DELETE FROM groups_message WHERE group_message_user_id = '" . $userId . "'; ";

    $query = $DB->prepare( $sql );
    $query->execute();

    $images_size = array( "200x200", "125x125", "115x100", "100x100", "300x300", "115x115", "50x50", "150x150", "600x600", "800x800" );
    $sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='" . $userId . "' AND um.media_id=m.media_id";
    $query = $DB->prepare( $sql );
    $query->execute();
    $get_del_image = $query->fetchAll();

    foreach ( $get_del_image as $del_image ) {
        $sql = "DELETE FROM user_media WHERE media_id='" . $del_image[ 'media_id' ] . "'";
        $query = $DB->prepare( $sql );
        $query->execute();

        $sql = "DELETE FROM user_to_media WHERE user_id='" . $userId . "' AND media_id='" . $del_image[ 'media_id' ] . "'";
        $query = $DB->prepare( $sql );
        $query->execute();

        if ( file_exists( SERVER_IMAGE_PATH . "user/" . $del_image[ 'media_file' ] . "-" . IMAGE_MAX_DEFAULT_SIZE . ".jpg" ) ) {
            unlink( SERVER_IMAGE_PATH . "user/" . $del_image[ 'media_file' ] . "-" . IMAGE_MAX_DEFAULT_SIZE . ".jpg" );
        } else {
            //	echo "nicht gelöscht<br><br>";
        }
        if ( file_exists( SERVER_IMAGE_PATH . "temp/" . $del_image[ 'media_file' ] . ".jpg" ) ) {
            unlink( SERVER_IMAGE_PATH . "temp/" . $del_image[ 'media_file' ] . ".jpg" );
        } else {
            //	echo "nicht gelöscht<br><br>";
        }

        foreach ( $images_size as $imageSize ) {
            if ( file_exists( SERVER_IMAGE_PATH . "_static/profil/" . $imageSize . "/" . $del_image[ 'media_file' ] . ".jpg" ) ) {
                unlink( SERVER_IMAGE_PATH . "_static/profil/" . $imageSize . "/" . $del_image[ 'media_file' ] . ".jpg" );
            } else {
                //echo "<br>nicht gelöscht<br><br>";
            }
        }
    }
}

// DELETE Image
function deleteImageFinaly( $mediaId ) {
    global $DB;

    $images_size = array( "200x200", "125x125", "115x100", "100x100", "300x300", "115x115", "50x50", "150x150", "600x600", "800x800" );
    $sql = "SELECT * FROM user_media m, user_to_media um WHERE um.user_id='" . $_SESSION[ 'user' ][ 'user_id' ] . "' AND um.media_id=m.media_id AND m.media_id = '" . $mediaId . "' LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $del_image = $query->fetch();

    $sql = "DELETE FROM user_media WHERE media_id='" . $del_image[ 'media_id' ] . "'";
    $query = $DB->prepare( $sql );
    $query->execute();

    $sql = "DELETE FROM user_to_media WHERE user_id='" . $userId . "' AND media_id='" . $del_image[ 'media_id' ] . "'";
    $query = $DB->prepare( $sql );
    $query->execute();

    if ( file_exists( SERVER_IMAGE_PATH . "user/" . $del_image[ 'media_file' ] . "-" . IMAGE_MAX_DEFAULT_SIZE . ".jpg" ) ) {
        unlink( SERVER_IMAGE_PATH . "user/" . $del_image[ 'media_file' ] . "-" . IMAGE_MAX_DEFAULT_SIZE . ".jpg" );
    } else {
        //	echo "nicht gelöscht<br><br>";
    }
    if ( file_exists( SERVER_IMAGE_PATH . "temp/" . $del_image[ 'media_file' ] . ".jpg" ) ) {
        unlink( SERVER_IMAGE_PATH . "temp/" . $del_image[ 'media_file' ] . ".jpg" );
    } else {
        //	echo "nicht gelöscht<br><br>";
    }

    foreach ( $images_size as $imageSize ) {
        if ( file_exists( SERVER_IMAGE_PATH . "_static/profil/" . $imageSize . "/" . $del_image[ 'media_file' ] . ".jpg" ) ) {
            unlink( SERVER_IMAGE_PATH . "_static/profil/" . $imageSize . "/" . $del_image[ 'media_file' ] . ".jpg" );
        } else {
            //echo "<br>nicht gelöscht<br><br>";
        }
    }

}

// DELETE Image Just Phisical Copies to Replace Original
function deleteImageForUpdate( $mediaId ) {
    global $DB;

    $images_size = array( "200x200", "125x125", "115x100", "100x100", "300x300", "115x115", "50x50", "150x150", "600x600", "800x800" );
    $sql = "SELECT m.media_file FROM user_media m, user_to_media um WHERE um.user_id='" . $_SESSION[ 'user' ][ 'user_id' ] . "' AND um.media_id=m.media_id AND m.media_id = '" . $mediaId . "' LIMIT 1";
    $query = $DB->prepare( $sql );
    $query->execute();
    $del_image = $query->fetch();

    foreach ( $images_size as $imageSize ) {
        if ( file_exists( SERVER_IMAGE_PATH . "_static/profil/" . $imageSize . "/" . $del_image[ 'media_file' ] . ".jpg" ) ) {
            unlink( SERVER_IMAGE_PATH . "_static/profil/" . $imageSize . "/" . $del_image[ 'media_file' ] . ".jpg" );
        }
    }

}