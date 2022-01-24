<?php 

require('/var/www/html/Portal.Branch.Local/bin/library/fpdf/fpdf.php');

class PDF extends FPDF {
    public $args;
    // Page header
    function __construct( $orientation, $unit, $size, $args ){
        $this->args = $args;
        parent::__construct( $orientation, $unit, $size );
    }
    function Header(){
        self::NouveauElevator( );
        $this->Ln(10);
        self::BillSummary( );
        $this->Ln(25);
        self::LinePerforation( );
        $this->Ln(10);

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
        //CustomerName
        $this->Ln(5);
        self::BillTo( );
        self::Account( );

        //Attn
        $this->Ln(5);
        self::Attn( );
        self::Invoice( );

        //Street
        $this->Ln(5);
        self::Street( );
        self::Amount( );

        //City, State Zip
        $this->Ln(5);
        self::CityStateZip( );
        self::Paid( );
    }
    function BillTo( ){
        $this->SetFont('Arial','B',10);
        $this->Cell(15,5, 'Bill To:',0,0,'R');
        $this->SetFont('Arial','',10);
        $this->Cell(100,5, str_pad( $this->args[ 'Customer_Name' ], 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );   
    }
    function Attn( ){
        $this->SetFont('Arial','B',10);
        $this->Cell(15,5, 'Attn:',0,0,'R');
        $this->SetFont('Arial','',10);
        $this->Cell(100,5, str_pad( $this->args[ 'Contact_Name' ], 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );   
    }
    function Street( ){
        $this->SetFont('Arial','B',10);
        $this->Cell(15,5, 'Mail To:',0,0,'R');
        $this->SetFont('Arial','',10);
        $this->Cell(100,5, str_pad( $this->args[ 'Customer_Street' ], 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );   
    }
    function CityStateZip( ){
        $this->SetFont('Arial','B',10);
        $this->Cell(15,5, '',0,0,'R');
        $this->SetFont('Arial','',10);
        $this->Cell(100,5, str_pad( $this->args[ 'Customer_City' ] . ', ' . $this->args[ 'Customer_City' ] . ' ' . $this->args[ 'Customer_Zip' ], 35, ' ', STR_PAD_RIGHT ), 0, 0, 'L' );   
    }
    function Account( ){
        $this->SetFont('Arial','B',10);
        $this->Cell( 10, 5, 'Account:', 0, 0, 'R' );
        $this->SetFont('Arial','',10);
        $this->Cell( 50, 5, $this->args[ 'Location_Name' ], 0, 0, 'L' );
    }
    function Invoice( ){
        $this->SetFont('Arial','B',10);
        $this->Cell( 10, 5, 'Invoice:', 0, 0, 'R' );
        $this->SetFont('Arial','',10); 
        $this->Cell( 50, 5, $this->args[ 'Invoice_ID' ], 0, 0, 'L' );
    }
    function Amount( ){
        $this->SetFont('Arial','B',10);
        $this->Cell( 10, 5, 'Amount:', 0, 0, 'R' );
        $this->SetFont('Arial','',10);
        $this->Cell( 50, 5, '$' . number_format( $this->args[ 'Amount' ], 2 ), 0, 0, 'L' );
    }
    function Paid( ){
        $this->SetFont('Arial','B',10);
        $this->Cell( 10, 5, 'Paid:', 0, 0, 'R' );
        $this->SetFont('Arial','',10);
        $this->Cell( 50, 5, '$', 1, 0, 'L' );
    }
    function LinePerforation( ){
        $this->cell( 200, 5, 'Please detach this portion and return with payment', 0, 1, 'C' );
        $this->cell( 200, 5, '---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'C' );
    }
    // Page footer
    function Footer()
    {
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
        'Invoice_Amount'        => 556.21,
        'Invoice_Paid'          => 0
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