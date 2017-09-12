<?php 
// Login Check über functions/session.php
if(isset($_GET['confirm']) && strlen($_GET['confirm'])==32) {
    $sql = "SELECT user_id FROM user WHERE user_auth_key='".$_GET['confirm']."' AND user_auth_key!=''";
    $query = $DB->prepare($sql);
    $query->execute();
    
    $confirm_user_id = $query->fetch();
    if($confirm_user_id) {
    
	$sql = "UPDATE user SET user_auth_key='', user_status=1 WHERE user_auth_key='".$_GET['confirm']."' AND user_auth_key!=''";
        
    $query = $DB->prepare($sql);
    $query->execute();

        if($query->rowCount() == 1) {
            $_SESSION['system_message'] .= set_system_message("success",TEXT_REGISTER_CONFIRM_THANKS." ".SITE_NAME."!");
            build_history_log($confirm_user_id['user_id'],"optin");
        }
        else {
            $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_CONFIRM_ERROR_LINK);
        }
    } else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_CONFIRM_ERROR_LINK);
    }
    
}



// RENEW
if(isset($_GET['confirm_renew_pw']) && strlen($_GET['confirm_renew_pw']) == 32) {
	
	$sql = "SELECT * FROM user u, user_details ud, user_history uh WHERE u.user_id= ud.user_id AND u.user_id= uh.user_id AND u.user_status=1 AND  uh.history_action='renew_pw' AND uh.history_info='".$_GET['confirm_renew_pw']."' LIMIT 1";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $renew_user_id = $query->fetch();
    

	if($renew_user_id['user_id']) {

        $pw=random_password();
        build_history_log($renew_user_id['user_id'],"renew_pw_value",$pw.":".md5($pw));

        // Passwort neu setzen
        $sql = "UPDATE user SET user_password='".md5($pw)."' WHERE user_id='".$renew_user_id['user_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
        // Alle Renew-Keys löschen
        $sql = "UPDATE user_history SET history_info='' WHERE history_action='renew_pw' AND user_id='".$renew_user_id['user_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();

        //require(PHP_MAILER_CLASS);
        $email_content_array = array (
                                "NAME" => $renew_user_id['user_firstname'],
                                "PASSWORD" => $pw
                                );

        $email_content_template=email_content_to_template("new-password",$email_content_array,"");
        $alt_content="";

        if(sending_email($renew_user_id['user_email'],$renew_user_id['user_firstname'],"
        Dein neues Passwort ".SITE_NAME,$email_content_template,$alt_content,0) === false) {
            $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_ERROR_SENDING_MAIL);
        }
        else {	
           $_SESSION['system_message'] .= set_system_message("success",TEXT_RESET_PASSWORT_SUCCESS_NEW_PW." ".SITE_NAME."!");
        }

	}
	else {
        $_SESSION['system_message'] .= set_system_message("error", TEXT_REGISTER_CONFIRM_ERROR_LINK_PW);

	}
}

$c_output='<div class="col-md-3 col-sm-0"></div><div class="col-md-6 col-sm-12">
    <header class="list-group-item start_register_header"><strong>'.TEXT_LOGIN_LOGIN_HEADER.'</strong></header>
        <div class="list-group-item  start_register_box">
       
		 
        <div class="row form_line">
		<div class="text-center">
		<div class="col-md-12">
        
        <form method="post" action="#SITE_URL#login/">
               <div class="col-sm-6 col-xs-12 login_box"><label>'.TEXT_LOGIN_EMAILADRESS.':<br>
</label><input class="form-control" type="text" name="login_name#SPAM_KEY#" required></div>
 <div class="col-sm-6 col-xs-12 login_box"><label>'.TEXT_LOGIN_PASSWORD.':<br>
</label><input  class="form-control" type="password" name="login_password#SPAM_KEY#" required><br>
<a href="#SITE_URL#reset-password/">'.TEXT_LOGIN_PASSWORD_FORGOTTON.'</a></div>
 <div class="col-sm-12 col-xs-12 login_box"><br>
<input type="submit" value="'.TEXT_LOGIN_LOGIN_BUTTON.'"  class="btn btn-sm btn-primary form-control"><br>
<input type="checkbox" name="login_remember#SPAM_KEY#" value="1"> '.TEXT_LOGIN_STAY_ONLINE.'
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
	
	
	<div class="col-md-3 col-sm-0"></div>';

$content_output = array('TITLE' => TEXT_LOGIN_LOGIN_HEADER.' - '.SITE_NAME, 'META_DESCRIPTION' => '', 'CONTENT' => $c_output, 'HEADER_EXT' => '', 'FOOTER_EXT' => '');
?>