<script>
    var Editor_Leads = new $.fn.dataTable.Editor({
  ajax: 'php/post/Lead.php?ID=<?php echo $_GET['ID'];?>',
  table: '#Table_Leads',
  template: '#Form_Lead',
  formOptions: {
    inline: {
      submit: 'allIfChanged'
    }
  },
  idSrc: 'ID',
  fields : [{
    label: 'ID',
    name: 'ID'
  },{
    label: 'Name',
    name: 'Name'
  },{
    label: 'Type',
    name: 'Type'
  },{
    label: 'Street',
    name: 'Street'
  },{
    label: 'City',
    name: 'City',
    type: 'select',
    options: [<?php
      $result = $database->query(null,
      "    SELECT   OwnerWithRol.City
           FROM     OwnerWithRol
           WHERE    OwnerWithRol.City <> ''
           GROUP BY OwnerWithRol.City
           ORDER BY OwnerWithRol.City ASC
      ;");
      $Cities = array();
      if($result){while($City = sqlsrv_fetch_array($result)){$Cities[] = '{' . 'label: '{$City['City']}', value:'{$City['City']}'' . '}';}}
      echo implode(',',$Cities);
    ?>]
  },{
    label: 'State',
    name: 'State',
    type: 'select',
    options: [<?php
      $result = $database->query(
        null,
        " SELECT   OwnerWithRol.State
          FROM     OwnerWithRol
          WHERE    OwnerWithRol.State <> ''
          GROUP BY OwnerWithRol.State
          ORDER BY OwnerWithRol.State ASC;"
      );
      $States = array();
      if($result){while($State = sqlsrv_fetch_array($result)){$States[] = '{' . 'label: '{$State['State']}', value:'{$State['State']}'' . '}';}}
      echo implode(',',$States);
    ?>]
  },{
    label: 'Zip',
    name: 'Zip'
  },{
    label:'Customer',
    name:'Customer',
    type:'select',
    options:[<?php
      $result = $database->query(
        null,
        " SELECT   OwnerWithRol.Name
          FROM     OwnerWithRol
          WHERE    OwnerWithRol.Name <> ''
          ORDER BY OwnerWithRol.Name ASC;",
        array( )
      );
      $Customers = array();
      if( $result ){ while( $Customer = sqlsrv_fetch_array( $result ) ){
        $Customer['Name'] = str_replace("'" ,'',$Customer['Name']);
        $Customers[] = '{' . "label: '{$Customer['Name']}', value:'{$Customer['Name']}'" . '}';}}
      echo implode( ',', $Customers );
    ?>]
  }]
});
var Table_Leads = $('#Table_Leads').DataTable( {
  responsive : true,
  ajax: {
    url:'bin/php/get/Leads.php',
    dataSrc:function(json){if(!json.data){json.data = [];}return json.data;}
  },
  columns: [
    {
      data: 'ID',
      className:'hidden'
    },{
      data: 'Name'
    },{
      data: 'Street'
    },{
      data: 'City'
    },{
      data: 'State'
    },{
      data: 'Zip'
    },{
      data: 'Customer'
    }
  ],
  language:{
    loadingRecords:"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
  },
  paging:true,
  dom:'tp',
  select:true,
  scrollY : '600px',
  scrollCollapse:true,
  lengthChange: false
} );
function hrefLeads(){hrefRow('Table_Leads','lead');}
$('Table#Table_Leads').on('draw.dt',function(){hrefLeads();});
</script>
