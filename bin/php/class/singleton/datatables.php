<?php
namespace singleton;
class datatables extends \singleton\index {
	public function ID($module){
        $url = "customer.php";
        $moduleName = "Customer";
        switch (strtolower($module)) {
            case 'customer':
                $this->url = "customer.php";
                $this->moduleName = "Customer";
                break;
            case 'employee':
                $this->url = "employee.php";
                $this->moduleName = "Employee";
                break;
            
            default:
                # code...
                break;
        }
		?>{
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='<?php echo $this->url;?>?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> <?php echo $this->moduleName;?> #" + row.ID + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }
          }
      }<?php
	}

    public function FirstName( ){
		?>{
            data : 'First_Name'            
        }<?php
	}

    public function LastName( ){
		?>{
            data : 'Last_Name'            
        }<?php
	}

    public function Supervisor( ){
		?>{
            data : 'Supervisor'            
        }<?php
	}

	public function Name( ){
		?>{
            data : 'Name',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='customer.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Name + "</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}
	public function Status( ){
		?>{
          data : 'Status'
        }<?php
	}
	public function Locations( ){
		?>{
            data : 'Locations',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Locations !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='locations.php?Customer=" + row.Name + "'><i class='fa fa-link fa-building fa-fw fa-1x'></i> " + row.Locations + " locations</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}
	public function Units( ){
		?>{
	        data : 'Units',
	        render : function( data, type, row, meta ){
	            switch( type ){
	                case 'display' :
	                    return  row.Units !== null
	                        ?   "<div class='row'>" +
	                                "<div class='col-12'><a href='units.php?Customer=" + row.Name + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " units</a></div>" +
	                            "</div>"
	                        :   null;
	                default :
	                    return data;
	            }
	        }
	    }<?php
	}
	public function Jobs( ){
		?>{
            data : 'Jobs',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Jobs !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='jobs.php?Customer=" + row.Name + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> " + row.Jobs + " jobs</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}
	public function Tickets( ){
		?>{
            data : 'Tickets',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Tickets !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='tickets.php?Customer=" + row.Name + "'><i class='fa fa-ticket fa-fw fa-1x'></i> " + row.Tickets + " tickets</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}
	public function Violations( ){
		?>{
            data : 'Violations',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Tickets !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='violations.php?Customer=" + row.Name + "'><i class='fa fa-warning fa-fw fa-1x'></i> " + row.Violations + " violations</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}
	public function Invoices( ){
		?>{
            data : 'Invoices',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Tickets !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='invoices.php?Customer=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i> " + row.Invoices + " invoices</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}

    public function GPSLocation( ){
		?>
            {
            className : 'GPSLocation',
            searchable: false,
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  (row.Latittude !== null && row.Longitude !== null)
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='https://www.google.com/maps/search/?api=1&query=" + row.Latitude + "," + row.Longitude + "' target='_blank' ><i class='fa fa-map-marker'></i> GPS </a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }
    <?php
	}
}?>
