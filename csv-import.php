<?php 

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( 'classes/class.Global.php' );

$import = new Import();

$import->loadCSV( '_csv/22-06-2023.csv' );

?>