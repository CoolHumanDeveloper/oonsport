<?php

if(isset($_POST['delete_account'])) {
	if($_POST['delete_account_comfirm']==1) {
		deleteUserFinaly($_SESSION['user']['user_id']);
		
		session_destroy();
		session_start();
		
		$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_THANK_YOU_FOR_USING_OON);
		header("Location: ".SITE_URL);
	}
	else {	
	$_SESSION['system_message'] .= set_system_message("error", TEXT_ACCOUNT_DELETE_CONFIRM.'<br>
	<div class="row"><div class="col-md-6 col-sm-6"><form method="post" action="#SITE_URL#profile/'.md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']).'">
<input type="hidden" name="delete_account" value="1">
<button type="submit" class="btn btn-sm btn-danger">'.TEXT_ACCOUNT_DELETE_NO.'</button>
</form></div><div class="col-md-6 col-sm-6">
<form method="post" action="#SITE_URL#me/settings/quit/">
<input type="hidden" name="delete_account" value="1">
<input type="hidden" name="delete_account_comfirm" value="1">
<button type="submit" class="btn btn-sm btn-primary">'.TEXT_ACCOUNT_DELETE_YES.'</button>
</form></div></div>
');
	}
	//header("Location: ".SITE_URL."me/settings/quit/");
}


$output = $sidebar.'<div class="col-md-9 col-sm-12">
    <h4 class="profile">'.TEXT_SETTINGS_DELETE_ACCOUNT.' </h4><br>
'.TEXT_INTRO_ACCOUNT_DELETE.'<br>

<form method="post" action="#STIE_URL#me/settings/quit/">
<input type="hidden" name="delete_account" value="1">
<button type="submit" class="btn btn-sm btn-danger">'.TEXT_ACCOUNT_DELETE.'</button>
</form><br>
<br>

</div>';


if($_SESSION['user']['user_sub_of'] > 0) {
	$output = $sidebar.'<div class="col-md-9 col-sm-12">
    <h4 class="profile">'.TEXT_SETTINGS_DELETE_ACCOUNT.' </h4><br>
'.set_system_message("error", TEXT_SWITCH_TO_USE).'<br></div>';
}


$content_output = array('TITLE' => 'Einstellungen -> Account löschen',
 'CONTENT' => $output,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
?>