<?php
namespace sql;
class column extends \sql\index {
  //variables
  protected $resource = null;
  ///arguments
  protected $id       = null;
  protected $name     = null;
  protected $datatype = null;
  protected $position = null;
  //functions
  ///magic
  public function __construct( $_args = null ){ parent::__construct( $_args ); }
  ///sql
  private function __connect( ){ }
}?>
