function columnVisibility( link, table ){
    var column = table.column( $(link).attr('data-column') );
    column.visible( ! column.visible() );
}