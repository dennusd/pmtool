<?php 

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( 'classes/class.Global.php' );

$products = new Products();

echo $products->updateItem( $_POST['id'], $_POST['part'] );

?>