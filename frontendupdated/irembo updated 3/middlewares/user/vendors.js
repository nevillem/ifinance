$(document).ready(function(){
    $('#vendorsForm').validate({
        rules: {
          firstname: {
            required: true,
          },
          lastname: {
              required: true
            },
            companyname: {
              required: true
            },
          email: {
              required: true,
              email:true
            },
          contact: {
              required: true
            },
          address: {
              required: true
            }
        },
        messages: {
          firstname:{
                  required: "please provide first name!",
                },
          lastname: {
              required: "please provide first name!"
          },
          companyname: {
              required: "company name missing!"
          },
          email: {
              required: "provide a valid email please!"
          },
          contact: {
              required: "vendor contact required please!"
          },
          address: {
              required: "adress required please!"
          }
          },
        errorElement: 'span',
        errorPlacement: function (error, element) {
          error.addClass('invalid-feedback');
          element.closest('.form-group').append(error);
        },
        highlight: function (element, _errorClass, validClass) {
          $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
          $(element).removeClass('is-invalid');
        }
      });
    $('#vendorsForm').submit(function(event) {
          event.preventDefault();
          if ($('#vendorsForm').valid()) {
            vendorsForm=$(this);


          var formData=JSON.stringify(vendorsForm.serializeObject());
              //   cancelIdleCallback
              saveVendors();
              // submit form data to api
              async function saveVendors() {
                  // start ajax loader
                  $.ajax({
                      url: base+"saccovendors",
                      headers: {
                          'Authorization': localStorage.token,
                          'Content-Type': 'application/json'
                      },
                      type: "POST",
                      contentType: 'application/json',
                      data: formData,
                      success: function(response) {
                          vendorsForm[0].reset();
                          $("#dataTables-sacco-vendors").DataTable().clear().destroy();
                          $("#sacco-vendorsmodal").modal('hide');
                          getallvendors();
                          var icon = 'success';
                          var message = 'Vendor Added!';
                          sweetalert(icon, message);
                          return;
                      },
                      error: function(xhr, status, error) {
                          if (xhr.status === 401) {
                              authchecker(saveVendors);
                          } else {
                              var icon = 'warning';
                              var message = xhr.responseJSON.messages;
                              sweetalert(icon, message);
  
                          }
                      }
  
                  });
                  return false;
              }
          }
      });


       // get all sacco vendors
      getallvendors();
      async function getallvendors() {
          $.ajax({
              url: base+"saccovendors",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "GET",
              success: function(response) {
                  var vendors = response.data.vendor.length;
                  console.log(vendors);
                  var no = 0
                  for (var i = 0; i < vendors; i++) {
                      no++
                      // vendors data

                      var allvendors = '';
                      allvendors += '<tr>';
                      allvendors += '<td> </td>';
                      // allvendors += '<td> </td>';
                      allvendors += '<td>' +no+ '</td>';
                      allvendors += '<td>' +response.data.vendor[i].firstname+ ' '+response.data.vendor[i].lastname+'</td>';           
                      allvendors += '<td>' +response.data.vendor[i].companyname+ '</td>';           
                      allvendors += '<td>' +response.data.vendor[i].contact+ '</td>';                                           
                      allvendors += '<td>' +response.data.vendor[i].email+ '</td>';                                           
                      allvendors += '<td>' +response.data.vendor[i].address+ '</td>';                                           
                     
                      allvendors += '</tr>';
                      $("#saccovendors").append(allvendors);
                  }

                  $('#dataTables-sacco-vendors').DataTable({
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
  
                      }
                      ,
                      fixedHeader: {
                          header: true,
                          footer: true
                      }
                  });


              },
              error: function(xhr, status, error) {
                  if (xhr.status == '401') {
                      authchecker(getallvendors);
                  }
              }
          })
      }
      // get all sacco vendors
  });