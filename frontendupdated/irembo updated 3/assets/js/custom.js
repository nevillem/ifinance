$(document).ready(function() {

    var table = $('#dataTables-example').DataTable({
      responsive: false,
       paging: true,
      dom: 'lBfrtip',
      buttons: [
      { extend: 'copy', className: 'btn btn-success mdi mdi-content-copy' },
      { extend: 'csv', className: 'btn btn-danger mdi mdi-file-excel' },
      { extend: 'excel', className: 'btn btn-warning mdi mdi-file-excel-box' },
      { extend: 'pdf', className: 'btn btn-info mdi mdi-file-pdf' },
      { extend: 'print', className: 'btn btn-dark mdi mdi-printer' }
        // 'pdf',  'excel', 'csv', 'print', 'copy',
      ],
      "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
      language: {
        "emptyTable": "There Is No Data",
        "zeroRecords":      "No Data That Matches Your Search Query",
        searchPlaceholder: "Search Data",
        search: "Filter Records",
      },
    });

    var table1 = $('#dataTables-example-2').DataTable({
      responsive: true,
       paging: true,
       // pageLength: 10,
      // "processing": true,
       // "serverSide": true,
      dom: 'lBfrtip',
      buttons: [
   { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy' },
   { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel' },
   { extend: 'excel', className: 'btn btn-warning btn-sm mdi mdi-file-excel-box' },
   { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf' },
   { extend: 'print', className: 'btn btn-dark btn-sm mdi mdi-printer' }
        // 'pdf',  'excel', 'csv', 'print', 'copy',
      ],
      "lengthMenu": [[2, 5, 10, 25, 50, -1], [2,5, 10, 25, 50, "All"]],
      language: {
        "emptyTable": "There Is No Data",
        "zeroRecords":      "No Data That Matches Your Search Query",
        searchPlaceholder: "Search Data",
        search: "Filter Records",
      }
    });
    var table2 = $('#dataTables-example-1').DataTable({
      responsive: true,
       paging: true,
       // pageLength: 10,
      // "processing": true,
       // "serverSide": true,
      dom: 'lBfrtip',
      buttons: [
   { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy' },
   { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel' },
   { extend: 'excel', className: 'btn btn-warning btn-sm mdi mdi-file-excel-box' },
   { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf' },
   { extend: 'print', className: 'btn btn-dark btn-sm mdi mdi-printer' }
        // 'pdf',  'excel', 'csv', 'print', 'copy',
      ],
      "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
      language: {
        "emptyTable": "There Is No Data",
        "zeroRecords":      "No Data That Matches Your Search Query",
        searchPlaceholder: "Search Data",
        search: "Filter Records",
      }
    });

});
