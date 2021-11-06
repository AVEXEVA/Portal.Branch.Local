<?php
namespace dom\nav\item;
class customer {
	public function __construct( ){?>
		<li class="nav-item dropdown active">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php new \icon\customer( );?> Customer</a>
          <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <div class='card'><form id='filter_table_customer' style='width:100%;'>
              <div class='card-header filters'>
                <div class='row'>
                  <div class='col-sm-8'>Filters</div>
                  <div class='col-sm-4' style='text-align:right;'><button onClick="$('.card-body.customer.filters').toggle();">-/+</button></div>
                </div>
              </div>
              <div class='card-body customer filters'>
                <div class='row'>
                  <div class='col-sm-4'>ID</div>
                  <div class='col-sm-8'><input type='text' name='ID' placeholder='ID' value='<?php echo $_GET['customers'];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>Name</div>
                  <div class='col-sm-8'><input type='text' name='Name' placeholder='Name' /></div>
                </div>
              </div>
              <div class='card-body'>
                <button onClick='filter_table_customer_search( );' style='width:100%;' type='button'>Search</button>
                <hr />  
                <div class='display-table'>Display Table</div>
                <table id='table_customer_search' class='display' cellspacing='0' width='100%'>
                  <thead><tr>
                    <th title='Select'>Select</th>
                    <th title='ID'>ID</th>
                    <th title='Name'>Name</th>
                  </tr></thead>
                </table>
                <script>
                var isChromium = window.chrome,
                  winNav = window.navigator,
                  vendorName = winNav.vendor,
                  isOpera = winNav.userAgent.indexOf("OPR") > -1,
                  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
                  isIOSChrome = winNav.userAgent.match("CriOS");
                var table_customer_search = $('#table_customer_search').DataTable( {
                  dom      : 'tlp',
                      processing : true,
                      serverSide : true,
                      autoWidth  : false,
                      paging     : true,
                      searching  : false,
                      ajax      : {
                              url : 'bin/php/get/Customers2.php',
                              data : function( d ){
                                  d = {
                                      start : d.start,
                                      length : d.length,
                                      order : {
                                          column : d.order[0].column,
                                          dir : d.order[0].dir
                                      }
                                  };
                                  d.Search = $('#filter_table_customer input[name="Search"]').val( );
                                  d.ID = $('#filter_table_customer input[name="ID"]').val( );
                                  d.Name = $('#filter_table_customer input[name="Name"]').val( );
                                  return d; 
                              }
                          },
                      columns   : [
                        {
                          data : 'Selected',
                          render: function( data, type, row, meta ){
                            if( data == true ){
                                return "<input type='checkbox' name='customer_search_ids[]' value='" + row.ID + "' " + " onChange='filterCustomerID( this );' checked />";
                            } else {
                                return "<input type='checkbox' name='customer_search_ids[]' value='" + row.ID + "' " + " onChange='filterCustomerID( this );' />";
                            }
                            
                          },
                          width: '25px'
                        },{
                          data    : 'ID',
                          className : 'hidden'
                        },{
                          data : 'Name'
                        }
                      ]
                    } );
                    function filter_table_customer_search( ){ table_customer_search.draw( ); }
                    function filterCustomerID( link ){
                      $.ajax({
                        url : 'bin/php/post/filterCustomerID.php',
                        data : { ID : $( link ).val( ) },
                        method : 'POST',
                        success: function ( ){ console.log( 'filterCustomerID : success' ); }
                      })
                    }
                    </script>
                  </div>
                </form></div>
              </div>
            </li><?php
	}
}?>