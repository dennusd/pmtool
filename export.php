<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( 'classes/class.Global.php' );

$export = new Export();

$export->generateCSV( 'all', true, $_GET['id'] );

?>