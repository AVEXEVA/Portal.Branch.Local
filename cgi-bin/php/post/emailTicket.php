<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if( session_id( ) == '' || !isset( $_SESSION ) ) {    
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/library/phpmailer/src/Exception.php');
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/library/phpmailer/src/PHPMailer.php');
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/library/phpmailer/src/SMTP.php');
    
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
    $r = sqlsrv_query($NEI,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($Privileges['Ticket']) && $Privileges['Ticket']['User_Privilege'] >= 4 && $Privileges['Ticket']['Group_Privilege'] >= 4 && $Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Ticket']['Group_Privilege'] >= 4){
        $r = sqlsrv_query(  $NEI,"SELECT LID FROM TicketO WHERE TicketO.ID='{$ID}'");
        $r2 = sqlsrv_query( $NEI,"SELECT Loc FROM TicketD WHERE TicketD.ID='{$ID}'");
        $r3 = sqlsrv_query( $NEI,"SELECT Loc FROM TicketDArchive WHERE TicketDArchive.ID='{$ID}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
        $r3 = sqlsrv_fetch_array($r3);
        $Location = NULL;
        if(is_array($r)){$Location = $r['LID'];}
        elseif(is_array($r2)){$Location = $r2['Loc'];}
        elseif(is_array($r3)){$Location = $r3['Loc'];}
        if(!is_null($Location)){
            $r = sqlsrv_query(  $NEI,"SELECT ID FROM TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT ID FROM TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT ID FROM TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$User['fWork']}'");
            if($r || $r2 || $r3){
                if($r){$a = sqlsrv_fetch_array($r);}
                if($r2){$a2 = sqlsrv_fetch_array($r2);}
                if($r3){$a3 = sqlsrv_fetch_array($r3);}
                if($a || $a2 || $a3){
                    $Privileged = true;
                }
            }
        }
        if(!$Privileged){
            if($Privileges['Ticket']['User_Privilege'] >= 4 && is_numeric($ID)){
                $r = sqlsrv_query(  $NEI,"SELECT ID FROM TicketO WHERE TicketO.ID='{$ID}' AND fWork='{$User['fWork']}'");
                $r2 = sqlsrv_query( $NEI,"SELECT ID FROM TicketD WHERE TicketD.ID='{$ID}' AND fWork='{$User['fWork']}'");
                $r3 = sqlsrv_query( $NEI,"SELECT ID FROM TicketDArchive WHERE TicketDArchive.ID='{$ID}' AND fWork='{$User['fWork']}'");
                if($r || $r2 || $r3){
                    if($r){$a = sqlsrv_fetch_array($r);}
                    if($r2){$a2 = sqlsrv_fetch_array($r2);}
                    if($r3){$a3 = sqlsrv_fetch_array($r3);}
                    if($a || $a2 || $a3){
                        $Privileged = true;
                    }
                }
            }
        }
    }
    if( !isset( $Connection['ID'] ) || !$Privileged){ echo 'Blocked'; }
    else {
        sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "post/emailTicket.php"));
        $_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "Nouveau_Elevator_Portal";
        function generateMessageID()
        {
          return sprintf(
            "<%s.%s@%s>",
            base_convert(microtime(), 10, 36),
            base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
            $_SERVER['SERVER_NAME']
          );
        }
        //$_POST[ 'Data '] = explode( ',', $_POST[ 'data' ] );
        if( is_array( $_POST[ 'data' ] ) && count( $_POST[ 'data' ] ) > 0 ){ foreach( $_POST[ 'data' ] AS $ID ){
            $r = sqlsrv_query(
                $NEI,
                "   SELECT  TicketD.*,
                            Customer.Name AS Customer,
                            Loc.Tag AS Location,
                            Job.fDesc AS Job,
                            JobType.Type AS Type,
                            Elev.Unit AS Unit,
                            Emp.fFirst + ' ' + Emp.Last AS Person,
                            PDATicketSignature.Signature AS Signature,
                            Zone.Name AS Division
                    FROM    TicketD
                            LEFT JOIN Job           ON TicketD.Job   = Job.ID
                            LEFT JOIN JobType       ON JobType.ID = Job.Type
                            LEFT JOIN (
                                SELECT  Owner.ID,
                                        Rol.Name 
                                FROM    Owner 
                                        LEFT JOIN Rol ON Rol.ID = Owner.Rol
                            ) AS Customer ON Job.Owner = Customer.ID
                            LEFT JOIN Loc           ON TicketD.Loc   = Loc.Loc
                            LEFT JOIN Elev          ON TicketD.Elev  = Elev.ID
                            LEFT JOIN Emp           ON TicketD.fWork = Emp.fWork
                            LEFT JOIN PDATicketSignature ON TicketD.ID = PDATicketSignature.PDATicketID
                            LEFT JOIN Zone          ON Zone.ID = Loc.Zone
                    WHERE   TicketD.ID = ?;",
                array(
                    $ID 
                ) 
            );

            $Ticket = $r ? sqlsrv_fetch_array($r) : null;
            $to = $_POST[ 'email' ];
            $from = "WebServices@NouveauElevator.com";
            $replyto = $from;
            $date = date("Y-m-d H:i:s");
            $subject = "Assistance: Ticket #{$ID}"; 
            $Ticket['fDate'] = date("m/d/Y",strtotime($Ticket['fDate']));
            $Ticket['TimeRoute'] = date("H:i A",strtotime($Ticket['TimeRoute']));
            $Ticket['TimeSite'] = date("H:i A",strtotime($Ticket['TimeSite']));
            $Ticket['TimeComp'] = date("H:i A",strtotime($Ticket['TimeComp']));
            //$bootstrap = str_replace('"', "'", file_get_contents( 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css' ) );
            $Levels = array(
                0  => '',
                1  => 'Service Call',
                2  => 'Trucking',
                3  => 'Modernization',
                4  => 'Violations',
                5  => 'Level 5',
                6  => 'Repair',
                7  => 'Annual',
                8  => 'Escalator',
                9  => 'Email',
                10 => 'Maintenance',
                11 => 'Survey',
                12 => 'Engineering',
                13 => 'Support',
                14 => "M/R"
            );
            $message = "<html>
<body style='width:100%;'>
    <table width='100%' style='background-color:#353535;color:white;table-layout:fixed;'>
        <tbody style='padding:10px;background-color:white;color:black;'>
            <tr><td colspan='4' style='font-size:18px;background-color:black;color:white;;text-align:center;padding:10px;'><h1><img src='https://www.nouveauelevator.com/Images/Icons/logo.png' width='25px' /> Nouveau Elevator</h1></td></tr>
            <tr><td colspan='4' style='text-decoration:underline;color:white;font-weight:bold;font-size:18px;background-color:#252525;'><h2 style='padding-left:10px;'>{$Ticket['Customer']}</h2></td></tr>
            <tr>
                <td colspan='2' style='padding:5px;vertical-align:top;'>
                    <table width='100%' style='table-layout:fixed;'>
                        <tbody>
                            <tr><td>
                                <table width='100%' style='table-layout:fixed;'>
                                    <tbody>
                                        <tr><td colspan='2' style='padding:5px;text-align:center;text-decoration:underline;'><h3 style='padding:10px;margin:0px;'>Ticket #{$ID}</h3></td></tr>
                                        <tr><td style='padding:5px;vertical-align:top;'>
                                            <table style='table-layout:fixed;'>
                                                <tbody>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Division:</td>
                                                        <td style='padding:5px;'>{$Ticket['Division']}</td></tr>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Customer:</td>
                                                        <td style='padding:5px;'>{$Ticket['Customer']}</td></tr>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Location:</td>
                                                        <td style='padding:5px;'>{$Ticket['Location']}</td></tr>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Unit:</td>
                                                        <td style='padding:5px;'>{$Ticket['Unit']}</td></tr>
                                                </tbody>
                                            </table>
                                        </td><td style='padding:5px;vertical-align:top;'>
                                            <table  width='100%' style='table-layout:fixed;'>
                                                <tbody>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Job:</td>
                                                        <td style='padding:5px;'>{$Ticket['Job']}</td></tr>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Type:</td>
                                                        <td style='padding:5px;'>{$Ticket['Type']}</td></tr>
                                                    <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Sub-Type:</td>
                                                        <td style='padding:5px;'>{$Levels[ $Ticket['Level'] ]}</td></tr>
                                                </tbody>
                                            </table>
                                        </td></tr>
                                    </tbody>
                                </table>
                            </td></tr>
                            <tr><td>
                                <table width='100%' style='table-layout:fixed;'>
                                    <tbody>
                                        <tr><td colspan='2' style='padding:5px;vertical-align:top;'>
                                            <tr><td style='padding:5px;'>
                                                <tr><td colspan='4' style='padding:5px;'>
                                                <h4>Description</h4>
                                                <pre>{$Ticket['fDesc']}</pre>
                                            </td></tr>
                                        </td></tr>
                                    </tbody>
                                </table>
                            </td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan='2' style='padding:5px;vertical-align:top;'>
                    <table  width='100%' style='table-layout:fixed;'>
                        <tbody>
                            <tr><td colspan='2' style='padding:5px;text-align:center;text-decoration:underline;'><h3>{$Ticket['Person']}</h3></td></tr>
                            <tr><td style='padding:5px;vertical-align:top;'>
                                <table  width='100%' style='table-layout:fixed;'>
                                    <tbody>
                                        <tr><td style='padding:5px;vertical-align:top;'>
                                            <table><tbody>
                                                <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Date</td>
                                                    <td style='padding:5px;'>{$Ticket['fDate']}</td></tr>
                                                <tr><td style='font-weight:bold;text-align:right;padding:5px;'>En Route</td>
                                                    <td style='padding:5px;'>{$Ticket['TimeRoute']}</td></tr>
                                                <tr><td style='font-weight:bold;text-align:right;padding:5px;'>On Site</td>
                                                    <td style='padding:5px;'>{$Ticket['TimeSite']}</td></tr>
                                                <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Completed</td>
                                                    <td style='padding:5px;'>{$Ticket['TimeComp']}</td></tr>
                                            </tbody></table>
                                        </td><td style='padding:5px;vertical-align:top;'>
                                                <table width='100%' style='table-layout:fixed;'>
                                                    <tbody>
                                                        <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Regular</td>
                                                            <td style='padding:5px;'>{$Ticket['Reg']}</td></tr>
                                                        <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Differential</td>
                                                            <td style='padding:5px;'>{$Ticket['NT']}</td></tr>
                                                        <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Overtime</td>
                                                            <td style='padding:5px;'>{$Ticket['OT']}</td></tr>
                                                        <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Doubletime</td>
                                                            <td style='padding:5px;'>{$Ticket['DT']}</td></tr>
                                                        <tr><td style='font-weight:bold;text-align:right;padding:5px;'>Total</td>
                                                            <td style='padding:5px;'>{$Ticket['Total']}</td></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </td></tr>
                                        <tr><td colspan='2'>
                                            <table width='100%' style='table-layout:fixed;'>
                                                <tbody>
                                                    <tr><td colspan='2' style='padding:5px;vertical-align:top;'>
                                                        <table  width='100%' style='table-layout:fixed'>
                                                            <tbody>
                                                                <tr><td style='padding:5px;'>
                                                                    <h4>Resolution</h4>
                                                                    <pre style='width:100%;'>{$Ticket['DescRes']}</pre>
                                                                </td></tr>
                                                                </td></tr>
                                                            </tbody>
                                                        </table>
                                                    </td></tr>
                                                </tbody>
                                            </table>
                                        </tr></td>
                                    </tbody>
                                </table>
                            </td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr><td colspan='4' style='padding:5px;'>
                <h4>Signee:</h4>
                <div>{$Ticket['SignatureText']}</div>
                <img align='left' src='cid:signature_image' alt='' />
            </td></tr>
        </tbody>
    </table>
</body>
</html>";
            $Arranger = "WebServices";

            $headers = array();
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'Mesaage-id: ' .generateMessageID();
            $headers[] = "From: 'WebServices' <$from>";
            $headers[] = 'Reply-To: $Arranger <$replyto>';
            $headers[] = 'Date: $date';
            $headers[] = "Return-Path: <$from>";
            $headers[] = 'X-Priority: 3';//1 = High, 3 = Normal, 5 = Low
            $headers[] = 'X-Mailer: PHP/' . phpversion();
            //$_SESSION['Email'] = $_POST['Email'];
            //mail($to, $subject, $message, implode("\r\n", $headers));
            //$message = implode("\r\n",$headers) . $message;

            //require('cgi-bin/libraries/PHPMailer-master/src/Exception.php');
            //require('cgi-bin/libraries/PHPMailer-master/src/PHPMailer.php');
            //require('cgi-bin/libraries/PHPMailer-master/src/SMTP.php');
            $mail = new PHPMailer(true);
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
                $mail->addStringEmbeddedImage( $Ticket[ 'Signature' ], 'signature_image', 'Signature.png', 'base64', 'image/png' );
                //Recipients
                $mail->setFrom('webservices@nouveauelevator.com', 'Web Services');
                $Emails = explode(";", $_POST['email']);
                /*$Emails = array(
                    'thinksperanza@gmail.com',
                    'psperanza@nouveauelevator.com'
                );*/
                if(count($Emails) > 0){
                    foreach($Emails as $Email){
                        $mail->addAddress($Email);     // Add a recipient
                    }
                } else {
                    $mail->addAddress($_POST['Email']);
                }
                //$mail->addCC('cc@example.com');

                //$mail->addAddress('ellen@example.com');               // Name is optional
                $mail->addReplyTo('webservices@nouveauelevator.com', 'NoReply');
                //$mail->addBCC('bcc@example.com');

                // Attachments
                //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $subject;
                $mail->Body = $message;
                ob_start();
                $mail->send();
                ob_end_clean();
                //echo 'Message has been sent';
            } catch (Exception $e) {
              //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } }
    }
}?>
