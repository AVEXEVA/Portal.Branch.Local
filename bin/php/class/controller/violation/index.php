<?php 
namespace controller;
class Violation extends \controller\index {
  protected $ID => null,
  protected $Name => null,
  protected $Elev => null,
  protected $Date => null,
  protected $Job => null,
  protected $Status => null,
  protected $Quote => null,
  protected $Ticket => null,
  protected $Remarks => null,
  protected $Estimate => null,
  protected $Price => null,
  protected $Address => null,
  protected $Phone => null,
  protected $Contact => null,
  protected $Street => null,
  protected $City => null,
  protected $State => null,
  protected $Zip => null,
  protected $Latitude => null,
  protected $Longitude => null,
  protected $Location_ID => null,
  protected $Location_Name => null
  public function __construct( $_ARGS = array( ) ){
    parent::__construct(  $_ARGS );
    if( isset( $_POST ) && count( $_POST ) > 0 ){ 
        parent::__set( $_POST );
        self::POST( );
        in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) 
          ? self::insert( )
          : self::update( );
    }
  }
  public function __select( $ID = null, $Name = null ){
    $result = \singleton\database::getInstance( )->query(
        null,
          " SELECT  Violation.ID        AS ID,
                    Unit.ID             AS Elev,
                    Customer.ID         AS Customer_ID,
                    Customer.Name       AS Customer_Name,
                    Loc.Loc             AS Location_ID,
                  Loc.Tag             AS Location_Name,
                  Violation.fdate     AS 'Date',
                  Violation.Status    AS Status,
                  Violation.Quote     AS Quote,
                  Violation.Ticket    AS Ticket,
                  Violation.Remarks   AS Remarks,
                  Violation.Estimate  AS Estimate,
                  Violation.Price     AS Price,
                  Rol.Phone           AS Phone,
                  Rol.Email           AS Email,
                  Rol.Contact         AS Contact,
                  Rol.Address         AS Street,
                  Rol.City            AS City,
                  Rol.State           AS State,
                  Rol.Zip             AS Zip,
                  Rol.Latt            AS Latitude,
                  Rol.fLong           AS Longitude,
          FROM    Violation
                  LEFT JOIN (
                    SELECT  Owner.ID, 
                            Rol.Name
                    FROM    Owner 
                            LEFT JOIN Rol    ON Owner.Rol = Rol.ID
                  ) AS Customer              ON Customer.ID = Job.Owner
                  LEFT JOIN Loc  AS Location ON Invoice.Loc = Loc.Loc
                  LEFT JOIN Job              ON Invoice.Job = Job.ID
                  LEFT JOIN Elev AS Unit     ON Violation.Elev = Unit.ID
          WHERE       Violation.ID = ?
                  OR  Customer.Name = ?;",
        array(
          $ID,
          $Name
              )
          );
          $Violation =   (  empty( $ID )
                       &&  !empty( $Name )
                       &&  !$result
                  )    || (empty( $ID )
                       &&  empty( $Name )
                  )    ? array(
      'ID' => null,
      'Name' => null,
      'Elev' => null,
      'Date' => null,
      'Job' => null,
      'Status' => null,
      'Quote' => null,
      'Ticket' => null,
      'Remarks' => null,
      'Estimate' => null,
      'Price' => null,
      'Address' => null,
      'Phone' => null,
      'Contact' => null,
      'Street' => null,
      'City' => null,
      'State' => null,
      'Zip' => null,
      'Latitude' => null,
      'Longitude' => null,
      'Location_ID' => null,
      'Location_Name' => null
    ) : sqlsrv_fetch_array($result);
  }
  public function insert( ){
    $result = \singleton\database::getInstance( )->query(
      null,
      " DECLARE @MAXID INT;
        SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Violation ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Violation ) END ;
        INSERT INTO Violation(
          ID,
          Locs,
          Elev,
          fDate,
          Status,
          Quote,
          Job,
          Ticket
          Remarks,
          Price,
          Address,
          City,
          State,
          Zip,
          Latt,
          fLong,
          Geolock
        )
        VALUES( @MAXID + 1 , 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        SELECT @MAXID + 1;",
      array(
        $Violation[ 'ID' ],
        $Violation[ 'Locs' ],
        $Violation[ 'Elev' ],
        $Violation[ 'fDate' ],
        $Violation[ 'Status' ],
        $Violation[ 'Quote' ],
        $Violation[ 'Job' ],
        $Violation[ 'Ticket' ],
        $Violation[ 'Remarks' ],
        $Violation[ 'Price' ],
        $Violation[ 'Address' ],
        $Violation[ 'Street' ],
        $Violation[ 'City' ],
        $Violation[ 'State' ],
        $Violation[ 'Zip' ],
        $Violation[ 'Latitude' ],
        $Violation[ 'Longitude' ],
        isset( $Violation[ 'Geofence' ] ) ? $Violation[ 'Geofence' ] : 0
      )
    );
    sqlsrv_next_result( $result );
  //Update query to fill values for $Violation and appends to $result for any updated colums
    $Violation[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
  // finds any result with the value of 0/ null
  // query that inserts values into the $Violation [rolodex] variable datatable and appends it to the $result variable
    sqlsrv_next_result( $result );
    $Violation[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
  // Checks the $Violation[ID] for any fields that are null, if none exit,
    header( 'Location: violation.php?ID=' . $Violation[ 'ID' ] );
    exit;
  }
  public function update( ){
    \singleton\database::getInstance( )->query(
      null,
      " UPDATE  Violation
        SET Violation.ID = ?,
            Violation.Locs = ?,
            Violation.Elev = ?,
            Violation.fdate = ?,
            Violation.Type = ?,
            Violation.Status = ?,
            Violation.Quote = ?,
            Violation.Job = ?,
            Violation.Ticket = ?,
            Violation.Remarks = ?,
            Violation.Price = ?,
            Violation.Address = ?,
            Violation.City = ?,
            Violation.Zip = ?,
        WHERE   Owner.ID = ?;",
      array(
        $Violation[ 'ID' ],
        $Violation[ 'Location' ],
        $Violation[ 'Elev' ],
        $Violation[ 'Date' ],
        $Violation[ 'Status' ],
        $Violation[ 'Quote' ],
        $Violation[ 'Job' ],
        $Violation[ 'Ticket' ],
        $Violation[ 'Remarks' ],
        $Violation[ 'Price' ],
        $Violation[ 'Address' ],
        $Violation[ 'Street' ],
        $Violation[ 'City' ],
        $Violation[ 'State' ],
        $Violation[ 'Zip' ],
        $Violation[ 'Latitude' ],
        $Violation[ 'Longitude' ]
      )
    );
    \singleton\database::getInstance( )->query(
      null,
      " UPDATE  Rol
        SET   Rol.Name = ?,
              Rol.Website = ?,
              Rol.Address = ?,
              Rol.Street = ?,
              Rol.City = ?,
              Rol.State = ?,
              Rol.Zip = ?,
              Rol.Latt = ?,
              Rol.fLong = ?,
              Rol.Phone = ?,
              Rol.EMail = ?

        WHERE   Rol.ID = ?;",
      array(
        $Violation[ 'Name' ],
        $Violation[ 'Website' ],
        $Violation[ 'Street' ],
        $Violation[ 'Address' ],
        $Violation[ 'City' ],
        $Violation[ 'State' ],
        $Violation[ 'Zip' ],
        $Violation[ 'Latitude' ],
        $Violation[ 'Longitude' ],
        $Violation[ 'Phone' ],
        $Violation[ 'Email' ],
        $Violation[ 'Rolodex' ]
      )
    ); 
  }
}?>