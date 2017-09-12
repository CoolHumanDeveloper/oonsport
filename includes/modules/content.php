<?php 
$sql = "SELECT * FROM content c, content_details cd WHERE c.content_id = cd.content_id AND cd.content_url='".$_GET['content_value']."' AND cd.language_code='".$_SESSION['language_code']."' AND cd.content_status=1";

$query = $DB->prepare($sql);
$query->execute();
$content = $query->fetch();

if($content['content_id']) {
    if($content['content_modul']=="default") { 
        $content_output = array('TITLE' => $content['content_title'], 'META_DESCRIPTION' => $content['content_meta'], 'CONTENT' => str_replace("OON",' <span class="light_green_bold">OON</span>',$content['content_text']), 'HEADER_EXT' => '', 'FOOTER_EXT' => '');

        $modul_template=SERVER_PATH . "template/modules/content.html";
        if(file_exists($modul_template)) {
            $modul_template=file_get_contents($modul_template);

            foreach ($content_output as $key => $value) {
                $modul_template = str_replace("#".$key."#",$value,$modul_template);
            }
            $content_output['CONTENT']=$modul_template;
        }
    }
    
	if($content['content_modul']=="special") {
		
		$sidebar='
    <div class="row">
		<div class="col-md-12 col-sm-6 text-center">
			<div class="text-center">
			'.TEXT_INDEX_BOX_WHY_OON.' <a href="#SITE_URL#'.build_content_url("warum-oon-sport").'">['.TEXT_GLOBAL_MORE.']</a>
			</div>
			<a href="#SITE_URL#'.build_content_url("warum-oon-sport").'"><img src="#SITE_URL#images/default/icons/info.jpg" width="70" height="120" alt=""/></a>
		</div>

		<div class="col-md-12 col-sm-6 text-center">
			<div class="text-center">
			'.TEXT_INDEX_BOX_WHY_MEMBERSHIP.' <a href="#SITE_URL#'.build_content_url("oon-sport-membership").'">['.TEXT_GLOBAL_MORE.']</a>
			</div>
			<a href="#SITE_URL#'.build_content_url("oon-sport-membership").'"><img src="#SITE_URL#images/default/icons/free.jpg" width="66" height="120" alt=""/></a>
		</div>
	</div>
   <div class="row">
		<div class="col-md-12 col-sm-6 text-center">
			<div class="text-center">
			'.TEXT_INDEX_BOX_NO_FRONTS.' <a href="#SITE_URL#'.build_content_url("sport-ohne-grenzen").'">['.TEXT_GLOBAL_MORE.']</a>
			</div>
			<a href="#SITE_URL#'.build_content_url("sport-ohne-grenzen").'"><img src="#SITE_URL#images/default/icons/globus.jpg" width="89" height="120" alt=""/></a>
		</div>
		<div class="col-md-12 col-sm-6 text-center">
			<div class="text-center">
			'.TEXT_INDEX_BOX_SUPPORT.' <a href="#SITE_URL#'.build_content_url("support-help").'">['.TEXT_GLOBAL_MORE.']</a>
			</div>
			<a href="#SITE_URL#'.build_content_url("support-help").'"><img src="#SITE_URL#images/default/icons/zahnrad.jpg" width="69" height="120" alt=""/></a>
		</div>
    </div>';
		
	$footer_box='</div><br>
        <br>
        <br>
        
   	<div class="col-md-12 col-sm-12">
    <header class="list-group-item start_register_header">'.TEXT_INDEX_REGISTER_HEADER.'</header>
        <div class="list-group-item  start_register_box choice-start_register_box">
		<div class="row choice_form_line">
       ';
	   
	   $sql = "SELECT * FROM user_types ut, user_types_details utd WHERE ut.user_type_id=utd.user_type_id AND utd.language_code='".$_SESSION['language_code']."' ORDER BY ut.user_type_id ASC";
        
        $query = $DB->prepare($sql);
        $query->execute();
        $get_user_types = $query->fetchAll();

		$choice_x=0;
		foreach( $get_user_types as $user_types){
            $footer_box.= '
            <div class="col-sm-6 col-xs-12 choice-element-'.$user_types['user_type_index'].'">
            <form method="post" action="#SITE_URL#register/"  class="form"><button type="submit" name="rs1" value="'.constant('TEXT_GLOBAL_REGISTER_AS_'.$user_types['user_type_id']).'" class="btn btn-sm btn-primary choice-button">'.constant('TEXT_GLOBAL_REGISTER_AS_'.$user_types['user_type_id']).'</button>
            <input type="hidden" name="register_type" value="'.$user_types['user_type_id'].'">
            <input type="hidden" name="register_step" value="1">

             </form>
             </div>
            ';
		}
		
        $footer_box.='
            
            </div>
            </div>
            <div class="clearfix"></div>';	
		
		$content_output = array('TITLE' => $content['content_title'], 'META_DESCRIPTION' => $content['content_meta'], 'CONTENT' => str_replace("OON",' <span class="light_green_bold">OON</span>',$content['content_text']), 'HEADER_EXT' => '', 'FOOTER_EXT' => '', 'SIDE_BAR' => $sidebar, 'FOOTER_BOX' => $footer_box);
		
		$modul_template=SERVER_PATH . "template/modules/special.html";
		if(file_exists($modul_template)) {
			$modul_template=file_get_contents($modul_template);
			
			foreach ($content_output as $key => $value) {
			   $modul_template = str_replace("#".$key."#",$value,$modul_template);
				}
			$content_output['CONTENT']=$modul_template;
		}
	}
	else {
		if(is_file(SERVER_PATH . "includes/modules/content/".$content['content_modul'])) {
			require(SERVER_PATH . "includes/modules/content/".$content['content_modul']);
		}
	}

}
else {
	header("location: ".SITE_URL."error/404");
}
