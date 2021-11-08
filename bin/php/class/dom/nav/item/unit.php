<?php
namespace dom\nav\item;
class unit {
	public function __construct( ){?>
		<li class="nav-item dropdown active">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php new \icon\unit( );?> Unit <span class="sr-only">(current)</span></a>
          <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <div class='card'><form id='filter_table_unit' style='width:100%;'>
              <div class='card-header filters'>
                <div class='row'>
                  <div class='col-sm-8'>Filters</div>
                  <div class='col-sm-4'><button onClick="$('.card-body.unit.filters').toggle();" type='button'>-/+</button></div>
                </div>
              </div>
              <?php new \dom\card\body\search\unit( );?>
              <div class='card-body'>
                <button onClick='filter_table_unit_search( );' style='width:100%;' type='button'>Search</button>
                <hr />  
                <div class='display-table'>Display Table</div>
                <?php new \dom\table\units( );?>
                <script>
                var isChromium = window.chrome,
                  winNav = window.navigator,
                  vendorName = winNav.vendor,
                  isOpera = winNav.userAgent.indexOf("OPR") > -1,
                  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
                  isIOSChrome = winNav.userAgent.match("CriOS");
                var table_unit_search = $('#table_unit_search').DataTable( {
                  dom      : 'tlp',
                      processing : true,
                      serverSide : true,
                      responsive : true,
                      autoWidth  : false,
                      paging     : true,
                      searching  : false,
                      ajax      : {
                              url : 'bin/php/get/Units2.php',
                              data : function( d ){
                                  d = {
                                      start : d.start,
                                      length : d.length,
                                      order : {
                                          column : d.order[0].column,
                                          dir : d.order[0].dir
                                      }
                                  };
                                  d.ID = $('#filter_table_unit input[name="ID"]').val( );
                                  d.City_ID = $('#filter_table_unit input[name="City_ID"]').val( );
                                  d.Building_ID = $('#filter_table_unit input[name="Building_ID"]').val( );
                                  d.Type = $('#filter_table_unit select[name="Type"]').val( );
                                  d.Status = $('#filter_table_unit select[name="Status"]').val( );
                                  return d; 
                              }
                          },
                      columns   : [
                        {
                          data : 'Selected',
                          render: function( data, type, row, meta ){
                            if( data == true ){
                                return "<input type='checkbox' name='unit_search_ids[]' value='" + row.ID + "' " + " onChange='filterUnitID( this );' checked />";
                            } else {
                                return "<input type='checkbox' name='unit_search_ids[]' value='" + row.ID + "' " + " onChange='filterUnitID( this );' />";
                            }
                            
                          },
                          width: '25px'
                        },{
                          data      : 'ID',
                          className : 'hidden'
                        },{
                          data : 'City_ID'
                        },{
                          data : 'Location'
                        },{
                          data : 'Building_ID'
                        },{
                          data : 'Type'
                        },{
                          data : 'Status',
                          render:function(data){
                            switch(data){
                              case 0:return 'Active';
                              case 1:return 'Inactive';
                              case 2:return 'Demolished';
                              case 3:return 'XXX';
                              case 4:return 'YYY';
                              case 5:return 'ZZZ';
                              case 6:return 'AAA';
                              default:return 'Error';
                            }
                          }
                        }
                      ]
                    } );
                    function filter_table_unit_search( ){ table_unit_search.draw( ); }
                    function filterUnitID( link ){
                      $.ajax({
                        url : 'bin/php/post/filterUnitID.php',
                        data : { ID : $( link ).val( ) },
                        method : 'POST',
                        success: function ( ){ console.log( 'filterunitID : success' ); }
                      })
                    }
                    </script>
                  </div>
                </form></div>
              </div>
            </li><?php
	}
}?>