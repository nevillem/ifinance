$('#incomecategoryform').validate({
    rules: {
      category: {
            required: true
        },
       
    },
    messages: {
      category: {
            required: "Income category missing!"
        },
    },
    errorElement: 'span',
    errorPlacement: function(error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
    },
    highlight: function(element, _errorClass, validClass) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
    }
});

  $('#incomecategoryform').submit(function(event) {
event.preventDefault();
if ($('#incomecategoryform').valid()) {
    var incomecategoryform = $(this);
    var form_data = JSON.stringify(incomecategoryform.serializeObject());
    addincomecategory();
    async function addincomecategory() {
        $.ajax({
            url: base + "managerincomescat",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "POST",
            contentType: 'application/json',
            data: form_data,
            success: function(response) {
                // console.log(response);
                $('#incomecategoryform').trigger('reset');
                $("#dataTables-incomecats").DataTable().clear().destroy();
                var icon = 'success'
                var message = 'Income category added!'
                sweetalert(icon,message)
                getIncomecat();
                return;
            },
            error: function(xhr, status, error) {
                if (xhr.status === '401') {
                    authchecker(addincomecategory);
                } 
                var icon = 'warning'
                var message = xhr.responseJSON.messages
                sweetalert(icon, message)
            }

        });
        return false;
    }
}
})
  getIncomecat();
async function getIncomecat(){
   $.ajax({
  url: base+"managerincomescat",
  headers: {
      'Authorization': localStorage.token,
      'Content-Type': 'application/json'
},
  type : "GET",
  success: function(response) {
      var nums = response.data.rows_returned;
    //   console.log(response)
      var no = 0
      for (var i = 0; i < nums; i++){
          no++
          var incomecTS = '';
          incomecTS += '<tr>';
          incomecTS += '<td></td>';
          incomecTS += '<td>' +no+ '</td>';
          incomecTS += '<td>' + response.data.categories[i].incomecategory+ '</td>';                           
          incomecTS += '</tr>';
          $('#income_user_table').append(incomecTS);
         }
         $('#dataTables-incomecats').DataTable({
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
        getIncomecat()
      }
    }
 })
}