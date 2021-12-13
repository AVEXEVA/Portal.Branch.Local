<?php
namespace singleton;
class datatables extends \singleton\index {
	public function ID($url, $moduleName){       
		?>{
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='<?php echo $url;?>?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> <?php echo $moduleName;?> #" + row.ID + "</a></div>" +
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
    public function UnitName( ){
		?>{
            data : 'Name',
            render : function ( data, type, row, meta ){
                switch ( type ) {
                    case 'display':
                        if( row.City_ID === null && row.Building_ID === null ){
                            return null;
                        } else {
                            return "<div class='row'>" +
                                ( row.City_ID !== null ? "<div class='col-12'><a href='unit.php?ID=" + row.ID + "'>" + row.City_ID + "</a></div>" : null ) +
                                ( row.Building_ID !== null ? "<div class='col-12'><a href='unit.php?ID=" + row.ID + "'>" + row.Building_ID + "</a></div>" : null ) +
                                "</div>";
                        }
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
    public function UnitStatus( ){
		?>{
            data : 'Status',
            render:function(data){
                switch(data){
                    case '0': return "<div class='row'><div class='col-12'>Active<div></div>";
                    case '1': return "<div class='row'><div class='col-12'>InActive<div></div>";
                    case '2': return "<div class='row'><div class='col-12'>Demolished<div></div>";
                }
            }
        }<?php
	}
    public function UnitType( ){
		?>{
          data : 'Type'
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

    public function CustomerID( ){
		?>{
            data : 'Customer_ID',
            render : function( data, type, row, meta ){

                switch( type ){
                    case 'display' :
                        return  row.Customer_ID !== null
                            ?   "<div class='row'>" +
                            "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i>" + row.Customer_Name + "</a></div>" +
                            "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}

    public function LocationID( ){
		?>{
            data : 'Location_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Location_ID !== null
                            ?   "<div class='row'>" +
                            "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Name + "</a></div>" +
                            "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}

    public function TicketID( ){
		?>{
            data : 'Ticket_ID',
            /*	render : function( data, type, row, meta ){
					switch ( type ){
						case 'display' :
							return row.Ticket_ID !== null
								?	"<div class='row'>" +
								"<div class='col-12'><a href='ticket.php?ID=" + row.Ticket_ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i>Ticket #" + row.Ticket_ID + "</a></div>" +
								"<div class='col-12'>" + row.Ticket_Date + "</div>" +
								"</div>"
								: 	null;
						default :
							return data;

					}
			}*/
        }<?php
	}

}?>
