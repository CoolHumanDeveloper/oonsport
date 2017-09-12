<?php

function db_start() {
    
    $db = new PDO (
		'mysql:host=localhost;' .
		'dbname=' . DB_DEFAULT . ';' .
		'charset=utf8',
		DB_USER,
		DB_PASSWORD, array(
			PDO::ATTR_PERSISTENT=> true
		)
	);
	
	if (!$db) {
		die ("Keine verbindung zu datenbank<br />");
	} else {
		//echo "Verbindung hergestellt<br />";
	}
    return $db;
}