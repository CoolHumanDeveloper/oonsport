<?php
$lang = isset($lang) ? $lang : 'en';

$sql = "SELECT c.*, t.country_name AS translatedName
        FROM geo_countries c
            LEFT JOIN geo_countries_translation t ON c.country_code = t.country_code AND t.language_code = '$lang'
        ORDER BY 
        `country_sort` DESC, 
        t.country_name  IS NULL ASC, 
        c.country_name ASC";

$query = $DB->prepare($sql);
$query->execute();
$countries = $query->fetchAll();

$response['countries'] = $countries;
?>