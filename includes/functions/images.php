<?php 
function build_user_image($image,$type,$size,$view,$class) {
    global $DB;
    
    $size_value="";

    if($class != '') $class=" class=\"".$class."\" ";
    $img = $type."/".$size."/".$image.".jpg";


    if($size != 0) {
        $size = explode("x",$size);
        $size_value = "width=\"".$size[0]."\" height=\"".$size[1]."\"";
    }

    // Fallback, falls eine URL Angeben ist für z.B. default image
    if(stristr($image, "#SITE_URL#") == true) {

        if($view == "plain") {
             $output = $image;
        }
        else {
            $output = "<img src=\"".$image."\"  ".$size_value." border=0 ".$class."/>";
        }

        $output = str_replace("#SITE_URL#",SITE_URL,$output);

        return $output;
    }

    if(!file_exists(SERVER_IMAGE_PATH . "_static/".$img)) {
        // Nur Rückgabe der URL
        if($view=="plain") {
                $output = SITE_IMAGE_URL."temp/".$img;
        }
        else {
            $output = "<img src=\"".SITE_IMAGE_URL."temp/".$img."\" ".$size_value." border=0 ".$class."/>";
        }
    }
    else {
        // Nur Rückgabe der URL
        if($view == "plain") {
                $output = SITE_IMAGE_URL."_static/".$img;
        }
        else {
            $output = "<img src=\"".SITE_IMAGE_URL."_static/".$img."\"  ".$size_value." border=0 ".$class."/>";
        }
    }
		
	return $output;
}

function build_default_image ($user_id,$size,$class) {
    global $DB;
    // user_media_type=1 = BILD
    $sql = "	SELECT 
            m.*,
            u.user_type,
            ud.user_gender
        FROM 
        user_media m
        INNER JOIN user_to_media um ON um.media_id=m.media_id
        INNER JOIN user u ON um.user_id = u.user_id
        INNER JOIN user_details ud ON ud.user_id = u.user_id 
        WHERE 
        u.user_id = '".$user_id."' AND
        um.user_default_media = 1 AND 
        m.media_type = 1 
        LIMIT 1";

    $view = "html";
    if($class == "plain") $view = "plain";

    $query = $DB->prepare($sql);
    $query->execute();
    $image = $query->fetch();
    
    if($image['media_id']) {
        $output = build_user_image($image['media_file'],"profil",$size,$view,$class);
    }
    else {
        $sql = "	SELECT 
            u.user_type,
            ud.user_gender
        FROM 
        user u 
        INNER JOIN user_details ud ON ud.user_id = u.user_id 
        WHERE 
        u.user_id = '".$user_id."'
        LIMIT 1";

        $query = $DB->prepare($sql);
        $query->execute();
        $image = $query->fetch();

        if($image['user_type'] <= 2 && $image != NULL) {
            if($image['user_gender'] == 'f') {
                $profileImage = 'user_blank_female.jpg';
            }
            else {
                $profileImage = 'user_blank_male.jpg';
            }
        }
        else if($image['user_type'] == 3) {
            $profileImage = 'user_blank_club.jpg';
        }
        else if($image['user_type'] == 4) {
            $profileImage = 'user_blank_location.jpg';
        }
        else {
            $profileImage = 'user_blank.jpg';
        }
        $output = build_user_image('#SITE_URL#images/default/user/'.$profileImage,"profil",$size,$view,$class);

    }

    return $output;
}

function count_total_media($user_id) {
	global $DB;
    $sql = "SELECT count(m.media_id) AS ANZAHL FROM user_media m, user_to_media um WHERE um.user_id='".$user_id."' AND um.media_id=m.media_id";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $galerie = $query->fetch();
	
	return($galerie['ANZAHL']);
}

// TODO
function clear_image_copies() {
}

function save_base64_image($base64_image_string, $output_file_without_extentnion, $path_with_end_slash="" ) {
    $splited = explode(',', substr( $base64_image_string , 5 ) , 2);
    $mime=$splited[0];
    $data=$splited[1];
    $output_file_with_extentnion = '';
    
    $mime_split_without_base64=explode(';', $mime,2);
    $mime_split=explode('/', $mime_split_without_base64[0],2);
    if(count($mime_split)==2)
    {
        $extension=$mime_split[1];
        if($extension=='jpeg')$extension='jpg';
        $output_file_with_extentnion.=$output_file_without_extentnion.'.'.$extension;
    }
    file_put_contents( $path_with_end_slash . $output_file_with_extentnion, base64_decode($data) );
    return $path_with_end_slash . $output_file_with_extentnion;
}