<?php

function build_sports_select() {
    global $DB;
    $allSportGroupString = getGroupsNamedAll();

    $option = "<select name=\"sport_groups\" id=\"sport_groups\"  class=\"form-control\" style=\" margin-bottom:5px;\" onChange='getSubGroup(this.value,0,0)'>#LIST#</select>
	<div id=\"sport_sub_group_div_0\"></div>
	<div id=\"sport_sub_group_div_1\"></div>
	<div id=\"sport_sub_group_div_2\"></div>";

    $sql = "SELECT * FROM sport_group sg, sport_group_details sgd WHERE sg.sport_group_id=sgd.sport_group_id AND sgd.language_code='" . $_SESSION[ 'language_code' ] . "' AND sg.sport_group_id != 0
	ORDER BY 
	FIELD(sgd.sport_group_name, " . $allSportGroupString . ") DESC, sgd.sport_group_name";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_groups = $query->fetchAll();

    $list = "<option value=\"\">" . TEXT_REGISTER_PLEASE_CHOOSE . "</option>";
    //$list="";
    
    
    
    $option_character = "";
    foreach ( $get_groups as $group ) {
        if ( stristr($allSportGroupString,$group[ 'sport_group_name' ]) == false ) {
            $character = substr( $group[ 'sport_group_name' ], 0, 1 );
            if ( is_numeric( $character ) ) {
                $character = "0-9";
            }

            if ( $character != $option_character ) {
                $option_character = $character;
                if ( $group[ 'sport_group_name' ] == 'Sonstige' || $group[ 'sport_group_name' ] == 'Other' ) {
                    $option_character = "______";
                }

                $list .= '<optgroup label="' . $option_character . '"></optgroup>';
            }
        }

        $list .= "<option value=\"" . $group[ 'sport_group_id' ] . "\">" . $group[ 'sport_group_name' ] . "</option>";
    }

    $output = str_replace( "#LIST#", $list, $option );

    return $output;
}


function build_search_sports_select( $selected = '', $search_lines = '', $first_level = false ) {
    global $DB;
    $allSportGroupString = getGroupsNamedAll();
    
    if($first_level == true) {
        $option = "<select name=\"shoutbox_group\" id=\"shoutbox_group\"  class=\"form-control\" style=\" margin-bottom:5px;\">#LIST#</select>";    
    } else {
     
        $option = "<select name=\"search_sport_groups_0\" id=\"search_sport_groups_0\"  class=\"form-control\" style=\" margin-bottom:5px;\" onChange='getSearchSubGroup(this.value,0,0)'>#LIST#</select>";

       if ( $search_lines == "" ) {

            $option .= "<div id=\"search_sport_sub_group_div_0\"></div>
            <div id=\"search_sport_sub_group_div_1\"></div>
            <div id=\"search_sport_sub_group_div_2\"></div>";
        } else {
            $option .= $search_lines;
        }
    }

    $sql = "SELECT * FROM sport_group sg, sport_group_details sgd WHERE sg.sport_group_id=sgd.sport_group_id AND sgd.language_code='" . $_SESSION[ 'language_code' ] . "' 
	ORDER BY 
	FIELD(sgd.sport_group_name, " . $allSportGroupString . ") DESC, FIELD(sgd.sport_group_name,'Sonstige','Other') ASC, IF(sgd.sport_group_name != 'Sonstige', sgd.sport_group_name, 0) ASC ";

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_groups = $query->fetchAll();

    //$list="<option value=\"\">bitte wählen</option>";
    $list = "";

    $option_character = "";
    foreach ( $get_groups as $group ) {
        if ( stristr($allSportGroupString,$group[ 'sport_group_name' ]) == false ) {
            $character = substr( $group[ 'sport_group_name' ], 0, 1 );
            if ( is_numeric( $character ) ) {
                $character = "0-9";
            }

            if ( $character != $option_character ) {
                $option_character = $character;
                if ( $group[ 'sport_group_name' ] == 'Sonstige' ||
                    $group[ 'sport_group_name' ] == 'Other' ) {
                    $option_character = "______";
                }

                $list .= '<optgroup label="' . $option_character . '"></optgroup>';
            }
        }


        $selected_value = "";
        if ( $group[ 'sport_group_id' ] == $selected )$selected_value = ' selected="selected" ';
        $list .= "<option value=\"" . $group[ 'sport_group_id' ] . "\" " . $selected_value . ">" . $group[ 'sport_group_name' ] . "</option>";
    }


    $output = str_replace( "#LIST#", $list, $option );

    return $output;
}


function build_sport_select_line( $group_id, $parent, $level, $selected ) {
    global $DB;
    $allSportGroupString = getGroupsNamedAll();
    if ( $group_id != 0 ) {
        $option = "<select name=\"search_sport_groups_" . ( $level + 1 ) . "\" id=\"search_sport_groups_" . ( $level + 1 ) . "\"  class=\"form-control\" onChange=\"getSearchSubGroup('" . $group_id . "',this.value," . ( $level + 1 ) . ")\"  style=\"padding-left:" . ( ( $level + 1 ) * 15 ) . "px; margin-bottom:5px;\">
	#LIST#
	</select>
	";

        $sql = "SELECT * FROM sport_group_value sg, sport_group_value_details sgd WHERE sg.sport_group_value_id=sgd.sport_group_value_id AND sgd.language_code='" . $_SESSION[ 'language_code' ] . "'
	AND sport_group_id='" . $group_id . "'
	AND sport_group_sub_of='" . $parent . "'
	
	ORDER BY 
	FIELD(sgd.sport_group_value_name, " . $allSportGroupString . ") DESC, sgd.sport_group_value_name";


        $query = $DB->prepare( $sql );
        $query->execute();
        $get_groups = $query->fetchAll();

        $list = "";
        foreach ( $get_groups as $group ) {
            $selected_value = "";
            if ( $group[ 'sport_group_value_id' ] == $selected )$selected_value = ' selected="selected" ';

            $list .= "<option value=\"" . $group[ 'sport_group_value_id' ] . "\" " . $selected_value . ">-> 
" . $group[ 'sport_group_value_name' ] . "</option>";
        }

        if ( $list == "" ) {
            $output = '';
        } else {
            $output = str_replace( "#LIST#", $list, $option );
        }
        return $output;

    } else {
        return "error: id missing";
    }

}




function form_get_user_sport_list( $user_id, $value_id ) {
    global $DB;
    $sql = "SELECT * FROM 
                sport_group_value sgv, 
                user_to_sport_group_value uts, 
                sport_group_value_details sgvd 
            WHERE 
                uts.sport_group_value_id = '" . $value_id . "' AND
                sgv.sport_group_value_id = sgvd.sport_group_value_id AND
                uts.sport_group_value_id = sgvd.sport_group_value_id AND 
                uts.user_id ='" . $user_id . "' AND 
                sgvd.language_code='" . $_SESSION[ 'language_code' ] . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $main_sport = $query->fetch();

    $output = '';

    $sub_of = $main_sport[ 'sport_group_sub_of' ];
    $x = 0;
    while ( $sub_of > 0 ) {

        $parent = form_get_sport_parent( $sub_of );

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

function form_get_sport_parent( $parent_id ) {
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


function build_age_range( $name, $selected, $default ) {
    if ( !$selected ) {
        $selected = $default;
    }

    $output = '<select name="' . $name . '">';

    for ( $x = REGISTER_MIN_YEARS; $x <= 99; $x++ ) {
        $selected_value = '';
        if ( $x == $selected )$selected_value = ' selected="selected" ';

        $output .= '<option value="' . $x . '" ' . $selected_value . '>' . $x . '</option>';
    }

    $output .= '</select>';

    return $output;
}

function getGroupsNamedAll(){
    global $DB;
    $sql = "SELECT GROUP_CONCAT('\'', sport_group_name, '\'') AS getGroupsNamedAll FROM sport_group_details WHERE sport_group_id =  0";
    
    $query = $DB->prepare( $sql );
    $query->execute();
    $result = $query->fetch();

    return $result['getGroupsNamedAll'];
}