<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
    require('/var/www/html/Portal.Branch.Local/bin/library/phpmailer/src/Exception.php');
    require('/var/www/html/Portal.Branch.Local/bin/library/phpmailer/src/PHPMailer.php');
    require('/var/www/html/Portal.Branch.Local/bin/library/phpmailer/src/SMTP.php');
    $_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) 
        ? $_SERVER['SERVER_NAME'] 
        : "Nouveau_Elevator_Portal";
    function generateMessageID() {
        return sprintf(
            "<%s.%s@%s>",
            base_convert(microtime(), 10, 36),
            base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
            $_SERVER['SERVER_NAME']
        );
    }
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
          FROM  Emp
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
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Invoice' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Invoice' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        $query = "  SELECT  TOP 1
                            Invoice.Ref                         AS ID,
                            Invoice.fDesc                       AS Description,
                            Invoice.fDate                       AS Date,
                            Invoice.Amount                      AS Price,
                            Invoice.Amount                      AS Amount,
                            Invoice.STax                        AS Sales_Tax,
                            Invoice.Total                       AS Total,
                            Invoice.Taxable                     AS Taxable,
                            OpenAR.Original - OpenAR.Balance    AS Paid,
                            OpenAR.Balance                      AS Due,
                            Customer.ID                         AS Customer_ID,
                            Customer.Name                       AS Customer_Name,
                            Customer.Street                     AS Customer_Street,
                            Customer.City                       AS Customer_City,
                            Customer.State                      AS Customer_State,
                            Customer.Zip                        AS Customer_Zip,
                            Customer.Contact                    AS Customer_Contact,
                            Location.Loc                        AS Location,
                            Location.Loc                        AS Location_ID,
                            Location.Tag                        AS Location_Name,
                            Location.Address                    AS Location_Street,
                            Location.City                       AS Location_City,
                            Location.State                      AS Location_State,
                            Location.Zip                        AS Location_Zip,
                            Job.ID                              AS Job_ID,
                            Job.fDesc                           AS Job_Name,
                            Job_Type.Type                       AS Job_Type,
                            Division.ID                         AS Division_ID,
                            Division.Name                       AS Division_Name,
                            Route.ID                            AS Route_ID,
                            Route.Name                          AS Route_Name,
                            Employee.ID                         AS Employee_ID,
                            Employee.fFirst                     AS Employee_First_Name,
                            Employee.Last                       AS Employee_Last_Name,
                            OpenAR.Due                          AS Due,
                            OpenAR.Balance                      AS Balance,
                            Rolodex.Contact                     AS Contact_Name,
                            Rolodex.EMail                       AS Contact_Email
                    FROM    Invoice
                            LEFT JOIN Loc                       AS Location ON Invoice.Loc      = Location.Loc
                            LEFT JOIN Job                       AS Job      ON Invoice.Job      = Job.ID
                            LEFT JOIN Zone                      AS Division ON Location.Zone    = Division.ID
                            LEFT JOIN Route                     AS Route    ON Location.Route   = Route.ID
                            LEFT JOIN OpenAR                    AS OpenAR   ON Invoice.Ref      = OpenAR.Ref
                            LEFT JOIN (
                              SELECT  Customer.ID               AS ID,
                                      Rolodex.Name              AS Name,
                                      Rolodex.Contact           AS Contact,
                                      Rolodex.Address           AS Street,
                                      Rolodex.City              AS City,
                                      Rolodex.State             AS State,
                                      Rolodex.Zip               AS Zip
                              FROM    Owner                     AS Customer
                                      LEFT JOIN Rol             AS Rolodex  ON Customer.Rol     = Rolodex.ID
                            ) AS Customer                                   ON Location.Owner   = Customer.ID
                            LEFT JOIN Emp                       AS Employee ON Route.Mech       = Employee.fWork
                            LEFT JOIN JobType                   AS Job_Type ON Job.Type         = Job_Type.ID
                            LEFT JOIN Rol                       AS Rolodex  ON Location.Rol     = Rolodex.ID
                    WHERE   Invoice.Ref = ?;";
        foreach( $_POST[ 'data' ] as $index=>$ID ){
            //SQL
            $result = \singleton\database::getInstance( )->query(
                null,
                $query,
                array( $ID )
            ); 
            $Invoice = $result 
                ? sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) 
                : null;
            $OpenAR = array(
                'Customer_Name'         => $Invoice[ 'Customer_Name' ],
                'Customer_Street'       => $Invoice[ 'Customer_Street' ],
                'Customer_City'         => $Invoice[ 'Customer_City' ],
                'Customer_State'        => $Invoice[ 'Customer_State' ],
                'Customer_Zip'          => $Invoice[ 'Customer_Zip' ],
                'Contact_Name'          => $Invoice[ 'Contact_Name' ],
                'Location_ID'           => $Invoice[ 'Location_ID' ],
                'Location_Name'         => $Invoice[ 'Location_Name' ],
                'Invoice_ID'            => $Invoice[ 'ID' ],
                'Invoice_Price'         => $Invoice[ 'Amount' ],
                'Invoice_Taxable'       => $Invoice[ 'Taxable' ],
                'Invoice_Subtotal'      => $Invoice[ 'Total' ],
                'Invoice_Sales_Tax'     => $Invoice[ 'Sales_Tax' ],
                'Invoice_Amount'        => $Invoice[ 'Amount' ],
                'Invoice_Paid'          => $Invoice[ 'Paid' ],
                'Unit_ID'               => $Invoice[ 'Unit_ID' ],
                'Unit_Name'             => $Invoice[ 'Unit_Name' ],
                'Description'           => $Invoice[ 'Description' ],
                'Date'                  => $Invoice[ 'Date' ],
                'Job'                   => $Invoice[ 'Job' ],
                'Terms'                 => 'NET 30',
                'PONumber'              => '',
                'InvoiceNumber'         => '',
                'Type'                  => 'Maintainence',
                'Contact_Email'         => 'psperanza@nouveauelevator.com',
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
            );
            //Message
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = 2;                                       // Enable verbose debug output
                $mail->isSMTP();                                            // Set mailer to use SMTP
                $mail->Host       = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'webservices@nouveauelevator.com';                     // SMTP username
                $mail->Password   = 'daxlxnzndgvwczth';                               // SMTP password
                $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
                $mail->Port       = 587;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('webservices@nouveauelevator.com', 'Web Services');

                $result = \singleton\database::getInstance( )->query(
                    null,
                    "   SELECT  Contact.EMail AS Email
                        FROM    Contact 
                        WHERE       Contact.Name    = ?
                                AND Contact.Type    = 4
                                AND Contact.Invoice = 1;",
                    array(
                        $Invoice[ 'Location_Name' ]
                    )
                );
                if( !$result ){ continue; }
                while( $row = sqlsrv_fetch_array( $result ) ){
                    var_dump( $row[ 'Email' ] );
                    //$mail->addAddress( $row[ 'Email' ] );
                }

                $mail->addReplyTo('webservices@nouveauelevator.com', 'NoReply');


                $pdf = new \pdf\Invoice(
                    'P',
                    'mm',
                    'A4',
                    $OpenAR
                );
                $pdf->AliasNbPages();
                $pdf->AddPage();
                $path = '/var/www/html/Portal.Branch.Local/bin/pdf/Invoice/' . $Invoice[ 'Invoice_ID' ] . '.pdf';
                
                $mail->addStringAttachment( $pdf->Output( $path, 'S' ), 'Invoice_' . $Invoice[ 'Invoice_ID' ] );
                // Attachments
                //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
                $subject = "Nouveau Elevator - Invoice #" . $Invoice[ 'Invoice_ID' ];
                $message = "Please find Invoice #" . $Invoice[ 'Invoice_ID' ] . " attached.";
                // Content
                $mail->isHTML( false );
                $mail->Subject = $subject;
                $mail->Body    = $message;
                //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                //$mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
        
    }
}?>
