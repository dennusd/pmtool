<?php

  libxml_use_internal_errors(true);
  
  $myXMLData =
  "<?xml version='1.0' encoding='UTF-8'?>
  <document>
  <user>John Doe</wronguser>
  <email>john@example.com</wrongemail>
  </document>";

  $xml = simplexml_load_file( 'temp/demo.WordPress.2023-02-20.xml' );
  if ($xml === false) {
    echo "Failed loading XML: ";
    foreach(libxml_get_errors() as $error) {
      echo "<br>", $error->message;
    }
  } else {
    print_r($xml);
  }
?>