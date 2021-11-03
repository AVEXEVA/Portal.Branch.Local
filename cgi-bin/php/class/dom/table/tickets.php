<?php 
namespace dom\table;
class tickets {
    public function __construct( ){
        ?><table rel='tickets' <?php new \dom\table\attributes( );?> style='min-width:500px;width:100%;max-width:750px;'>
            <thead><tr>
                <th title='Select'>Select</th>
                <th title='ID'>ID</th>
                <th title='Date'>Date</th>
                <th title='Customer'>Customer</th>
                <th title='Location'>Location</th>
                <th title='Device'>Device</th>
                <th title='Type'>Type</th>
                <th title='Status'>Status</th>
            </tr></thead>
            <tfoot><tr>
                <th><input type='checkbox' name='Select' /></th>
                <th><input type='text' name='Ticket' placeholder='Ticket #' onChange='redraw( this );' /></th>
                <th><div class='row'>
                    <div class='col-xs-12'><input id='12345' type='text' name='Date_Start' placeholder='Start' onChange='redraw( this );' /></div>
                    <div class='col-xs-12'><input id='98765' type='text' name='Date_End' placeholder='End' onChange='redraw( this );' /></div>
                </div></th>
                <th><input type='text' name='Customer' placeholder='Customer' onChange='redraw( this );' /></th>
                <th><input type='text' name='Location' placeholder='Location' onChange='redraw( this );' /></th>
                <th><input type='text' name='Device' placeholder='Device' onChange='redraw( this );' /></th>
                <th><select name='Type' onChange='redraw( this );'>
                    <option value='' >Select</option>
                    <option value='Service Call' >Service Call</option>
                    <option value='Modernization' >Modernization</option>
                    <option value='Repair' >Repair</option>
                    <option value='Maintenance' >Maintenance</option>
                    <option value='Violations' >Compliance</option>
                </select></th>
                <th><select name='Status' onChange='redraw( this );'>
                    <option value=''>Select</option>
                    <option value='Unassigned'>Unassigned</option>
                    <option value='Assigned'>Assigned</option>
                    <option value='En Route'>En Route</option>
                    <option value='On Site'>On Site</option>
                    <option value='Completed'>Completed</option>
                    <option value='On Hold'>On Hold</option>
                    <option value='Reviewing'>Reviewing</option>
                </select></th>
            </tr></tfoot>
        </table><?php
    }
}?>