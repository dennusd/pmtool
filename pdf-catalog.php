<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once __DIR__ . '/vendor/autoload.php';

/*
    FONT
*/
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '',
    ]),
    'fontdata' => $fontData + [
        'eurostile' => [
            'R' => 'EurostileBold.ttf',
        ],
        'fasolid' => [
            'R' => 'fa-solid-900.ttf',
        ]
    ],
    'default_font' => 'dejavusans'
]);





$mysqli = new mysqli( 'localhost','root','root','wordpress4' );

if( $mysqli -> connect_errno ) :
    echo 'Failed to connect to MySQL: ' . $mysqli -> connect_error;
    exit();
endif;

$q = ' SELECT p.ID, p.post_title, p.post_excerpt, p.post_content, am.meta_value
            FROM wp_posts AS p
            LEFT JOIN
                wp_postmeta pm ON
                    pm.post_id = p.ID AND
                    pm.meta_key = "_thumbnail_id"
            LEFT JOIN
                wp_postmeta am ON
                    am.post_id = pm.meta_value AND
                    am.meta_key = "_wp_attached_file"
            WHERE post_type = "product"
            AND ID >= 2083
            ORDER BY p.ID
            LIMIT 100
';

$q1      = ' SELECT p.ID, p.post_title, p.post_excerpt, p.post_content
            FROM wp_posts AS p
            WHERE post_type = "product"
            AND ID >= 2083
            
';

//$mpdf->showImageErrors = false;

$stylesheet = file_get_contents( 'css/mpdf.css' );

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'iso-8859-4';
$mpdf->WriteHTML( $stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS );


if( $result = $mysqli -> query( $q ) ) :
    while( $row = $result->fetch_assoc() ) :
        $post_title = $row['post_title'];
        $post_excerpt = $row['post_excerpt'];
        $post_content = $row['post_content'];
        //$image = 'http://dev.romynox.nl/cms/wp-content/uploads/'. $row['meta_value'];

        $mpdf->SetHTMLHeader( '<div class="header"><h1>'. $post_title .'</h1><div class="short-desc">'. $post_excerpt .'</div></div>', 'O' );
        //$mpdf->WriteHTML( '<img src="http://dev.romynox.nl/cms/wp-content/uploads/2022/12/ar_tk-inline-tkl.jpg" style="float: left; border: 1px; width:5cm; height:5cm" >' );

        //$mpdf->WriteHTML( '<div class="post_content"><img src="'. $image .'" style="float: left; border: 1px; width:5cm; height:5cm" >'. $post_content .'</div>' );
        $mpdf->WriteHTML( '<div class="post_content">'. $post_content .'</div>' );
        $mpdf->SetHTMLFooter( '<div class="footer"><img class="footer--image" src="img/pdf_footer.JPG"></div>', 'O' );
        $mpdf->AddPage();

    endwhile;
endif;


//$mpdf->WriteHTML( '<div class="first-paragraph">'. htmlspecialchars_decode($products->pdf_product_paragraph) .'</div>' );
//$mpdf->WriteHTML( '<div class="features"><h2>Features</h2>'. $features .'</div>' );

//$mpdf->Image( $image, 15, 30, 45, 45);
//$mpdf->SetHeader( $headerConfig, 'BLANK', false );
//$mpdf->SetFooter( '<strong>Test</strong>' );
//$mpdf->WriteHTML( $products->pdf_product_features );

//$mpdf->WriteHTML( '<div><img class="photo" src="'.$image.'">'. $html .'</div>' );


$mpdf->Output();



?>