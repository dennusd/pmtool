<?php

class Export extends Products {

    public function CSV_getCategories( $vmID, $lang ) {

        $cats = NULL;

        $q = '  SELECT cn.Woocommerce_en, cn.Woocommerce_nl FROM prod_cat_new_link AS pcnl
                LEFT JOIN categories_new cn
                ON cn.category_id = pcnl.category_id
                WHERE pcnl.virtuemart_product_id = '. $vmID;

        // echo $q;
        if( $result = $this->mysqli->query($q) ):
            $counter = 0;
            while( $row = $result->fetch_assoc() ):
                if( $counter>0 ) $cats .= ', ';
                if( $lang == 'nl' ) $cats .= $row['Woocommerce_nl'];
                if( $lang == 'en' ) $cats .= $row['Woocommerce_en'];
                $counter++;
            endwhile;
        endif;

        return $cats;
    }

    public function totalProducts() {

        $q = 'SELECT COUNT(id) AS totalProducts FROM producten';

        if( $result = $this->mysqli->query( $q ) ) :
            return $result->num_rows;
        endif;
        
    }

    public function CSV_spec( $prod_id ) {

        $specs = array();
        $value = NULL;

        $q = '  SELECT ps.id, ps.value, s.type, s.json, s.wc_identifier
                FROM prods_specs as ps
                JOIN specifications s
                ON ps.spec_id = s.id
                WHERE ps.product_id = '. $prod_id;
        // echo '<div>'. $q .'</div>';
        
        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $specdata = NULL;

                switch( $row['type'] ) {

                    case 'select':
                    case 'field':
                        $specdata .= $row['value'];
                        if( !isset($specs[$row['wc_identifier']], $specs) ):
                            $specs[$row['wc_identifier']] = NULL;
                        endif;
                        $specs[$row['wc_identifier']] .= $specdata;
                    break;

                    case 'checkbox':
                        //echo $row['wc_identifier'] .': ';

                        $json_fields = json_decode( $row['json'], true );
                        $json_array = json_decode( $row['value'], true );
                        $json_values_counter = 0;

                        foreach( $json_array as $json_key => $json_value ):

                            if( $json_value == 'on' ):
                                if( $json_values_counter > 0 ) $specdata .= ', ';
                                if( array_key_exists( $json_key, $json_fields )):
                                    $specdata .= $json_fields[$json_key];
                                endif;
                                $json_values_counter++;
                            endif;

                        endforeach;

                        if( !isset($specs[$row['wc_identifier']], $specs) ):
                            $specs[$row['wc_identifier']] = NULL;
                        endif;
                        $specs[$row['wc_identifier']] .= $specdata;
                    break;
                }

               
            endwhile;
        endif;

        return $specs;
    
    }

    public function CSV_getImages( $vmID ) {

        $images = NULL;
        $q = '  SELECT * FROM prod_image_link 
                WHERE prod_image_link.main_image = 1 AND prod_image_link.virtuemart_product_id = '. $vmID;
        
        if( $result = $this->mysqli -> query($q) ):
            $counter = 0;
            while( $row = $result->fetch_assoc() ):
                if( $counter>0 ) $images .= ', ';
                $images    .= 'https://www.romynox.nl/'. $row['image'];                            
                //$images    .= 'http://10.25.115.55/development/producten/images/'. $row_images['image'];
                $counter++;
            endwhile;
        endif;

        $q = '  SELECT * FROM prod_image_link 
                WHERE prod_image_link.main_image != 1 AND prod_image_link.virtuemart_product_id = '. $vmID;
        
        if( $result = $this->mysqli -> query($q) ):
            $images .= ', ';
            $counter = 0;
            while( $row = $result->fetch_assoc() ):
                if( $counter>0 ) $images .= ', ';
                $images    .= 'https://www.romynox.nl/'. $row['image'];                            
                //$images    .= 'http://10.25.115.55/development/producten/images/'. $row_images['image'];
                $counter++;
            endwhile;
        endif;

        return $images;
    }

    public function CSV_decode( $input, bool $features = false, bool $technical = false ) {

        if( !is_null($input) ):
            //$input = html_entity_decode( html_entity_decode( $input, ENT_HTML5, 'UTF-8' ));
            $input = html_entity_decode( $input, ENT_HTML5, 'UTF-8' );

            $input = utf8_decode( $input );
            $input = trim( preg_replace( '/\s\s+/', ' ', $input ));

            if( $features == true ):
                $input = str_replace( '<ul>', '', $input );
                $input = str_replace( '</ul>', '', $input );
                $input = str_replace( '<li>', 'RXBLLT', $input );
                $input = preg_replace( '/RXBLLT/', '', $input, 1);
                $input = str_replace( '</li>', '', $input );
                $input = str_replace( '<p>', '', $input );
                $input = str_replace( '</p>', '', $input );
                $input = trim(preg_replace( '/\s\s+/', ' ', $input ));
            endif; // if( $features == true ):

            if( $technical == true ):
                $input = $input;
                //$input = html_entity_decode(html_entity_decode( $input ));
                $input = str_replace( 'Technical information', '', $input );
                $input = str_replace( 'Technical Information', '', $input );
                $input = str_replace( 'technical information', '', $input );
                $input = str_replace( 'TECHNICAL INFORMATION', '', $input );
                $input = str_replace( 'Technical specifications', '', $input );
                $input = str_replace( 'Technical Specifications', '', $input );
                $input = str_replace( 'technical specifications', '', $input );
                $input = str_replace( 'TECHNICAL SPECIFICATIONS', '', $input );
                $input = str_replace( 'Technische informatie', '', $input );
                $input = str_replace( 'Technische Informatie', '', $input );
                $input = str_replace( 'technische informatie', '', $input );
                $input = str_replace( 'TECHNISCHE INFORMATIE', '', $input );

                $input = explode( '<table', $input );

                $input = $input[0];
            endif; // if( $technical == true ):

            $input = str_replace( "’","'", $input );
            $input = str_replace( "&amp;rsquo;", "'", $input );
            $input = str_replace( "&amp;mu;", "μ", $input );
            $input = str_replace( "&nbsp;", " ", $input );
            $input = str_replace( "&ldquo;", "\"", $input );
            $input = str_replace( "&rdquo;", "\"", $input );

            return $input;
        endif;

    }


    public function generateCSV( $lang, $simple, $ID ) {

        $output = NULL;
        $catid = NULL;
        $cat = NULL;
        if( !empty($_REQUEST['cat']) ):
            $catid  = self::safeinput( $_REQUEST['cat'] );
        endif;

        $q  = 'SELECT
                    p.*,
                    cn.category_id,
                    cn.Woocommerce_nl,
                    cn.Woocommerce_en
                FROM producten AS p 
                RIGHT JOIN prod_cat_new_link pcnl 
                    ON p.virtuemart_product_id = pcnl.virtuemart_product_id
                JOIN categories_new cn 
                    ON pcnl.category_id = cn.category_id
                    AND pcnl.category_id <> 118';
        if( !empty($ID) ):
            $q .= ' WHERE p.virtuemart_product_id = '. $ID;
        else:
            $q .= 'AND pcnl.category_id <> 70';
        endif;

        $q .= ' ORDER BY p.product_name';
        
        if( $lang == 'en' ):
            $csv_filename = 'products-en.csv';
        endif;

        if( $lang == 'nl' ):
            $csv_filename = 'products-nl.csv';
        endif;

        if( $lang == 'all' ):
            $csv_filename = 'product-'. time() .'.csv';
        endif;

        $f = fopen( $csv_filename, 'w' );
        if ($f === false) {
            die( 'Error opening the file ' . $csv_filename );
        }     

        $csv_columns_simple = [
            'ID',
            'Type',
            //'SKU',
            'Name',
            'Published',
            'Is featured?',
            'Visibility in catalog',
            'Short description',
            'Description',
            'Categories',
            'Meta: _virtuemart_product_id',
            'Meta: _rxcode',
            'Meta: first_paragraph',
            'Meta: _yoast_wpseo_metadesc',
            'Meta: product_features'
        ];

        $csv_columns_extended = [
            'ID',
            'Type',
            //'SKU',
            'Name',
            'Published',
            'Is featured?',
            'Visibility in catalog',
            'Short description',
            'Meta: first_paragraph',
            'Description',
            'Meta: technical_information',
            'Meta: product_features',
            'Images',
            'Categories',
            'Meta: _virtuemart_product_id',
            'Meta: _rxcode',
            'Meta: _rx_suitable_for',
            'Meta: _rx_connection',
            'Meta: _rx_finishing',
            'Meta: _rx_man_process',
            'Meta: _rx_material',
            'Meta: _rx_max_press',
            'Meta: _rx_operation',
            'Meta: _rx_size_max',
            'Meta: _rx_size_min',
            'Meta: _rx_size_dia_max',
            'Meta: _rx_size_dia_min',
            'Meta: _rx_temp_max',
            'Meta: _rx_temp_min',
            'Meta: _rx_weight',
            'Meta: _rx_weld_conn',
            'Attribute 1 name', // Suitable for
            'Attribute 1 value(s)',
            'Attribute 1 visible',
            'Attribute 1 global',
            'Attribute 2 name', // Connection
            'Attribute 2 value(s)',
            'Attribute 2 visible',
            'Attribute 2 global',
            'Attribute 3 name', // Finishing
            'Attribute 3 value(s)',
            'Attribute 3 visible',
            'Attribute 3 global',
            'Attribute 4 name', // Operation
            'Attribute 4 value(s)',
            'Attribute 4 visible',
            'Attribute 4 global',
            'Attribute 5 name', // Operation
            'Attribute 5 value(s)',
            'Attribute 5 visible',
            'Attribute 5 global',
            'Attribute 6 name', // Operation
            'Attribute 6 value(s)',
            'Attribute 6 visible',
            'Attribute 6 global',
            'Attribute 7 name', // Operation
            'Attribute 7 value(s)',
            'Attribute 7 visible',
            'Attribute 7 global',
        ];

        if( $simple == true ) fputcsv( $f, $csv_columns_simple, ',' );
        if( $simple == false ) fputcsv( $f, $csv_columns_extended, ',' );

        if( $result = $this->mysqli->query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :

                $q_sub = '  SELECT p.* FROM producten AS p WHERE p.virtuemart_product_id = '. $row['virtuemart_product_id'];
                //echo $q_sub .'<br>';
                if( $result_sub = $this->mysqli->query( $q_sub ) ) :
                    while( $row_sub = $result_sub->fetch_assoc() ) :
                        $virtuemartID       = $row_sub['virtuemart_product_id'];
                        $rxcode             = htmlspecialchars_decode( $row_sub['rxcode'] );
                        $vmid               = NULL;
                        $product_name_nl    = NULL;

                        if( $lang == 'en' ):
                            $vmid                   = 'vmid'. $row['virtuemart_product_id'] .'en';
                            $wc_id                  = $row['wc_id_en'];
                            $product_name           = self::CSV_decode( $row_sub['product_name'], false );
                            $product_paragraph      = self::CSV_decode( $row_sub['product_paragraph'], false );
                            $product_features       = self::CSV_decode( $row_sub['product_features'], false );
                            $product_s_desc         = self::CSV_decode( $row_sub['product_s_desc'], false );
                            $product_desc           = self::CSV_decode( $row_sub['product_desc'], false, true ); 
                            $technical_inf          = self::CSV_decode( $row_sub['product_desc'], false, true );
                        endif;

                        if( $lang == 'nl' ):
                            $vmid                   = 'vmid'. $row['virtuemart_product_id'] .'nl';
                            $wc_id                  = $row['wc_id_nl'];
                            $product_name           = self::CSV_decode( $row_sub['product_name_nl'] );
                            $product_paragraph      = self::CSV_decode( $row_sub['product_paragraph_nl'], false );
                            $product_features       = self::CSV_decode( $row_sub['product_features_nl'], false );
                            $product_s_desc         = self::CSV_decode( $row_sub['product_s_desc_nl'], false );
                            $product_desc           = self::CSV_decode( $row_sub['product_desc_nl'], false, true );
                            $technical_inf          = self::CSV_decode( $row_sub['product_desc_nl'], false, true );
                        endif;

                        if( $lang == 'all' ):                            
                            $vmid_nl                = 'vmid'. $row['virtuemart_product_id'] .'nl';
                            $wc_id_nl               = $row['wc_id_nl'];
                            $product_name_nl        = self::CSV_decode( $row_sub['product_name_nl'] );
                            $product_paragraph_nl   = self::CSV_decode( $row_sub['product_paragraph_nl'], false );
                            $product_features_nl    = self::CSV_decode( $row_sub['product_features_nl'], false );
                            $product_s_desc_nl      = self::CSV_decode( $row_sub['product_s_desc_nl'], false );
                            $product_desc_nl        = self::CSV_decode( $row_sub['product_desc_nl'], false, true );
                            $technical_inf_nl       = self::CSV_decode( $row_sub['product_desc_nl'], false, true );
                            
                            $vmid_en                = 'vmid'. $row['virtuemart_product_id'] .'en';
                            $wc_id_en               = $row['wc_id_en'];
                            $product_name           = self::CSV_decode( $row_sub['product_name'], false );
                            $product_paragraph      = self::CSV_decode( $row_sub['product_paragraph'], false );
                            $product_features       = self::CSV_decode( $row_sub['product_features'], false );
                            $product_s_desc         = self::CSV_decode( $row_sub['product_s_desc'], false );
                            $product_desc           = self::CSV_decode( $row_sub['product_desc'], false, true ); 
                            $technical_inf          = self::CSV_decode( $row_sub['product_desc'], false, true );
                        endif;

                        $images             = NULL;
                        $images             = self::CSV_getImages( $row['virtuemart_product_id'] );
                        $cat                = self::CSV_getCategories( $row['virtuemart_product_id'], $lang );

                        $specs = self::CSV_spec( $row['virtuemart_product_id'] );

                        if( $simple == true && $lang = 'all' ):
                            $csv_array = [
                                $wc_id_en,
                                '',
                                $product_name,
                                '-1',
                                '0',
                                'visible',
                                $product_s_desc,
                                $product_desc,
                                $cat,
                                $vmid_en,
                                $rxcode,
                                $product_paragraph,
                                $product_paragraph,
                                $product_features
                            ];

                            fputcsv( $f, $csv_array, ',' );

                            if( !is_array( $product_desc_nl )):

                                $csv_array = [
                                    $wc_id_nl,
                                    '',
                                    $product_name_nl,
                                    '-1',
                                    '0',
                                    'visible',
                                    $product_s_desc_nl,
                                    $product_desc_nl,
                                    $cat, 
                                    $vmid_nl,
                                    $rxcode,
                                    $product_paragraph_nl,
                                    $product_paragraph_nl,
                                    $product_features_nl
                                ];

                                fputcsv( $f, $csv_array, ',' );
                            endif;

                        endif;

                        if( $simple == true && $lang <> 'all' ):
                            $csv_array = [
                                $wc_id,
                                '',
                                $rxcode,
                                $product_name,
                                '-1',
                                '0',
                                'visible',
                                $product_s_desc,
                                $product_desc,
                                $cat,
                                $vmid,
                                $rxcode,
                                $product_paragraph,
                                $product_features
                            ];
                            fputcsv( $f, $csv_array, ',' );
                        endif;

                        if( $simple == false ):
                            $csv_array = [
                                $wc_id,
                                '',
                                //SKU VELD '', 
                                $product_name,
                                '-1',
                                '0',
                                'visible',
                                $product_s_desc,
                                $product_paragraph,
                                $product_desc[0],
                                $technical_inf,
                                $product_features,
                                $images,
                                $cat,
                                // 'NEWLY IMPORTED',
                                $vmid,
                                $rxcode,
                                $specs['_rx_suitable_for'],
                                $specs['_rx_connection'],
                                $specs['_rx_finishing'],
                                $specs['_rx_man_process'],
                                $specs['_rx_material'],
                                $specs['_rx_max_press'],
                                $specs['_rx_operation'],
                                $specs['_rx_size_max'],
                                $specs['_rx_size_min'],
                                $specs['_rx_size_dia_max'],
                                $specs['_rx_size_dia_min'],
                                $specs['_rx_temp_max'],
                                $specs['_rx_temp_min'],
                                $specs['_rx_weight'],
                                $specs['_rx_weld_conn'],
                                // WC attribute 1
                                'Suitable for',
                                $specs['_rx_suitable_for'],
                                '1',
                                '1',
                                // WC attribute 2
                                'Connection',
                                $specs['_rx_connection'],
                                '1',
                                '1',
                                // WC attribute 3
                                'Finishing',
                                $specs['_rx_finishing'],
                                '1',
                                '1',
                                // WC attribute 4
                                'Man. process',
                                $specs['_rx_man_process'],
                                '1',
                                '1',
                                // WC attribute 5
                                'Material',
                                $specs['_rx_material'],
                                '1',
                                '1',
                                // WC attribute 6
                                'Operation',
                                $specs['_rx_operation'],
                                '1',
                                '1',
                                // WC attribute 7
                                'Weld connection',
                                $specs['_rx_weld_conn'],
                                '1',
                                '1',
                            ];
                            fputcsv( $f, $csv_array, ',' );
                        endif;

                    endwhile;
                endif;

            endwhile;
        endif;

        fclose( $f );


        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' .basename( $csv_filename ). '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $csv_filename ));
        readfile( $csv_filename );

    }



    public function generateCSV_update() {

        $output = NULL;

        $q      = 'SELECT p.*
                   FROM producten AS p 
                   JOIN prod_cat_new_link pcnl
                   ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                   WHERE pcnl.category_id <> 118
                   ORDER BY p.product_name';
        //echo $q;

        $csv_filename = 'products-meta-dutch.csv';
        $f = fopen($csv_filename, 'w');
        if ($f === false) {
            die('Error opening the file ' . $csv_filename);
        }


        // SEO META DATA
        $csv_columns = [
            'ID',
            'Name',
            'Meta: _yoast_wpseo_metadesc'
        ];

        $csv_columns = [
            'ID',
            'Meta: _yoast_wpseo_metadesc'
        ];

        $csv_columns_SKU = [
            'SKU',
            'Name',
            'Short description',
            'Description',
            'Meta: _virtuemart_product_id',
            'Meta: _rxcode',
            'Meta: first_paragraph',
            'Meta: product_features',
        ];

        $csv_columns_SKU = [
            'SKU',
            'Name',
            'Short description',
            'Description',
            'Meta: _virtuemart_product_id',
            'Meta: _rxcode',
            'Meta: first_paragraph',
            'Meta: product_features',
        ];

        /* 

            Export volledige data ex. specs
        
        */
        $csv_columns_ERWOIEROIW = [
            'ID',
            'Meta: _rxcode',
            'Name',
            'Short description',
            'Meta: first_paragraph',
            'Description',
            'Meta: technical_information',
            'Meta: product_features',
        ];

        /* 

            Export with PDF FILES
        
        */
        $csv_columns_PDF = [
            'ID',
            'Name',
            'Meta: download_1_url',
            'Meta: download_1_file_name',
            'Meta: download_2_url',
            'Meta: download_2_file_name',
            'Meta: download_3_url',
            'Meta: download_3_file_name',
            'Meta: download_4_url',
            'Meta: download_4_file_name',
            'Meta: download_5_url',
            'Meta: download_5_file_name',
            'Meta: download_6_url',
            'Meta: download_6_file_name',
        ];


        fputcsv( $f, $csv_columns, ',' );

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
 
                 // Product details ophalen
                $q_sub = '  SELECT 
                                p.*,
                                a.pdf1, a.pdf2, a.pdf3, a.pdf4, a.pdf5, a.pdf6
                            FROM producten AS p
                            LEFT JOIN attachements a
                            ON p.virtuemart_product_id = a.virtuemart_product_id
                            WHERE p.virtuemart_product_id = '. $row['virtuemart_product_id'];
                //echo $q_sub .'<br>';
                if( $result_sub = $this->mysqli -> query( $q_sub ) ) :
                    while( $row_sub = $result_sub->fetch_assoc() ) :
                        $pdf1_url           = NULL;
                        $pdf2_url           = NULL;
                        $pdf3_url           = NULL;
                        $pdf4_url           = NULL;
                        $pdf5_url           = NULL;
                        $pdf6_url           = NULL;
                        $pdf1_file_name     = NULL;
                        $pdf2_file_name     = NULL;
                        $pdf3_file_name     = NULL;
                        $pdf4_file_name     = NULL;
                        $pdf5_file_name     = NULL;
                        $pdf6_file_name     = NULL;
                        $virtuemartID       = $row_sub['virtuemart_product_id'];
                        $rxcode             = htmlspecialchars_decode( $row_sub['rxcode'] );
                        
                        $product_name           = self::CSV_decode( $row_sub['product_name'], false );
                        $product_s_desc         = self::CSV_decode( $row_sub['product_s_desc'], false );
                        $product_paragraph      = self::CSV_decode( $row_sub['product_paragraph'], false );
                        $product_features       = self::CSV_decode( $row_sub['product_features'], true );
                        $product_desc           = self::CSV_decode( $row_sub['product_desc'], false );
                        
                        $product_name_nl        = self::CSV_decode( $row_sub['product_name_nl'] );
                        $product_s_desc_nl      = self::CSV_decode( $row_sub['product_s_desc_nl'], false );
                        $product_paragraph_nl   = self::CSV_decode( $row_sub['product_paragraph_nl'], false );
                        $product_features_nl    = self::CSV_decode( $row_sub['product_features_nl'], true );
                        $product_desc_nl        = self::CSV_decode( $row_sub['product_desc_nl'], false );

                        $product_desc       = $row_sub['product_desc'];
                        $product_desc       = html_entity_decode(html_entity_decode($row_sub['product_desc']));
                        $product_desc       = str_replace( 'Technical information', '', $product_desc);
                        $product_desc       = str_replace( 'Technical Information', '', $product_desc);
                        $product_desc       = str_replace( 'technical information', '', $product_desc);
                        $product_desc       = str_replace( 'TECHNICAL INFORMATION', '', $product_desc);
                        $product_desc       = str_replace( 'Technical specifications', '', $product_desc);
                        $product_desc       = str_replace( 'Technical Specifications', '', $product_desc);
                        $product_desc       = str_replace( 'technical specifications', '', $product_desc);
                        $product_desc       = str_replace( 'TECHNICAL SPECIFICATIONS', '', $product_desc);
                        $product_desc       = explode( '<table', $product_desc );

                        $product_desc_nl    = $row_sub['product_desc_nl'];
                        $product_desc_nl    = html_entity_decode(html_entity_decode($row_sub['product_desc_nl']));
                        $product_desc_nl    = str_replace( 'Technical information', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'Technical Information', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'technical information', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'TECHNICAL INFORMATION', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'Technical specifications', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'Technical Specifications', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'technical specifications', '', $product_desc_nl);
                        $product_desc_nl    = str_replace( 'TECHNICAL SPECIFICATIONS', '', $product_desc_nl);
                        $product_desc_nl    = explode( '<table', $product_desc_nl );

                        $technical_inf      = NULL;
                        $technical_inf_nl   = NULL;

                        if( !empty($product_desc[1])): 
                            $technical_inf      = '<table'. $product_desc[1];
                        endif;
                        if( !empty($product_desc_nl[1])): 
                            $technical_inf_nl   = '<table'. $product_desc_nl[1];
                        endif;
                        // $product_desc       = strip_tags($product_desc);
                        
/* 
    Below removed any line-breaks, to put all the content after eachother. Especially for the catalogue
*/
                        // $product_desc       = trim(preg_replace('/\s\s+/', ' ', $product_desc));

                        echo '<strong>'. $product_name .' - '. $product_name_nl .'</strong><br>';
                        echo $product_desc_nl[0] .'<hr>';

                        $specs = self::CSV_spec( $row['virtuemart_product_id'] );

                        if( !empty($row_sub['pdf1']) ):
                            $pdf1_url       = 'https://www.romynox.nl/downloads/'. $row_sub['pdf1'];
                            $pdf1_file_name = explode( '.pdf', $row_sub['pdf1'] );
                            $pdf1_file_name = $pdf1_file_name[0];
                        endif;
                        if( !empty($row_sub['pdf2']) ):
                            $pdf2_url       = 'https://www.romynox.nl/downloads/'. $row_sub['pdf2'];
                            $pdf2_file_name = explode( '.pdf', $row_sub['pdf2'] );
                            $pdf2_file_name = $pdf2_file_name[0];
                        endif;
                        if( !empty($row_sub['pdf3']) ):
                            $pdf3_url       = 'https://www.romynox.nl/downloads/'. $row_sub['pdf3'];
                            $pdf3_file_name = explode( '.pdf', $row_sub['pdf3'] );
                            $pdf3_file_name = $pdf3_file_name[0];
                        endif;
                        if( !empty($row_sub['pdf4']) ):
                            $pdf4_url       = 'https://www.romynox.nl/downloads/'. $row_sub['pdf4'];
                            $pdf4_file_name = explode( '.pdf', $row_sub['pdf4'] );
                            $pdf4_file_name = $pdf4_file_name[0];
                        endif;
                        if( !empty($row_sub['pdf5']) ):
                            $pdf5_url       = 'https://www.romynox.nl/downloads/'. $row_sub['pdf5'];
                            $pdf5_file_name = explode( '.pdf', $row_sub['pdf5'] );
                            $pdf5_file_name = $pdf5_file_name[0];
                        endif;
                        if( !empty($row_sub['pdf6']) ):
                            $pdf6_url       = 'https://www.romynox.nl/downloads/'. $row_sub['pdf6'];
                            $pdf6_file_name = explode( '.pdf', $row_sub['pdf6'] );
                            $pdf6_file_name = $pdf6_file_name[0];
                        endif;

                        $csv_array_OLD = [
                            'vmid'. $row['virtuemart_product_id'],
                            $product_name,
                            $product_s_desc,
                            $product_desc,
                            $virtuemartID,
                            $rxcode,
                            $product_paragraph,
                            $product_features,
                        ];

                        $csv_array_WELKEISDIT = [
                            $row['wc_id_en'],
                            $rxcode,
                            $product_name,
                            $product_s_desc,
                            $product_paragraph,
                            $product_desc[0],
                            $technical_inf,
                            $product_features,
                        ];

                        $csv_array = [
                            $row['wc_id_en'],
                            $product_name_nl,
                            $product_paragraph_nl,
                        ];

                        $csv_array_NL = [
                            $row['wc_id_nl'],
                            $rxcode,
                            $product_name_nl,
                            $product_s_desc_nl,
                            $product_paragraph_nl,
                            $product_desc_nl[0],
                            $technical_inf_nl,
                            $product_features_nl,
                        ];

                        $csv_array_PDF = [
                            $row['wc_id_en'],
                            $product_name,
                            $pdf1_url,
                            $pdf1_file_name,
                            $pdf2_url,
                            $pdf2_file_name,
                            $pdf3_url,
                            $pdf3_file_name,
                            $pdf4_url,
                            $pdf4_file_name,
                            $pdf5_url,
                            $pdf5_file_name,
                            $pdf6_url,
                            $pdf6_file_name,
                        ];

                        fputcsv( $f, $csv_array, ',' );

                        unset($technical_inf);
                        unset($technical_inf_nl);

                    endwhile;
                endif;

            endwhile;
        endif;

        fclose( $f );
    }


    public function convertSize( $size ) {

        $output = NULL;

        $q      = ' SELECT mv.IMP, mv.DIN FROM maatvoering AS mv
                    WHERE mv.decimaal = '. $size .' LIMIT 1';

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                return $row['IMP'];
            endwhile;
        endif;

/*
        switch( $size ) {
            case '0.03125':
                return '1/8';
                break;
            case '0.12500':
                return '1/8';
                break;
            case '0.25000':
                return '1/4';
                break;
            case '0.37500':
                return '3/8';
                break;
            case '0.50000':
                return '1/2';
                break;
            case '0.75000':
                return '3/4';
                break;
            case '0.33300':
                return '3/8';
                break;
            case '0.37500':
                return '3/8';
                break;
            case '37500':
                return '3/8';
                break;
            case '37500':
                return '3/8';
                break;
            case '37500':
                return '3/8';
                break;
            case '37500':
                return '3/8';
                break;
            case '37500':
                return '3/8';
                break;

        }
*/

    }


    public function generateCSV_inDesign( $page_type = 0 ) {

        $output = NULL;

        $q_old = ' SELECT 
                    p.*,
                    cn.Woocommerce_en,
                    cn.Woocommerce_nl,
                    cn.page
                FROM producten AS p
                LEFT JOIN prod_cat_new_link pcnl
                ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                LEFT JOIN categories_new cn
                ON cn.category_id = pcnl.category_id
                WHERE cn.category_id = 70
                /*AND cn.page = '. $page_type .'*/
                ORDER BY cn.Woocommerce_en ASC';
        $q = ' SELECT 
                    p.*,
                    cn.Woocommerce_en,
                    cn.Woocommerce_nl,
                    cn.page
                FROM 
                    producten AS p
                    LEFT JOIN prod_cat_new_link pcnl ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                    LEFT JOIN categories_new cn ON cn.category_id = pcnl.category_id
                WHERE 
                    p.prodtype = "SU"
                ORDER BY cn.Woocommerce_en ASC';
        echo $q;

        $csv_columns = [
            'Category_name',
            'Website_link',
            'Page_type',
            'ID',
            'Name',
            'Short_description',
            'Description',
            'rxcode',
            'first_paragraph',
            'features',
            '@image',
            'sizes_dia',
            'size_min',
            'size_max',
            'max_pressure',
            'temp_min',
            'temp_max',
            'operation',
            'connection',
            'material',
            'materialsu',
            'sterilizable',
            'shore_hardness',
            'leak_resistance',
            'flowrate_min',
            'flowrate_max',
        ];

        switch( $page_type ) {
            case 0:
                $csv_filename = 'products-SU.csv';
                break;
            case 1:
                $csv_filename = 'products-indesign-1.csv';
                break;
            case 2:
                $csv_filename = 'products-indesign-2.csv';
                break;
            case 3:
                $csv_filename = 'products-indesign-3.csv';
                break;
            case 4:
                $csv_filename = 'metadata.csv';
                break;
            case 5:
                $csv_filename = 'features.csv';
                $csv_columns = [
                    'ID',
                    'features'
                ];
                break;
        }

        //$csv_filename = 'products-indesign.csv';
        $f = fopen( $csv_filename, 'w' );
        if ($f === false) {
            die( 'Error opening the file ' . $csv_filename);
        }

        fputcsv( $f, $csv_columns, ',' );

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :

                // Product details ophalen
                $q_sub = '  SELECT 
                                cn.Woocommerce_en,
                                p.*,
                                pil.image
                            FROM 
                                producten AS p
                                JOIN prod_image_link pil ON pil.virtuemart_product_id = '. $row['virtuemart_product_id'] .'
                                JOIN prod_cat_new_link pcnl ON pcnl.virtuemart_product_id = '. $row['virtuemart_product_id'] .'
                                JOIN categories_new cn ON cn.category_id = pcnl.category_id
                                AND pil.main_image = 1
                            WHERE 
                                p.virtuemart_product_id = '. $row['virtuemart_product_id'] .'
                            LIMIT 1';
                if( $result_sub = $this->mysqli -> query( $q_sub ) ) :
                    while( $row_sub = $result_sub->fetch_assoc() ) :
                        $virtuemartID           = $row_sub['virtuemart_product_id'];
                        $rxcode                 = $row_sub['rxcode'];
                        $category               = $row_sub['Woocommerce_en'];
                        
                        $product_name           = self::CSV_decode( $row_sub['product_name'], false );
                        $product_s_desc         = self::CSV_decode( $row_sub['product_s_desc'], false );
                        $product_paragraph      = self::CSV_decode( $row_sub['product_paragraph'], false );
                        $product_features       = self::CSV_decode( $row_sub['product_features'], true );
                        $product_desc           = self::CSV_decode( $row_sub['product_desc'], false );
                        
                        $product_name_nl        = self::CSV_decode( $row_sub['product_name_nl'] );
                        $product_s_desc_nl      = self::CSV_decode( $row_sub['product_s_desc_nl'], false );
                        $product_paragraph_nl   = self::CSV_decode( $row_sub['product_paragraph_nl'], false );
                        $product_features_nl    = self::CSV_decode( $row_sub['product_features_nl'], true );
                        $product_desc_nl        = self::CSV_decode( $row_sub['product_desc_nl'], false );
                        
                        if( strstr( $product_desc, '<table', true ) == true ):
                            $product_desc = strstr( $product_desc, '<table', true);
                        endif;

                        $product_desc       = str_replace( 'Technical information', '', $product_desc);
                        $product_desc       = str_replace( 'TECHNICAL INFORMATION', '', $product_desc);
                        $product_desc       = str_replace( 'Technical information', '', $product_desc);
                        $product_desc       = str_replace( 'Technical Information', '', $product_desc);
                        $product_desc       = str_replace( 'technical information', '', $product_desc);
                        $product_desc       = str_replace( 'TECHNICAL INFORMATION', '', $product_desc);
                        $product_desc       = strip_tags($product_desc);
                        $product_desc       = trim(preg_replace('/\s\s+/', ' ', $product_desc));
                    
                        $image              = '/product-images/'. self::stripImageURLS( $row_sub['image'] );
                        //$image              = str_replace('https://www.romynox.nl/images/stories/virtuemart/product/', '',$row_sub['image']);

                        $specs = self::CSV_spec( $virtuemartID );

                        //echo 'SPECS for VMID: '. $virtuemartID .'<br>';
                        //var_dump( $specs );

                        $product_sizes_dia = NULL;
/* DIAPHRAGMS
                        $product_sizes_dia = isset($specs['_rx_size_dia_min']) ? 'MA '.$specs['_rx_size_dia_min']:NULL;

                        if( $specs['_rx_size_dia_max']):
                            $product_sizes_dia .= ' to MA '. $specs['_rx_size_dia_max'];
                        endif;
                        if( $specs['_rx_size_dia_min'] == $specs['_rx_size_dia_max']) $product_sizes_dia = 'MA '. $specs['_rx_size_dia_min'];
*/


                        $page                       = isset($row['page']) ? $row['page'] : NULL;
                        $product_size_min           = isset($specs['_rx_size_min']) ? $specs['_rx_size_min'] : NULL;
                        $product_size_min           = self::convertSize( $product_size_min );
                        $product_size_max           = isset($specs['_rx_size_max']) ? $specs['_rx_size_max'] : NULL;
                        $product_size_max           = self::convertSize( $product_size_max );
                        $product_max_pressure       = isset($specs['_rx_max_press']) ? $specs['_rx_max_press'] : NULL;
                        $product_temp_min           = isset($specs['_rx_temp_min']) ? $specs['_rx_temp_min'] : NULL;
                        $product_temp_max           = isset($specs['_rx_temp_max']) ? $specs['_rx_temp_max'] : NULL;
                        $product_operation          = isset($specs['_rx_operation']) ? $specs['_rx_operation'] : NULL;
                        $product_connection         = isset($specs['_rx_connection']) ? $specs['_rx_connection'] : NULL;
                        $product_material           = isset($specs['_rx_material']) ? $specs['_rx_material'] : NULL;
                        // Single-Use specific specs
                        $product_materialsu         = isset($specs['_rx_materialsu']) ? $specs['_rx_materialsu'] : NULL;
                        $product_sterilizable       = isset($specs['_rx_sterilizable']) ? $specs['_rx_sterilizable'] : NULL;
                        $product_shore_hardness     = isset($specs['_rx_shore_hardness']) ? $specs['_rx_shore_hardness'] : NULL;
                        $product_leak_resistance    = isset($specs['_rx_leak_resistance']) ? $specs['_rx_leak_resistance'] : NULL;                        
                        $product_flowrate_min       = isset($specs['_rx_flowrate_min']) ? $specs['_rx_flowrate_min'] : NULL;                        
                        $product_flowrate_max       = isset($specs['_rx_flowrate_max']) ? $specs['_rx_flowrate_max'] : NULL;                        

                        $slug = NULL;

                        if( !empty($row['wc_slug_en']) ):
                            $slug = 'https://romynox.nl/'. $row['wc_slug_en'] .'?utm_source=catalogue_RVS&utm_medium=qrcode&utm_campaign=catalogue_page';
                        endif;

                        $csv_array = [
                            $row_sub['Woocommerce_en'],
                            $slug,
                            $row['page'],
                            $row_sub['wc_id_en'],
                            $product_name,
                            $product_s_desc,
                            $product_desc,
                            $rxcode,
                            $product_paragraph,
                            $product_features,
                            $image,
                            $product_sizes_dia,
                            $product_size_min,
                            $product_size_max,                            
                            $product_max_pressure,
                            $product_temp_min,
                            $product_temp_max,
                            $product_operation,
                            $product_connection,
                            $product_material,
                            $product_materialsu,
                            $product_sterilizable,
                            $product_shore_hardness,
                            $product_leak_resistance,
                            $product_flowrate_min,
                            $product_flowrate_max
                        ];


                        switch( $page_type ) {
                            case 0:
                                break;
                            case 1:
                                break;
                            case 2:
                                break;
                            case 3:
                                break;
                            case 4:
                                break;
                            case 5:
                                $csv_array = [
                                    $row_sub['wc_id_en'],
                                    $product_features
                                ];
                                break;
                        }
                        //var_dump( $csv_array );
/*
                        echo '========================================================================<br>';
                        echo $q_sub .'<br>';
                        echo '<strong>'. $product_name .'</strong> - '. $row_sub['wc_id_en'] .'<br>';
                        echo '========================================================================<br>';
*/
                        fputcsv( $f, $csv_array, ',' );
                    endwhile;
                endif;

            endwhile;
        endif;

        fclose( $f );
    }

    public function CSV() {

        $q  = '
            SELECT p.*, cn.Woocommerce_en FROM producten AS p
            JOIN prod_cat_new_link pcnl
                ON pcnl.virtuemart_product_id = p.virtuemart_product_id
            JOIN categories_new cn
                ON cn.category_id = pcnl.category_id
            WHERE cn.category_id = 70
            ';

    }



}

?>