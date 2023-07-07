$(document).ready(function () {
   
   getLoginTimes()
  async function getLoginTimes(){
     $.ajax({
    url: base+"activityuser",
    headers: {
        'Authorization': localStorage.token,
        'Content-Type': 'application/json'
 },
    type : "GET",
    success: function(response) {
      // console.log(response)
        var nums = response.data.rows_returned;
        $('#activitylogin').html(nums);
        var no = 0
        for (var i = 0; i < nums; i++){
            no++
            var activity = '';
            activity += '<tr>';
            activity += '<td></td>';
            activity += '<td>' +no+ '</td>';
            activity += '<td>' +response.data.activity[i].ip+ '</td>';           
            activity += '<td>' +response.data.activity[i].os+ '</td>';           
            activity += '<td>' +response.data.activity[i].timestamp+ '</td>';           
            activity += '</tr>';
            $('#login_table').append(activity);
           }
           $('#dataTables-login').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            retrieve: true,
            autoWidth: true,
            paging: true,
            dom: 'lBfrtip',
            ordering: true,
            info: true,
            select: true,
            keys: true,
            autoFill: true,
            colReorder: true,
            pageLength: 5,
            buttons: [
            { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy', exportOptions: { columns: [1 , ':visible' ]}},
            { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel', exportOptions: { columns: [ 1, ':visible' ]} },
            { extend: 'excel', className: 'btn btn-dark btn-sm mdi mdi-file-excel-box', exportOptions: { columns: [1 , ':visible' ]} },
            { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf', exportOptions: { columns: [ 1, ':visible' ]} },
            { extend: 'print', className: 'btn btn-warning btn-sm mdi mdi-printer', exportOptions: { columns: [ 1, ':visible' ]}},
            { extend: 'colvis', className: 'btn btn-sm btn-white'}
            ],
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            language: {
              "emptyTable": "There Is No Data",
              "zeroRecords":      "No Data That Matches Your Search Query",
              searchPlaceholder: "Search Data",
              search: "Filter Records"
            },
            columnDefs: [ {
              orderable: false,
              className: 'select-checkbox',
              targets:   0
          } ],
          select: {
              style:    'multi',
              selector: 'td:first-child'

          },
          fixedHeader: {
            header: true,
            footer: true
        }
          });
      },
      error: function(xhr,status,error){
        if (xhr.status == '401'){
          getLoginTimes()
        }
      }
   })
  }
});