<?php

class admin {
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

    public function showSpecs() {

        $status = NULL;
        $category_id = self::safeinput()
        $q_cat_name = 'SELECT category_name FROM categories WHERE category_id = '. $category_id; 
        if( $result = $this->mysqli->query($q_cat_name) ):
            while( $row = $result->fetch_row() ):
                echo('<h1>'. $row[0] .'</h1>');
            endwhile;
        endif;

        $q = 'SELECT 
                s.id AS specificationID,
                s.spec_en,
                scl.category_id AS catID
                FROM specifications AS s
                LEFT JOIN spec_cat_link scl
                ON s.id = scl.spec_id
                AND scl.category_id = '. $category_id .'
                ORDER BY s.spec_en';

        if( $result = $this->mysqli->query($q) ):
            while( $row = $result->fetch_assoc() ):
                if( $row['catID']) $status = 'checked';

                echo '<input type="checkbox"'. $status .'>';
                echo '<label id>'. $row['spec_en'] .'</label>';
                echo '<br>';

                $status = NULL;
            endwhile;
        endif;

    }

    public function saveTest() {
        $q = 'IF EXISTS (SELECT * FROM spec_cat_link WHERE id = 999 AND spec_id 888)
                BEGIN
                    
                END
                ELSE
                BEGIN
                   
                END';

        echo $q;

        if( $this->mysqli->query($q) ) echo $q. 'Done';
    }

}

?>