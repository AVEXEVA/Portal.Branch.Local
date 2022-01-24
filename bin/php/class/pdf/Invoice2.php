<?php

require('/var/www/html/Portal.Branch.Local/bin/library/fpdf/fpdf.php');

class PPDF extends FPDF {
  public $X = 0;
  public $Y = 0;
  public $LnHeight = 5;
  public $keyWidth = 20;
  public $valueWidth = 50;
  public $blankWidth = 40;
  public $fontSize = 10;
  function TrackXY( $Width, $Height ){
    $this->X = $Width;
    $this->Y = $Height;
  }
  function cell_header( $key ){
    self::fontBold( );
    self::fontWhite ();
    $this->Cell( $this->keyWidth + $this->valueWidth, $this->LnHeight, 'Customer', 1, 0, 'C', true  );
    self::TrackXY(
      $this->X + $this->keyWidth + $this->valueWidth,
      $this->Y + $this->LnHeight
    );
  }

  function cell_key( $key = null ) {
    self::fill( );
    self::fontWhite( );
    self::fontbold( );
    $this->Cell( $this->keyWidth, $this->LnHeight, str_pad( $key, 20, ' ', STR_PAD_LEFT ), 1, 0, 'R', true );
    self::TrackXY( $this->X + $this->keyWidth, $this->Y + $this->LnHeight );
  }
  function cell_value( $value = null) {
    self::fontBlack( );
    self::font( );
    $this->Cell( $this->valueWidth, $this->LnHeight, $value, 1, 0, 'L', false );
    self::TrackXY( $this->X + $this->valueWidth, $this->Y + $this->LnHeight );
  }
  function cell_key_value( $key, $value) {
    $this->cell_key( $key );
    $this->cell_value( $value );
  }
  function cell_block( $header, $array = array( ) ) {
    $this->cell_header( $header );
    if( count( $array ) > 0){
      foreach( $array as $key=>$value ){
        $this->cell_key_value( $key, $value );
      }
    }
  }
  function cell_blank( $key, $value) {
    $this->Cell( $this->blankWidth, $this->LnHeight, '' );
    $this->X = $this->X + $this->blankWidth;
    $this->Y = $this->Y + $this->LnHeight;
  }
  function nLn() {
    $this->Ln(  $this->LnHeight );
    self::TrackXY( 0, $this->Y + $this->LnHeight );
  }
  function fontWhite( ){ $this->SetTextColor( 255, 255, 255 ); }
  function fontBlack( ){ $this->SetTextColor( 0, 0, 0 ); }
  function font( ){ $this->SetFont('Arial','', $this->fontSize ); }
  function fontBold( ){ $this->SetFont('Arial','B',$this->fontSize ); }
  function fill( ){ $this->SetFillColor( 50, 50, 50 ); }
}
class PDF extends PPDF {
    public $args;
    // Page header
    function __construct( $orientation, $unit, $size, $args ){
        $this->args = $args;
        $this->SetFillColor( 50, 50, 50 );
        parent::__construct( $orientation, $unit, $size );
    }
    function Header(){
        $this->SetFillColor( 50, 50, 50 );
        self::NouveauElevator( );
        $this->Ln(10);
        self::BillSummary( );
        self::Invoice();
        $this->Ln(5);
        $this->Cell(  120, 5, '',  0);
        self::Amount( );
        $this->Ln(5);
        $this->Cell(  120, 5, '',  0);
        self::Paid( );
        $this->Ln(5);
        self::LinePerforation( );
        $this->Ln(5);

    }
    function NouveauElevator()
    {
        // Logo
        $this->Image('/var/www/html/Portal.Branch.Local/bin/media/image/nouveau_elevator.jpg',10,6,65);
        //Contact Information
        $this->Ln(0);
        self::Address( );
        $this->Ln(5);
        self::Telephone( );
        $this->Ln(5);
        self::Fax( );
        $this->Ln(5);
        self::URL( );
    }

    function Address( ){
        $this->SetFont('Arial','B',8);
        $this->Cell(150,5, 'addr:',0,0,'R');//
        $this->SetFont('Arial','',8);
        $this->Cell(50,5, str_pad( '47-55 37th Street LIC, NY 1181', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function Telephone( ){
        $this->SetFont('Arial','B',8);
        $this->Cell(150,5, '   tel: ',0,0,'R');//
        $this->SetFont('Arial','',8);
        $this->Cell(50,5, str_pad( '(718) 349-4700', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function Fax( ){
        $this->SetFont('Arial','B',8);
        $this->Cell(150,5, '  fax:',0,0,'R');//
        $this->SetFont('Arial','',8);
        $this->Cell(50,5, str_pad( '(718) 383-3218', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function URL( ){
        $this->SetFont('Arial','B',8);
        $this->Cell(150,5,'   url:',0,0,'R');//
        $this->SetFont('Arial','',8);
        $this->Cell(50,5, str_pad( 'www.NouveauElevator.com', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function Billsummary( ){
        //CustomerName\
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->cell_header( 'Customer' );
        $this->Cell(40, 5,  null, 0, 0, 'C', 0);
        $this->cell_header( 'Account' );
        $this->Ln(5);
        self::Name( );
        $this->Cell(  40, 5, '',  0);
        self::Location( );

        //Attn
        $this->Ln(5);
        self::Attn( );
        $this->Cell(40, 5, '',  0);
        $this->Unit( );

        //Street
        $this->Ln(5);
        self::Street( );
        $this->Cell(40, 5, '',  0);
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell(70, 5, 'Invoice', 1, 0, 'C', true  );


        //City, State Zip
        $this->Ln(5);
        self::CityStateZip( );
        $this->Cell(40, 5, '',  0);

    }
    function Name( ){
        $this->cell_key( 'Name' );
        $this->SetTextColor( 50, 50, 50 );
        $this->SetFont('Arial','',10);
        $this->cell_value( $this->args[ 'Customer_Name' ] );
        //$this->Cell(60,5, str_pad( , 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Attn( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell(20,5, 'Attn:',1,0,'R',true);
        $this->SetTextColor( 0, 0, 0 );
        $this->SetFont('Arial','',10);
        $this->Cell(60,5, str_pad( $this->args[ 'Contact_Name' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Street( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell(20,5, 'Address:',1,0,'R',true);
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell(60,5, str_pad( $this->args[ 'Customer_Street' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function CityStateZip( ){
        $this->SetTextColor( 255, 255, 255 );
        $this->SetFont('Arial','B',10);
        $this->Cell(20,5, '',0,0,'R');
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell(60,5, str_pad( $this->args[ 'Customer_City' ] . ', ' . $this->args[ 'Customer_City' ] . ' ' . $this->args[ 'Customer_Zip' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Location( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell( 20, 5, 'Location:', 1, 0, 'R',  true );
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell( 50, 5, $this->args[ 'Location_Name' ], 1, 0, 'L' );
    }
    function Invoice( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell( 20, 5, 'Reference:', 1, 0, 'R',  true);
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell( 50, 5, $this->args[ 'Invoice_ID' ], 1, 0, 'L' );
    }
    function Amount( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell( 20, 5, 'Amount:', 1, 0, 'R', true );
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell( 50, 5, '$' . number_format( $this->args[ 'Amount' ], 2 ), 1, 0, 'L' );
    }
    function Paid( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell( 20, 5, 'Paid:', 1, 0, 'R', true );
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell( 50, 5, '$', 1, 0, 'L' );
    }
    function LinePerforation( ){
        $this->cell( 200, 5, 'Please detach this portion and return with payment', 0, 1, 'C' );
        $this->cell( 200, 5, '---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'C' );
    }

    function BillsummaryBottom( ){
        //Customer
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell(80, 5, 'Customer', 1, 0, 'C', true  );
        $this->Cell(40, 5,  null, 0, 0, 'C', 0);
        $this->Cell(70, 5, 'Location', 1, 0, 'C', true  );
        $this->Ln(5);
        self::Name( );
        $this->Cell(  40, 5, '',  0);
        $this->SetTextColor( 0, 0, 0 );
        self::Location( );
        $this->Ln(5);
        self::Attn( );
        $this->Cell(  40, 5, '',  0);
        $this->Unit( );
        $this->Ln(5);
        self::Street( );


        $this->Ln(5);
        self::CityStateZip( );
        $this->Cell(  120, 5, '',  0);
        self::Unit();
        $this->Cell(  40, 5, '',  0);
    }


    function InvoiceTitle( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell( 20, 5, 'Invoice', 1, 0, 'R', true );
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell( 50, 5, $this->args['Invoice_ID'], 1, 0, 'C' );
    }
    function Unit( ){
        $this->SetFont('Arial','B',10);
        $this->SetTextColor( 255, 255, 255 );
        $this->Cell( 20, 5, 'Unit', 1, 0, 'R', true );
        $this->SetFont('Arial','',10);
        $this->SetTextColor( 0, 0, 0 );
        $this->Cell( 50, 5, $this->args[ 'Unit_Name' ], 1, 0, 'L' );
    }
    // Page footer
    function InvoiceTable() {
      $this->Ln(15);
      self::Date( );
      self::Terms( );
      self::Job( );
      $this->Ln(5);
      self::InvoiceNumber();
      self::PONumber();
      self::Type();
      $this->Ln(10);
      self::Disclaimer();
      $this->Ln(10);
      $this->SetTextColor( 255, 255, 255 );
      self::Description( );
      self::UnitTable();
      self::Price( );
      self::AmountTable();
      $this->Ln();
      $this->SetTextColor( 0, 0, 0 );
      self::PageTable();
      $this->Ln();
      self::InvoiceMoney( );
      $this->Ln();

    }
    function Date( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(15,5, 'Date:',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(49,5, str_pad( $this->args[ 'Date' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Terms ( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(15,5, 'Terms',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(49,5, str_pad( $this->args[ 'Terms' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Job ( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(15,5, 'Job',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(49,5, str_pad( $this->args[ 'Job' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function PONumber ( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(15,5, 'PO#',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(49,5, str_pad( $this->args[ 'PONumber' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function InvoiceNumber ( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(15,5, 'Invoice#',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(49,5, str_pad( $this->args[ 'InvoiceNumber' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Type ( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(15,5, 'Type',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(49,5, str_pad( $this->args[ 'Type' ], 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Description ( ){
      $this->SetFont('Arial','B',10);
      $this->Cell(120,10, 'Description',1,0,'C', true);
      $this->SetFont('Arial','',10);
    }
    function UnitTable ( ){
      $this->SetFont('Arial','B',10);
      $this->Cell(25,10, 'Unit',1,0,'C', true);
    }
    function Price ( ){
      $this->SetFont('Arial','B',10);
      $this->Cell(30,10, 'Price',1,0,'C', true);
    }
    function AmountTable ( ){
      $this->SetFont('Arial','B',10);
      $this->Cell(17,10, 'Amount',1,0,'C', true);
    }
    function PageTable ( ){
      self::InvoiceDescription( );
      $this->SetXY( 130, 155 );
      self::InvoiceUnit();
      self::InvoicePriceLiteral( );
      self::InvoiceAmountLiteral( );
    }
    function InvoiceDescription( ){
      $this->SetFont('Arial','',9);
      $this->MultiCell(120,5, $this->args[ 'Invoice_Description' ],1);
    }
    function InvoiceUnit( ){
      $this->SetFont('Arial','',10);
      $this->Cell(25,75, 'Each',1,0,'C');
    }
    function InvoicePriceLiteral( ){
      $this->SetFont('Arial','B',10);
      $this->Cell(30,75, '$' . number_format( $this->args[ 'Invoice_Price' ], 2 ),1,0,'C');
    }
    function InvoiceAmountLiteral( ){
      $this->SetFont('Arial','B',10);
      $this->Cell(17,75, '$' . number_format( $this->args[ 'Invoice_Amount' ], 2 ),1,0,'C');
    }
    function InvoiceMoney( ){
      self::BottomPadding( );
      self::PriceBottom();
      $this->Ln( );
      self::BottomPadding( );
      self::TaxableBottom();
      $this->Ln( );
      self::BottomPadding( );
      self::SubtotalBottom();
      $this->Ln( );
      self::BottomPadding( );
      self::SalesTaxBottom();
      $this->Ln( );
      self::BottomPadding( );
      self::TotalBottom();
    }
    function BottomPadding( ){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(120,10, '',0,0,'C');
      $this->SetFont('Arial','',10);
    }
    function PriceBottom(){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(36,5, 'Price:',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(36,5, '$' . number_format( $this->args[ 'Invoice_Price' ], 2 ),1,0,'L');
    }
    function TaxableBottom(){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(36,5, 'Taxable:',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(36,5, '$' . number_format( $this->args[ 'Invoice_Taxable' ], 2 ),1,0,'L');
    }
    function SubtotalBottom(){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(36,5, 'Subtotal:',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(36,5, '$'. number_format( $this->args[ 'Invoice_Subtotal' ], 2 ),1,0,'L');
    }
    function SalesTaxBottom(){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(36,5, 'Sales Tax:',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(36,5, '$' . number_format( $this->args[ 'Invoice_Sales_Tax' ], 2 ),1,0,'L');
    }
    function TotalBottom(){
      $this->SetTextColor( 255, 255, 255 );
      $this->SetFont('Arial','B',10);
      $this->Cell(36,5, 'Total:',1,0,'R',true);
      $this->SetTextColor( 0, 0, 0 );
      $this->SetFont('Arial','',10);
      $this->Cell(36,5, '$' . number_format( $this->args[ 'Invoice_Amount' ], 2 ),1,0,'L');
    }
    function Disclaimer ( ){
          $this->SetFont('Arial','B',9);
          $this->Cell(192 ,10, 'Invoices not paid within terms may be subject to a service charge of 1.5% per month, or the maximum permitted by law.',1,0,'L');
          $this->SetFont('Arial','',10);
    }

    function Footer()
    {
        $this->SetFillColor( 50, 50, 50 );
        self::BillSummaryBottom();
        self::InvoiceTable();

        self::Footer_Pages( );
    }
    function Footer_Pages( ){
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF(
    'P',
    'mm',
    'A4',
    array(
        'Customer_Name'         => 'Northwell',
        'Customer_Street'       => '12345 Fifth Avenue',
        'Customer_City'         => 'Manhattan',
        'Customer_State'        => 'NY',
        'Customer_Zip'          => 12345,
        'Contact_Name'          => 'Joe Schmoe',
        'Location_Name'         => '481 8th Avenue',
        'Invoice_ID'            => 752348,
        'Invoice_Price'         => 510.87,
        'Invoice_Taxable'       => 0,
        'Invoice_Subtotal'      => 0,
        'Invoice_Sales_Tax'     => 0,
        'Invoice_Amount'        => 556.21,
        'Invoice_Paid'          => 0,
        'Unit_Name'             => '1P12345',
        'Description'           => 'something goes here',
        'Date'                  => '1/24/2022',
        'Job'                   =>  'Job description',
        'Terms'                 => 'Terms go here',
        'PONumber'              =>  'PO# 15754213',
        'InvoiceNumber'         =>  '123156421',
        'Type'                  => ' Maintainence',
        'Invoice_Description'   => "Preventative maintenance service for the period of January, 2022 per
your contract MAINTENANCE - One (1) Elevator.
Nouveau Elevator News
https://www.nouveauelevator.com/nyc-dob-service-update/
Notice:
As per the Dept. of Buildings, Testing is required to be filed within 21 Days of the Inspection.
Affirmations of Correction are to be made within 90 Days of the Inspection.
AOC's are required to be submitted within 14 Days of the Correction.
 1.00
 "
      )
);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
for($i=1;$i<=40;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
}
$pdf->Output();

?>
