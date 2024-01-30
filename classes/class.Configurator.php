<?php

class configurator {
    var $mysqli;

/*

    Open database connection

*/
    public function __construct() {
        $this->mysqli = new mysqli( 'localhost','u215708619_RomyDen','~0Mi2Kg9E!w','u215708619_producten' );
        
        if( $this->mysqli -> connect_errno ) :
            echo 'Failed to connect to MySQL: ' . $this->mysqli -> connect_error;
            exit();
        endif;
    }

/*

    Strip input for safety

*/
    function safeinput( $input ) {
        $input = stripslashes( $input );
        $input = strip_tags( $input );
        $input = htmlspecialchars( $input );
        $input = htmlentities( $input );
        return $input; 
    }



    function generateOptions( $productIDs ) {
        $output = 'NULL';
        $q = 'SELECT virtuemart_product_id, product_name FROM producten WHERE virtuemart_product_id IN ('. $productIDs .') ORDER BY product_name';

        if( $result = $this->mysqli -> query($q) ) :
            while( $row = $result->fetch_assoc() ) :
                $output .= '<option value="'. $row['virtuemart_product_id'] .'">'. utf8_decode($row['product_name']) .'</option>';
            endwhile;
        endif;

        return $output;
    }



    function getImage() {
        $output = 'No photo in the database yet :-(';
        $productID = safeinput( $_POST['pid']);
        $q = 'SELECT image FROM prod_image_link WHERE virtuemart_product_id = '. $productID .' LIMIT 1';
        if( $result = $this->mysqli -> query($q) ) :
            while( $row = $result->fetch_assoc() ) :
                $output = '<img src="../product-images/'. $row['image'] .'" style="width:200px;height:200px;">';
            endwhile;
        endif;

        return $output;
    }

}

?>