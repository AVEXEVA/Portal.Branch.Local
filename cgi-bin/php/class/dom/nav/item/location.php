<?php
namespace dom\nav\item;
class location {
	public function __construct( ){?>
		<li class="nav-item dropdown active">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php new \icon\location( );?> Location <span class="sr-only">(current)</span></a>
          <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <div class='card'><form id='filter_table_location' style='width:100%;'>
              <div class='card-header filters'>
                <div class='row'>
                  <div class='col-sm-8'>Filters</div>
                  <div class='col-sm-4'><button onClick="$('.card-body.location.filters').toggle();" type='button'>-/+</button></div>
                </div>
              </div>
              <div class='card-body location filters'>
                <div class='row'>
                  <div class='col-sm-4'>Name</div>
                  <div class='col-sm-8'><input type='text' name='Name' placeholder='Name' /></div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>Street</div>
                  <div class='col-sm-8'><input type='text' name='Street' placeholder='Street' /></div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>City</div>
                  <div class='col-sm-8'><input type='text' name='City' placeholder='City' /></div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>State</div>
                  <div class='col-sm-8'><?php 
                  	new \dom\select\state( );
                  ?></div>
                </div>
              </div>
              <div class='card-body'>
                <button onClick='filter_table_location_search( );' style='width:100%;' type='button'>Search</button>
                <hr />  
                <div class='display-table'>Display Table</div>
                <table id='table_location_search' class='display' cellspacing='0' style='min-width:500px;width:100%;max-width:750px;'>
                  <thead><tr>
                    <th title='Select'>Select</th>
                    <th title='ID'>ID</th>
                    <th title='Name'>Name</th>
                    <th title='Street'>Street</th>
                    <th title='City'>City</th>
                    <th title='State'>State</th>
                  </tr></thead>
                </table>
                <script>
                var isChromium = window.chrome,
                  winNav = window.navigator,
                  vendorName = winNav.vendor,
                  isOpera = winNav.userAgent.indexOf("OPR") > -1,
                  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
                  isIOSChrome = winNav.userAgent.match("CriOS");
                var table_location_search = $('#table_location_search').DataTable( {
                  dom      : 'tlp',
                      processing : true,
                      serverSide : true,
                      responsive : true,
                      autoWidth  : false,
                      paging     : true,
                      searching  : false,
                      ajax      : {
                              url : 'bin/php/get/Locations2.php',
                              data : function( d ){
                                  d = {
                                      start : d.start,
                                      length : d.length,
                                      order : {
                                          column : d.order[0].column,
                                          dir : d.order[0].dir
                                      }
                                  };
                                  d.Search = $('#filter_table_location input[name="Search"]').val( );
                                  d.ID = $('#filter_table_location input[name="ID"]').val( );
                                  d.Name = $('#filter_table_location input[name="Name"]').val( );
                                  d.City = $('#filter_table_location input[name="City"]').val( );
                                  d.Street = $('#filter_table_location input[name="Street"]').val( );
                                  d.State = $('#filter_table_location select[name="Street"]').val( );
                                  return d; 
                              }
                          },
                      columns   : [
                        {
                          data : 'Selected',
                          render: function( data, type, row, meta ){
                            if( data == true ){
                                return "<input type='checkbox' name='location_search_ids[]' value='" + row.ID + "' " + " onChange='filterLocationID( this );' checked />";
                            } else {
                                return "<input type='checkbox' name='location_search_ids[]' value='" + row.ID + "' " + " onChange='filterLocationID( this );' />";
                            }
                            
                          },
                          width: '25px'
                        },{
                          data    : 'ID',
                          className : 'hidden'
                        },{
                          data : 'Name'
                        },{
                          data : 'Street'
                        },{
                          data : 'City'
                        },{
                          data : 'State'
                        }
                      ]
                    } );
                    function filter_table_location_search( ){ table_location_search.draw( ); }
                    function filterLocationID( link ){
                      $.ajax({
                        url : 'bin/php/post/filterLocationID.php',
                        data : { ID : $( link ).val( ) },
                        method : 'POST',
                        success: function ( ){ console.log( 'filterLocationID : success' ); }
                      })
                    }
                    </script>
                  </div>
                </form></div>
              </div>
            </li><?php
	}
}?>