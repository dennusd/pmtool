<?php
// require_once( dirname(__FILE__) .'/../includes/inc_config.php' );

function safeinput( $input ) {
  $input = stripslashes( $input );
  $input = strip_tags( $input );
  $input = htmlspecialchars( $input );
  $input = htmlentities( $input );
  return $input; 
}

// include( dirname(__FILE__) .'/class.Details.php');

//  Autoloader for classes :)


function autoLoaderClasses( $className ) {
  $path = 'classes/';

  include 'class.'. $className .'.php';
}
spl_autoload_register( 'autoLoaderClasses' );

?>