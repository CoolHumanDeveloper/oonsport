<?php 


$c_output='<div class="col-md-3 col-sm-0"></div><div class="col-md-6 col-sm-12">
    <header class="list-group-item start_register_header"><strong>'.TEXT_RESET_PASSWORT_HEADER.'</strong><br><small>'.TEXT_RESET_PASSWORT_HEADER_SUBTITLE.'</small></header>
        <div class="list-group-item  start_register_box">
       
		 
        <div class="row form_line">
		<div class="text-center">
		<div class="col-md-12">
        
        <form method="post" action="#SITE_URL#reset-password/">
              <div class="col-sm-3 col-xs-hidden login_box"></div><div class="col-sm-6 col-xs-6 login_box"><label>'.TEXT_RESET_PASSWORT_EMAILADRESS.':<br>
</label><input class="form-control" type="email" name="renew_email#SPAM_KEY#" value="'.$_POST['renew_email'.$_SESSION['user']['secure_spam_key']].'" required></div>
 <div class="col-sm-3 col-xs-hidden login_box"></div>
 <div class="col-sm-12 col-xs-12 login_box"><br>
<input type="submit" value="'.TEXT_RESET_PASSWORT_SUBMIT.'"  class="btn btn-sm btn-primary form-control"><br>
</div>

				</form><br>
        </div>
		
    </div>
	</div>
	</div>
	<br>
<br>
<br>
<br>
<br>
<br>
<br>

	</div>
';
	

if(isset($_POST['renew_email'.$_SESSION['user']['secure_spam_key']])) {
	$renew_email = $_POST['renew_email'.$_SESSION['user']['secure_spam_key']];
	$renew_error=0;
	if(check_email($renew_email)==true) {
        if(check_user_email($renew_email) == true) {
            $sql = "SELECT * FROM `user` u, user_details ud WHERE u.`user_email` = '".$renew_email."' AND u.user_id = ud.user_id LIMIT 1";
            
            $query = $DB->prepare($sql);
            $query->execute();
            $renew_user = $query->fetch();

            $auth_key=md5($renew_user['user_password']. $renew_user['user_email']. $renew_user['user_type']. time(). rand(0,999) );

            build_history_log($renew_user['user_id'],"renew_pw",$auth_key);

            $email_content_array = array (
                "NAME" => $renew_user['user_firstname'],
                "AUTHKEY" => $auth_key
                );

            $email_content_template=email_content_to_template("reset-password",$email_content_array,"");
            $alt_content="";

            if(sending_email($renew_user['user_email'],$renew_user['user_firstname'],"
            Passwort erneuern auf ".SITE_NAME,$email_content_template,$alt_content,0) === false) {
                $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_SENDING_MAIL);
                $renew_error=1;
            }


        }
        else {

            $_SESSION['system_message'] .= set_system_message("error", TEXT_RESET_PASSWORT_NOT_FOUND);
        $renew_error=1;

        }
    }
    else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_YOUR_EMAIL_INVALID);
        $renew_error=1;
    }

	if($renew_error == 0) {
		$_SESSION['system_message'] .= set_system_message("success",TEXT_RESET_PASSWORD_SUCCESS);
		
		
		// Überschreiben des Outputs
		$c_output='<div class="col-md-3 col-sm-0"></div><div class="col-md-6 col-sm-12">
            <header class="list-group-item start_register_header"><strong>'.TEXT_RESET_PASSWORT_HEADER.'</strong><br><small>'.TEXT_RESET_PASSWORT_HEADER_SUBTITLE.'</small></header>
                <div class="list-group-item  start_register_box">


                <div class="row form_line">
                <div class="text-center">
                <div class="col-md-12">

                <form method="post" action="#SITE_URL#reset-password/">
                      <div class="col-sm-3 col-xs-hidden login_box"></div><div class="col-sm-6 col-xs-6 login_box"><label>'.TEXT_RESET_PASSWORT_EMAILADRESS.':<br>
        </label><input class="form-control" type="email" name="renew_email#SPAM_KEY#" required></div>
         <div class="col-sm-3 col-xs-hidden login_box"></div>
         <div class="col-sm-12 col-xs-12 login_box"><br>
        <input type="submit" value="'.TEXT_RESET_PASSWORT_SUBMIT.'"  class="btn btn-sm btn-primary form-control"><br>
        </div>

                        </form><br>
                </div>
            </div>
            </div>
            </div>
            <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>

            </div>
        ';
		
	}
}


$content_output = array('TITLE' => TEXT_RESET_PASSWORT_HEADER.' - '.SITE_NAME, 'META_DESCRIPTION' => '', 'CONTENT' => $c_output, 'HEADER_EXT' => '', 'FOOTER_EXT' => '');