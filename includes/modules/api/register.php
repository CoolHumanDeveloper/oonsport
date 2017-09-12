<?php
if (!isset($resister_step)) {
    header(HEADER_SERVERERR);
    $response['error'] = MISSING_PARAMETER;
    die(json_encode($response));
}
switch ($resister_step) {
    CASE 1:
        if (!isset($register_nickname) || $register_nickname == ""
            || !isset($register_email) || $register_email == ""
        ) {
            header(HEADER_SERVERERR);
            $response['error'] = MISSING_PARAMETER;
            break;
        }

        if (strlen($_POST['register_nickname']) < 3) {
            header(HEADER_SERVERERR);
            $response['error'] = SHORT_NICKNAME;
            break;
        }
        if (check_user_nickname($register_nickname) == true) {
            header(HEADER_SERVERERR);
            $response['error'] = DUPLICATED_NICKNAME;
            break;
        }

        if (check_email($register_email) == true) {
            if (check_user_email($_POST['register_email']) == true) {
                header(HEADER_SERVERERR);
                $response['error'] = DUPLICATED_EMAIL;
                break;
            }
        } else {
            header(HEADER_SERVERERR);
            $response['error'] = INVALID_EMAIL;
            break;
        }
        break;
    CASE 2:
        if (!isset($register_email)
            || !isset($register_password)
            || !isset($register_type)
            || !isset($register_city_id)
            || !isset($register_country)
            || !isset($register_nickname)
            || !isset($register_firstname)
            || !isset($register_lastname)
            || !isset($register_dob)
            || !isset($register_gender)
            || !isset($geo_place_id)
            || (!isset($sport_groups_3) && !isset($sport_groups_2) && !isset($sport_groups_1))
            || !isset($sport_groups)
            || !isset($sport_groups_profession)
            || !isset($sport_groups_handycap)
        ) {
            header(HEADER_SERVERERR);
            $response['error'] = MISSING_PARAMETER;
            break;
        }

        $auth_key = md5($register_password . $register_email . $register_type . time() . rand(0, 999));

        $sql = "INSERT INTO `user` (`user_password`, `user_email`, `user_type`, `user_status`, `user_auth_key`, user_register_date) VALUES ('" . md5($register_password) . "', '" . $register_email . "', '" . $register_type . "', 0, '" . $auth_key . "', NOW())";
        $query = $DB->prepare($sql);
        $query->execute();
        $user_id = $DB->lastInsertId();

        if ($user_id > 0) {
            $profile_geo = get_city_latlng($register_city_id, $register_country);

            $sql = "INSERT INTO `user_details` (`user_id`, `user_nickname`, `user_firstname`, `user_lastname`, `user_dob`, `user_gender`, user_country, `user_geo_city_id`) VALUES ('" . $user_id . "', '" . $register_nickname . "', '" . $register_firstname . "', '" . $register_lastname . "', '" . date("Y-m-d", strtotime($register_dob)) . "', '" . $register_gender . "', '" . $register_country . "', '" . $geo_place_id . "');";
            $query = $DB->prepare($sql);
            $query->execute();
            $sport_group_value = 0;
            for ($sg_value = 3; $sg_value >= 1; $sg_value--) {
                if (${'sport_groups_' . $sg_value} != '') {
                    $sport_group_value = ${'sport_groups_' . $sg_value};
                    break;
                }
            }

            $sql = "INSERT INTO `user_to_sport_group_value` (`user_id`, `sport_group_id`, `sport_group_value_id`, sport_group_profession, sport_group_handycap, sport_group_in_club) VALUES ('" . $user_id . "', '" . $sport_groups . "', '" . $sport_group_value . "', '" . $sport_groups_profession . "', '" . $sport_groups_handycap . "', '" . $sport_groups_status . "')";
            $query = $DB->prepare($sql);
            $query->execute();

            $sql = "INSERT INTO `user_profile` (user_id) VALUES ('" . $user_id . "')";
            $query = $DB->prepare($sql);
            $query->execute();

            // SET DEFAULT USER SETTINGS
            $sql = str_replace("#USERID#", $user_id, DEFAULT_SETTINGS_SQL);
            $query = $DB->prepare($sql);
            $query->execute();

            //require(PHP_MAILER_CLASS);
            $email_content_array = array(
                "NAME" => $register_firstname,
                "AUTHKEY" => $auth_key
            );
            $email_content_template = email_content_to_template("register", $email_content_array, '');
            $alt_content = '';

            if (sending_email($register_email, $register_firstname, "Registrierung auf " . SITE_NAME, $email_content_template, $alt_content, 0) == true) {
                $register_complete = 1;
                build_history_log($user_id, "register");

                $params = array(
                    "email" => $register_email,
                    "logintime" => date("Y-m-d H:i:s")
                );
                $token = $jwt::encode($params, JWT_KEY, JWT_ALG);
                $response['token'] = $token;
            } else {
                $response['error'] = FAIL_SENDMAIL;
                break;
            }

        } else {
            $response['error'] = FAIL_USER;
            break;
        }
        break;
}

die(json_encode($response));
?>