<?php 

$output = '<section>
   	<div class="col-md-6 col-sm-12">
    <header class="list-group-item start_register_header">' . TEXT_INDEX_REGISTER_HEADER . '</header>
        <div class="list-group-item  start_register_box">';
		
switch ($_SESSION['register_step']) {
// STEP1

    case 1:
        // Section überschreiben
        $output = '<section>
        <div class="col-md-6 col-sm-12">
        <header class="list-group-item start_register_header">' . TEXT_INDEX_REGISTER_HEADER . '</header>
            <div class="list-group-item  start_register_box choice-start_register_box"><div class="row choice_form_line">
           ';


         $sql = "SELECT * FROM user_types ut, user_types_details utd WHERE ut.user_type_id=utd.user_type_id AND utd.language_code='".$_SESSION['language_code']."' ORDER BY ut.user_type_id ASC";

        $query = $DB->prepare($sql);
        $query->execute();
        $get_user_types = $query->fetchAll();

        $choice_x=0;
        foreach ( $get_user_types as $user_types) {

            $output .= '
                <div class="col-md-6 col-sm-3 choice-element-' . $user_types['user_type_index'] . '">
                <form method="post" action="#SITE_URL#register/"  class="form"><input type="submit" name="rs1" value="'.constant('TEXT_GLOBAL_REGISTER_AS_' . $user_types['user_type_id']) . '" class="btn btn-sm btn-primary choice-button">
                <input type="hidden" name="register_type" value="' . $user_types['user_type_id'] . '">
                <input type="hidden" name="register_step" value="1">
                 </form>
                 </div>';
        }

        $output .= '</div>';

    break;

    case 2:		
    // STEP2

        $nickname = TEXT_REGISTER_YOUR_NICKNAME;
        if($_SESSION['register_type'] == 3) $nickname = TEXT_REGISTER_YOUR_CLUB;
        if($_SESSION['register_type'] == 4) $nickname = TEXT_REGISTER_YOUR_LOCATION;

        $output .= '<form method="post" action="#SITE_URL#register/"  class="form">
        <div class="text-center">
        <div class="row form_line">
        <div class="col-md-6">
            <label>' . $nickname . ':</label><br>
            <small>' . TEXT_REGISTER_NAME_VISIBLE . '</small>
        </div>
        <div class="col-md-6"><input type="text" name="register_nickname" value="' . $_SESSION['register_nickname'] . '"  class="form-control" required></div>

        </div>

        <div class="row form_line">
        <div class="col-md-6">
        <label>' . TEXT_REGISTER_PASSWORD . ':</label></div>
        <div class="col-md-6"><input type="password" name="register_password" value="' . $_SESSION['register_password'] . '"  class="form-control" required></div>
        </div>

        <div class="row form_line">
        <div class="col-md-6">
        <label>' . TEXT_REGISTER_PASSWORD_REPEAT . ':</label></div>
        <div class="col-md-6"><input type="password" name="register_password_repeat" value="' . $_SESSION['register_password_repeat'] . '"  class="form-control" required></div></div>


        <div class="row form_line">
            <div class="col-md-6">
                <label>' . TEXT_REGISTER_EMAIL . ':</label></div>
            <div class="col-md-6"><input type="email" name="register_email" value="' . $_SESSION['register_email'] . '"  class="form-control" required>
            </div>
            <div class="col-md-12">
                <small>' . TEXT_REGISTER_YOUR_EMAIL_INFO . '</small>
            </div>
        </div>

            <div class="row form_line">
                <div class="col-md-12">
                    <input type="hidden" name="register_step" value="2">
                    <input type="submit" name="rs2" value="' . TEXT_REGISTER_NEXT_STEP . '" class="btn btn-sm btn-primary form-control">
                </div>
            </div>

        </div>
        </form>';
    break;

    case 3:

        // STEP3

        $placeholder_register_first_name = TEXT_REGISTER_YOUR_FIRSTNAME;
        if($_SESSION['register_type'] == 3 || $_SESSION['register_type'] == 4) { 
            $placeholder_register_first_name = TEXT_REGISTER_YOUR_FIRSTNAME_CLUB_LOCATION;
        }


        $placeholder_register_last_name = TEXT_REGISTER_YOUR_LASTNAME;
        if($_SESSION['register_type'] == 3 || $_SESSION['register_type'] == 4) { 
            $placeholder_register_last_name = TEXT_REGISTER_YOUR_LASTNAME_CLUB_LOCATION;
        }

        $placeholder_register_birthday = TEXT_REGISTER_YOUR_BIRTHDAY;
        if($_SESSION['register_type'] == 3) { 
             $placeholder_register_birthday = TEXT_REGISTER_YOUR_BIRTHDAY_CLUB;
        }

        if($_SESSION['register_type'] == 4) { 
             $placeholder_register_birthday = TEXT_REGISTER_YOUR_BIRTHDAY_LOCATION;
        }




        $output .= '
            <form method="post" action="#SITE_URL#register/" id="register_steps"  class="form">
            <div class="text-center">
            <div class="row form_line">

            <div class="col-md-6">
                <label>' . $placeholder_register_first_name . ':</label>
            </div>
            <div class="col-md-6">
                <input type="text" name="register_firstname" value="' . $_SESSION['register_firstname'] . '" required class="form-control">
            </div>
            </div>

            <div class="row form_line">

                <div class="col-md-6">
                    <label>' . $placeholder_register_last_name . ':</label></div>
                <div class="col-md-6">
                    <input type="text" name="register_lastname" value="' . $_SESSION['register_lastname'] . '" required class="form-control">
                </div>
            </div>

            <div class="row form_line">

            <div class="col-md-12"><small>' . TEXT_REGISTER_YOUR_NAME_INFO . '</small></div>
            </div>';

            if($_SESSION['register_type']!=3 && $_SESSION['register_type']!=4) {
                $output .= '
                    <div class="row form_line">

                    <div class="col-md-6">
                    <label>' . TEXT_REGISTER_GENDER . ':</label>
                    </div>
                    <div class="col-md-6"><select name="register_gender" class="form-control">
                    <option value="">' . TEXT_REGISTER_PLEASE_CHOOSE . '</option>
                    <option value="f"';
                if($_SESSION['register_gender'] == "f") {
                    $output .= ' selected="selected"';
                }

                $output .= '>' . TEXT_REGISTER_FEMALE . '</option>
                    <option value="m"';
                if($_SESSION['register_gender'] == "m") {
                    $output .= 'selected="selected"';
                }

                $output .= '>' . TEXT_REGISTER_MALE . '</option>
                    </select></div>
                    </div>';

            }
        $output .= '<div class="row form_line">

            <div class="col-md-6">
                <label>' . $placeholder_register_birthday . '(' . TEXT_REGISTER_DATE_FORMAT . '):</label>
            </div>
            <div class="col-md-6">
                  <input type="date" name="register_dob" value="' . $_SESSION['register_dob'] . '" class="form-control" required max="'.date("Y-m-d",strtotime("- " .REGISTER_MIN_YEARS . "years")) . '">
            </div>
        </div><div class="row form_line">

        <div class="col-md-6">
        <label>' . TEXT_REGISTER_LAND . ':</label>
        </div>
        <div class="col-md-6"><select name="register_country" id="register_country" class="form-control">
        <option value=0 >' . TEXT_REGISTER_PLEASE_CHOOSE . ':'  . $_SESSION['register_country'] . '</option>
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
        
        $sort=1;
        foreach ($get_countries as $countries){
            $select_hr="";
            if($sort!=$countries['country_sort']) { 
                $select_hr='class="select-hr"'; 
            }
            $output .= '<option value="' . $countries['country_code'] . '" ' . $select_hr . ' ';

            if($_SESSION['register_country'] == $countries['country_code']) {
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
                    <label>' . TEXT_REGISTER_ZIPCODE . ':</label>
                </div>
                <div class="col-md-6">
                <input type="text" name="geo_register" id="geo_register" value="' . $_SESSION['geo_register'] . '" required  class="form-control">
                

        <input type="hidden" id="geo_place_id" name="geo_place_id" value="' . $_SESSION['geo_place_id'] . '"/>
        <br><br>

                </div>
            </div>

        </div>

        <div class="row form_line">

                <div class="col-md-12">
        <input type="hidden" name="register_step" value="3">
        <input type="submit" name="rs3" value="' . TEXT_REGISTER_NEXT_STEP . '" class="btn btn-sm btn-primary form-control">
        </div>

        </div>
        </form>';

        // FOOTER JS ...

        $header_ext = '<link rel="stylesheet" type="text/css" href="#SITE_URL#css/bootstrap-datepicker3.css">
        ';

        $place_holder_register_date_end='endDate: "-'.REGISTER_MIN_YEARS . 'y",';
        if($_SESSION['register_type'] == 3 || $_SESSION['register_type'] == 4) {
            $place_holder_register_date_end='';
        }

        $footer_ext = '
        
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAAUCGinXBvsdx8WHD_PdVvoXA42lakEd4"></script>
        
        <script src="#SITE_URL#js/geo.js"></script>
';


    break;

    // STEP 4
    case 4:
        $output .= '
            <form method="post" action="#SITE_URL#register/" id="register_steps"  class="form">
            <div class="text-center">
            <div class="row form_line">

            <div class="col-md-6">
                <label>' . TEXT_REGISTER_YOUR_SPORTS . ':</label>
            </div>
            <div class="col-md-6">
                '.build_sports_select() . '
            </div>
            </div>
            <div class="col-md-12">
            <small>' . TEXT_REGISTER_CHOOSE_SPORT . '</small>
            </div>
            <div class="row form_line">
            </div>';

        $output .= '
        <div class="row form_line">

        <div class="col-md-6">
            <label>'.constant('TEXT_GLOBAL_PROFESSION_' . $_SESSION['register_type']) . ':</label>
        </div>
        <div class="col-md-6"  style="text-align:left; font-weight:normal;">
            <label style=" font-weight:normal;"><input  type="radio" name="sport_groups_profession" value="1"';

        if($_SESSION['sport_groups_profession'] == 1) {
            $output .= ' checked=checked ';
        }

        $output .= '> '.constant('TEXT_GLOBAL_PROFESSION_BEGINNER_' . $_SESSION['register_type']) . '</label><br>
    <label style=" font-weight:normal;"><input  type="radio" name="sport_groups_profession" value="2"';

        if($_SESSION['sport_groups_profession'] == 2) {
            $output .= ' checked=checked ';
        }

        $output .= '> '.constant('TEXT_GLOBAL_PROFESSION_ROOKIE_' . $_SESSION['register_type']) . '</label><br>
    <label style=" font-weight:normal;"><input  type="radio" name="sport_groups_profession" value="3"';

        if($_SESSION['sport_groups_profession'] == 3) {
            $output .= ' checked=checked ';
        }

        $output .= '> '.constant('TEXT_GLOBAL_PROFESSION_AMATEUR_' . $_SESSION['register_type']) . '</label><br>
    <label style=" font-weight:normal;"><input  type="radio" name="sport_groups_profession" value="4"';

        if($_SESSION['sport_groups_profession'] == 4) {
            $output .= ' checked=checked ';
        }

        $output .= '> '.constant('TEXT_GLOBAL_PROFESSION_PROFI_' . $_SESSION['register_type']) . '</label><br>
    <label style=" font-weight:normal;"><input  type="radio" name="sport_groups_profession" value="5"';

        if($_SESSION['sport_groups_profession'] == 5) {
            $output .= ' checked=checked ';
        }

        $output .= '> ' . TEXT_GLOBAL_PROFESSION_OTHER . '</label>
        </div>
        </div>';


        if($_SESSION['register_type']!=3 && $_SESSION['register_type']!=4) {
        $output .= '
        <div class="row form_line">

        <div class="col-md-6">
            <label>' . TEXT_REGISTER_YOUR_STATUS . ':</label>
        </div>
        <div class="col-md-6" style="text-align:left; font-weight:normal;">
            <label style=" font-weight:normal;"><input type="radio" name="sport_groups_status" value="1"';

            if($_SESSION['sport_groups_status'] == 1) {
                $output .= ' checked=checked ';
            }

            $output .= '> ' . TEXT_REGISTER_YOUR_STATUS_FREE . '</label><br>
        <label style=" font-weight:normal;"><input type="radio" name="sport_groups_status" value="2"';

            if($_SESSION['sport_groups_status'] == 2) {
                $output .= ' checked=checked ';
            }

            $output .= '> ' . TEXT_REGISTER_YOUR_STATUS_MEMBER . '</label><br>
        <label style=" font-weight:normal;"><input type="radio" name="sport_groups_status" value="3"';

            if($_SESSION['sport_groups_status'] == 3) {
                $output .= ' checked=checked ';
            }

            $output .= '> ' . TEXT_REGISTER_YOUR_STATUS_CONTRACT . '</label><br>
        <label style=" font-weight:normal;"><input type="radio" name="sport_groups_status" value="3"';

            if($_SESSION['sport_groups_status'] == 4) {
                $output .= ' checked=checked ';
            }

            $output .= '> ' . TEXT_GLOBAL_OTHER . '</label>
        </div>
        </div>';


        }

        $placeholder_register_handycap = TEXT_REGISTER_YOUR_HANDYCAP_INFO;
        if($_SESSION['register_type'] == 3 ) {
            $placeholder_register_handycap = TEXT_REGISTER_YOUR_HANDYCAP_INFO_CLUB;
        }
        if($_SESSION['register_type'] == 4 ) {
            $placeholder_register_handycap = TEXT_REGISTER_YOUR_HANDYCAP_INFO_LOCATION;
        }

        $output .= '<div class="row form_line">

        <div class="col-md-6">
            <label>' . TEXT_REGISTER_YOUR_HANDYCAP . ':</label>
        </div>
        <div class="col-md-6" style="text-align:left; font-weight:normal;">
            <label style=" font-weight:normal;"><input type="checkbox" name="sport_groups_handycap" value="1"';

            if($_SESSION['sport_groups_handycap'] == 1) $output .= ' checked=checked ';

            $output .= '> ' . $placeholder_register_handycap . '</label>
            </div>
            </div>

            <div class="row form_line">

            <div class="col-md-12">
            <input type="hidden" name="register_step" value="4">
            <input type="submit" name="rs4" value="' . TEXT_REGISTER_NEXT_STEP . '" class="btn btn-sm btn-primary form-control">
            </div>
            </div>

            </div>
            </form>';

        $header_ext = '';

        $footer_ext = '<script src="#SITE_URL#js/sport_subgroup.js"></script>';

    break;

    // STEP 4
    case 5:
        //var_dump($_SESSION);
        $output .= '

        <div class="text-center">

        <form method="post" action="#SITE_URL#register/" id="register_steps"  class="form">
        <div class="row form_line">

            <div class="col-md-6">
                <label>' . TEXT_REGSITER_NEARLY_COMPLETE . '</label>
            </div>
            <div class="col-md-6" style="text-align:left;">
            ' . TEXT_REGISTER_YOUR_TERMS_INFO . '
                <input type="checkbox" name="register_terms"> ' . TEXT_REGISTER_YOUR_TERMS_INFO_TERMS . '
                <input type="checkbox" name="register_privacy"> ' . TEXT_REGISTER_YOUR_TERMS_INFO_PRIVACY . '
            </div>
        </div>

        <div class="row form_line">
            <div class="col-md-12"><br>
                <br>
                <input type="hidden" name="register_step" value="5">
                <input type="submit" name="rs5" value="' . TEXT_REGISTER_FINISH_NOW . '" class="btn btn-sm btn-primary form-control">
                <br>
                <br>
            </div>
        </div>
        </form>

        <div class="row form_line text-left">
            <div class="col-md-12">
                <strong>' . TEXT_REGISTER_SUMMARY . ':</strong>
            ';

        $step2_form = '<form action="#SITE_URL#register/" method="post">
        <button type="submit"  class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i>
        </button>
        <input type="hidden" name="register_step" value="2">
        <input type="hidden" name="register_step_back" value="2">
        </form>';

        $step3_form = '<form action="#SITE_URL#register/" method="post">
        <button type="submit"  class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i>
        </button>
        <input type="hidden" name="register_step" value="3">
        <input type="hidden" name="register_step_back" value="3">
        </form>';	

        $step4_form = '<form action="#SITE_URL#register/" method="post">
        <button type="submit"  class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i>
        </button>
        <input type="hidden" name="register_step" value="4">
        <input type="hidden" name="register_step_back" value="4">
        </form>';			

        $nickname = TEXT_REGISTER_YOUR_NICKNAME;
        if($_SESSION['register_type'] == 3) $nickname = TEXT_REGISTER_YOUR_CLUB;
        if($_SESSION['register_type'] == 4) $nickname = TEXT_REGISTER_YOUR_LOCATION;


        $output .= '

        <div class="row form_line">
        <div class="col-md-6">
        <label>' . TEXT_REGISTER_AS . ':</label>
        </div>
        <div class="col-md-6">' . constant('TEXT_REGISTER_USER_TYPE_' . $_SESSION['register_type']) . '</div>
        </div>
        <div class="row form_line">
        <div class="col-md-6">
        <label>' . $nickname . ':</label>
        </div>
        <div class="col-xs-10 col-sm-10 col-md-5">' . $_SESSION['register_nickname'] . ' 
        </div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step2_form . '</div>

        </div>

        <div class="row form_line">
        <div class="col-md-6">
        <label>' . TEXT_REGISTER_PASSWORD . ':</label></div>
        <div class="col-xs-10 col-sm-10 col-md-5">******</div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step2_form . '</div>
        </div>


        <div class="row form_line">
        <div class="col-md-6">
        <label>' . TEXT_REGISTER_EMAIL . ':</label></div>
        <div class="col-xs-10 col-sm-10 col-md-5">' . $_SESSION['register_email'] . '
        </div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step2_form . '</div>';


            $placeholder_register_first_name = TEXT_REGISTER_YOUR_FIRSTNAME;
        if($_SESSION['register_type'] == 3 || $_SESSION['register_type'] == 4) $placeholder_register_first_name = TEXT_REGISTER_YOUR_FIRSTNAME_CLUB_LOCATION;

        $placeholder_register_last_name = TEXT_REGISTER_YOUR_LASTNAME;
        if($_SESSION['register_type'] == 3 || $_SESSION['register_type'] == 4) $placeholder_register_last_name = TEXT_REGISTER_YOUR_LASTNAME_CLUB_LOCATION;


        $placeholder_register_birthday = TEXT_REGISTER_YOUR_BIRTHDAY;
        if($_SESSION['register_type'] == 3) $placeholder_register_birthday = TEXT_REGISTER_YOUR_BIRTHDAY_CLUB;

        if($_SESSION['register_type'] == 4) $placeholder_register_birthday = TEXT_REGISTER_YOUR_BIRTHDAY_LOCATION;




        $output .= '
        </div>
        <br>

        <div class="row form_line">

        <div class="col-md-6">
            <label>' . $placeholder_register_first_name . ':</label>
        </div>
        <div class="col-xs-10 col-sm-10 col-md-5">
            ' . $_SESSION['register_firstname'] . ' 
        </div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step3_form . '</div>
        </div>

        <div class="row form_line">

            <div class="col-md-6">
                <label>' . $placeholder_register_last_name . ':</label></div>
            <div class="col-xs-10 col-sm-10 col-md-5">
                ' . $_SESSION['register_lastname'] . '
            </div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step3_form . '</div>
        </div>

        <div class="row form_line">

        <div class="col-md-12"><small>' . TEXT_REGISTER_YOUR_NAME_INFO . '</small></div>
        <br><br>
        </div>';

        if($_SESSION['register_type']!=3 && $_SESSION['register_type']!=4) 
        {
        $output .= '
        <div class="row form_line">

        <div class="col-md-6">
            <label>' . TEXT_REGISTER_GENDER . ':</label>
        </div>
        <div class="col-xs-10 col-sm-10 col-md-5">';
        if($_SESSION['register_gender'] == "f")
        {
            $output .= TEXT_REGISTER_FEMALE;
        }


        if($_SESSION['register_gender'] == "m")
        {
            $output .= TEXT_REGISTER_MALE;
        }

        $output .= '</div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step3_form . '</div>
        </div>';

        }
        $output .= '<div class="row form_line">

            <div class="col-md-6">
                <label>' . $placeholder_register_birthday . '(' . TEXT_REGISTER_DATE_FORMAT . '):</label>
            </div>
            <div class="col-xs-10 col-sm-10 col-md-5">' . $_SESSION['register_dob'] . '
            </div>
            <div class="col-xs-2 col-sm-2 col-md-1">' . $step3_form . '</div>
            </div><div class="row form_line">

            <div class="col-md-6">
            <label>' . TEXT_REGISTER_LAND . ':</label>
            </div>
            <div class="col-xs-10 col-sm-10 col-md-5">
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
            if($_SESSION['register_country'] == $countries['country_code']) {
                if($countries['translatedName']) {
                    $countries['country_name'] = $countries['translatedName'];
                }

                $output .= $countries['country_name'];
            }
        }		
       
            $output .= '</div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step3_form . '</div>
        </div>

        <div class="row form_line">

                <div class="col-md-6">
                    <label>' . TEXT_REGISTER_ZIPCODE . ':</label>
                </div>
                <div class="col-xs-10 col-sm-10 col-md-5">
                '. get_city_name($_SESSION['geo_place_id']).'
                </div>
                
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step3_form . '</div>
        </div>
        <div class="col-md-12"><small><!--' . $_SESSION['geo_register'] . ' (-->' . TEXT_REGISTER_YOUR_GEO_INFO . '</small></div>
        <br><br>
        <div class="row form_line">

        <div class="col-md-6">
            <label>' . TEXT_REGISTER_YOUR_SPORTS . ':</label>
        </div>
        <div class="col-xs-10 col-sm-10 col-md-5">
        ';

        for($sg_value=3; $sg_value >= 1; $sg_value--) {
            if($_SESSION['sport_groups_' . $sg_value] != '') {
                $sport_group_value = $_SESSION['sport_groups_' . $sg_value];
                break;
            }
        }

        //$output.=build_sports_select() . '
        $output.=get_user_main_sport("register", $_SESSION['sport_groups']) . ' > '.get_user_sport_list(0,$sport_group_value) . '			
        </div>
        <div class="col-xs-2 col-sm-2 col-md-1">' . $step4_form . '</div>
        </div>

        <div class="row form_line">
        </div>';

        $output .= '
            <div class="row form_line">

            <div class="col-md-6">
                <label>'.constant('TEXT_GLOBAL_PROFESSION_' . $_SESSION['register_type']) . ':</label>
            </div>
            <div class="col-xs-10 col-sm-10 col-md-5">';

        if($_SESSION['sport_groups_profession'] == 1) {
            $output.= constant('TEXT_GLOBAL_PROFESSION_BEGINNER_' . $_SESSION['register_type']);
        }

        if($_SESSION['sport_groups_profession'] == 2) {
            $output.= constant('TEXT_GLOBAL_PROFESSION_ROOKIE_' . $_SESSION['register_type']);
        }
        if($_SESSION['sport_groups_profession'] == 3) {
            $output.= constant('TEXT_GLOBAL_PROFESSION_AMATEUR_' . $_SESSION['register_type']);
        }
        if($_SESSION['sport_groups_profession'] == 4) {
            $output.= constant('TEXT_GLOBAL_PROFESSION_PROFI_' . $_SESSION['register_type']);
        }

        if($_SESSION['sport_groups_profession'] == 5) {
            $output .= TEXT_GLOBAL_PROFESSION_OTHER;
        }

        $output .= '	
            </div>
            <div class="col-xs-2 col-sm-2 col-md-1">' . $step4_form . '</div>
            </div>';


        if($_SESSION['register_type']!=3 && $_SESSION['register_type']!=4) {
            $output .= '
            <div class="row form_line">

            <div class="col-md-6">
                <label>' . TEXT_REGISTER_YOUR_STATUS . ':</label>
            </div>
            <div class="col-xs-10 col-sm-10 col-md-5">';

                if($_SESSION['sport_groups_status'] == 1) {
                    $output .= TEXT_REGISTER_YOUR_STATUS_FREE;
                }

                if($_SESSION['sport_groups_status'] == 2) {
                    $output .= TEXT_REGISTER_YOUR_STATUS_MEMBER;
                }

                if($_SESSION['sport_groups_status'] == 3) {
                    $output .= TEXT_REGISTER_YOUR_STATUS_CONTRACT;
                }

                if($_SESSION['sport_groups_status'] == 4) {
                    $output .= TEXT_GLOBAL_OTHER;
                }

            $output .= '	
            </div>
            <div class="col-xs-2 col-sm-2 col-md-1">' . $step4_form . '</div>
            </div>';
        }

        $placeholder_register_handycap = TEXT_REGISTER_YOUR_HANDYCAP_INFO;
        if($_SESSION['register_type'] == 3 ) {
            $placeholder_register_handycap = TEXT_REGISTER_YOUR_HANDYCAP_INFO_CLUB;
        }
        if($_SESSION['register_type'] == 4 ) {
            $placeholder_register_handycap = TEXT_REGISTER_YOUR_HANDYCAP_INFO_LOCATION;
        }

        if($_SESSION['sport_groups_handycap'] == 1)  {

            $output .= '<div class="row form_line">
                <div class="col-md-6">
                    <label>' . TEXT_REGISTER_YOUR_HANDYCAP . ':</label>
                </div>
                <div class="col-xs-10 col-sm-10 col-md-5">' . $placeholder_register_handycap . '
                </div>
                <div class="col-xs-2 col-sm-2 col-md-1">' . $step4_form . '</div>
                </div>	';
        }

        $output .= '	
                </div>
            </div>
            </div>
            ';

        $header_ext = '';
        $footer_ext = '';

    break;

}

$output .= '
        </div>
		<div class="clearfix"></div>
    </div>
	
	
    <div class="col-md-6 col-sm-12">
    <header  class="list-group-item start_register_header"><strong>' . TEXT_REGISTER_STEP_HEADER . '</strong></header>
            <div class="list-group-item  start_register_box">
			<div class="row form_line">
		<div class="col-md-12">';
		
if($_SESSION['register_step_2'] == 1) {
    $step_class = 'primary';
    $step_symbol = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
    $step_status = '';
}
else {
    $step_class = 'default';
    $step_status = 'disabled';
    $step_symbol = '';
}

if($_SESSION['register_step'] == 2) {
    $step_class = 'primary';
    $step_status = '';
    $step_symbol = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
}


$output .= '<form action="#SITE_URL#register/" method="post">
    <button type="submit"  class="btn btn-sm btn-' . $step_class . ' form-control step" ' . $step_status . '>' . $step_symbol . ' ' . TEXT_REGISTER_STEP_1 . '</button>
    <input type="hidden" name="register_step" value="2">
    <input type="hidden" name="register_step_back" value="2">
    </form>
    </div>
    </div>
    <div class="row form_line">
    <div class="col-md-12">';

if($_SESSION['register_step_3'] == 1) {
    $step_class = 'primary';
    $step_symbol = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
    $step_status = '';
}
else {
    $step_status = 'disabled';
    $step_symbol = '';
    $step_class = 'default';
}

if($_SESSION['register_step'] == 3) {
    $step_class = 'primary';
    $step_status = '';
    $step_symbol = '<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>';
}


$output .= '<form action="#SITE_URL#register/" method="post">
    <button type="submit"  class="btn btn-sm btn-' . $step_class . ' form-control step" ' . $step_status . '>' . $step_symbol . ' ' . TEXT_REGISTER_STEP_2 . '</button>
    <input type="hidden" name="register_step" value="3">
    <input type="hidden" name="register_step_back" value="3">
    </form>
    </div>
    </div>
    <div class="row form_line">
    <div class="col-md-12">';

if($_SESSION['register_step_4'] == 1) {
    $step_class = 'primary';
    $step_symbol = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
    $step_status = '';
}
else {
    $step_status = 'disabled';
    $step_symbol = '';
    $step_class = 'default';
}

if($_SESSION['register_step'] == 4) {
    $step_class = 'primary';
    $step_status = '';
    $step_symbol = '<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>';
}


$output .= '<form action="#SITE_URL#register/" method="post">
    <button type="submit"  class="btn btn-sm btn-' . $step_class . ' form-control step" ' . $step_status . '>' . $step_symbol . ' ' . TEXT_REGISTER_STEP_3 . '</button>
    <input type="hidden" name="register_step" value="4">
    </form>
    </div>
    </div>
    <div class="row form_line">
    <div class="col-md-12">';

if($_SESSION['register_step_5'] == 1) {
    $step_class = 'primary';
    $step_symbol = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
    $step_status = '';
}
else {
    $step_status = 'disabled';
    $step_symbol = '';
    $step_class = 'default';
}
if($_SESSION['register_step'] == 5) {
    $step_class = 'primary';
    $step_status = '';
    $step_symbol = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
}


$output .= '<form action="#SITE_URL#register/" method="post">
		<button type="submit"  class="btn btn-sm btn-' . $step_class . ' form-control step" ' . $step_status . '>' . $step_symbol . ' ' . TEXT_REGISTER_STEP_4 . '</button>
        <input type="hidden" name="register_step" value="5">
        </form>
		</div>
		</div>
        </div>
    </div>
	<div class="clearfix"></div>
    </section>';


if($_SESSION['register_complete'] == 1) {
  session_destroy();
  session_start();
  
  	$output = '<section>
	
	<div class="col-md-3 col-sm-12"></div><div class="col-md-6 col-sm-12">
    <header class="list-group-item start_register_header">
    <strong>' . TEXT_REGISTER_COMPLETE_DONE . '</strong> ' . TEXT_REGISTER_COMPLETE_THANKS . '</header>
        <div class="list-group-item  start_register_box">
		' . TEXT_REGISTER_COMPLETE_RECEIVE_EMAIL . '
		</div>
		</div>
		<div class="col-md-3 col-sm-12"></div>
	<div class="clearfix"></div>
    </section>
	';
	
	$header_ext = '';
	$footer_ext = '';
}

$content_output = array(
    'TITLE' => 'Kostenlos Anmelden - '.SITE_NAME , 
    'META_DESCRIPTION' =>'', 'CONTENT' => $output, 
    'HEADER_EXT' => $header_ext, 'FOOTER_EXT' => $footer_ext);