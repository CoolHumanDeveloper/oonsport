<?php

function get_shoutbox_media($shoutbox_id) {
    global $DB;
    $sql = "SELECT * FROM user_shoutboxmedia m, user_to_shoutboxmedia um WHERE um.shoutbox_message_id = '".$shoutbox_id. "' AND um.media_id=m.media_id ORDER BY um.user_default_media DESC LIMIT 1";
    $output = '';
    $query = $DB->prepare( $sql );
    $query->execute();
    $marketplaceOfferImage = $query->fetch();
    if($marketplaceOfferImage) {
        $output = build_user_image( $marketplaceOfferImage[ 'media_file' ], "profil", "100x100", "html", "img-thumbnail" ).'<br>';
    }
    
    return $output;
}

function build_shoutbox_feed( $user_id, $page, $filter = false ) {
    global $DB;
    //CLEARING
    $sql = "UPDATE `user_shoutbox` SET shoutbox_status = 1 WHERE shoutbox_durration < NOW()";
    $query = $DB->prepare( $sql );
    $query->execute();
    
    $cleared_shoutbox = $query->rowCount();

    if ( $cleared_shoutbox > 0 ) {
        build_history_log( 0, "deacvtivate_shoutbox", $cleared_shoutbox );
    }
    
    
    $sql = "DELETE FROM `user_shoutbox` WHERE shoutbox_status = 1 AND shoutbox_durration < '" . date("Y-m-d", strtotime('- '.KEEP_MARKETPLACE_DAYS.' days')) . "'";
    $query = $DB->prepare( $sql );
    $query->execute();
    
    
    $cleared_shoutbox = $query->rowCount();

    if ( $cleared_shoutbox > 0 ) {
        build_history_log( 0, "clear_shoutbox", $cleared_shoutbox );
    }

    // News
    $output = '';
    $show_marketplace = false;

    if ( $page == 'marketplace' || $page == 'marketplace_profil'  ) {
        $limit = 20;
        $distance = get_setting( "marketplace_display_radius", $user_id );
        $show_sports = get_setting( "marketplace_display_sports", $user_id );
        $show_friends = false;



    } else {

        $limit = 4;
        if ( $page == "shoutbox" ) {
            $limit = 20;
        }
        if ( $page == "profile" ) {
            $limit = 20;
        }

        $distance = get_setting( "shoutbox_display_radius", $user_id );
        $show_sports = get_setting( "shoutbox_display_sports", $user_id );
        $show_friends = get_setting( "shoutbox_display_friends", $user_id );
        $show_marketplace = get_setting( "shoutbox_display_marketplace", $user_id );

        $friends_sql = "";
        $friends_sql_join = "";

        if ( $show_friends == 1 ) {
            $friends_sql_join = " LEFT JOIN user_friendship AS uf ON uf.user_id = u.user_id OR uf.friendship_user_id=u.user_id ";
            $friends_sql = " (uf.user_id = '" . $user_id . "' OR uf.friendship_user_id = '" . $user_id . "') AND ";
        }
    }


    $marketplace_sql = "";
    if ( $show_marketplace == 0 || $page == 'profile') {
        $marketplace_sql = " ubox.shoutbox_type NOT LIKE 'marketplace%' AND ";
    }

    if ( $distance === false ) {
        $distance = 50;
    }

    $distance_sql = "";
    if ( $distance > 0 ) {
        $distance_sql = " (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION[ 'user' ][ 'user_lng' ] . ")))*" . RADIUS . ") < '" . $distance . "' AND ";
    }

    $sports_sql = "";
    $sports_sql_join = "";

    if ( $show_sports == 1 ) {
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

        $query = $DB->prepare( $sql );
        $query->execute();
        $get_main_sport = $query->fetchAll();

        $sports_sql = " (";
        $or_sports_sql = "";
        foreach ( $get_main_sport as $main_sport ) {
            //$sports_sql .= $or_sports_sql . "uts.sport_group_id = '" . $main_sport[ 'sport_group_id' ] . "'";
            
            $sports_sql .= $or_sports_sql . "ubox.shoutbox_sport_group_id = '" . $main_sport[ 'sport_group_id' ] . "'";
            
            $or_sports_sql = " OR ";
            
            
            
        }
        $sports_sql .= ") AND ";
        $sports_sql_join = "";//LEFT JOIN user_to_sport_group_value uts ON uts.user_id = u.user_id";
    }


    if ( $page == 'marketplace') {
        
        
        if(isset($_GET['page'])) {
            $actpage = $_GET['page']-1;
        } else {
             $actpage = 0;
        }
        $view_per_page = VIEW_PER_PAGE;
        $site = 'marketplace/';
        //Suche
        $search = '';
        $search_sql = '';
        
        
        if(isset($_GET['shoutbox_group']) && $_GET['shoutbox_group'] != 0) {
            $search .= '&shoutbox_group=' . $_GET['shoutbox_group'];
            $sports_sql = " ubox.shoutbox_sport_group_id = '" . $_GET['shoutbox_group'] . "' AND ";
        }
        
        if(isset($_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]])) {
            $search .= '&marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '=' . $_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]];
            
            $search_sql = "(ubox.shoutbox_title LIKE '%" . $_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]] . "%' OR ubox.shoutbox_text LIKE '%" . $_GET['marketplace_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]] . "%' ) AND ";
            
        }
        if(isset($_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0])) {
            $search .= '&marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]=' . $_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0];
            if($_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] != 'marketplace_all' ){
               $search_sql = "shoutbox_type = '" . $_GET['marketplace_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] . "' AND "; 
            }
            
        }
        
        
        $sql = "
            SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION[ 'user' ][ 'user_lng' ] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
				" . $sports_sql_join . "
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "')) AND
                ubox.shoutbox_type LIKE 'marketplace%' AND 
				" . $distance_sql . "

				" . $sports_sql . "
                " . $search_sql . "
				ud.user_country='" . $_SESSION[ 'user' ][ 'user_country' ] . "' 
			GROUP BY ubox.shoutbox_message_id";
        
            $query = $DB->prepare( $sql );
            $query->execute();
            $total = $query->rowCount();
                
        $view_box='
    <ul class="pagination view_box"><li>' .TEXT_SEARCH_SORT_BY. ': </li><li><select class="form-control"><option value="latest">' .TEXT_SEARCH_SORT_BY_NEWEST. '</option>
    <option value="distance">' .TEXT_SEARCH_SORT_BY_DISTANCE. '</option></select></li></ul>
    ';

            $output .= build_site_pagination( $site, $total, $_GET['page'], $view_per_page, $search, $view_box, true );
        
        
        $sql.=" ORDER BY 
				ubox.shoutbox_date DESC 
			LIMIT " . $actpage*$view_per_page.",".$view_per_page;
        
    } else if ($page == 'marketplace_profil') {
        $sql = "
            SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION[ 'user' ][ 'user_lng' ] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "')) AND
                ubox.shoutbox_type LIKE 'marketplace%' AND 
				ud.user_country='" . $_SESSION[ 'user' ][ 'user_country' ] . "' AND
                u.user_id = '" . $user_id . "'
			GROUP BY ubox.shoutbox_message_id
			ORDER BY 
				ubox.shoutbox_date DESC 
			LIMIT " . $limit . "";
    } else if ( $page == 'profile' ) {
        $sql = "SELECT u.*, ud.*, ubox.*
 
             FROM 

             user u
             INNER JOIN user_details ud ON u.user_id=ud.user_id
             INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id

             WHERE 
            u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "')) AND
            " . $marketplace_sql . "
            u.user_id = '" . $user_id . "' AND
            ud.user_country='" . $_SESSION[ 'user' ][ 'user_country' ] . "'
            GROUP BY ubox.shoutbox_message_id 
            ORDER BY ubox.shoutbox_date DESC LIMIT " . $limit . "";
    } else if($page == 'shoutbox') {
        
        if(isset($_GET['page'])) {
            $actpage = $_GET['page']-1;
        } else {
             $actpage = 0;
        }
        $view_per_page = VIEW_PER_PAGE;
        $site = 'shoutbox/';
        //Suche
        $search = '';
        $search_sql = '';
        
        
        if(isset($_GET['shoutbox_group']) && $_GET['shoutbox_group'] != 0) {
            $search .= '&shoutbox_group=' . $_GET['shoutbox_group'];
            $sports_sql = " ubox.shoutbox_sport_group_id = '" . $_GET['shoutbox_group'] . "' AND ";
        }
        
        if(isset($_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]])) {
            $search .= '&shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '=' . $_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]];
            
            $search_sql = "(ubox.shoutbox_title LIKE '%" . $_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]] . "%' OR ubox.shoutbox_text LIKE '%" . $_GET['shoutbox_search_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]] . "%' ) AND ";
            
        }
        if(isset($_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0])) {
            $search .= '&shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '[]=' . $_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0];
            if($_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] != 'shoutbox_all' ){
               $search_sql = "shoutbox_type = '" . $_GET['shoutbox_type_' . $_SESSION[ 'user' ][ 'secure_spam_key' ]][0] . "' AND "; 
                echo 'here2';
            }
            
            
        }
        
        
        $sql = "	SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION[ 'user' ][ 'user_lng' ] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
				" . $friends_sql_join . "
				" . $sports_sql_join . "
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "')) AND
				" . $distance_sql . "
				" . $friends_sql . "
				" . $sports_sql . "
                " . $marketplace_sql . "
                " . $search_sql . "
				ud.user_country='" . $_SESSION[ 'user' ][ 'user_country' ] . "' 
			GROUP BY ubox.shoutbox_message_id
			";

            $query = $DB->prepare( $sql );
            $query->execute();
            $total = $query->rowCount();
        
        $view_box='
    <ul class="pagination view_box"><li>' .TEXT_SEARCH_SORT_BY. ': </li><li><select class="form-control"><option value="latest">' .TEXT_SEARCH_SORT_BY_NEWEST. '</option>
    <option value="distance">' .TEXT_SEARCH_SORT_BY_DISTANCE. '</option></select></li></ul>
    ';

            $output .= build_site_pagination( $site, $total, $_GET['page'], $view_per_page, $search, $view_box, true );


            $sql.="ORDER BY 
				ubox.shoutbox_date DESC
                LIMIT " . $actpage*$view_per_page.",".$view_per_page;

        
    } else {
                $sql = "	SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $_SESSION[ 'user' ][ 'user_lat' ] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $_SESSION[ 'user' ][ 'user_lng' ] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
				" . $friends_sql_join . "
				" . $sports_sql_join . "
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION[ 'user' ][ 'user_id' ] . "')) AND
				" . $distance_sql . "
				" . $friends_sql . "
				" . $sports_sql . "
                " . $marketplace_sql . "
				ud.user_country='" . $_SESSION[ 'user' ][ 'user_country' ] . "' 
			GROUP BY ubox.shoutbox_message_id
			ORDER BY 
				ubox.shoutbox_date DESC LIMIT " . $limit . "";
    }
    
    


    $query = $DB->prepare( $sql );
    $query->execute();
    $get_shoutbox = $query->fetchAll();
    
    foreach ( $get_shoutbox as $shoutbox ) {
        $output .= '<div class="row" style="">
                    <div class="shoutbox-left">
                        <a href="#SITE_URL#profile/' . md5( $shoutbox[ 'user_id' ] . $shoutbox[ 'user_nickname' ] ) . '">' . build_default_image( $shoutbox[ 'user_id' ], "50x50", "" ) . '</a>
                    </div>
                <div  class="shoutbox-right">
                <span class="shoutbox_action">
                ';

        if ( stristr( $shoutbox[ 'shoutbox_type' ], 'marketplace_' ) == true ) {
            
            $output .= '<a href="#SITE_URL#marketplace/offer/' . md5( $shoutbox[ 'shoutbox_message_id' ] . $shoutbox[ 'shoutbox_date' ] . $shoutbox[ 'user_id' ] ) . '" style="float:right;">' . get_shoutbox_media($shoutbox[ 'shoutbox_message_id' ]) .'</a><br>
                        <a href="#SITE_URL#marketplace/offer/' . md5( $shoutbox[ 'shoutbox_message_id' ] . $shoutbox[ 'shoutbox_date' ] . $shoutbox[ 'user_id' ] ) . '" class="btn btn-xs btn-primary"  style="float:right; margin-top:10px;">' . TEXT_MARKETPLACE_GOTO_OFFER . '</a>
                    ';
        }



        $output .= '</span>
        <strong><span class="light_green small">' . constant( 'TEXT_SHOUTBOX_TYPE_' . strtoupper( $shoutbox[ 'shoutbox_type' ] ) ) . '</span> <span class="light_grey small">' .get_sport_group_name($shoutbox[ 'shoutbox_sport_group_id' ]). '</span>';
        
        if($shoutbox['shoutbox_status'] == 1) {
        $output.= ' <span class="btn-xs btn-danger">' . TEXT_SHOUTBOX_FINISHED. '</span>';
        }
        
        $output.='<br>' . $shoutbox[ 'shoutbox_title' ] . '</strong><br>
        <span class="small">' . date( "d.m.Y H:i", strtotime( $shoutbox[ 'shoutbox_date' ] ) ) . ' von ' . $shoutbox[ 'user_nickname' ] . ' aus ' . get_city_name( $shoutbox[ 'user_city_id' ], $shoutbox[ 'user_country' ] ) . '</span>
        
        <br>
        ' . nl2br( $shoutbox[ 'shoutbox_text' ] );
       
        
                if ( $shoutbox[ 'user_id' ] == $_SESSION[ 'user' ][ 'user_id' ] && $page != 'profile' ) {
            $output .= '<br><div>
                        <form method="post" action="#SITE_URL#' . $page . '/" style="float:left;" >
                            <input name="shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="' . md5( $shoutbox[ 'shoutbox_message_id' ] . $shoutbox[ 'shoutbox_date' ] . $shoutbox[ 'user_id' ] ) . '" type="hidden">
                            <button name="delete_shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" class="btn btn-xs btn-danger" type="submit" value="1" >
                            <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    ';
            if($shoutbox['shoutbox_status'] == 1) {
                $output .= '
                        <form method="post" action="#SITE_URL#' . $page . '/"  style="float:left;" >
                            <input name="shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" value="' . md5( $shoutbox[ 'shoutbox_message_id' ] . $shoutbox[ 'shoutbox_date' ] . $shoutbox[ 'user_id' ] ) . '" type="hidden">
                             <button name="reactivate_shoutbox_key_' . $_SESSION[ 'user' ][ 'secure_spam_key' ] . '" class="btn btn-xs btn-success" type="submit" value="1" >
                            ' . TEXT_REACTIVATE_MARKETPLACE . '
                            </button>
                        </form>
                    ';
            }
                    $output .= '</div><br>';
        }
        
         if ( stristr( $shoutbox[ 'shoutbox_type' ], 'marketplace_' ) == true ) {
            if(get_shoutbox_media($shoutbox[ 'shoutbox_message_id' ]) != false) {
              $output .= '<br><br>';
            };
        }
        
        $output .= '</div>
        <hr>
        <div class="clearfix"></div>
        </div>
        ';
    }
    if ( $output == '' || $total == 0) {
        if ( $page == 'marketplace'  || $page == 'marketplace_profil') {
            $output = TEXT_SHOUTBOX_MARKETPLACE_EMPTY;
        } else if ( $page != 'profile' ) {
            $output = TEXT_SHOUTBOX_BE_FIRST;
        } else if ( $page == 'profile' ) {
            $output = TEXT_SHOUTBOX_PROFILE_EMPTY;
        }
    }
    if ( $page == 'profile' ) {
        $output .= '	<div class="col-md-12 col-sm-12">
						<div class="row">
                        <br>
							<a href="#SITE_URL#shoutbox/" class="btn btn-default">' . TEXT_GOTO_SHOUTBOX . '</a>
						</div>
					</div>';
    }
    if ( $page == 'marketplace_profil') {
        $output .= '	<div class="col-md-12 col-sm-12">
						<div class="row">
                        <br>
							<a href="#SITE_URL#marketplace/" class="btn btn-default">' . TEXT_GOTO_MARKETPLACE . '</a>
						</div>
					</div>';
    }
    return $output;
}