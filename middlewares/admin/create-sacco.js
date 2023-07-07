$("document").ready(function(){
    $('#createSaccoForm').validate({
  rules: {
    name: {
          required: true
      },
    shortname: {
          required: true
      },
    contact: {
          required: true
      },
    email: {
          required: true,
      },
    address: {
          required: true,
      },
    password: {
          required: true
      },
     
    status: {
          required: true
      },
     
  },
  messages: {
    name: {
          required: "Please provide Sacco Name!"
      },
    shortname: {
          required: "Please provide Sacco Short Name!"
      },
    contact: {
          required: "Please provide a valid Phone Number Please!"
      },
    email: {
          required: "Please provide a valid email!"
      },
    address: {
          required: "Please provide a sacco adress!"
      },
    password: {
          required: "Password required!"
      },
    status: {
          required: "Sacco Status required!"
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


$('#createSaccoForm').submit(function(event) {
  event.preventDefault();
  if ($('#createSaccoForm').valid()) {
      var createSaccoForm = $(this);
      var form_data = JSON.stringify(createSaccoForm.serializeObject());
      // console.log(form_data);
      addSacco();
      async function addSacco() {
          $.ajax({
              url: base + "allsaccos",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "POST",
              contentType: 'application/json',
              data: form_data,
              success: function(response) {
                  $("#createsacco_modal").modal('hide');
                  createSaccoForm[0].reset();
                  $("#dataTables-saccos-all").DataTable().clear().destroy();
                  var icon = 'success'
                  var message = 'Sacco has been created!'
                  sweetalert(icon,message)
                  getSaccos();
                  return;
              },
              error: function(xhr, status, error) {
                  if (xhr.status === 401) {
                      authchecker(addSacco);
                  } 
                  var icon = 'warning'
                  var message = xhr.responseJSON.messages
                  sweetalert(icon, message)
              }

          });
          return false;
      }
  }
});

getSaccos();
async function getSaccos() {
  $.ajax({
      url: base + "allsaccos",
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
      },
      type: "GET",
      success: function(response) {
        var nums = response.data.rows_returned;
        $('#numofsaccos').html(nums);
        var no = 0;
        
        for (var i = 0; i < nums; i++) {
          no++
          var saccos = '';
          saccos += '<tr>';
          saccos += '<td></td>';
          saccos += '<td>' + no + '</td>';
          saccos += '<td>' + response.data.saccos[i].name + '</td>';
          saccos += '<td>' + response.data.saccos[i].contact + '</td>';
          saccos += '<td>' + response.data.saccos[i].email + '</td>';
          saccos += '<td>' + response.data.saccos[i].address + '</td>';
          saccos += '<td class="fw-60"><a href="#" data-toggle="modal" class=\'edit\' data-target=\'#\' id=' + response.data.saccos[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-primary fa fa-eye fa-1.5x"></i></a></td>';
          saccos += '</tr>';
          $('#saccos_table').append(saccos);
          }
          $('#dataTables-saccos-all').DataTable({
              responsive: false,
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
                  { extend: 'copy', className: 'btn btn-success btn-sm mdi mdi-content-copy', exportOptions: { columns: [1, ':visible'] } },
                  { extend: 'csv', className: 'btn btn-danger btn-sm mdi mdi-file-excel', exportOptions: { columns: [1, ':visible'] } },
                  { extend: 'excel', className: 'btn btn-dark btn-sm mdi mdi-file-excel-box', exportOptions: { columns: [1, ':visible'] } },
                  { extend: 'pdf', className: 'btn btn-info btn-sm mdi mdi-file-pdf', exportOptions: { columns: [1, ':visible'] } },
                  { extend: 'print', className: 'btn btn-warning btn-sm mdi mdi-printer', exportOptions: { columns: [1, ':visible'] } },
                  { extend: 'colvis', className: 'btn btn-sm btn-white' }
                  // 'pdf',  'excel', 'csv', 'print', 'copy',
              ],
              "lengthMenu": [
                  [5, 10, 25, 50, -1],
                  [5, 10, 25, 50, "All"]
              ],
              language: {
                  "emptyTable": "There Is No Data",
                  "zeroRecords": "No Data That Matches Your Search Query",
                  searchPlaceholder: "Search Data",
                  search: "Filter Records"
              },
              columnDefs: [{
                  orderable: false,
                  className: 'select-checkbox',
                  targets: 0
              }],
              select: {
                  style: 'multi',
                  selector: 'td:first-child'

              },
              fixedHeader: {
                  header: true,
                  footer: true
              }
          });
      },
      error: function(xhr) {
          if (xhr.status == '401') {
            authchecker(getSaccos);
          }
      }
  });
}

  });