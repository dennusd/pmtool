<?php

class products {
    var $mysqli;

/*

    Open database connection

*/
    public function __construct() {
        $this->mysqli = new mysqli( 'localhost','root','root','producten' );
        
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

/*

    Topbar Category menu

*/
    public function categoryMenu() {
        $output = NULL;
        $q      = 'SELECT c.category_id,
                         c.category_name,
                        COUNT(sd.prod_group) AS SpecCount
                  FROM spec_datasets AS sd
                  RIGHT JOIN categories c
                    ON sd.prod_group = c.dataset
                  GROUP BY c.category_id
                  ORDER BY c.category_name';

/*        $q      = ' SELECT c.category_id,
                    c.category_name,
                    COUNT(sd.category_id) AS SpecCount
                    FROM spec_datasets AS sd
                    RIGHT JOIN categories c
                    ON sd.category_id = c.category_id
                    GROUP BY c.category_id
                    ORDER BY c.category_name'; */

        if( $result = $this->mysqli -> query($q) ) :
            $output .= '<form id="topbar-form" method="post">
            <select id="cat-select" name="cat-select">
                <option value="">Alles</option>';
            while( $row = $result->fetch_assoc() ) :
                $output .= '<option value="'. $row['category_id'] .'">'. $row['category_name'] .' ('. $row['SpecCount'] .')</option>';
            endwhile;
            $output .= '</select></form><br>';
        endif;

        return $output;
    }

}

?>