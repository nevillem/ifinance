
$(document).ready(function () {
        $('#colateralForm').validate({
            rules: {
                memberid: {
                    required: true
                },
                collateralname: {
                    required: true,
               
                },
                serialnumber: {
                    required: true
                },
                valueprice: {
                    required: true
                },
                entranotice: {
                    required: true
                }
            },
            messages: {
                memberid: {
                    required: "please select member"
                },
                collateralname: {
                    required: "please provide collateral name",
                   
                },
                serialnumber: {
                    required: "please enter serial or collateral registration number"
                },
                valueprice: {
                    required: "please enter price value of the collateral given"
                },
                entranotice: {
                    required: "extra description needed"
                }
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

        async function getMembersView() {
            $.ajax({
                url: base + "members",
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "GET",
                success: function(response) {
                    // console.log(response)
                    nums = response.data.rows_returned;
                    for (var i = 0; i < nums; i++) {
                        var members = '';
                        members += '<option value=' + response.data.members[i].id + '>' + response.data.members[i].firstname + ' | '+response.data.members[i].lastname +' | '+response.data.members[i].account+ ' | 0'+response.data.members[i].contact+'</option>';
                        $('#memberid').append(members);
                    }
                    $('#memberid').select2({
                        theme: 'bootstrap5',
                        width: 'resolve',
                    });
                },
                error: function(xhr){
                        if (xhr.status == '401') {
                            getMembersView()
                        }
                }
            })
        }
        getMembersView();
       
// add collatreal
         $('#colateralForm').submit(function(event) {
           event.preventDefault();
           if ($('#colateralForm').valid()) {
               var colateralForm = $(this);
               var form_data = JSON.stringify(colateralForm.serializeObject());
               captureCollateral()
               async function captureCollateral() {
                   $.ajax({
                       url: base + "collateral",
                       headers: {
                           'Authorization': localStorage.token,
                           'Content-Type': 'application/json'
                       },
                       type: "POST",
                       contentType: 'application/json',
                       data: form_data,
                       success: function(response) {
                           $("#collateralsmodal").modal('hide')
                           colateralForm[0].reset();
                           $("#dataTables-collateral").DataTable().clear().destroy();
                           var icon = 'success'
                           var message = 'Collateral Captured'
                           sweetalert(icon,message)
                           getCollaterals();
                           return;
                       },
                       error: function(xhr, status, error) {
                           console.log(xhr.status);
                           if (xhr.status === 401) {
                               authchecker(captureCollateral);
                           } else {
                           var icon = 'warning'
                           var message = xhr.responseJSON.messages
                           sweetalert(icon, message)
                       }
                    }
         
                   });
                   return false;
               }
           }
         });


         getCollaterals();
         async function getCollaterals(){
            $.ajax({
           url: base+"collateral",
           headers: {
               'Authorization': localStorage.token,
               'Content-Type': 'application/json'
             },
           type : "GET",
           success: function(response) {
               var nums = response.data.rows_returned;
               $('#numofcollaterals').html(nums);
               var no = 0
               for (var i = 0; i < nums; i++){
                   no++

                   var collateral = '';
                   collateral += '<tr>';
                   collateral += '<td></td>';
                   collateral += '<td>' +no+ '</td>';
                   var mColats = response.data.members[i].collaterals;
            
                   for (var c=0; c < mColats.length; c++){
                       collateral += '<td>' +mColats[c].registrationno+ '</td>';
                       collateral += '<td>' +mColats[c].name+ '</td>';
                       collateral += '<td>' +mColats[c].notes+ '</td>';
                       collateral += '<td>' +mColats[c].valueprice+ '</td>';
                    }
                    collateral += '<td>' +response.data.members[i].firstname+ ' ' +response.data.members[i].lastname+ '</td>';
                   collateral += '</tr>';

                    $('#collateral_table').append(collateral);
                  }
    
                  $('#dataTables-collateral').DataTable({
                   responsive: false,
                   processing: true,
                   serverSide: false,
                   retrieve: true,
                   autoWidth: true,
                   paging: true,
                   dom: 'PlBfrtip',
                     searchPanes: {
                       initCollapsed: true,
                       count: '{total} found',
                       countFiltered: '{shown} / {total}'
                   },language: {
                    
                   },
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
                authchecker(getCollaterals);
               }
             }
          })
         }
 });