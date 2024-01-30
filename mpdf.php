<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( 'classes/class.Global.php' );

$products = new Products();
$products->getDetailsPDF();

/*
    public pdf_rxcode;
    public pdf_product_name;
    public pdf_product_s_desc;
    public pdf_product_paragraph;
    public pdf_product_features;
    public pdf_product_desc;
*/

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

$html = htmlspecialchars_decode( $products->pdf_product_desc );
$techinfo = htmlspecialchars_decode( $products->pdf_product_techinfo );
$image = htmlspecialchars_decode( $products->pdf_image );
$features = str_replace( '<ul>', '<ul class="featureslist">', $products->pdf_product_features );
$features = str_replace( '<li>', '<li class="featuresli">+ ', $features );
$mpdf->showImageErrors = true;

/*

    Load stylesheet 'css/mpdf.css'

*/
$stylesheet = file_get_contents( 'css/mpdf.css' );
$mpdf->WriteHTML( $stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS );

$mpdf->SetHTMLHeader( '<div class="header"><h1>'. $products->pdf_product_name .'</h1></div>', 'O' );
$mpdf->WriteHTML( '<div class="short-desc">'. $products->pdf_product_s_desc .'</div>' );
$mpdf->WriteHTML( '<div class="line">&nbsp;</div>' );
$mpdf->WriteHTML( '<div class="first-paragraph">'. htmlspecialchars_decode($products->pdf_product_paragraph) .'</div>' );
$mpdf->WriteHTML( '<div class="rxcode">'. $products->pdf_rxcode .'</div>' );

$mpdf->WriteHTML( '<div class="features-container"><div class="features"><h2>Features</h2>'. $features .'</div></div>' );

$mpdf->WriteHTML( '<div class="techinfo-header"><h2>TECHNICAL INFORMATION</h2></div>' );
$mpdf->WriteHTML( '<div class="techinfo-container">'. $techinfo .'</div>' );
//$mpdf->Image( $image, 15, 30, 45, 45);
//$mpdf->SetHeader( $headerConfig, 'BLANK', false );
//$mpdf->SetFooter( '<strong>Test</strong>' );
//$mpdf->WriteHTML( $products->pdf_product_features );
//$mpdf->WriteHTML( '<img src="'.$image.'" style="float: left; border: 1px; width:5cm; height:5cm" >' );

$mpdf->WriteHTML( '<div class="content">'. $html .'</div>' );
$mpdf->WriteHTML( '<div class="content-image"><img class="photo" src="'.$image.'"></div>' );
$mpdf->SetHTMLFooter( '<div class="footer"><img class="footer--image" src="img/pdf_footer.JPG"></div>', 'O' );

$mpdf->Output();

?>