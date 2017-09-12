<?php 

if($_SESSION['logged_in']==1){
	header("Location: ".SITE_URL."dashboard/");
}

// OPTIN 

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

$header_ext = '<!-- bxSlider CSS file -->
<link href="#SITE_URL#css/jquery.bxslider.min.css" rel="stylesheet" />';

$footer_ext = '
<script src="#SITE_URL#js/search_sport_subgroup.min.js"></script>

<!-- bxSlider Javascript file -->
<script src="#SITE_URL#js/jquery.bxslider.min.js"></script>
<script>
$(document).ready(function(){
  $(\'.bxslider\').bxSlider({
  minSlides: 4,
  maxSlides: 17,
  slideWidth: 60,
  slideMargin: 10,
  pager: false,
  controls: false,
  autoplay: true,
  speed: 40000,
  ticker: true
});

});

$("#search_type_all").change(function () {
    $(".search_type_checkbox").prop(\'checked\', $(this).prop("checked"));
});

$(".search_type_checkbox").change(function () {
    $("#search_type_all").removeAttr("checked");
});
	
	</script>';





// Banner Header
$output = '
	<section>
    <div class="row-index">
	 <div class="col-md-12 header_start">
	<ul class="bxslider">
  <li><img src="#SITE_URL#images/default/header_start/icons/img001.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img002.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img003.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img004.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img005.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img006.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img007.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img008.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img009.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img010.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img011.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img012.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img013.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img014.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img015.gif" width="60" height="60" alt=""/></li>
  <li><img src="#SITE_URL#images/default/header_start/icons/img016.gif" width="60" height="60" alt=""/></li>
 <li><img src="#SITE_URL#images/default/header_start/icons/img017.gif" width="60" height="60" alt=""/></li>
</ul>

	</div>
	</div>
	</section>
	
	
		<div class="row index_intro_header">
		<div class="col-md-6 col-sm-6 col-xs-6">
			<h2 class="index_header index_left">'.TEXT_INDEX_TEASER_LEFT.'</h2>
		</div>
		
		<div class="col-md-6 col-sm-6">
			<h2  class="index_header index_right">'.TEXT_INDEX_TEASER_RIGHT.'</h2>
		</div>
	</div>';
	
// START REGISTER / SEARCH BOX

$output .= '<section class="start_boxes">
   	<div class="col-md-9 col-sm-12">
    <header class="list-group-item start_register_header">'.TEXT_INDEX_REGISTER_HEADER.'</header>
        <div class="list-group-item  start_register_box choice-start_register_box"><div class="row choice_form_line">
       ';
	   
	   $sql = "SELECT * FROM user_types ut, user_types_details utd WHERE ut.user_type_id=utd.user_type_id AND utd.language_code='".$_SESSION['language_code']."' ORDER BY ut.user_type_id ASC";

        $query = $DB->prepare($sql);
        $query->execute();
        $get_user_types = $query->fetchAll();

		$choice_x=0;
		foreach( $get_user_types as $user_types){
            $output .= '
            <div class="col-sm-6 col-xs-12 choice-element-'.$user_types['user_type_index'].'">
            <form method="post" action="#SITE_URL#register/"  class="form"><button type="submit" name="rs1" value="'.constant('TEXT_GLOBAL_REGISTER_AS_'.$user_types['user_type_id']).'" class="btn btn-sm btn-primary choice-button">'.constant('TEXT_GLOBAL_REGISTER_AS_'.$user_types['user_type_id']).'</button>
            <input type="hidden" name="register_type" value="'.$user_types['user_type_id'].'">
            <input type="hidden" name="register_step" value="1">

             </form>
             </div>
            ';
		}
		
        $output .= '</div></div>
		<div class="clearfix"></div>
    </div>
    <div class="col-md-3 col-sm-12">
    <header  class="list-group-item start_register_header"><strong>'.TEXT_INDEX_QUICKSEARCH.'</strong></header>
            <div class="list-group-item  start_register_box">
        <form action="#SITE_URL#search/" method="get">
        <div class="text-center">
        <table class="table">
        <tr><td class="text-left">'.TEXT_GLOBAL_I_SEARCH.':</td><tr><tr><td class="text-left">
		<label><input type="checkbox" name="search_type_all_'.$_SESSION['user']['secure_spam_key'].'" id="search_type_all" value="1" checked="checked"> '.TEXT_GLOBAL_ALL.'</label><br>
<label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="1"> '.TEXT_SEARCH_USER_TYPE_1.'</label><br>
<label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="2"> '.TEXT_SEARCH_USER_TYPE_2.'</label><br>
<label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="3"> '.TEXT_SEARCH_USER_TYPE_3.'</label><br>
<label><input type="checkbox" name="search_type_'.$_SESSION['user']['secure_spam_key'].'[]" class="search_type_checkbox" value="4"> '.TEXT_SEARCH_USER_TYPE_4.'</label>
		
		
		</td></tr>
        <tr><td class="text-left">'.TEXT_GLOBAL_SPORTTYPE.':</td></tr><tr><td>
		'.build_search_sports_select().'</td></tr>
    </table>
    
         <div class="col-md-12 col-sm-12">
		 <input id="search_action" name="search_action_'.$_SESSION['user']['secure_spam_key'].'" type="hidden" value="search.do"  />
		 <input type="submit" value="&gt;&gt;&gt; '.TEXT_GLOBAL_SEARCH.'"   class="btn btn-sm btn-primary form-control"></div><br>
<br>
        </div>
        </form>
        </div>
    </div>
	<div class="clearfix"></div>
    </section>';

	
// Banner Icons
$output .= '<div class="row content-box-right">
	<h1>'.TEXT_HEADER_WHY_OON.'</h1>
    <div class="col-sm-3 col-xs-6 text-center">
    <div class="text-center info_box_start">'.TEXT_INDEX_BOX_WHY_OON.' <a href="#SITE_URL#'.build_content_url("warum-oon-sport").'">['.TEXT_GLOBAL_MORE.']</a></div><a href="#SITE_URL#'.build_content_url("warum-oon-sport").'"><img src="#SITE_URL#images/default/icons/info.jpg" width="70" height="120" alt=""/></a></div>
	
    <div class="col-sm-3 col-xs-6 text-center"><div class="text-center info_box_start">'.TEXT_INDEX_BOX_WHY_MEMBERSHIP.' <a href="#SITE_URL#'.build_content_url("oon-sport-membership").'">['.TEXT_GLOBAL_MORE.']</a></div><a href="#SITE_URL#'.build_content_url("oon-sport-membership").'"><img src="#SITE_URL#images/default/icons/free.jpg" width="66" height="120" alt=""/></a></div>
	
	<div class="clearfix visible-xs-block"></div>
	
    <div class="col-sm-3 col-xs-6 text-center"><div class="text-center info_box_start">'.TEXT_INDEX_BOX_NO_FRONTS.' <a href="#SITE_URL#'.build_content_url("sport-ohne-grenzen").'">['.TEXT_GLOBAL_MORE.']</a></div><a href="#SITE_URL#'.build_content_url("sport-ohne-grenzen").'"><img src="#SITE_URL#images/default/icons/globus.jpg" width="89" height="120" alt=""/></a></div>
	
    <div class="col-sm-3 col-xs-6 text-center"><div class="text-center info_box_start">'.TEXT_INDEX_BOX_SUPPORT.' <a href="#SITE_URL#'.build_content_url("support-help").'">['.TEXT_GLOBAL_MORE.']</a></div><a href="#SITE_URL#'.build_content_url("support-help").'"><img src="#SITE_URL#images/default/icons/zahnrad.jpg" width="69" height="120" alt=""/></a></div>
    
    </div>
	<div class="row">
	 '.build_banner('index','xs','',0,'').'
'.build_banner('index','sm','',0,'').'

	</div>
';



$content_output = array('TITLE' => SITE_NAME." - ".TEXT_SITE_INDEX_TITLE, 'META_DESCRIPTION' => TEXT_INDEX_META_DESCRIPTION , 'CONTENT' => $output, 'FOOTER_EXT' => $footer_ext,  'HEADER_EXT' => $header_ext);