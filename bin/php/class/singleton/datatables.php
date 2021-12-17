<?php
namespace singleton;
class datatables extends \singleton\index {
    //Helpers
    public function preferences( ){
        ?>dom            : "<'row'<'col-sm-9'B><'col-sm-3 search'>><'row'<'col-sm-12't>>",
        processing     : true,
        serverSide     : true,
        autoWidth      : false,
        searching      : false,
        lengthChange   : false,
        scrollResize   : true,
        scrollY        : 100,
        scroller       : true,
        scrollCollapse : true,
        paging         : true,
        orderCellsTop  : true,
        autoWidth      : true,
        responsive     : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        }<?php
    }
    public function initComplete( $reference ){
        ?>initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' class='form-control redraw' />" );
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_<?php echo ucfirst( $reference );?>.draw(); });
        }<?php
    }
    public function ajax_data( ){
        ?>data    : function( d ){
            d = {
                draw : d.draw,
                start : d.start,
                length : d.length,
                order : {
                    column : d.order[0].column,
                    dir : d.order[0].dir
                }
            };
            $( "input:visible, input[type='hidden'], select:visible, textarea:visible" ).each( function( ){
                if( d[ $( this ).attr( 'Name' ) ] === undefined ){
                    d[ $( this ).attr( 'Name' ) ] = $( this ).val( );
                }
            } );
            return d;
        }<?php
    }
    public function buttons( $singular, $plural, $key ){
        ?>buttons: [
            <?php \singleton\datatables::getInstance( )->button_url( $plural );?>,
            <?php \singleton\datatables::getInstance( )->button_print( );?>,
            <?php \singleton\datatables::getInstance( )->button_reset( $plural );?>,
            <?php \singleton\datatables::getInstance( )->button_create( $singular );?>,
            <?php \singleton\datatables::getInstance( )->button_edit( $singular, $key );?>,
            <?php \singleton\datatables::getInstance( )->button_delete( $singular, $plural );?>,
            <?php \singleton\datatables::getInstance( )->button_export( );?>
        ]<?php
    }
    public function button_print( ){
        ?>{
            extend : 'print',
            text : "<?php \singleton\fontawesome::getInstance( )->Print( 1 );?><span class='desktop'>Print</span>",
            className : 'form-control'
        }<?php
    }
    public function button_url( $reference ){
        ?>{
            text : "<?php \singleton\fontawesome::getInstance( )->Refresh( 1 );?><span class='desktop'>Refresh</span>",
            className : 'form-control',
            action : function( e, dt, node, config ){
                d = { }
                $( 'input, select, textarea' ).filter( ':visible' ).each( function( ){
                    if( d[ $( this ).attr( 'Name' ) ] === undefined ){
                        d[ $( this ).attr( 'Name' ) ] = $( this ).val( );
                    }
                } );
                document.location.href = '<?php echo $reference;?>.php?' + new URLSearchParams( d ).toString();
            }
        }<?php
    }
    public function button_create( $reference ){
        ?>{
            text : "<?php \singleton\fontawesome::getInstance( )->Create( );?><span class='desktop'>Create</span>",
            className: 'form-control',
            action : function( e, dt, node, config ){
                document.location.href='<?php echo $reference;?>.php';
            }
        }<?php
    }
    public function button_reset( $reference ){
        ?>{
            text : "<?php \singleton\fontawesome::getInstance( )->Eraser( );?><span class='desktop'>Reset</span>",
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                $( 'input:visible, select:visible' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_<?php echo ucfirst( $reference );?>.draw( );
            }
        }<?php
    }
    public function button_delete( $singular, $plural ){
        ?>{
            text : "<?php \singleton\fontawesome::getInstance( )->Delete( );?><span class='desktop'>Delete</span>",
            className: 'form-control',
            action : function( e, dt, node, config ){
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                $.ajax ({
                    url    : 'bin/php/post/<?php echo $singular;?>.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_<?php echo ucfirst( $plural );?>.draw();
                    }
                })
            }
        }<?php
    }
    public function button_edit( $reference, $key ){
        ?>{
            text : "<?php \singleton\fontawesome::getInstance( )->Edit( );?><span class='desktop'>Edit</span>",
            className: 'form-control',
            action : function( e, dt, node, config ){ document.location.href='<?php echo $reference;?>.php?ID=' + row.<?php echo $key;?>; }
        }<?php
    }
    public function button_export( ){ ?>{ extend: 'csv', className: 'form-control', text : "<?php \singleton\fontawesome::getInstance( )->Export( 1 );?><span class='desktop'>Export</span>" }<?php }
    //Helper for Columns
    public function data_column( $key ){
        ?>{ 
            data : '<?php echo $key;?>',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.<?php echo $key;?> != null && row.<?php echo $key;?> != ''
                            ?   row.<?php echo $key;?>
                            :   '';
                    default:
                        return '';
                }
            } 
        }<?php 
    }
    public function data_column_currency( $key ){
    	?>{
    		data : '<?php echo $key;?>',
    		render : function( data, type, row, meta ){
    			switch( type ){
    				case 'display':
    					return 	row.<?php echo $key;?> !== null && row.<?php echo $key;?> != 0 && row.<?php echo $key;?> != '.00'
    						?	dollarUSLocale.format(row.<?php echo $key;?>)
    						:	'';
    				deafult : 
    					return '';
    			}
    		}
    	}<?php
    }

    //Columns
    public function data_column_id( $reference, $key ){
        ?>{
          className : '<?php echo $key;?>',
          data : '<?php echo $key;?>',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.<?php echo $key;?> !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='<?php echo strtolower( $reference );?>.php?<?php echo $key;?>=" + row.<?php echo $key;?> + "'><?php \singleton\fontawesome::getInstance( )->$reference( 1 );?> <?php echo ucfirst( $reference );?> #" + row.<?php echo $key;?> + "</a></div>" +
                              "</div>"
                          :   '';
                  default :
                      return '';
              }
          }
      }<?php
    }
    public function data_column_link( $reference, $key ){
        ?>{
          className : '<?php echo $key;?>',
          data : '<?php echo $key;?>',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.<?php echo $key;?> !== null && row.<?php echo $key;?> != ''
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='<?php echo strtolower( $reference );?>.php?<?php echo $key;?>=" + row.<?php echo $key;?> + "'><?php \singleton\fontawesome::getInstance( )->$reference( 1 );?> " + row.<?php echo $key;?> + "</a></div>" +
                              "</div>"
                          :   '';
                  default :
                      return '';
              }
          }
      }<?php
    }
    public function data_column_tel( $key ){
        ?>{
            data : '<?php echo $key;?>',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return row.<?php echo $key;?> !== null && row.<?php echo $key;?> != ''
                            ?   "<a href='tel:" + row.<?php echo $key;?> + "'><?php \singleton\fontawesome::getInstance( )->Phone( );?>" + row.<?php echo $key;?> + "</a>"
                            :   '';
                    default :
                        return '';
                }
            }
        }<?php
    }
    public function data_column_email( $key ){
        ?>{
            data : '<?php echo $key;?>',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return row.<?php echo $key;?> !== null && row.<?php echo $key;?> != ''
                            ?   "<a href='mailto:" + row.<?php echo $key;?> + "'><?php \singleton\fontawesome::getInstance( )->Email( );?>" + row.<?php echo $key;?> + "</a>"
                            :   '';
                    default :
                        return '';
                }
            }
        }<?php
    }
    public function data_column_address( ){
        ?>{
            data : 'Street',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Street !== null && row.Street != ''
                                ?   "<div class='row'>" +
                                        "<div class='col-12'>" +
                                            "<div class='row'>" +
                                                "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Street + "</div>" +
                                                "<div class='col-12'>" + row.City + ", " + row.State + " " + row.Zip + "</div>" +
                                            "</div>" +
                                        "</div>" +
                                    "</div>"
                                :   '';
                    default :
                        return '';
                }
            }
        }<?php
    }
    public function data_column_image( $key = 'Picture' ){
        ?>{
            data:'Picture',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return   row.<?php echo $key;?>_Type !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><img src=data:"+row.<?php echo $key;?>_Type +";base64,"+row.<?php echo $key;?> +" width='100px;' height='100px;'></div>" +
                                "</div>"
                            :   null;
                    default :
                        return '';
                }
            }
        }<?php
    }

    //Columns
    public function ID( $reference ){ self::data_column_id( $reference, 'ID' ); }
    public function Name( $reference ){ self::data_column_link( $reference, 'Name' ); }
    public function Date( $key = 'Date' ){
        ?>{
            data : '<?php echo $key;?>',
            render: function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.<?php echo $key;?> !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><?php \singleton\fontawesome::getInstance( )->Date( 1 );?>" + row.<?php echo $key;?> + "</div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;

                }
            }
        }<?php
    }
    //Columns that are Foreign Keys
    public function Customer( ){
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
    public function Location( ){
        ?>{
            data : 'Location_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Location_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Name + "</a></div>" +
                                    "<div class='col-12'>" +
                                        "<div class='row'>" +
                                            "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Location_Street + "</div>" +
                                            "<div class='col-12'>" + row.Location_City + ", " + row.Location_State + " " + row.Location_Zip + "</div>" +
                                        "</div>" +
                                    "</div>" +
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
    public function Ticket( ){
        ?>{
            data : 'Ticket_ID',
            /*  render : function( data, type, row, meta ){
                    switch ( type ){
                        case 'display' :
                            return row.Ticket_ID !== null
                                ?   "<div class='row'>" +
                                "<div class='col-12'><a href='ticket.php?ID=" + row.Ticket_ID + "'><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?></i>Ticket #" + row.Ticket_ID + "</a></div>" +
                                "<div class='col-12'>" + row.Ticket_Date + "</div>" +
                                "</div>"
                                :   null;
                        default :
                            return data;

                    }
            }*/
        }<?php
    }
    public function Job( ){
        ?>{
            data : 'Job_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Job_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'><?php \singleton\fontawesome::getInstance( )->Job( 1 );?>Job #" + row.Job_ID + "</a></div>" +
                                    ( row.Job_Name !== null ? "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_Name + "</a></div>" : '' ) +
                                "</div>"
                            :   '';
                        default :
                            return data;
                }
            }
        }<?php
    }
    public function Employee( ){
        ?>{
            data : 'Employee_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.Employee_ID !== null 
                            ?   "<a href='user.php?ID=" + row.Employee_ID + "'><?php \singleton\fontawesome::getInstance( )->Employee( 1 );?>" + row.Employee_Name + "</a>"
                            :   null;
                    default :
                        return data;
                }
            }
        }<?php
    }
    public function Territory( ){
        ?>{
            data : 'Territory_ID',
            render : function( data, type, row, meta ){

                switch( type ){
                    case 'display' :
                        return  row.Territory_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='territory.php?ID=" + row.Territory_ID + "'><?php \singleton\fontawesome::getInstance( )->Territory( 1 );?>" + row.Territory_Name + "</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
    }
    public function Division( ){
        ?>{
            data : 'Division_ID',
            render : function( data, type, row, meta ){

                switch( type ){
                    case 'display' :
                        return  row.Division_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='division.php?ID=" + row.Division_ID + "'><?php \singleton\fontawesome::getInstance( )->Division( 1 );?>" + row.Division_Name + "</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
    }
    public function Route( ){
        ?>{
            data : 'Route_ID',
            render : function( data, type, row, meta ){

                switch( type ){
                    case 'display' :
                        return  row.Route_ID !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='route.php?ID=" + row.Route_ID + "'><?php \singleton\fontawesome::getInstance( )->Route( 1 );?>" + row.Route_Name + "</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }

            }
        }<?php
    }
    public function Contact( ){
        ?>{
            data : 'Contact_ID',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Contact_ID !== null && row.Contact_ID != ''
                            ?   "<div class='row'>" +
                            "<div class='col-12'><a href='contact.php?ID=" + row.Contact_ID + "'><?php \singleton\fontawesome::getInstance( )->Contact( 1 );?>" + row.Contact_Name + "</a></div>" +
                            "</div>"
                            :   '';
                    default :
                        return data;
                }

            }
        }<?php
    }
    public function Time( $key ){
        ?>{
            data : '<?php echo $key;?>',
            render: function( data, type, row, meta ){
                switch( type ){
                    case 'display':
                        return row.<?php echo $key;?> !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><?php \singleton\fontawesome::getInstance( )->Time( 1 );?>" + row.<?php echo $key;?> + "</div>" +
                                "</div>"
                            :   null;
                        default :
                            return data;

                }
            }
        }<?php
    }

    //Columns using Helpers
    public function First_Name( ){ self::data_column( 'First_Name' ); }
    public function Last_Name( ){ self::data_column( 'Last_Name' ); }
    public function Supervisor( ){ self::data_column( 'Supervisor' ); }
    public function Status( ){ self::data_column( 'Status' ); }
    public function Type( ){ self::data_column( 'Type' ); }
    public function TimeRoute( ){ self::Time( 'Time_Route' ); }
    public function TimeSite( ){ self::Time( 'Time_Site' ); }
    public function TimeCompleted( ){ self::Time( 'Time_Completed' ); }


    public function data_column_count( $key, $Reference, $Reference_Key, $icon = 'blank' ){
        ?>{
            data : '<?php echo ucfirst( $key );?>',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.<?php echo ucfirst( $key );?> !== null && row.<?php echo ucfirst( $key );?> != 0
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='<?php echo $key;?>.php?<?php echo $Reference;?>=" + row.<?php echo $Reference_Key;?> + "'><?php \singleton\fontawesome::getInstance( )->$icon( 1 );?> " + row.<?php echo ucfirst( $key );?> + " <?php echo $key;?></a></div>" +
                                "</div>"
                            :   '';
                    default :
                        return data;
                }
            }
      }<?php
    }
    //Columns that are counts
    public function Units( $Reference, $Reference_Key ){ self::data_column_count( 'units', $Reference, $Reference_Key, 'Unit' ); }
    public function Locations( $Reference, $Reference_Key ){ self::data_column_count( 'locations', $Reference, $Reference_Key, 'Location' ); }
    public function Jobs( $Reference, $Reference_Key ){ self::data_column_count( 'jobs', $Reference, $Reference_Key, 'Job' ); }
    public function Tickets( $Reference, $Reference_Key ){ self::data_column_count( 'tickets', $Reference, $Reference_Key, 'Ticket' ); }
    public function Violations( $Reference, $Reference_Key){ self::data_column_count( 'violations', $Reference, $Reference_Key, 'Violation' ); }
    public function Invoices( $Reference, $Reference_Key ){ self::data_column_count( 'invoices', $Reference, $Reference_Key, 'Invoice' ); }
    public function Proposals( $Reference, $Reference_Key ){ self::data_column_count( 'proposals', $Reference, $Reference_Key, 'Proposal' ); }
    public function Collections( $Reference, $Reference_Key ){ self::data_column_count( 'collections', $Reference, $Reference_Key, 'Collection' ); }
    public function Leads( $Reference, $Reference_Key ){ self::data_column_count( 'leads', $Reference, $Reference_Key, 'Lead' ); }


    //Columns need Rewrite
    public function FirstName( ){ self::First_Name( );	}
    public function LastName( ){ self::Last_Name( ); }
    public function UnitType( ){ self::Type( ); }
    public function UnitStatus( ){ self::Status( ); }
    public function LocationID( ){ self::Location( ); }
    public function TicketID( ){ self::Ticket( ); }
    public function TicketDate( ){ self::Date( ); }
    public function CustomerID( ){ self::Customer( ); }
    public function UnitID( ){ self::Unit( ); }
    public function JobID( ){ self::Job( ); }
    public function TicketTimeRoute( ){ self::TimeRoute( ); }
    public function TicketTimeSite( ){ self::TimeSite( ); }
    public function TicketTimeCompleted( ){ self::TimeCompleted( ); }
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
    public function TerritoryLeads( ){
        ?>{ data : 'Proposal',
            render : function( data, type, row, meta ){
               switch( type ){
                   case 'display' :
                       return  row.Proposal !== null
                           ?   "<div class='row'>" +
                                   "<div class='col-12'><a href='leads.php?Territory=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i><span " + row.Leads + " leads</a></div>" +
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
    public function GPSLocation( ){
		?>{
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
}?>
