<?php
namespace sql;
class database extends \sql\index {
  //variables
  ///links
  protected $resource     = null;
  ///arguments
  protected $id           = null;
  protected $dataserver   = null;
  protected $user         = null;
  protected $ip           = null;
  protected $name         = null;
  ///children
  protected $datatables   = array();
  //functions
  ///magic
  public function __construct( $_args = null ){
    parent::__construct( $_args );
    self::__connect( );
  }
  private function __connect( ){
    self::__resource( );
    self::__tables( );
  }
  //constructors
  private function __resource(){
    parent::__set( 
      'resource',
      new \sql\resource( array ( 
        'database' => $this,
        'type' => 'default'
      ) )
    );
  }
  private function __table( $table = null ){ return new \sql\table ( $table ); }
  private function __tables( ){
    switch( parent::__get( 'resource' )->__get( 'type' ) ){
      case 'sqlsrv' : self::__tables_sqlsrv( ); break;
      case 'mysqli' : self::__tables_mysqli( ); break;
      default       : self::__tables_myslqi( ); break;
    }
  }
  private function __tables_sqlsrv( ){ }
  private function __tables_mysqli( ){
    $tables = array();
    $query = "select * from information_schema.columns where columns.table_catalog = ?;";
    $statement = mysqli_prepare( 
      parent::__get( 'resource' )->__get( 'link' ),
      $query
    );
    mysqli_stmt_bind_param(
      $statement,
      'i',
      parent::__get( 'name' )
    );
    mysqli_stmt_execute( $statement );
    $result = mysqli_stmt_get_result( $statement );
    if( $query ){while( $row = mysqli_fetch_row( $result )){ $tables[] = self::__table( $row ); }}
    parent::__set( 'tables', $tables );
  }
}
?>
