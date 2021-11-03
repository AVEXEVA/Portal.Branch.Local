<?php
namespace dom\nav\item;
class job {
	public function __construct( ){?>
		<li class="nav-item dropdown active">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php new \icon\job( );?> Job <span class="sr-only">(current)</span></a>
          <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <div class='card'><form id='filter_table_job' style='width:100%;'>
              <div class='card-header filters'>
                <div class='row'>
                  <div class='col-sm-8'>Filters</div>
                  <div class='col-sm-4'><button onClick="$('.card-body.job.filters').toggle();" type='button'>-/+</button></div>
                </div>
              </div>
              <div class='card-body job filters'>
                <div class='row'>
                  <div class='col-sm-4'>Name:</div>
                  <div class='col-sm-8'><input type='text' name='Name' placeholder='Name' onChange='' /></div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>Type:</div>
                  <div class='col-sm-8'><select name='Type'>
                    <option value=''>Select</option>
                    <?php 
                      foreach( JOB_TYPES as $JOB_TYPE ){
                        ?><option value='<?php echo $JOB_TYPE;?>'><?php echo $JOB_TYPE;?></option><?php
                      }
                    ?>
                  </select></div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>Date:</div>
                  <div class='col-sm-8'>
                    <div class='row'>
                      <div class='col-sm-12'><input type='text' name='Date_Start' placeholder='Start' onChange='' autocomplete='off' /></div>
                      <div class='col-sm-12'><input type='text' name='Date_End' placeholder='End' onChange='' autocomplete='off' /></div>
                    </div>
                  </div>
                </div>
                <div class='row'>
                  <div class='col-sm-4'>Status:</div>
                  <div class='col-sm-8'><select name='Status' onChange=''>
                    
                  </select></div>
                </div>
              </div>
              <div class='card-body'>
                <button onClick='filter_table_job_search( );' style='width:100%;' type='button'>Search</button>
                <hr />  
                <div class='display-table'>Display Table</div>
                <table id='table_job_search' class='display' cellspacing='0' style='min-width:500px;width:100%;max-width:750px;'>
                  <thead><tr>
                    <th title='Selected'>Select</th>
                    <th title='ID'>ID</th>
                    <th title='Name'>Name</th>
                    <th title='Type'>Type</th>
                    <th title='Date'>Date</th>
                    <th title='Status'>Status</th>
                  </tr></thead>
                </table>
                <script>
                $("#filter_table_job input[name='Date_Start']").datepicker( { } );
                $("#filter_table_job input[name='Date_End']").datepicker( { } );
                var isChromium = window.chrome,
                  winNav = window.navigator,
                  vendorName = winNav.vendor,
                  isOpera = winNav.userAgent.indexOf("OPR") > -1,
                  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
                  isIOSChrome = winNav.userAgent.match("CriOS");
                var table_job_search = $('#table_job_search').DataTable( {
                  dom      : 'tlp',
                      processing : true,
                      serverSide : true,
                      responsive : true,
                      autoWidth  : false,
                      paging     : true,
                      searching  : false,
                      ajax      : {
                              url : 'bin/php/get/Jobs2.php',
                              data : function( d ){
                                  d = {
                                      start : d.start,
                                      length : d.length,
                                      order : {
                                          column : d.order[0].column,
                                          dir : d.order[0].dir
                                      }
                                  };
                                  d.Search = $('#filter_table_job input[name="Search"]').val( );
                                  d.ID = $('#filter_table_job input[name="ID"]').val( );
                                  d.Name = $('#filter_table_job input[name="Name"]').val( );
                                  d.Type = $('#filter_table_job input[name="Type"]').val( );
                                  d.Date = $('#filter_table_job input[name="Date"]').val( );
                                  d.Status = $('#filter_table_job select[name="Status"]').val( );
                                  return d; 
                              }
                          },
                      columns   : [
                        {
                          data : 'Selected',
                          render: function( data, type, row, meta ){
                            if( data == true ){
                                return "<input type='checkbox' name='job_search_ids[]' value='" + row.ID + "' " + " onChange='filterJobID( this );' checked />";
                            } else {
                                return "<input type='checkbox' name='job_search_ids[]' value='" + row.ID + "' " + " onChange='filterJobID( this );' />";
                            }
                            
                          },
                          width: '25px'
                        },{
                          data      : 'ID',
                          className : 'hidden'
                        },{
                          data : 'Name'
                        },{
                          data : 'Type'
                        },{
                          data : 'Date'
                        },{
                          data : 'Status',
                          render:function(data){ return data; }
                        }
                      ]
                    } );
                    function filter_table_job_search( ){ table_job_search.draw( ); }
                    function filterJobID( link ){
                      $.ajax({
                        url : 'bin/php/post/filterJobID.php',
                        data : { ID : $( link ).val( ) },
                        method : 'POST',
                        success: function ( ){ console.log( 'filterJobID : success' ); }
                      })
                    }
                    </script>
                  </div>
                </form></div>
              </div>
            </li><?php
	}
}?>