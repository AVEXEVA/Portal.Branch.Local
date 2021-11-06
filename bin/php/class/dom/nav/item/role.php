<?php
namespace dom\nav\item;
class role {
	public function __construct( ){?>
		<li class="nav-item dropdown active">
      <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php new \icon\role( );?> Role</a>
      <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
        <div class='card'><form id='filter_table_role' style='width:100%;'>
          <div class='card-header filters'>
            <div class='row'>
              <div class='col-sm-8'>Filters</div>
              <div class='col-sm-4' style='text-align:right;'><button onClick="$('.card-body.role.filters').toggle();">-/+</button></div>
            </div>
          </div>
          <div class='card-body role filters'>
            <div class='row'>
              <div class='col-sm-4'>ID</div>
              <div class='col-sm-8'><input type='text' name='ID' placeholder='ID' value='<?php echo $_GET['roles'];?>' /></div>
            </div>
            <div class='row'>
              <div class='col-sm-4'>Name</div>
              <div class='col-sm-8'><input type='text' name='Name' placeholder='Name' /></div>
            </div>
          </div>
          <div class='card-body'>
            <button onClick='filter_table_role_search( );' style='width:100%;'>Search</button>
            <hr />  
            <div class='display-table'>Display Table</div>
            <table id='table_role_search' class='display' cellspacing='0' style='min-width:500px;width:100%;max-width:750px;'>
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
            var table_role_search = $('#table_role_search').DataTable( {
              dom      : 'tlp',
                  processing : true,
                  serverSide : true,
                  autoWidth  : false,
                  paging     : true,
                  searching  : false,
                  ajax      : {
                          url : 'bin/php/dataset/roles.php',
                          data : function( d ){
                              d = {
                                  start : d.start,
                                  length : d.length,
                                  order : {
                                      column : d.order[0].column,
                                      dir : d.order[0].dir
                                  }
                              };
                              d.Search = $('#filter_table_role input[name="Search"]').val( );
                              d.ID = $('#filter_table_role input[name="ID"]').val( );
                              d.Name = $('#filter_table_role input[name="Name"]').val( );
                              return d; 
                          }
                      },
                  columns   : [
                    {
                      data : 'Selected',
                      render: function( data, type, row, meta ){
                        if( data == true ){
                            return "<input type='checkbox' name='role_search_ids[]' value='" + row.ID + "' " + " onChange='filterRoleID( this );' checked />";
                        } else {
                            return "<input type='checkbox' name='role_search_ids[]' value='" + row.ID + "' " + " onChange='filterRoleID( this );' />";
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
                function filter_table_role_search( ){ table_role_search.draw( ); }
                function filterroleID( link ){
                  $.ajax({
                    url : 'bin/php/post/filterRoleID.php',
                    data : { ID : $( link ).val( ) },
                    method : 'POST',
                    success: function ( ){ console.log( 'filterRoleID : success' ); }
                  })
                }
                </script>
              </div>
            </form></div>
          </div>
        </li><?php
	}
}?>