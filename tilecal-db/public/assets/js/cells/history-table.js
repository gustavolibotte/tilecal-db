$(document).ready(function(){
    $('#history-table-full').DataTable({
        searching: true,
        paging: true,
        order: [[0, 'desc']]
    });
});
