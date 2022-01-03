<?php
//Statics
$sql  = " INSERT INTO [Privilege] ([User], [Access], [Owner], [Group], [Department], [Database], [Server], [Other], [Token], [Internet])
          VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$accesses = array(
  'Contact',
  'Contract',
  'Collection',
  'Customer',
  'Division',
  'User',
  'Job',
  'Lead',
  'Location',
  'Proposal',
  'Requisition',
  'Route',
  'Ticket',
  'Territory',
  'Code',
  'Unit',
  'Violation',
  'Map',
  'User'
  //etc
);
//Dynamic
foreach( $accesses as $access ){
  $parameters = array(
    1,
    $access,
    15,
    15,
    15,
    15,
    15,
    15,
    15,
    15
  );
  \singleton\database::getInstance()->query(
    Portal,
    $sql,
    $parameters
  );
}
?>
