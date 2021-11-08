<?php 
namespace dom\table;
class units {
    public function __construct( ){
        ?><table id='table_unit_search' <?php new \dom\table\attributes( );?> style='min-width:500px;width:100%;max-width:750px;'>
            <thead><tr>
                <th title='Selected'>Select</th>
                <th title='ID'>ID</th>
                <th title='City ID'>City ID</th>
                <th title='Location'>Location</th>
                <th title='Building ID'>Building ID</th>
                <th title='Type'>Type</th>
                <th title='Status'>Status</th>
            </tr></thead>
        </table><?php
    }
}?>