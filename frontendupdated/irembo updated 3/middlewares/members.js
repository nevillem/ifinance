$(document).ready(function () {
  $("body").children().first().before($(".modal"));
   
   getMembers()
  async function getMembers(){
     $.ajax({
    url: base+"saccomembers",
    headers: {
      'Authorization': localStorage.token,
      'Content-Type': 'application/json'
  },
    type : "GET",
    success: function(response) {
      // console.log(response);
        var nums = response.data.rows_returned;
        $('#numofmembers').html(nums);
        // console.log(response)
        var no = 0
        for (var i = 0; i < nums; i++){
            no++
            var member = '';
            member += '<tr>';
            member += '<td></td>';
            member += '<td>' +no+ '</td>';
            member += '<td>' +response.data.members[i].account+ '</td>';           
            member += '<td>' +response.data.members[i].firstname+ ' ' +response.data.members[i].lastname + '</td>';           
            member += '<td>' + numberWithCommas(response.data.members[i].balance)+ '</td>';                   
            member += '<td>0'+response.data.members[i].contact+ '</td>';           
            member += '<td>' +response.data.members[i].gender+ '</td>';                   
            member += '<td>' +response.data.members[i].identification+ '</td>';           
            member += '<td>' +response.data.members[i].status+ '</td>';           
            member += '<td class="fw-60 text-center"><a href="#"><i class="text-success fa fa-eye fa-1.5x"></i></a></td>';
            member += '</tr>';
            $('#member_table').append(member);
           }
           $('#dataTables-members').DataTable({
            responsive: false,
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
            pageLength: 10,
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
          getMembers()
        }
      }
   })
  }
});