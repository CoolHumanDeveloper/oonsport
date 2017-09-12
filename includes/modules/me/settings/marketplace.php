<?php


if ( isset( $_POST[ 'update_settings' ] ) ) {
    //echo "TEST";
    $sql = "UPDATE user_settings SET settings_value='0' WHERE  user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "'";
    $query = $DB->prepare( $sql );
    $query->execute();

    foreach ( $_POST as $getkey => $getvalue ) {
        if ( stristr( $getkey, "MySet_" ) == true ) {
            //echo $getkey.':'.$getvalue;
            $sql = "UPDATE user_settings SET settings_value='" . $getvalue . "' WHERE settings_key='" . str_replace( "MySet_", "", $getkey ) . "' AND user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "'";
            $query = $DB->prepare( $sql );
            $query->execute();
        }

    }
    $_SESSION[ 'system_temp_message' ] .= set_system_message( "success", TEXT_SETTINGS_UPDATED );
    header( "Location: " . SITE_URL . "me/settings/marketplace/" );
    die();

}

$output = '
    <div class="col-md-9 col-sm-12 content-box-right">

    <form method="post" action="#SITE_URL#me/settings/marketplace/">
    ';

$settings_types = array( 'marketplace_display_radius' => 'radius',
                        'marketplace_display_sports' => 'checkbox');

$radius_distances = array( 0, 10, 25, 50, 100, 200 );

$setting_headlines = array();

$sql = "SELECT * FROM user_settings WHERE user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "' AND settings_type='marketplace' ORDER BY settings_type ASC, settings_key ASC";
$query = $DB->prepare( $sql );
$query->execute();
$get_settings = $query->fetchAll();

foreach ( $get_settings as $settings ) {

    if ( !in_array( $settings[ 'settings_type' ], $setting_headlines ) ) {
        array_push( $setting_headlines, $settings[ 'settings_type' ] );
        $output .= '<h4 class="profile">' . TEXT_SETTING_HEADER_MARKETPLACE . '</h4><br>';
    }

    $output .= '	<div class="row">
				<div class="col-md-9 col-sm-9">' . constant( 'TEXT_SETTING_' . strtoupper( $settings[ 'settings_key' ] ) ) . '</div>
				<div class="col-md-3 col-sm-3">';

    if ( $settings_types[ $settings[ 'settings_key' ] ] == 'checkbox' ) {
        $checked_value = '';
        if ( $settings[ 'settings_value' ] == 1 )$checked_value = ' checked="checked"';
        $output .= '<input type="checkbox" name="MySet_' . $settings[ 'settings_key' ] . '" ' . $checked_value . ' value="1">';
    }

    if ( $settings_types[ $settings[ 'settings_key' ] ] == 'radius' ) {
        $output .= '<select name="MySet_' . $settings[ 'settings_key' ] . '" class="form-control">';
        for ( $x_rad = 0; $x_rad < count( $radius_distances ); $x_rad++ ) {
            $radius_distances_select = "";

            if ( $radius_distances[ $x_rad ] == 0 ) {
                $radius_distances_text = TEXT_GLOBAL_WITHOUT;
            } else {

                $radius_distances_text = $radius_distances[ $x_rad ] . " " . TEXT_GLOBAL_KM;
            }

            if ( $settings[ 'settings_value' ] == $radius_distances[ $x_rad ] ) {
                $radius_distances_select = " selected";
            }

            $output .= '<option' . $radius_distances_select . ' value="' . $radius_distances[ $x_rad ] . '">' . $radius_distances_text . '</option>';
        }

        $output .= '</select>';
    }

    $output .= '</div>
        </div>';
}


$output .= '<br>
    <br>

    <input type="hidden" name="update_settings" value="1">
    <button type="submit" class="btn btn-primary">' . TEXT_GLOBAL_SAVE . '</button></form>

    </div>';

$header_ext = '';
$footer_ext = '';

$content_output = array( 'TITLE' => SITE_NAME, 'CONTENT' => $sidebar . $output, 'FOOTER_EXT' => $footer_ext, 'HEADER_EXT' => $header_ext );