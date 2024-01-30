<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( 'classes/class.Global.php' );

$products = new Products();

$products->setMainImage( $_POST['productid'], $_POST['pilinkid']);

?>