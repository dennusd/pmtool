<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( 'classes/class.Global.php' );

$products = new Products();
$_POST['id'] = 684;
$products->getDetailsPDF();

/*
    public $pdf_rxcode;
    public $pdf_product_name;
    public $pdf_product_s_desc;
    public $pdf_product_paragraph;
    public $pdf_product_features;
    public $pdf_product_desc;
*/

require_once( 'assets/fpdf/fpdf.php' );

class PDF extends FPDF {
    // Page header
    function Header() {
        $this->SetFillColor( 238, 35, 19 );
        $this->SetDrawColor( 238, 35, 19 );
        $this->rect(0, 0, 210, 16.5, 'F');
        $this->Image( 'img/ROMYNOX-R.png', 95, 0, 10, 10 );
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('helvetica','I',8);
        // Page number
        $this->Cell( 0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C' );
    }
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->SetMargins( 20, 20, 20 );
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont( 'helvetica','b',16 );
$pdf->SetTextColor( 255, 255, 255 );
$pdf->SetXY( 5, 3 );
$pdf->Cell( 210,10, $products->pdf_product_name, 0, 1 );

$pdf->SetFont( 'helvetica','',12 );
$pdf->SetTextColor( 0, 0, 0 );
$pdf->SetXY( 5, 20 );
$pdf->Cell( 210,10, $products->pdf_product_paragraph, 0, 1 );

$pdf->SetXY( 5, 40 );
$pdf->Cell( 210,10, $products->pdf_product_features, 0, 1 );

$pdf->Output();
?>