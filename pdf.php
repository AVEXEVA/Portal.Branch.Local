<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM    Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

      $key = $Privilege['Access'];
      unset( $Privilege[ 'Access' ] );
      $Privileges[ $key ] = implode( '', array(
      	dechex( $Privilege[ 'Owner' ] ),
      	dechex( $Privilege[ 'Group' ] ),
      	dechex( $Privilege[ 'Department' ] ),
      	dechex( $Privilege[ 'Database' ] ),
      	dechex( $Privilege[ 'Server' ] ),
      	dechex( $Privilege[ 'Other' ] ),
      	dechex( $Privilege[ 'Token' ] ),
      	dechex( $Privilege[ 'Internet' ] )
      ) );
  }}
  if( 	!isset( $Connection[ 'ID' ] )
      ||  !isset( $Privileges[ 'Invoice' ] )
      || 	!check( privilege_read, level_group, $Privileges[ 'Invoice' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    $query = "  SELECT        0 AS ID,
                              0 AS Price,
                              0 AS Customer_ID,
              Rolodex.Name      AS Customer_Name,
              Rolodex.Phone     AS Customer_Phone,
              Rolodex.Email     AS Customer_Email,
              Rolodex.Contact   AS Contact_Name,
              Rolodex.Address   AS Customer_Street,
              Rolodex.City      AS Customer_City,
              Rolodex.State     AS Customer_State,
              Rolodex.Zip       AS Customer_Zip,
              Invoice.Price     AS  Invoice_Price,
              Invoice.Taxable   AS  Invoice_Taxable,
              Invoice.Subtotal  AS  Invoice_Subtotal,
              Invoice.STax      AS  Invoice_Sales_Tax,
              Invoice.Amount    AS  Invoice_Amount,
              Invoice.Paid      AS  Invoice_Paid,
                              0 AS  Taxable,
                              0 AS  Subtotal,
                              0 AS  Sales_Tax,
                              0 AS  Amount,
                              0 AS  Paid,
                              0 AS  Unit_Name,
                              0 AS  Description,
                              0 AS  [Date],
                              0 AS  Job,
                              0 AS  Terms,
                              0 AS  PONumber,
                              0 AS  InvoiceNumber,
                              0 AS  Type,
                              0 AS  Invoice_Description,
                              0 AS  Invoice_ID,
                              0 AS  Violation_ID,
                              0 AS  Location_ID,
                              0 AS  Tickets_ID,
                              0 AS  Estimate_ID,
                              0 AS  Job_ID,
                              0 AS  Proposal_ID,
                              0 AS  Unit_ID,

                FROM    Invoice
                        LEFT JOIN OpenAR AS Collection ON Invoice.Ref = Collection.Ref
                        LEFT JOIN Rol    AS Rolodex    ON Rol.ID      = Invoice.ID
                WHERE   Invoice.Ref = ?;";
    $ID = array();
    $result = \singleton\database::getInstance( )->query(
      null,
      $query,

      array( $ID )
    );
    if( $result ){
      $pdf = new \pdf\Invoice(
        'P',
        'mm',
        'A4',
        sqlsrv_fetch_array( $result )
      );
    }

    $pdf = new \pdf\Invoice(
        'P',
        'mm',
        'A4',
        array(
            'Customer_Name'       => isset( $_GET [ 'Customer_Name' ] )       ? $_GET ['Customer_Name'] : null,
            'Customer_Street'     => isset( $_GET [ 'Customer_Street' ] )     ? $_GET ['Customer_Street'] : null,
            'Customer_City'       => isset( $_GET [ 'Customer_City' ] )       ? $_GET ['Customer_City'] : null,
            'Customer_State'      => isset( $_GET [ 'Customer_State' ] )      ? $_GET ['Customer_State'] : null,
            'Customer_Zip'        => isset( $_GET [ 'Customer_Zip' ] )        ? $_GET ['Customer_Zip'] : null,
            'Contact_Name'        => isset( $_GET [ 'Contact_Name' ] )        ? $_GET ['Contact_Name'] : null,
            'Location_Name'       => isset( $_GET [ 'Location_Name' ] )       ? $_GET ['Location_Name'] : null,
            'Invoice_ID'          => isset( $_GET [ 'Invoice_ID' ] )          ? $_GET ['Invoice_ID'] : null,
            'Invoice_Price'       => isset( $_GET [ 'Invoice_Price' ] )       ? $_GET ['Invoice_Price'] : null,
            'Invoice_Taxable'     => isset( $_GET [ 'Invoice_Taxable' ] )     ? $_GET ['Invoice_Taxable'] : null,
            'Invoice_Subtotal'    => isset( $_GET [ 'Invoice_Subtotal' ] )    ? $_GET ['Invoice_Subtotal'] : null,
            'Invoice_Sales_Tax'   => isset( $_GET [ 'Invoice_Sales_Tax' ] )   ? $_GET ['Invoice_Sales_Tax'] : null,
            'Invoice_Amount'      => isset( $_GET [ 'Invoice_Amount' ] )      ? $_GET ['Invoice_Amount'] : null,
            'Invoice_Paid'        => isset( $_GET [ 'Invoice_Paid' ] )        ? $_GET ['Invoice_Paid'] : null,
            'ID'                  => isset( $_GET [ 'ID' ] )                  ? $_GET ['ID'] : null,
            'Price'               => isset( $_GET [ 'Price' ] )               ? $_GET ['Price'] : null,
            'Taxable'             => isset( $_GET [ 'Taxable' ] )             ? $_GET ['Taxable'] : null,
            'Subtotal'            => isset( $_GET [ 'Subtotal' ] )            ? $_GET ['Subtotal'] : null,
            'Sales_Tax'           => isset( $_GET [ 'Sales_Tax' ] )           ? $_GET ['Sales_Tax'] : null,
            'Amount'              => isset( $_GET [ 'Amount' ] )              ? $_GET ['Amount'] : null,
            'Paid'                => isset( $_GET [ 'Paid' ] )                ? $_GET ['Paid'] : null,
            'Unit_Name'           => isset( $_GET [ 'Unit_Name' ] )           ? $_GET ['Unit_Name'] : null,
            'Description'         => isset( $_GET [ 'Description' ] )         ? $_GET ['Description'] : null,
            'Date'                => isset( $_GET [ 'Date' ] )                ? $_GET ['Date'] : null,
            'Job'                 => isset( $_GET [ 'Job' ] )                 ? $_GET ['Job'] : null,
            'Terms'               => isset( $_GET [ 'Terms' ] )               ? $_GET ['Terms'] : null,
            'PONumber'            => isset( $_GET [ 'PONumber' ] )            ? $_GET ['PONumber'] : null,
            'InvoiceNumber'       => isset( $_GET [ 'InvoiceNumber' ] )       ? $_GET ['InvoiceNumber'] : null,
            'Type'                => isset( $_GET [ 'Type' ] )                ? $_GET ['Type'] : null,
            'Invoice_Description' => isset( $_GET [ 'Invoice_Description' ] ) ? $_GET ['Invoice_Description'] : null
          )
    );
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Times','',12);
    for($i=1;$i<=40;$i++){
        //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    }
    $pdf->Output();
  }
}
?>
