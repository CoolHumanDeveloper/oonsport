<?php
if(isset($_POST['update_profile']) && $_POST['update_profile']==1) {
	$sql = "UPDATE `user_profile` SET `user_details`='".$_POST['profile_details']."' WHERE `user_id`='".$_SESSION['user']['user_id']."'";
	$query = $DB->prepare($sql);
    $query->execute();
		
    $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_PROFILE_PROFILE_UPDATE_SUCCESS);
    header("location: ".SITE_URL."me/profile/chronik/");
    die();

}

$sql = "SELECT * FROM user_profile WHERE user_id='".$_SESSION['user']['user_id']."' LIMIT 1";
	$query = $DB->prepare($sql);
    $query->execute();
    $profile = $query->fetch(); 

$output = $sidebar.'<div class="col-md-9 col-sm-12">
<h4 class="profile">'.TEXT_PROFILE_CHRONIK_HEADER.' </h4><br>
<form method="post" action="#SITE_URL#me/profile/chronik/" id="my_profile"  class="form">
	
		
		<div class="row form_line">
	
		<div class="col-md-12">
        	<label>'.constant('TEXT_PROFILE_INTRO_CHRONIK_'.$_SESSION['user']['user_type']).':</label>
		</div>
		</div>
		
		<div class="row form_line">
		
		<div class="col-md-12">
			<textarea name="profile_details" rows="10" class="form-control">'.$profile['user_details'].'</textarea>
		</div>
		</div>
		
		<div class="row form_line">
				<div class="col-md-6">
				</div>
				<div class="col-md-6">
				<input type="hidden" name="update_profile" value="1">
				<button type="submit" class="btn btn-sm btn-primary form-control">'.TEXT_GLOBAL_SAVE.'</button>
				</div>
		</div>
		
        </form>
		</div>';
$content_output = array('TITLE' => 'Profil -> '.TEXT_PROFILE_CHRONIK_HEADER,
 'CONTENT' => $output,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');