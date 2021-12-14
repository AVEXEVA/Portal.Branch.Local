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

    public function DataElement($data,$className){
		?>{
            data : '<?php echo $data;?>'    
            <?php if(!empty($className) || $className != null) {
                echo ", className :".$className;
            } ?>        
        }<?php
	}


	public function Name($url){
		?>{
            data : 'Name',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='<?php echo $url;?>?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Name + "</a></div>" +
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
	public function Locations($redirect='Customer'){
		?>{
            data : 'Locations',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Locations !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='locations.php?<?php echo $redirect;?>=" + row.Name + "'><i class='fa fa-link fa-building fa-fw fa-1x'></i> " + row.Locations + " locations</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}
	public function Units($redirect='Customer'){
		?>{
	        data : 'Units',
	        render : function( data, type, row, meta ){
	            switch( type ){
	                case 'display' :
	                    return  row.Units !== null
	                        ?   "<div class='row'>" +
	                                "<div class='col-12'><a href='units.php?<?php echo $redirect;?>=" + row.Name + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " units</a></div>" +
	                            "</div>"
	                        :   null;
	                default :
	                    return data;
	            }
	        }
	    }<?php
	}

	public function Unit( ){
		?>{
            data : 'Units',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Units !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " </a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
      }<?php
	}

	public function Jobs($redirect='Customer' ){
		?>{
            data : 'Jobs',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Jobs !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='jobs.php?<?php echo $redirect;?>=" + row.Name + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> " + row.Jobs + " jobs</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}
	public function Tickets($redirect='Customer' ){
		?>{
            data : 'Tickets',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Tickets !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='tickets.php?<?php echo $redirect;?>=" + row.Name + "'><i class='fa fa-ticket fa-fw fa-1x'></i> " + row.Tickets + " tickets</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}
	public function Violations($redirect='Customer' ){
		?>{
            data : 'Violations',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Tickets !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='violations.php?<?php echo $redirect;?>=" + row.Name + "'><i class='fa fa-warning fa-fw fa-1x'></i> " + row.Violations + " violations</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}
	public function Invoices($redirect='Customer'){
		?>{
            data : 'Invoices',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Tickets !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='invoices.php?<?php echo $redirect;?>=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i> " + row.Invoices + " invoices</a></div>" +
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

    public function LocationID($extra){
		?>{
            data : 'Location_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Location_ID !== null
                            ?   "<div class='row'>" +
                            "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Name + "</a></div>" +
                            <?php if($extra ==1 ) { ?>
                                "<div class='col-12'>" +
                                    "<div class='row'>" +
                                        "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Location_Street + "</div>" +
                                        "<div class='col-12'>" + row.Location_City + ", " + row.Location_State + " " + row.Location_Zip + "</div>" +
                                    "</div>" +
                                "</div>" +
                                <?php } ?>                    
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


    public function UnitID( ){
		?>{
            data : 'Unit_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Unit_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-cogs fa-fw fa-1x'></i>" + ( row.Unit_City_ID !== null && !row.Unit_City_ID.replace(/\s/g, '' ).length < 1 ? row.Unit_City_ID : 'Missing City ID' ) + "</a></div>" +
                                    "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Unit_Building_ID + "</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
	}  
    public function TerritoryUnit( ){
		?>{
            data : 'Unit',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Unit !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='unit.php?Territory=" + row.Name + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> " + row.Unit + " Unit</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}  
    public function TerritoryProposal( ){
		?>{ data : 'Proposal',
            render : function( data, type, row, meta ){
               switch( type ){
                   case 'display' :
                       return  row.Proposal !== null
                           ?   "<div class='row'>" +
                                   "<div class='col-12'><a href='proposal.php?Territory=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i><span " + row.Proposal + " invoices</a></div>" +
                               "</div>"
                           :   null;
                   default :
                       return data;
            }
        }
      }<?php
	}  
    public function TerritoryCollections( ){
		?>{
            data : 'Collection',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Collection !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='collections.php?Territory=" + row.Name + "'><i class='fa fa-warning fa-fw fa-1x'></i> " + row.Collection + " Collection</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
          }<?php
	}

    public function TerritoryInvoices( ){
		?>{
            data : 'Invoices',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Invoice !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='invoices.php?Territory=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i> " + row.Invoice + " invoices</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                    }
            }

        }<?php
	}

    public function TerritoryID( ){
		?>{
            data : 'Territory_ID',
            render : function( data, type, row, meta ){
              switch( type ){
                case 'display' :
                    return  row.Territory_ID !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><a href='territory.php?ID=" + row.Territory_ID + "'><i class='fa fa-black-tie fa-fw fa-1x'></i>" + row.Territory_Name + "</a></div>" +
                            "</div>"
                        :   null;
                default :
                    return data;
              }
            }
          }<?php
	}

    public function JobID( ){
		?>{
            data : 'Job_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Job_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'><i class='fa fa-suitcase fa-fw fa-1x'></i>" + row.Job_ID + "</a></div>" +
                                    "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_Name + "</a></div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;
                }
            }
        }<?php
	}


    public function TicketLevel( ){
		?>{
            data : 'Level',
            render : function( data, type, row, meta ){
                switch ( type ){
                    case 'display':
                        return row.Job_Type !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'>" + row.Job_Type + "</div>" +
                                    "<div class='col-12'>" + row.Level + "</div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}

    public function TicketDate( ){
		?>{
            data : 'Date',
            render: function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Date !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><i class='fa fa-calendar fa-fw fa-1x'></i>" + row.Date + "</div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;

                }
            }
        }<?php
	}

    public function TicketTimeRoute( ){
		?>{
            data : 'Time_Route',
            render: function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Date !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><i class='fa fa-clock-o fa-fw fa-1x'></i>" + row.Time_Route + "</div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;

                }
            }
        }<?php
	}

    public function TicketTimeSite( ){
		?>{
            data : 'Time_Site',
            render: function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Date !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><i class='fa fa-clock-o fa-fw fa-1x'></i>" + row.Time_Site + "</div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;

                }
            }
        }<?php
	}

    public function TicketTimeCompleted( ){
		?>{
            data : 'Time_Completed',
            render: function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Date !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><i class='fa fa-clock-o fa-fw fa-1x'></i>" + row.Time_Completed + "</div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;

                }
            }
        }<?php
	}

    public function TicketHours( ){
		?>{
            data : 'Hours',
            defaultContent :"0"
        }<?php
	}

    public function TicketLSD( ){
		?>{
            data : 'LSD',
            render : function( data, type, row, meta ){
                switch ( type ){
                    case 'display':
                        return row.LSD == 1
                            ? 'LSD'
                            : 'Running';
                    default :
                        return data;
                }
            }
        }<?php
	}    
    public function TicketPerson( ){
		?>{
            data : 'Person',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Employee_ID !== null
                            ?   "<a href='user.php?ID=" + row.Employee_ID + "'><i class='fa fa-user fa-fw fa-1x'></i>" + row.Person + "</a>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}    


    public function ContactName( ){
		?>{
            data : 'Name',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                      return row.Name !== null
                          ?   (
                                  row.Type == 'Customer'
                                      ?   "<div class='row'>" +
                                              "<div class='col-12'><a href='customer.php?Name=" + row.Name + "'><i class='fa fa-user fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                          "</div>"
                                      :   (
                                              row.Type == 'Location'
                                                  ?   "<div class='row'>" +
                                                          "<div class='col-12'><a href='location.php?Name=" + row.Name + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                                      "</div>"
                                                  :   (
                                                          row.Type == 'Employee'
                                                              ?   "<div class='row'>" +
                                                                      "<div class='col-12'><a href='employee.php?Name=" + row.Name + "'><i class='fa fa-users fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                                                  "</div>"
                                                              : null
                                                      )
                                          )
                              )
                          :   null;
                    default :
                        return data;
                }

            }
          }<?php
	}
    public function Email( ){
		?>{
            data : 'Email',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return row.Email !== null && row.Email != ''
                            ?   "<a href='mailto:" + row.Email + "'><i class='fa fa-envelope fa-fw fa-1x'></i>" + row.Email + "</a>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}
    public function Phone( ){
		?>{
            data : 'Phone',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return row.Email !== null && row.Phone != ''
                            ?   "<a href='tel:" + row.Phone + "'><i class='fa fa-phone fa-fw fa-1x'></i>" + row.Phone + "</a>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}

    public function Street( ){
		?>{
            data : 'Street',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  "<div class='row'>" +
                                    "<div class='col-12'>" +
                                        "<div class='row'>" +
                                            "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Street + "</div>" +
                                            "<div class='col-12'>" + row.City + ", " + row.State + " " + row.Zip + "</div>" +
                                        "</div>" +
                                    "</div>" +
                                "</div>"
                    default :
                        return data;
                }
            }
        }<?php
	}

    public function Contact( ){
		?>{
            data : 'Contact',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='contact.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Contact + "</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
	}

    public function CollectionOriginal( ){
		?>{
            data      : 'Original',
            render : function( data, type, row, meta ){
              switch( type ){
                case 'display':
                  if( row.Original > 0 ){
                    return "<div class='row'>" +
                      "<div class='col-12'>" + row.Original + "</div>" +
                    "</div>"
                  } else if( row.Original < 0 ){
                    return "<div class='row'>" +
                      "<div class='col-12'>" + row.Original + "</div>" +
                    "</div>"
                  } else if( row.Original == 0 ){
                    return "<div class='row'>" +
                      "<div class='col-12'>" + row.Original + "</div>" +
                    "</div>"
                  }
                default :
                  return null;
              }
            }
          }<?php
	}

}?>
