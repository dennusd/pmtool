<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 0 );

require_once( 'classes/class.Global.php' );

$export = new Export2();

$export->generateCSV( 'indesign', 'en', '' );

?>