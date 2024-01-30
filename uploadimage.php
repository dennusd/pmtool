<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( 'classes/class.Global.php' );

$products = new Products();

$products->uploadImage( $_POST['id'], $_FILES["fileToUpload"]["name"] );
//$products->saveCatOverview( $_POST['id'], $_POST['quick-cat']);

?>