<?php

include ("includes/config.db.php");

$selectLanguageValue = array(   //'de' => 'Deutsch',
                                //'en' => 'English',
                                'es' => 'Español',
                                'fr' => 'Français',
                                'fi' => 'Suomi',// Finish
                                'pt' => 'Português',
                                'dk' => 'Dansk',
                                'it' => 'Italiano',
                             
                             'sv' => 'svenska',// Swedish
                             'nl' => 'Nederlands',// Finish
                             'ru' => 'Русский',// Russisch
                             'pl' => 'Polski',// Finish
                             'cz' => 'český',// Tschechisch
                             'ar' => 'العربية',// Arabisch
                            'gr' => 'ελληνικά',// griechisch
                             'th' => 'ไทย',// Thailändisch
                             'tk' => 'Türkçe',// Türkisch
                             'no' => 'Norsk',// norwegisch
                             'cn' => '中国',// chinesisch
                             'jp' => '日本語',// japanisch
                             
                            
                            ); 
foreach($selectLanguageValue as $sLanguage => $sLanguageValue) {

    $sql = "SELECT * FROM text t WHERE language_code = 'en' AND NOT EXISTS (SELECT * FROM text t2 WHERE t.text_key = t2.text_key AND t2.language_code = '".$sLanguage."')";
    echo $sql;
        $query = $DB->prepare($sql);
        $query->execute();
    $get_basic = $query->fetchAll();
    foreach ($get_basic as $data) {
        echo $data['text_key'].":"  . $sLanguage . "<br>";
        $sql = "INSERT INTO text (`text_key`, `language_code`, `text_value`) VALUES
        ('".$data['text_key']."', '".$sLanguage."', '".$data['text_value']."')";
        $query = $DB->prepare($sql);
        $query->execute();
    }
}


?>