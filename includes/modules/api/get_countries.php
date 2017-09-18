<?php
$lang = isset($lang) ? $lang : 'en';

$sql = "SELECT c.*, t.country_name AS translatedName
        FROM geo_countries c
            LEFT JOIN geo_countries_translation t ON c.country_code = t.country_code AND t.language_code = '$lang'
        GROUP BY c.country_code
        ORDER BY 
        `country_sort` DESC, 
        t.country_name  IS NULL ASC, 
        c.country_name ASC";

$query = $DB->prepare($sql);
$query->execute();
$countries = $query->fetchAll(PDO::FETCH_ASSOC);

$response['countries'] = $countries;
?>