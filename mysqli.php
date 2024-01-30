<style>
img {
  height: 200px;
}
</style>
<?php
$mysqli = new mysqli( 'localhost','root','root','producten' );

// Check connection
if( $mysqli -> connect_errno ) {
  echo 'Failed to connect to MySQL: ' . $mysqli -> connect_error;
  exit();
}

?>
<table>
<?php

// Perform query
if( $result = $mysqli -> query( 'SELECT * FROM producten GROUP BY virtuemart_product_id' )) {
  while( $row = $result->fetch_assoc()) {
?>
  <tr>
    <td>
<?php
    $virtuemart_product_id = $row['virtuemart_product_id'];

    echo '<h2>'. $row['product_name'] .' ('. $row['virtuemart_product_id'] .')</h2>';
    echo '<strong>'. $row['product_s_desc'] .'</strong><br>';


//    categories
      $categories = NULL;
      if( $result_sub = $mysqli -> query( 'SELECT * FROM producten WHERE virtuemart_product_id='. $virtuemart_product_id .' GROUP BY category_name' )) {
        while( $row_sub = $result_sub->fetch_assoc()) {

          $categories[] .= $row_sub['category_name'];

        }
      }
//    -categories

//    images 
      $images = NULL;
      if( $result_sub = $mysqli -> query( 'SELECT * FROM producten WHERE virtuemart_product_id='. $virtuemart_product_id .' GROUP BY images' )) {
        while( $row_sub = $result_sub->fetch_assoc()) {

          if( pathinfo($row_sub['images'], PATHINFO_EXTENSION) != 'pdf') {

            $images[] .= str_replace( 'stories/virtuemart/product/', '', $row_sub['images'] );
          }

        }
      }
//    /images

    echo 'categories: ';

    if( is_array($categories)) {
      foreach( $categories as $categorie ) {
        echo $categorie;
        if( $categorie != end($categories) ) echo ', ';
      }
    }
    echo '<br>';
    echo 'images: ';

    if( is_array($images)) {
      foreach( $images as $image ) {
        echo $image;
        if( $image != end($images) ) echo ', ';
      }
    }

?> 
    </td>
    <td>
    <?php

    if( is_array($images)) {
      foreach( $images as $image ) {
        echo '<img src="'. $image .'">';
      }
    }

    ?>
    </td>
  </tr>
<?php
  }
}

$mysqli -> close();
?>