<?php 
namespace sql;
class datatable extends \sql\index {
  //variables
  protected $resource    = null;
  //arguments
  protected $id          = null;
  protected $database    = null;
  protected $name        = null;
  protected $description = null;
  //functions
  ///magic
  public function __construct( $_args ){
    parent::__construct( $_args );
    if( parent::__validate( ) ){ self::__constructors( ); }
  }
  ///constructors
  private function __constructors( ){
    self::__columns( );
  }
  private static function __column( $_args = null ){ return \sql\column( $_args ); }
  private function __columns( ){
    switch( parent::__get( 'resource' )->__get( 'type' ){
      case 'sqlsrv' : self::__columns_sqlsrv( ); break;
      case 'mysqli' : self::__columns_mysqli( ); break;
      default       : self::__columns_mysqli( ); break;
    }
  }
  private function __columns_sqlsrv( ){
    $columns = array( );
    $query = "select * from information_schema.columns where columns.table_name = ?";
    $result = $database->query(
      parent::__get( 'resource' )->__get( 'link' ),
      $query,
      array( 
        parent::__get( 'name' )
      )
    );
    if( $result ){
      while( $row = sqlsrv_fetch_array( $result ) ){
        $columns[] = self::__column( $row );
      }
    }
    parent::__set( 'columns', $columns );
  }
  private function __columns_mysqli( ){
    $columns = array( );
    $query = "select * from information_schema.columns where columns.table_name = ?;";
    $statement = mysqli_prepare(
      parent::__get( 'resource' )->__get( 'link' ),
      $query
    );
    mysqli_stmt_bind_param(
      $statement,
      's',
      parent::__get( 'name' )
    );
    mysqli_stmt_execute( $statement );
    $result = mysqli_stmt_get_result( $statement );
    if( $result ){
      while( $row = mysqli_fetch_row( $result ){
        $columns[] = self::__column( $row );
      }
    }
    parent::__set( 'columns', $columns );
  }
  private function __row( $_args = null ){
    $table = parent::__get( 'name' );
    if(class_exists( '\\sql\\table\\' . $table ) ){ return \sql\table\$table( $_args ); }
    else { return \sql\row( $_args ); }
  }
  private function __rows( ){
    switch( parent::__get( 'resource' )->__get( 'type' ) ){
      case 'sqlsrv' : self::__rows_sqlsrv( ); break;
      case 'mysqli' : self::__rows_mysqli( ); break;
      default       : self::__rows_mysqli( ); break;
    }
  }
  private function __rows_sqlsrv( ){
    $rows = array();
    $table = parent::__get( 'name' );
    $query = "select * from {$table};";
    $result = $database->query(
      parent::__get( 'resource' )->__get( 'link' ),
      $query,
      array(
        parent::__get( 'name' )
      )
    );
    if( $result ){
      while( $row = sqlsrv_fetch_array( $result ){
        $rows[] = self::__row( $row );
      }
    }
  }
  private function __rows_mysqli( ){
    $rows = array();
    $table = parent::__get( 'name' );
    $query = "select * from {$table};";
    $statement = mysqli_prepare(
      parent::__get( 'resource' )->__get( 'link' )
      $query
    );
    mysqli_execute( $statement );
    $result = mysqli_stmt_get_result( $statement );
    if( $result ){
      while( $row = mysqli_fetch_row( $result ) ){
        $rows[] = self::__row( $row );
      }
    }
  }
}
?>
