<?php
function debug($obj)
{
    if (!DEBUG_MODE) return;
    $fp = fopen("debug.txt", "a");
    fputs($fp, print_r($obj, true) . "\n");
    fclose($fp);
}

function api_build_default_image($user_id, $size)
{
    global $DB;
    $sql = "SELECT 
            m.*,
            u.user_type,
            ud.user_gender
        FROM 
        user_media m
        INNER JOIN user_to_media um ON um.media_id=m.media_id
        INNER JOIN user u ON um.user_id = u.user_id
        INNER JOIN user_details ud ON ud.user_id = u.user_id 
        WHERE 
        u.user_id = '" . $user_id . "' AND
        um.user_default_media = 1 AND 
        m.media_type = 1 
        LIMIT 1";

    $query = $DB->prepare($sql);
    $query->execute();
    $image = $query->fetch();

    if ($image['media_id']) {
        $output = api_build_user_image($image['media_file'], "profil", $size);
    } else {
        $sql = "SELECT 
            u.user_type,
            ud.user_gender
        FROM 
        user u 
        INNER JOIN user_details ud ON ud.user_id = u.user_id 
        WHERE 
        u.user_id = '" . $user_id . "'
        LIMIT 1";

        $query = $DB->prepare($sql);
        $query->execute();
        $image = $query->fetch();

        if ($image['user_type'] <= 2 && $image != NULL) {
            if ($image['user_gender'] == 'f') {
                $profileImage = 'user_blank_female.jpg';
            } else {
                $profileImage = 'user_blank_male.jpg';
            }
        } else if ($image['user_type'] == 3) {
            $profileImage = 'user_blank_club.jpg';
        } else if ($image['user_type'] == 4) {
            $profileImage = 'user_blank_location.jpg';
        } else {
            $profileImage = 'user_blank.jpg';
        }
        $output = api_build_user_image('#SITE_URL#images/default/user/' . $profileImage, "profil", $size);
    }

    return $output;
}

function api_build_user_image($image, $type, $size)
{
    $img = $type . "/" . $size . "/" . $image . ".jpg";

    // Fallback, falls eine URL Angeben ist für z.B. default image
    if (stristr($image, "#SITE_URL#") == true) {
        $output = str_replace("#SITE_URL#", SITE_URL, $image);
        return $output;
    }

    if (!file_exists(SERVER_IMAGE_PATH . "_static/" . $img))
        $output = SITE_IMAGE_URL . "temp/" . $img;
    else
        $output = SITE_IMAGE_URL . "_static/" . $img;

    return $output;
}

function api_get_sport_group_name($group_id)
{
    global $DB;
    if ($group_id != 0) {
        $sql = "SELECT 
                    sgd.sport_group_name 
                FROM 
                    sport_group sg 
                    LEFT JOIN sport_group_details AS sgd ON sgd.sport_group_id = sg.sport_group_id
                WHERE 
                    sg.sport_group_id = '" . $group_id . "' AND
                    sgd.language_code='" . $_SESSION['language_code'] . "' 
                LIMIT 1";

        $query = $DB->prepare($sql);
        $query->execute();
        $group_name = $query->fetch();

        return $group_name['sport_group_name'];
    }

    return '';
}

function api_build_group_select($user_id, $profile)
{

    global $DB;
    $sql = "SELECT 
			* 
			FROM 
				groups g,
				user_to_groups utg
			WHERE 
				utg.user_id='$user_id' AND
				utg.group_id = g.group_id
			GROUP BY 
				g.group_id
			ORDER BY
				g.group_id DESC";


    $query = $DB->prepare($sql);
    $query->execute();
    $get_groups = $query->fetchAll(PDO::FETCH_ASSOC);

    return $get_groups;
}

function api_build_shoutbox_feed($user_id, $page)
{
    global $DB, $userinfo, $lang, $actpage;
    //CLEARING
    $sql = "UPDATE `user_shoutbox` SET shoutbox_status = 1 WHERE shoutbox_durration < NOW()";
    $query = $DB->prepare($sql);
    $query->execute();

    $cleared_shoutbox = $query->rowCount();

    if ($cleared_shoutbox > 0) {
        build_history_log(0, "deacvtivate_shoutbox", $cleared_shoutbox);
    }


    $sql = "DELETE FROM `user_shoutbox` WHERE shoutbox_status = 1 AND shoutbox_durration < '" . date("Y-m-d", strtotime('- ' . KEEP_MARKETPLACE_DAYS . ' days')) . "'";
    $query = $DB->prepare($sql);
    $query->execute();


    $cleared_shoutbox = $query->rowCount();

    if ($cleared_shoutbox > 0) {
        build_history_log(0, "clear_shoutbox", $cleared_shoutbox);
    }

    // News
    $output = '';
    $show_marketplace = false;

    if ($page == 'marketplace' || $page == 'marketplace_profil') {
        $limit = 20;
        $distance = get_setting("marketplace_display_radius", $user_id);
        $show_sports = get_setting("marketplace_display_sports", $user_id);
    } else {
        $limit = 4;
        if ($page == "shoutbox") {
            $limit = 20;
        }
        if ($page == "profile") {
            $limit = 20;
        }

        $distance = get_setting("shoutbox_display_radius", $user_id);
        $show_sports = get_setting("shoutbox_display_sports", $user_id);
        $show_friends = get_setting("shoutbox_display_friends", $user_id);
        $show_marketplace = get_setting("shoutbox_display_marketplace", $user_id);

        $friends_sql = "";
        $friends_sql_join = "";

        if ($show_friends == 1) {
            $friends_sql_join = " LEFT JOIN user_friendship AS uf ON uf.user_id = u.user_id OR uf.friendship_user_id=u.user_id ";
            $friends_sql = " (uf.user_id = '" . $user_id . "' OR uf.friendship_user_id = '" . $user_id . "') AND ";
        }
    }


    $marketplace_sql = "";
    if ($show_marketplace == 0 || $page == 'profile') {
        $marketplace_sql = " ubox.shoutbox_type NOT LIKE 'marketplace%' AND ";
    }

    if ($distance === false) {
        $distance = 50;
    }

    $distance_sql = "";
    if ($distance > 0) {
        $distance_sql = " (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo['user_lng'] . ")))*" . RADIUS . ") < '" . $distance . "' AND ";
    }

    $sports_sql = "";
    $sports_sql_join = "";

    if ($show_sports == 1) {
        $sql = "SELECT * 
			FROM 
				user_to_sport_group_value uts
				LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
				LEFT JOIN user AS u ON uts.user_id = u.user_id
			WHERE 
				uts.user_id ='" . $user_id . "' AND 
				sgd.language_code='" . $lang . "' 
			GROUP BY 
				sgd.sport_group_id";

        $query = $DB->prepare($sql);
        $query->execute();
        $get_main_sport = $query->fetchAll();

        $sports_sql = " (";
        $or_sports_sql = "";
        foreach ($get_main_sport as $main_sport) {
            $sports_sql .= $or_sports_sql . "ubox.shoutbox_sport_group_id = '" . $main_sport['sport_group_id'] . "'";
            $or_sports_sql = " OR ";
        }
        $sports_sql .= ") AND ";
        $sports_sql_join = "";
    }

    if ($page == 'marketplace') {
        $view_per_page = VIEW_PER_PAGE;
        $site = 'marketplace/';
        //Suche
        $search = '';
        $search_sql = '';

        if (isset($_GET['shoutbox_group']) && $_GET['shoutbox_group'] != 0) {
            $search .= '&shoutbox_group=' . $_GET['shoutbox_group'];
            $sports_sql = " ubox.shoutbox_sport_group_id = '" . $_GET['shoutbox_group'] . "' AND ";
        }

        if (isset($_GET['marketplace_search_' . $_SESSION['user']['secure_spam_key']])) {
            $search .= '&marketplace_search_' . $_SESSION['user']['secure_spam_key'] . '=' . $_GET['marketplace_search_' . $_SESSION['user']['secure_spam_key']];

            $search_sql .= "(ubox.shoutbox_title LIKE '%" . $_GET['marketplace_search_' . $_SESSION['user']['secure_spam_key']] . "%' OR ubox.shoutbox_text LIKE '%" . $_GET['marketplace_search_' . $_SESSION['user']['secure_spam_key']] . "%' ) AND ";

        }
        if (isset($_GET['marketplace_type_' . $_SESSION['user']['secure_spam_key']][0])) {
            $search .= '&marketplace_type_' . $_SESSION['user']['secure_spam_key'] . '[]=' . $_GET['marketplace_type_' . $_SESSION['user']['secure_spam_key']][0];
            if ($_GET['marketplace_type_' . $_SESSION['user']['secure_spam_key']][0] != 'marketplace_all') {
                $search_sql .= "shoutbox_type = '" . $_GET['marketplace_type_' . $_SESSION['user']['secure_spam_key']][0] . "' AND ";
            }
        }

        $sql = "
            SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo['user_lng'] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
				" . $sports_sql_join . "
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION['user']['user_id'] . "')) AND
                ubox.shoutbox_type LIKE 'marketplace%' AND 
				" . $distance_sql . "
				" . $sports_sql . "
                " . $search_sql . "
				ud.user_country='" . $userinfo['user_country'] . "' 
			GROUP BY ubox.shoutbox_message_id";

        $sql .= " ORDER BY ubox.shoutbox_date DESC LIMIT " . $actpage * $view_per_page . "," . $view_per_page;

    } else if ($page == 'marketplace_profil') {
        $sql = "
            SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo['user_lng'] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $userinfo['user_id'] . "')) AND
                ubox.shoutbox_type LIKE 'marketplace%' AND 
				ud.user_country='" . $userinfo['user_country'] . "' AND
                u.user_id = '" . $user_id . "'
			GROUP BY ubox.shoutbox_message_id
			ORDER BY 
				ubox.shoutbox_date DESC 
			LIMIT " . $limit . "";
    } else if ($page == 'profile') {
        $sql = "SELECT u.*, ud.*, ubox.*
                FROM 
             user u
             INNER JOIN user_details ud ON u.user_id=ud.user_id
             INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
             WHERE 
            u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $userinfo['user_id'] . "')) AND
            " . $marketplace_sql . "
            u.user_id = '" . $user_id . "' AND
            ud.user_country='" . $userinfo['user_country'] . "'
            GROUP BY ubox.shoutbox_message_id 
            ORDER BY ubox.shoutbox_date DESC LIMIT " . $limit . "";
    } else if ($page == 'shoutbox') {
        $view_per_page = VIEW_PER_PAGE;
        //Suche
        $search_sql = '';

        if (isset($_GET['shoutbox_group']) && $_GET['shoutbox_group'] != 0) {
            $sports_sql = " ubox.shoutbox_sport_group_id = '" . $_GET['shoutbox_group'] . "' AND ";
        }

        if (isset($_GET['shoutbox_search_' . $_SESSION['user']['secure_spam_key']])) {

            $search_sql = "(ubox.shoutbox_title LIKE '%" . $_GET['shoutbox_search_' . $_SESSION['user']['secure_spam_key']] . "%' OR ubox.shoutbox_text LIKE '%" . $_GET['shoutbox_search_' . $_SESSION['user']['secure_spam_key']] . "%' ) AND ";

        }
        if (isset($_GET['shoutbox_type_' . $_SESSION['user']['secure_spam_key']][0])) {
            if ($_GET['shoutbox_type_' . $_SESSION['user']['secure_spam_key']][0] != 'shoutbox_all') {
                $search_sql = "shoutbox_type = '" . $_GET['shoutbox_type_' . $_SESSION['user']['secure_spam_key']][0] . "' AND ";
            }
        }

        $sql = "SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo['user_lng'] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
				" . $friends_sql_join . "
				" . $sports_sql_join . "
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $_SESSION['user']['user_id'] . "')) AND
				" . $distance_sql . "
				" . $friends_sql . "
				" . $sports_sql . "
                " . $marketplace_sql . "
                " . $search_sql . "
				ud.user_country='" . $userinfo['user_country'] . "' 
			GROUP BY ubox.shoutbox_message_id
			";

        $sql .= "ORDER BY 
				ubox.shoutbox_date DESC
                LIMIT " . $actpage * $view_per_page . "," . $view_per_page;
    } else {
        $sql = "	SELECT 
				u.*, 
				ud.*, 
				ubox.* ,
                (acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(" . $userinfo['user_lat'] . "))+cos(RADIANS(ud.user_lat))*cos(RADIANS(" . $userinfo['user_lat'] . "))*cos(RADIANS(ud.user_lng) - RADIANS(" . $userinfo['user_lng'] . ")))*" . RADIUS . ") AS DISTANCE
            FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN user_shoutbox ubox ON u.user_id = ubox.user_id
				" . $friends_sql_join . "
				" . $sports_sql_join . "
			WHERE 
				u.user_status=1 AND
                (ubox.shoutbox_status = 0 OR (ubox.shoutbox_status = 1 AND ubox.user_id = '" . $userinfo['user_id'] . "')) AND
				" . $distance_sql . "
				" . $friends_sql . "
				" . $sports_sql . "
                " . $marketplace_sql . "
				ud.user_country='" . $userinfo['user_country'] . "' 
			GROUP BY ubox.shoutbox_message_id
			ORDER BY 
				ubox.shoutbox_date DESC LIMIT " . $limit . "";
    }


    $query = $DB->prepare($sql);
    $query->execute();
    $get_shoutbox = $query->fetchAll();

    foreach ($get_shoutbox as $k => $shoutbox) {
        $get_shoutbox[$k]['default_image'] .= api_build_default_image($shoutbox['user_id'], "50x50");

        if (stristr($shoutbox['shoutbox_type'], 'marketplace_') == true) {
            $get_shoutbox[$k]['shoutbox_media'] = api_get_shoutbox_media($shoutbox['shoutbox_message_id']);
        }

        $get_shoutbox[$k]['sport_group_name'] = api_get_sport_group_name($shoutbox['shoutbox_sport_group_id']);
        $get_shoutbox[$k]['city_name'] = get_city_name($shoutbox['user_city_id'], $shoutbox['user_country']);
    }

    return $get_shoutbox;
}

function api_get_shoutbox_media($shoutbox_id) {
    global $DB;
    $sql = "SELECT * FROM user_shoutboxmedia m, user_to_shoutboxmedia um WHERE um.shoutbox_message_id = '".$shoutbox_id. "' AND um.media_id=m.media_id ORDER BY um.user_default_media DESC LIMIT 1";
    $output = '';
    $query = $DB->prepare( $sql );
    $query->execute();
    $marketplaceOfferImage = $query->fetch();
    if($marketplaceOfferImage) {
        $output = api_build_user_image( $marketplaceOfferImage[ 'media_file' ], "profil", "100x100");
    }

    return $output;
}

function api_build_group_feed($group) {
    global $DB;

    $sql = "	SELECT 
				u.*, 
				ud.*,
				gs.* 
			FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN groups_message gs ON u.user_id = gs.group_message_user_id
			WHERE 
				u.user_status=1 AND
				gs.group_message_group_id = '".$group."'
			GROUP BY gs.group_message_id
			ORDER BY 
				gs.group_message_date DESC 
			LIMIT 20";

    $query = $DB->prepare($sql);
    $query->execute();
    $get_group_message = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($get_group_message as $key => $group_message) {
        $get_group_message[$key]['user_image'] = api_build_default_image($group_message['user_id'],"50x50");
        $get_group_message[$key]['message_date'] = date("H:i d.m.Y",strtotime($group_message['group_message_date']));
        $get_group_message[$key]['city_name'] = get_city_name($group_message['user_geo_city_id'], $group_message['user_country']);
    }

    return $get_group_message;
}
function api_get_user_sport_list( $user_id, $value_id ) {
    global $DB;
    global $lang;
    $lang = $lang ?: 'en';
    if ( $user_id === 0 ) {
        $sql = "SELECT * 
			FROM 
				sport_group_value sgv, 
				sport_group_value_details sgvd 
			WHERE 
				sgv.sport_group_value_id = '" . $value_id . "' AND
				sgv.sport_group_value_id = sgvd.sport_group_value_id AND
				sgvd.language_code='" . $lang . "'
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
				sgvd.language_code='" . $lang . "'
			LIMIT 1";
    }

    $query = $DB->prepare( $sql );
    $query->execute();
    $main_sport = $query->fetch(PDO::FETCH_ASSOC);
    if (!$main_sport)
        return [];

    $sub_of = $main_sport[ 'sport_group_sub_of' ];
    $res = array();
    $res[] = $main_sport;
    while ( $sub_of > 0 ) {
        $parent = api_get_sport_parent( $sub_of );
        $res[] = $parent;

        $sub_of = $parent[ 'sport_group_sub_of' ];
    }

    $res = array_reverse($res);
    return $res;
}
function api_get_sport_parent( $parent_id ) {
    global $DB;
    global $lang;
    $sql = "SELECT * FROM 
	sport_group_value sgv, 
	sport_group_value_details sgvd 
	WHERE 
	sgv.sport_group_value_id = '" . $parent_id . "' AND
	sgv.sport_group_value_id = sgvd.sport_group_value_id AND 
	sgvd.language_code='" . $lang . "' LIMIT 1";


    $query = $DB->prepare( $sql );
    $query->execute();
    $sport_name = $query->fetch(PDO::FETCH_ASSOC);

    return $sport_name;
}

function api_get_user_main_sport( $user_id, $lang, $type = "plain" ) {
    global $DB;
    if ( $user_id === "register" ) {
        $sql = "SELECT * 
			FROM 
				sport_group_details AS sgd
			WHERE 
				sgd.sport_group_id = '" . $type . "' AND
				sgd.language_code='$lang' 
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
				sgd.language_code='$lang' 
			ORDER BY sgd.sport_group_name ASC";
    } else {
        $sql = "SELECT * 
			FROM 
				user_to_sport_group_value uts
				LEFT JOIN sport_group_details AS sgd ON uts.sport_group_id = sgd.sport_group_id
				LEFT JOIN user AS u ON uts.user_id = u.user_id
			WHERE 
				uts.user_id ='" . $user_id . "' AND 
				sgd.language_code='$lang' 
			GROUP BY 
				sgd.sport_group_id";
    }

    $query = $DB->prepare( $sql );
    $query->execute();
    $get_main_sport = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ( $get_main_sport as $key => $main_sport ) {
        if ( $type == "detail_list" ) {
            $get_main_sport[$key]['subgroup'] = get_user_sport_list( $user_id, $main_sport[ 'sport_group_value_id' ] );
            $get_main_sport[$key]['profession_name'] = get_profession_name( $main_sport[ 'sport_group_profession' ], $main_sport[ 'user_type' ] );
        }
    }

    return $get_main_sport;
}
function api_isValidUserProfile( $switchID, $userid ) {
    global $DB;
    $sql = "SELECT 
			u.user_id, u.user_sub_of
		 FROM 
			user u
			LEFT JOIN user_details ud ON u.user_id = ud.user_id
		 WHERE 
			u.user_id = '" . $switchID . "' LIMIT 1";

    $query = $DB->prepare( $sql );
    $query->execute();
    $profile = $query->fetch();

    if ( $profile[ 'user_sub_of' ] > 0 && $userid == $profile[ 'user_sub_of' ] ) {
        return true;
    }

    return false;
}