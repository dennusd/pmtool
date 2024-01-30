<?php

class Export2 extends Products {

    public function CSV_decode( $input, bool $features = false, bool $technical = false ) {

        if( !is_null($input) ):
            $input = html_entity_decode( html_entity_decode( $input, ENT_HTML5, 'UTF-8' ));
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
                $input = html_entity_decode(html_entity_decode( $input ));
                $input = str_ireplace( 'technical information:', '', $input );
                $input = str_ireplace( 'technical specifications:', '', $input );
                $input = str_ireplace( 'technical data:', '', $input );
                $input = str_ireplace( 'technische informatie:', '', $input );
                $input = str_ireplace( 'technical information :', '', $input );
                $input = str_ireplace( 'technical specifications :', '', $input );
                $input = str_ireplace( 'technical data :', '', $input );
                $input = str_ireplace( 'technische informatie :', '', $input );
                
                $input = explode( '<table', $input );

                $input = $input[0];
            endif; // if( $technical == true ):

            return $input;
        endif;

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


    public function generateCSV( $what, $lang, $ID ) {

        $q  = 'SELECT
                    p.*, pil.image, cn.category_id, cn.Woocommerce_en, cn.page
                FROM
                    producten AS p 
                RIGHT JOIN 
                    prod_cat_new_link pcnl ON p.virtuemart_product_id = pcnl.virtuemart_product_id
                JOIN 
                    categories_new cn ON pcnl.category_id = cn.category_id
                LEFT JOIN
                    prod_image_link pil ON pil.virtuemart_product_id = p.virtuemart_product_id AND pil.main_image = 1
                WHERE
                    pcnl.category_id <> 118 AND pcnl.category_id <> 1337 AND pcnl.category_id <> 31';
        
        if( !empty($ID) ):
            $q .= ' AND p.virtuemart_product_id = '. $ID;
        endif;

        $q .= ' ORDER BY p.prodtype, cn.Woocommerce_en, p.product_name';

        echo $q;

        if( $what == 'simple' )     $csv_filename = 'simple-';
        if( $what == 'extended' )   $csv_filename = 'extended-';
        if( $what == 'features' )   $csv_filename = 'features-';
        if( $what == 'indesign' )   $csv_filename = 'indesign-';
        if( $what == 'slug' )       $csv_filename = 'slug-';

        if( $lang == 'nl' )     $csv_filename .= 'nl';
        if( $lang == 'en' )     $csv_filename .= 'en';
        if( $lang == 'all' )    $csv_filename .= 'all';

        $csv_filename .= '.csv';

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
            'Attribute 7 global'
        ];

        $csv_columns_features = [
            'ID',
            'Meta: product_features'
        ];

        $csv_columns_indesign = [
            'Category_name',
            'prodtype',
            '#Website_link',
            'Page_type',
            'VMID',
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

        $csv_columns_slug = [
            'id',
            'virtuemart_product_id',
            'wc_slug_en',
            'product_name'
        ];

        if( $what == 'simple' )     fputcsv( $f, $csv_columns_simple, ',' );
        if( $what == 'extended' )   fputcsv( $f, $csv_columns_extended, ',' );
        if( $what == 'features' )   fputcsv( $f, $csv_columns_features, ',' );
        if( $what == 'indesign' )   fputcsv( $f, $csv_columns_indesign, ',' );
        if( $what == 'slug' )       fputcsv( $f, $csv_columns_slug, ',' );

        if( $result = $this->mysqli->query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :

                $image                  = 
                $vmid                   = $row['virtuemart_product_id'];
                $rxcode                 = $row['rxcode'];
                $vmid_nl                = 'vmid'. $row['virtuemart_product_id'] .'nl';
                $wc_id_nl               = $row['wc_id_nl'];
                $product_name_nl        = self::CSV_decode( $row['product_name_nl'] );
                $product_paragraph_nl   = self::CSV_decode( $row['product_paragraph_nl'], false );
                $product_features_nl    = self::CSV_decode( $row['product_features_nl'], false );
                $product_s_desc_nl      = self::CSV_decode( $row['product_s_desc_nl'], false );
                $product_desc_nl        = self::CSV_decode( $row['product_desc_nl'], false, true );
                $technical_inf_nl       = self::CSV_decode( $row['product_desc_nl'], false, true );
                
                $vmid_en                = 'vmid'. $row['virtuemart_product_id'] .'en';
                $wc_id_en               = $row['wc_id_en'];
                $product_name           = self::CSV_decode( $row['product_name'], false );
                $product_paragraph      = self::CSV_decode( $row['product_paragraph'], false );
                $product_features       = self::CSV_decode( $row['product_features'], false );
                $product_s_desc         = self::CSV_decode( $row['product_s_desc'], false );
                $product_desc           = self::CSV_decode( $row['product_desc'], false, true );
                $technical_inf          = self::CSV_decode( $row['product_desc'], false, true );

                if( $what == 'indesign' ):
                    $product_desc       = str_replace( 'Technical information', '', $product_desc );
                    $product_desc       = str_replace( 'TECHNICAL INFORMATION', '', $product_desc );
                    $product_desc       = str_replace( 'Technical information', '', $product_desc );
                    $product_desc       = str_replace( 'Technical Information', '', $product_desc );
                    $product_desc       = str_replace( 'technical information', '', $product_desc );
                    $product_desc       = str_replace( 'TECHNICAL INFORMATION', '', $product_desc );
                    $product_desc       = strip_tags( $product_desc );
                    $product_desc       = trim( preg_replace('/\s\s+/', ' ', $product_desc) );
                    $product_features   = self::CSV_decode( $row['product_features'], true );
                endif;

                $image                  = '/product-images/'. $row['image'];

                $specs = self::CSV_spec( $vmid );

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

                $product_sizes_dia = NULL;

                if( $row['category_id'] == 1 || 
                    $row['category_id'] == 72 ||
                    $row['category_id'] == 31 ||
                    $row['category_id'] == 28 ||
                    $row['category_id'] == 32 ||
                    $row['category_id'] == 29 ):

                    $product_sizes_dia = isset($specs['_rx_size_dia_min']) ? 'MA '.$specs['_rx_size_dia_min']:NULL;

                    if( $specs['_rx_size_dia_max']):
                        $product_sizes_dia .= ' to MA '. $specs['_rx_size_dia_max'];
                    endif;
                    if( $specs['_rx_size_dia_min'] == $specs['_rx_size_dia_max']) $product_sizes_dia = 'MA '. $specs['_rx_size_dia_min'];
                endif;


                $slug = NULL;

                if( !empty($row['wc_slug_en']) ):
                    $slug = 'https://romynox.nl/'. $row['wc_slug_en'] .'?utm_source=catalogue_RVS&utm_medium=qrcode&utm_campaign=catalogue_page';
                endif;

                if( $what == 'features' && $lang == 'en' ):
                    $csv_array = [
                        $wc_id_en,
                        $product_features,
                    ];
                endif;

                if( $what == 'features' && $lang == 'nl' ):
                    $csv_array = [
                        $wc_id_nl,
                        $product_features_nl,
                    ];
                endif;

                if( $what == 'indesign' && $lang == 'en' ):
                    $csv_array = [
                        $row['Woocommerce_en'],
                        $row['prodtype'],
                        $slug,
                        $row['page'],
                        $vmid,
                        $row['wc_id_en'],
                        $product_name,
                        $product_s_desc,
                        $product_desc,
                        $rxcode.' ',
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

                endif;

                if( $what == 'slug' && $lang == 'en' ):
                    $csv_array = [
                        $row['id'],
                        $slug,
                        $row['wc_slug_en'],
                        $product_name,
                    ];
                endif;

                fputcsv( $f, $csv_array, ',' );

                $csv_array = NULL;

            endwhile;
        endif;

        fclose( $f );
/*
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' .basename( $csv_filename ). '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $csv_filename ));*/
        //readfile( $csv_filename );


    }

}

?>