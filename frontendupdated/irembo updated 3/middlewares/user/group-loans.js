// var minDate, maxDate;
 
// Custom filtering function which will search data in column four between two values
// $.fn.dataTable.ext.search.push(
//     function( settings, data, dataIndex ) {
//         var min = minDate.val();
//         var max = maxDate.val();
//         var date = new Date( data[6] );
 
//         if (
//             ( min === null && max === null ) ||
//             ( min === null && date <= max ) ||
//             ( min <= date   && max === null ) ||
//             ( min <= date   && date <= max )
//         ) {
//             return true;
//         }
//         return false;
//     }
// );
$(document).ready(function () {


// loan application

    $('#loanAplicationForm').validate({
        rules: {
            groupid: {
                required: true
            },
            loanproduct: {
                required: true,
           
            },
            amount: {
                required: true
            },
            dateapplied: {
                required: true
            },
            tenureperiod: {
                required: true
            },
            graceperiod: {
                required: true
            },
            amornitizationinterval: {
                required: true
            },
            reason: {
                required: true
            }
        },
        messages: {
            groupid: {
                required: "please select group"
            },
            loanproduct: {
                required: "please choose loan product",
               
            },
            amount: {
                required: "please enter loan amount"
            },
            amount: {
                required: "please enter date of application"
            },
            tenureperiod: {
                required: "please provide tenure"
            },
            graceperiod: {
                required: "grace period missing"
            },
            amornitizationinterval: {
                required: "amortisation interval a must please"
            },
            reason: {
                required: "please fill out this field"
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

    async function getLoansView() {
        $.ajax({
            url: base + "groups",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                // console.log(response)
                nums = response.data.rows_returned;
                for (var i = 0; i < nums; i++) {
                    var groups = '';
                    groups += '<option value=' + response.data.groups[i].id + '>' + response.data.groups[i].account + ' | '+response.data.groups[i].groupname +' | '+response.data.groups[i].chairperson+ ' | 0'+response.data.groups[i].branch+'</option>';
                    $('#groupid').append(groups);
                }
                $('#groupid').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getLoansView()
                    }
            }
        })
    }
    getLoansView();

    async function getloanproductView() {
        $.ajax({
            url: base + "getloanproduct",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                nums = response.data.rows_returned;
                for (var i = 0; i < nums; i++) {
                    var loanproducts = '';
                    loanproducts += '<option value=' + response.data.loanproducts[i].id + '>' + response.data.loanproducts[i].productname +'</option>';
                    $('#loanproduct').append(loanproducts);
                }
                $('#loanproduct').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getloanproductView()
                    }
            }
        })
    }
    getloanproductView();


     $('#loanAplicationForm').submit(function(event) {
       event.preventDefault();
       if ($('#loanAplicationForm').valid()) {
           var loanAplicationForm = $(this);
           var form_data = JSON.stringify(loanAplicationForm.serializeObject());
           addLoanApplication();
           async function addLoanApplication() {
               $.ajax({
                   url: base + "groupapplication",
                   headers: {
                       'Authorization': localStorage.token,
                       'Content-Type': 'application/json'
                   },
                   type: "POST",
                   contentType: 'application/json',
                   data: form_data,
                   success: function(response) {
                       // console.log(response);
                       loanAplicationForm[0].reset();
                       var icon = 'success'
                       var message = 'loan application success'
                       sweetalert(icon,message)
                       return;
                   },
                   error: function(xhr, status, error) {
                       console.log(xhr.status);
                       if (xhr.status === 401) {
                           authchecker(addLoanApplication);
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

// loan application end



       $("body").children().first().before($(".modal"));
      //  $('#date_default').val(new Date().toDateInputValue());
    //   document.getElementById('date_default').valueAsDate = new Date();
    //   document.getElementById('date_default_schedule').valueAsDate = new Date();
      $('#addloanschedule').submit(function(event) {
        event.preventDefault();
        if ($('#addloanschedule').valid()) {
        var loanscheduleform = $(this);
        var form_data = JSON.stringify(loanscheduleform.serializeObject());
      async  function loanSheducle(){
        $.ajax({
            url: base + "amortization",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "POST",
            contentType: 'application/json',
            data: form_data,
            success: function(response) {
                // console.log(response);
                $("#addschedulemodal").modal('hide')
                $("#loandscheduledisplay").modal('show')
                loanscheduleform[0].reset();
                var loan_periods = response.data.length;
                var sum = 0;    
                var principal = 0;    
                var total = 0;    
                for (let i = 0; i < loan_periods; i++) {
                    var loan_schedule = "";
                    loan_schedule += '<tr>';
                    loan_schedule += '<td>'+response.data[i].period+'</td>'
                    loan_schedule += '<td>'+response.data[i].date+'</td>';
                    loan_schedule += '<td>'+numberWithCommas(response.data[i].minrate)+'</td>';
                    loan_schedule += '<td>'+numberWithCommas(response.data[i].minpay)+'</td>';
                    loan_schedule += '<td>'+numberWithCommas(response.data[i].amount)+'</td>';
                    loan_schedule += '<td>'+numberWithCommas(parseInt(response.data[i].minrate) + parseInt(response.data[i].minpay))+'</td>';
                    loan_schedule += '</tr>';
                    $('#loandschedule_period').append(loan_schedule)
                    sum += Number(response.data[i].minrate)
                    principal += Number(response.data[i].minpay)
                    total += Number(parseInt(response.data[i].minrate) + parseInt(response.data[i].minpay))
                }
                $('#totalInterest').html(sum)
                $('#principalpaid').html(principal)
                $('#amountpaid').html(total)
                console.log(sum, principal, total)

                return;
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    authchecker(addMembers);
                } 
                var icon = 'warning'
                var message = xhr.responseJSON.messages
                sweetalert(icon, message)
            }

        });
        return false;
      }
      loanSheducle()
      }
    });

    //    async function getLoansView() {
    //           $.ajax({
    //               url: base + "members",
    //               headers: {
    //                   'Authorization': localStorage.token,
    //                   'Content-Type': 'application/json'
    //               },
    //               type: "GET",
    //               success: function(response) {
    //                   // console.log(response)
    //                   nums = response.data.rows_returned;
    //                   for (var i = 0; i < nums; i++) {
    //                       var members = '';
    //                       members += '<option value=' + response.data.members[i].id + '>' + response.data.members[i].firstname + ' | '+response.data.members[i].lastname +' | '+response.data.members[i].account+ ' | 0'+response.data.members[i].contact+'</option>';
    //                       $('#account').append(members);
    //                   }
    //                   $('#account').select2({
    //                       theme: 'bootstrap5',
    //                       width: 'resolve',
    //                       dropdownParent: $("#addloanform")
    //                   });
    //               },
    //               error: function(xhr){
    //                       if (xhr.status == '401') {
    //                           getLoansView()
    //                       }
    //               }
    //           })
    //       }
    //       getLoansView()
       async function getLoantypes() {
         $.ajax({
             url: base + "setting/loans",
             headers: {
                 'Authorization': localStorage.token,
                 'Content-Type': 'application/json'
             },
             type: "GET",
             success: function(response) {
                 // console.log(response)
                 nums = response.data.rows_returned;
                 for (var i = 0; i < nums; i++) {
                  //  console.log(response.data.loans[i].id)
                     var loans = '';
                     loans += '<option value=' + response.data.loans[i].id + '>' + response.data.loans[i].name +'</option>';
                     $('#loantype').append(loans);
                 }
                 $('#loantype').select2({
                     theme: 'bootstrap5',
                     width: 'resolve',
                     dropdownParent: $("#addloanform")
                 });
             },
             error: function(xhr){
                     if (xhr.status == '401') {
                      getLoantypes()
                     }
             }
         })
     }
     getLoantypes()

     async function getLoantypesSchedule() {
      $.ajax({
          url: base + "setting/loans",
          headers: {
              'Authorization': localStorage.token,
              'Content-Type': 'application/json'
          },
          type: "GET",
          success: function(response) {
              // console.log(response)
              nums = response.data.rows_returned;
              for (var i = 0; i < nums; i++) {
                // console.log(response.data.loans[i].id)
                  var loans = '';
                  loans += '<option value=' + response.data.loans[i].id + '>' + response.data.loans[i].name +'</option>';
                  $('#loanschedule').append(loans);
              }
              $('#loanschedule').select2({
                  theme: 'bootstrap5',
                  width: 'resolve',
                  dropdownParent: $("#addloanschedule")
              });
          },
          error: function(xhr){
                  if (xhr.status == '401') {
                    getLoantypesSchedule()
                  }
          }
      })
  }
  getLoantypesSchedule()

        getLoans()
       async function getLoans(){
          $.ajax({
         url: base+"applications",
         headers: {
             'Authorization': localStorage.token,
             'Content-Type': 'application/json'
      },
         type : "GET",
         success: function(response) {
             var nums = response.data.rows_returned;
             $('#numofloansapps').html(nums);
            //  console.log(nums)
            //  console.log(response)
             var no = 0
             for (var i = 0; i < nums; i++){
                 no++
              if (response.data.loans[i].status === 'approved'){
                     var status = '<td class="badge badge-success"> Approved </td>'
                     var action = '<td><p class="badge badge-dark p-1">No Action </p></td>'
              } else if (response.data.loans[i].status === 'pending'){
                     var status = '<td class="badge badge-warning"> Pending </td>'
                     var action = '<td class="fw-60 text-center"><a href="#" class=\'approveLoan\' id='+ response.data.loans[i].id +'><i class="text-success fa fa-check fa-1.5x"></i></a> <a href="#"  class=\'rejectLoan\' id='+ response.data.loans[i].id +'><i class="text-danger fa fa-remove fa-1.5x"></i></a> <a href="#" data-toggle="modal" class=\'editloanapp\' data-target=\'#editloansmodal\' id=' + response.data.loans[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'deleteloans\' id=' + response.data.loans[i].id + '><i class="text-danger fa fa-trash fa-1.5x"></i></a></td>';
              } else if(response.data.loans[i].status === 'declined'){
                     var status = '<td class="badge badge-danger"> Rejected </td>'
                     var action = '<td><p class="badge badge-dark p-1">No Action</p></td>'

              }

                 var member = '';
                 member += '<tr>';
                 member += '<td></td>';
                 member += '<td>' +no+ '</td>';
                 member += '<td>' +response.data.loans[i].account+ '</td>';           
                 member += '<td>' +response.data.loans[i].firstname+ ' ' +response.data.loans[i].lastname + '</td>';           
                 member += '<td>' + response.data.loans[i].loanid+ '</td>';                   
                 member += '<td>' + numberWithCommas(response.data.loans[i].amount)+ '</td>';                   
                 member += status;                   
                 member += '<td>'+response.data.loans[i].date+ '</td>';
                 member += action
                 member += '</tr>';
                 $('#loans_table').append(member);
                }
                if (localStorage.getItem('role') == 'loansofficer') {
                    $('.rejectLoan').hide()
                    $('.approveLoan').hide()
                }
                $('#dataTables-loans').DataTable({
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
              getLoans()
             }
           }
        })
       }
     
     $('#addloanform').submit(function(event) {
       event.preventDefault();
       if ($('#addloanform').valid()) {
           var addloanform = $(this);
           var form_data = JSON.stringify(addloanform.serializeObject());
           addLoanApplication()
           async function addLoanApplication() {
               $.ajax({
                   url: base + "applications",
                   headers: {
                       'Authorization': localStorage.token,
                       'Content-Type': 'application/json'
                   },
                   type: "POST",
                   contentType: 'application/json',
                   data: form_data,
                   success: function(response) {
                       // console.log(response);
                       $("#addloanmodal").modal('hide')
                    //    $("#addmemberimagesmodal").modal('show')
                       addloanform[0].reset();
                       $("#dataTables-loans").DataTable().clear().destroy();
                       var icon = 'success'
                       var message = 'loan application success'
                       sweetalert(icon,message)
                       getLoans()
                       return;
                   },
                   error: function(xhr, status, error) {
                       console.log(xhr.status);
                       if (xhr.status === 401) {
                           authchecker(addLoanApplication);
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
     })
    //  approve append
    $(document).delegate('.rejectLoan', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
         console.log(id);
         Swal.fire({
             title: 'Are you sure?',
             text: "You want to Reject this loan application",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#8c939f',
             cancelButtonColor: '#73B41A',
             confirmButtonText: 'Reject'
           }).then((result) => {
             if (result.isConfirmed) {
                approveloans();
             }
           })
        async function approveloans(){
            var data = {
                status : 'declined'
            }
        $.ajax({
            url: base + "applications/" + id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            type: "PUT",
            success: function() {
                $("#dataTables-loans").DataTable().clear().destroy();
                var icon = 'warning'
                var message = 'Loan Application being declined please wait'
                sweetalert(icon, message)
                getLoans()
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    authchecker(approveloans)
                }
                        var icon = 'danger'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
            }
        })
        }
      })
       //  approve append
    $(document).delegate('.approveLoan', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
         console.log(id);
         Swal.fire({
             title: 'Are you sure?',
             text: "You want to approve this loan application",
             icon: 'success',
             showCancelButton: true,
             confirmButtonColor: '#7CF29C',
             cancelButtonColor: '#d33',
             confirmButtonText: 'Approve'
           }).then((result) => {
             if (result.isConfirmed) {
                approveloans();
             }
           })
        async function approveloans(){
            var data = {
                status : 'active'
            }
        $.ajax({
            url: base + "applications/" + id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            type: "PUT",
            success: function() {
                $("#dataTables-loans").DataTable().clear().destroy();
                var icon = 'success'
                var message = 'Loan Application being approved please wait'
                sweetalert(icon, message)
                getLoans()
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    authchecker(approveloans)
                }
                        var icon = 'danger'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
            }
        })
        }
      })
     // delete
     $(document).delegate('.deleteloans', 'click', function(event) {
       event.preventDefault();
       var id = $(this).attr('id')
        console.log(id);
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
            if (result.isConfirmed) {
                deleteloans();
            }
          })
       async function deleteloans(){
       $.ajax({
           url: base + "applications/" + id,
           headers: {
               'Authorization': localStorage.token,
               'Content-Type': 'application/json'
           },
           type: "DELETE",
           success: function() {
               $("#dataTables-loans").DataTable().clear().destroy();
               var icon = 'success'
               var message = 'Deleting loan application please wait'
               sweetalert(icon, message)
               getLoans()
           },
           error: function(xhr) {
               if (xhr.status == '401') {
                   authchecker(deleteloans)
               }
                       var icon = 'danger'
                       var message = xhr.responseJSON.messages
                       sweetalert(icon, message)
           }
       })
       }
     })
     
     $(document).delegate('.editloanapp', 'click', function(event) {
       event.preventDefault();
       var id = $(this).attr('id')
        geteditLoanApp()
        async function geteditLoanApp(){
        $.ajax({
            url: base + "applications/" + id,
            method: "GET",
            dataType: "json",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
      success: function(response) {
        //   console.log(response);
          $('#default_amount').val(response.data.loanapp[0].amount);
          $('#id_update').val(response.data.loanapp[0].id);
          $('#editloanappmodal').modal('show');
      },
      error: function (xhr){
          if (xhr.status === '401') {
              authchecker(geteditLoanApp)
          }

      }
  })
}
         $('#editloanappform').submit(function(event) {
           event.preventDefault();
           if ($('#editloanappform').valid()) {
               var editloanappform = $(this);
               var form_data = JSON.stringify(editloanappform.serializeObject());
               var loan_id = $('#id_update').val();
               editloanapp();
               async function editloanapp() {
                   // start ajax loader
                   $.ajax({
                       url: base + "applications/" + loan_id,
                       headers: {
                           'Authorization': localStorage.token,
                           'Content-Type': 'application/json'
                       },
                       type: "PATCH",
                       contentType: 'application/json',
                       cache: false,
                       data: form_data,
                       success: function(response) {
                           // console.log(staff_id);
                           $("#editloanappmodal").modal('hide')
                           editloanappform[0].reset()
                           $("#dataTables-loans").DataTable().clear().destroy()
                           var icon = 'success'
                           var message = 'update success'
                           sweetalert(icon, message)
                           getLoans()
     
                       },
                       error: function(xhr, status, error) {
                           if (xhr.status === 401) {
                               authchecker(editloanapp);
                        } else { 
                           var icon = 'warning'
                           var message = xhr.responseJSON.messages
                           sweetalert(icon, message)
                           console.log(message);
                       }
                    }
     
                   });
                   return false;
               }
           }
       })
     })
     $(document).delegate('.viewschedule', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
        Swal.fire('Coming soon...')

      })
     getActiveLoans();
     async function getActiveLoans(){
        $.ajax({
       url: base+"active-loans",
       headers: {
           'Authorization': localStorage.token,
           'Content-Type': 'application/json'
    },
       type : "GET",
       success: function(response) {
           var nums = response.data.rows_returned;
           $('#numofloansapps').html(nums);
           console.log(response)
           var no = 0
           for (var i = 0; i < nums; i++){
               no++
            if (response.data.loans[i].status === 'open'){
                   var status = '<td class="badge badge-success"> On Going </td>'
                   var action = '<td class="fw-60 text-center"><a href="#" class=\'viewschedule\' id='+ response.data.loans[i].id +'><i class="text-success fa fa-eye fa-1.5x"></i></a></td>';
            } else if (response.data.loans[i].status === 'closed'){
                   var status = '<td class="badge badge-warning"> Closed </td>'
                   var action = '<td class="fw-60 text-center"><a href="#" class=\'viewschedule\' id='+ response.data.loans[i].id +'><i class="text-success fa fa-eye fa-1.5x"></i></a></td>';
            }

               var member = '';
               member += '<tr>';
               member += '<td></td>';
               member += '<td>' +no+ '</td>';
               member += '<td>' +response.data.loans[i].account+ '</td>';           
               member += '<td>' +response.data.loans[i].firstname+ ' ' +response.data.loans[i].lastname + '</td>';           
               member += '<td>' + response.data.loans[i].loanid+ '</td>';                   
               member += '<td>' + numberWithCommas(response.data.loans[i].amount)+ '</td>';                   
               member += '<td>' + numberWithCommas(response.data.loans[i].balance)+ '</td>';                   
               member += status;                   
               member += '<td>'+response.data.loans[i].date+ '</td>';
               member += action
               member += '</tr>';
               $('#loans_active_table').append(member);
              }

              $('#dataTables-loans-active').DataTable({
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
            getLoans()
           }
         }
      })
     }
   
              
     })