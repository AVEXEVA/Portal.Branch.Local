<?php
namespace pdf;
require('/var/www/html/Portal.Branch.Local/bin/library/fpdf/fpdf.php');

class PPDF extends \FPDF {
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
class Invoice extends \pdf\PPDF {
    public $args;
    // Page header
    function __construct( $orientation, $unit, $size, $args ){
        $this->args = $args;
        $this->fill( 50, 50, 50 );
        parent::__construct( $orientation, $unit, $size );
    }
    function Header(){
        $this->fill();
        self::NouveauElevator( );
        $this->Ln(10);
        self::BillSummary( );
        self::Invoice();
        $this->Ln(5);
        $this->Cell(  110, 5, '',  0);
        self::Amount( );
        $this->Ln(5);
        $this->Cell(  110, 5, '',  0);
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
        $this->fontBold();
        $this->Cell(150,5, 'addr:',0,0,'R');//
        $this->font();
        $this->Cell(50,5, str_pad( '47-55 37th Street LIC, NY 1181', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function Telephone( ){
        $this->fontBold();
        $this->Cell(150,5, '   tel: ',0,0,'R');//
        $this->font();
        $this->Cell(50,5, str_pad( '(718) 349-4700', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function Fax( ){
        $this->fontBold();
        $this->Cell(150,5, '  fax:',0,0,'R');//
        $this->font();
        $this->Cell(50,5, str_pad( '(718) 383-3218', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function URL( ){
        $this->fontBold();
        $this->Cell(150,5,'   url:',0,0,'R');//
        $this->font();
        $this->Cell(50,5, str_pad( 'www.NouveauElevator.com', 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );
    }
    function Billsummary( ){
        //CustomerName\
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->fontWhite();
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
        $this->fontBold();
        $this->fontWhite();
        $this->Cell(70, 5, 'Invoice', 1, 0, 'C', true  );
        //City, State Zip
        $this->Ln(5);
        self::CityStateZip( );
        $this->Cell(40, 5, '',  0);

    }
    function Name( ){
        $this->cell_key_value( 'Name',  $this->args[ 'Customer_Name' ] );
        //$this->Cell(60,5, str_pad( , 35, ' ', STR_PAD_RIGHT ), 1, 0, 'L' );
    }
    function Attn( ){
      $this->cell_key_value( 'attn' , $this->args[ 'Contact_Name' ]);
    }
    function Street( ){
        $this->cell_key_value('Street' , $this->args[ 'Customer_Street' ] );
    }
    function CityStateZip( ){
        $this->Cell(20,5, '',0,0,'R');
        $this->cell_value( $this->args[ 'Customer_City' ] . ', ' . $this->args[ 'Customer_City' ] . ' ' . $this->args[ 'Customer_Zip' ] );
    }
    function Location( ){
        $this->cell_key_value( 'Location' , $this->args[ 'Location_Name' ]);
    }
    function Invoice( ){
        $this->cell_key_value(  'Invoice' , $this->args[ 'Invoice_ID' ]);
    }
    function Amount( ){
        $this->cell_key_value( 'Amount', '$'.number_format ( $this->args[ 'Amount' ]));
    }
    function Paid( ){
        $this->cell_key_value( 'Paid', '$'.number_format ( $this->args[ 'Paid' ]));
    }
    function LinePerforation( ){
        $this->cell( 200, 5, 'Please detach this portion and return with payment', 0, 1, 'C' );
        $this->cell( 200, 5, '---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'C' );
    }
    function BillsummaryBottom( ){
        //Customer
        $this->Ln(5);
        $this->fontBold();
        $this->fontWhite();
        $this->Cell(70, 5, 'Customer', 1, 0, 'C', true  );
        $this->Cell(40, 5,  null, 0, 0, 'C', 0);
        $this->Cell(70, 5, 'Location', 1, 0, 'C', true  );
        $this->Ln(5);
        self::Name( );
        $this->Cell(  40, 5, '',  0);
        $this->fontBlack();
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
        $this->Cell(  40, 5, '',  0);
    }
    function InvoiceTitle( ){
        $this->cell_key_value('InvoiceTitle', $this->args['Invoice_ID']);
    }
    function Unit( ){
        $this->cell_key_value('Unit', $this->args[ 'Unit_Name' ]);
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
      $this->fontWhite();
      self::Description( );
      self::Data();
      self::QuantityTable();
      self::Price( );
      self::AmountTable();
      $this->Ln();
      $this->fontBlack();
      self::PageTable();
      $this->Ln();
      self::InvoiceMoney( );
      $this->Ln();

    }
    function Date( ){
      $this->cell_key_value('Date', $this->args[ 'Date' ]);
    }
    function Terms ( ){
      $this->cell_key_value( 'Terms',  $this->args[ 'Terms' ] );
    }
    function Job ( ){
      $this->cell_key_value('Job' , $this->args[ 'Job' ] );
    }
    function PONumber ( ){
      $this->cell_key_value('PONumber' , $this->args[ 'PONumber' ]);
    }
    function InvoiceNumber ( ){
      $this->cell_key_value(  'Invoice' , $this->args[ 'InvoiceNumber' ] );
    }
    function Type ( ){
      $this->cell_key_value(  'Type' , $this->args[ 'Type' ] );
    }
    function Description ( ){
      $this->fontBold();
      $this->Cell(100,10, 'Description',1,0,'C', true);
      $this->font();
    }
    function Data ( ){
      $this->fontBold();
      $this->Cell(20,10, 'Data',1,0,'C', true);
      $this->font();
    }
    function QuantityTable ( ){
      $this->fontBold();
      $this->Cell(25,10, 'Quantity',1,0,'C', true);
    }
    function Price ( ){
      $this->fontBold();
      $this->Cell(30,10, 'Price',1,0,'C', true);
    }
    function AmountTable ( ){
      $this->fontBold();
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
      $this->fontBold();
      $this->Cell(30,75, '$' . number_format( $this->args[ 'Invoice_Price' ], 2 ),1,0,'C');
    }
    function InvoiceAmountLiteral( ){
      $this->fontBold();
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
      $this->fontWhite();
      $this->fontBold();
      $this->Cell(122,10, '',0,0,'C');
      $this->font();
    }
    function PriceBottom(){
      $this->cell_key_value( 'Price', '$'.number_format ( $this->args[ 'Invoice_Price' ]));
    }
    function TaxableBottom(){
      $this->cell_key_value( 'Taxable', '$'.number_format ( $this->args[ 'Invoice_Taxable' ]));
    }
    function SubtotalBottom(){
      $this->cell_key_value( 'Subtotal', '$'.number_format ( $this->args[ 'Invoice_Subtotal' ]));
    }
    function SalesTaxBottom(){
      $this->cell_key_value( 'Sales Tax', '$'.number_format ( $this->args[ 'Invoice_Sales_Tax' ]));
    }
    function TotalBottom(){
      $this->cell_key_value( 'Total', '$'.number_format ( $this->args[ 'Invoice_Amount' ]));
    }
    function Disclaimer ( ){
          $this->SetFont('Arial','B',9);
          $this->Cell(192 ,10, 'Invoices not paid within terms may be subject to a service charge of 1.5% per month, or the maximum permitted by law.',1,0,'L');
          $this->font();
    }

    function Footer()
    {
        $this->fill();
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

?>
