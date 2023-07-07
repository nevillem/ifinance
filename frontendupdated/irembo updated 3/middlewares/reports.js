$(document).ready(function () {
  $("body").children().first().before($(".modal"));
   
  getSMS()
  async function getSMS(){
     $.ajax({
    url: base+"communication/members",
    headers: {
      'Authorization': localStorage.token,
      'Content-Type': 'application/json'
  },
    type : "GET",
    success: function(response) {
      // console.log(response);
        var nums = response.data.rows_returned;
        // $('#numofsms').html(nums);
        // console.log(response)
        var no = 0
        for (var i = 0; i < nums; i++){
            no++
            var sms = '';
            sms += '<tr>';
            sms += '<td></td>';
            sms += '<td>' +no+ '</td>';
            sms += '<td>' +response.data.sms[i].contact+ '</td>';                         
            sms += '<td>' +response.data.sms[i].message+ '</td>';           
            sms += '<td>' +response.data.sms[i].status+ '</td>';           
            sms += '<td>' +response.data.sms[i].timestamp+ '</td>';           
            sms += '</tr>';
            $('#sms_table').append(sms);
           }
           $('#dataTables-sms').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            retrieve: true,
            autoWidth: false,
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
          getSMS()
        }
      }
   })
  }
});

//emails

$(document).ready(function () {
  $("body").children().first().before($(".modal"));
   
  getSMS()
  async function getSMS(){
     $.ajax({
    url: base+"emails",
    headers: {
      'Authorization': localStorage.token,
      'Content-Type': 'application/json'
  },
    type : "GET",
    success: function(response) {
      // console.log(response);
        var nums = response.data.rows_returned;
        // $('#numofsms').html(nums);
        // console.log(response)
        var no = 0
        for (var i = 0; i < nums; i++){
            no++
            var emails = '';
            emails += '<tr>';
            emails += '<td></td>';
            emails += '<td>' +no+ '</td>';
            emails += '<td>' +response.data.emails[i].email+ '</td>';                         
            emails += '<td>' +response.data.emails[i].subject+ '</td>';           
            emails += '<td>' +response.data.emails[i].message+ '</td>';           
            emails += '<td>' +response.data.emails[i].status+ '</td>';           
            emails += '<td>' +response.data.emails[i].timestamp+ '</td>';           
            emails += '</tr>';
            $('#email_table').append(emails);
           }
           $('#dataTables-email').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            retrieve: true,
            autoWidth: false,
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
          getSMS()
        }
      }
   })
  }
});