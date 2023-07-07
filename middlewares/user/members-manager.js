$(document).ready(function () {
  $("body").children().first().before($(".modal"));
  $('.dropify').dropify();
  async function getGroups() {
    $.ajax({
        url: base + "groups",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            nums = response.data.rows_returned;
            for (var i = 0; i < nums; i++) {
                var groups = '';
                groups += '<option value=' + response.data.groups[i].id + '>' + response.data.groups[i].account +'-' + response.data.groups[i].groupname + '</option>';
                $('#sacco_group').append(groups);
            }

            $('#sacco_group').select2({
                      // theme: 'bootstrap5',
                      // width: 'resolve',
                  });

        },
        error: function(xhr){
                if (xhr.status == '401') {
                  getGroups()
                }
        }
    })
}
getGroups()

  async function getAccounttypes() {
    $.ajax({
        url: base + "setting/accounts",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            // console.log(response)
            nums = response.data.rows_returned;
            for (var i = 0; i < nums; i++) {
                var accounts = '';
                accounts += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].name +'</option>';
                $('#category').append(accounts);
            }
            $('#category').select2({
                theme: 'bootstrap5',
                width: 'resolve',
                dropdownParent: $("#addmemberform")
            });
        },
        error: function(xhr){
                if (xhr.status == '401') {
                  getAccounttypes()
                }
        }
    })
}
getAccounttypes()

   getMembers()
  async function getMembers(){
     $.ajax({
    url: base+"members",
    headers: {
        'Authorization': localStorage.token,
        'Content-Type': 'application/json'
 },
    type : "GET",
    success: function(response) {
        var nums = response.data.rows_returned;
        $('#numofmembers').html(nums);
        var no = 0
        for (var i = 0; i < nums; i++){
            no++
            let status = response.data.members[i].status=='active'?'<td> <span class="badge badge-success">Active</span> </td>':'<td> <span class="badge badge-danger">Inactive</span> </td>'
            var member = '';
            member += '<tr>';
            member += '<td></td>';
            member += '<td>' +no+ '</td>';
            member += '<td>' +response.data.members[i].account+ '</td>';
            member += '<td>' +response.data.members[i].firstname+ '</td>';
            member += '<td>' +response.data.members[i].lastname+ '</td>';
            member += '<td>' +response.data.members[i].gender+ '</td>';
            member += '<td>0'+response.data.members[i].contact+ '</td>';
            member += '<td>' + response.data.members[i].identification+ '</td>';
            member += status;
            // member += '<td class="fw-60 text-center"><a href="details?id=' + encodeURI(response.data.members[i].id) + '" class="viewdetails1" id="' + encodeURI(response.data.members[i].id) + '"><i class="text-info fa fa-eye fa-1.5x"></i></a><a href="#" data-toggle="modal" class=\'editmember\' data-target=\'#editmembermodal\' id=' + response.data.members[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a></td>';
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
            dom: 'PlBfrtip',
              searchPanes: {
              columns:[5,8,3] ,
              initCollapsed: true,
              count: '{total} found',
              countFiltered: '{shown} / {total}'
            },
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
        console.log(xhr);
        if (xhr.status == '401'){
          getMembers()
        }
      }
   })
  }


  $(".next").click(function(){
    var form = $("#addmemberform");
    form.validate({
      errorElement: 'span',
      errorClass: 'invalid-feedback',
      highlight: function(element, errorClass, validClass) {
        $(element).closest('.form-group').addClass("has-error");
      },
      errorPlacement: function (error, element) {
        if(element.hasClass('select2') && element.next('.select2-container').length) {
            error.insertAfter(element.next('.select2-container'));
        }
      element.closest('.form-group').append(error);
    },
      unhighlight: function(element, errorClass, validClass) {
        $(element).closest('.form-group').removeClass("has-error");
      },
      rules: {


      },
      messages: {

      }
    })
    if (form.valid() === true){
      if ($('#biodata').is(":visible")){
        current_fs = $('#biodata')
        next_fs = $('#middledata')
      }else if($('#middledata').is(":visible")){
        current_fs = $('#middledata')
        next_fs = $('#contactinfo')
      }
      else if($('#contactinfo').is(":visible")){
        current_fs = $('#contactinfo')
        next_fs = $('#documentsinfo')
      }

      next_fs.show();
      current_fs.hide()
    }
})

$('#backtoBio').click(function(){
  if($('#middledata').is(":visible")){
    current_fs = $('#middledata');
    next_fs = $('#biodata');
  }
  next_fs.show();
  current_fs.hide();
});
$('#previous').click(function(){
  if($('#contactinfo').is(":visible")){
    current_fs = $('#contactinfo');
    next_fs = $('#middledata');
  }
  next_fs.show();
  current_fs.hide();
})

$('#addmemberform').submit(function(event) {
  event.preventDefault();
  if ($('#addmemberform').valid()) {
    var groups= $("#sacco_group").val();
    var groupsarr= groups.toString().split(',');
    var firstname=$("#firstname").val();
    var lastname=$("#lastname").val();
    var gender=$("#gender").val();
    var midlename=$("#midlename").val();
    var dob=$("#dob").val();
    var contact=$("#contact").val();
    var doj=$("#doj").val();
    var address=$("#address").val();
    var email=$("#email").val();
    var employment_status=$("#employment_status").val();
    var identification=$("#identification").val();
    var id_update=$("#id_update").val();
    var gross_income=$("#gross_income").val();
    var marital_status=$("#marital_status").val();
    var attach=$("#attach").val();
    var status=$("#status").val();

    var form_data=JSON.stringify({
    "firstname":firstname,
    "lastname":lastname,
    "midlename":midlename,
    "gender":gender,
    "dob":dob,
    "contact":contact,
    "doj":doj,
    "address":address,
    "email":email,
    "employment_status":employment_status,
    "identification":identification,
    "id_update":id_update,
    "gross_income":gross_income,
    "marital_status":marital_status,
    "attach":attach,
    "status":status,
    "sacco_group":groupsarr});
      var addmemberform = $(this);
      // var form_data = JSON.stringify(addmemberform.serializeObject());
      console.log(form_data);
      addMembers()
      async function addMembers() {
          $.ajax({
              url: base + "members",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "POST",
              contentType: 'application/json',
              data: form_data,
              success: function(response) {
                  // console.log(response);
                  localStorage.setItem("memberid", response.data.member[0].id)
                  $("#addmembermodal").modal('hide')
                  $("#addmemberimagesmodal").modal('show')
                  addmemberform[0].reset();
                  $("#dataTables-members").DataTable().clear().destroy();
                  var icon = 'success'
                  var message = 'Member Added Successfully. Please Upload related Images'
                  sweetalert(icon,message)
                  getMembers()
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
  }
})
$(document).delegate('.viewdetails1', 'click', function(event) {
  var id = $(this).attr('id')
  localStorage.setItem("memberid1", id)
});
// delete
$(document).delegate('.deletemember', 'click', function(event) {
  event.preventDefault();
  var id = $(this).attr('id')
  Swal.fire({
    title: 'Do you want to save the changes?',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: 'Delete',
    denyButtonText: `Don't Delete`,
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      Swal.fire('Data is being deleted', '', 'warning')
      deletemember();
    } else if (result.isDenied) {
      Swal.fire('Your data is safe', '', 'info')
    }
  })
  // deletemember();
  async function deletemember(){
  $.ajax({
      url: base + "members/" + id,
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
      },
      type: "DELETE",
      success: function() {
          $("#dataTables-members").DataTable().clear().destroy();
          var icon = 'warning'
          var message = 'Account deleted'
          sweetalert(icon, message)
          getMembers()
      },
      error: function(xhr) {
          if (xhr.status == '401') {
              authchecker(deletemember)
          }
                  var icon = 'warning'
                  var message = xhr.responseJSON.messages
                  sweetalert(icon, message)
      }
  })
  }
})

$(document).delegate('.editmember', 'click', function(event) {
  event.preventDefault();
  var id = $(this).attr('id')
  geteditmember()
async function geteditmember(){
  $.ajax({
      url: base + "members/" + id,
      method: "GET",
      dataType: "json",
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
      },
      success: function(response) {
          $('#firstname_update').val(response.data.members[0].firstname);
          $('#midlename_update').val(response.data.members[0].midlename);
          $('#lastname_update').val(response.data.members[0].lastname);
          $('#id_update').val(response.data.members[0].id);
          $('#email_update').val(response.data.members[0].email);
          $('#status_update').val(response.data.members[0].status);
          $('#contact_update').val('0' + response.data.members[0].contact);
          $('#employment_status_update').val(response.data.members[0].employment_status);
          $('#gross_income_update').val(response.data.members[0].gross_income);
          $('#marital_status_update').val(response.data.members[0].marital_status);
          $('#gender_update').val(response.data.members[0].gender);
          $('#gender_update').val(response.data.members[0].gender);
          $('#address_update').val(response.data.members[0].address);
          $('#dob_update').val(response.data.members[0].dob);
          $('#doj_update').val(response.data.members[0].doj);
          $('#identification_update').val(response.data.members[0].identification);
          $('#category_update').val(response.data.members[0].category);
          $('#type_update').val(response.data.members[0].type);
          $('#editmembermodal').modal('show');
      },
      error: function (xhr){
          if (xhr.status == '401') {
              authchecker(geteditmember)
          }

      }
  })
}
$(".next_one").click(function(){
  var form = $("#editmemberform");
  form.validate({
    errorElement: 'span',
    errorClass: 'invalid-feedback',
    highlight: function(element, errorClass, validClass) {
      $(element).closest('.form-group').addClass("has-error");
    },
    unhighlight: function(element, errorClass, validClass) {
      $(element).closest('.form-group').removeClass("has-error");
    },
    rules: {


    },
    messages: {

    }
  })
  if (form.valid() === true){
    if ($('#biodata_update').is(":visible")){
      current_fs = $('#biodata_update')
      next_fs = $('#contactinfo_update')
    }
    // else if($('#contactinfo_update').is(":visible")){
    //   current_fs = $('#contactinfo_update')
    //   next_fs = $('#documentsinfo_update')
    // }

    next_fs.show()
    current_fs.hide()
  }
})

    $('#previous_one').click(function(){
    if($('#contactinfo_update').is(":visible")){
      current_fs = $('#contactinfo_update');
      next_fs = $('#biodata_update');
    }
    next_fs.show()
    current_fs.hide()
    })

    $('#editmemberform').submit(function(event) {
      event.preventDefault();
      if ($('#editmemberform').valid()) {
          var editmemberform = $(this);
          var form_data = JSON.stringify(editmemberform.serializeObject());
          var member_id = $('#id_update').val();
          editMembers();
          async function editMembers() {
              // start ajax loader
              $.ajax({
                  url: base + "members/" + member_id,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "PATCH",
                  contentType: 'application/json',
                  cache: false,
                  data: form_data,
                  success: function(response) {
                      console.log(response);
                      localStorage.setItem("memberid", response.data.member[0].id)
                      $("#editmembermodal").modal('hide')
                      editmemberform[0].reset()
                      $('#previous_one').click()
                      // $("#addmemberimagesmodal").modal('show')
                      $("#dataTables-members").DataTable().clear().destroy()
                      var icon = 'success'
                      var message = 'edit successful'
                      sweetalert(icon, message)
                      getMembers()

                  },
                  error: function(xhr, status, error) {
                      if (xhr.status === 401) {
                          authchecker(editMembers);
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
})


//image upolad starts here
$('#addmemberimages').submit(function(event) {
  event.preventDefault();
  if ($('#addmemberimages').valid()) {
    let randomname = Math.floor(Math.random() * 9000009);
    let randomtitle = Math.floor(Math.random() * 9000009);
    var attribute = JSON.stringify({"title": randomtitle, "filename": randomname})
    var formData = new FormData(this)
      formData.append('attributes', attribute)
        addMembersImages()
        async function addMembersImages() {
          $.ajax({
              url: base + "members/"+localStorage.memberid+"/images",
              headers: {'Authorization': localStorage.token},
              type: "POST",
              contentType: false,
              processData: false,
              cache: false,
              data: formData,
              success: function(response) {
                  // $("#addmemberimagesmodal").modal('hide');
                  $('#addmemberimages').trigger('reset');
                  $(".dropify-clear").click();
                  var icon = 'success'
                  var message = 'User Image uploaded'
                  sweetalert(icon,message)
                  return;
              },
              error: function(xhr, status, error) {
                  if (xhr.status === 401) {
                      authchecker(addMembersImages);
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
  // pick the modal member id
  $(document).delegate('.rangemember', 'click', function(event) {
    event.preventDefault();
    var id = $(this).attr('id')
    // console.log(id)
    $('#member_statement_id').val(id);
  })

// please don't touch
$('#accountstatement').submit(function(event) {
  event.preventDefault();
  if ($('#accountstatement').valid()) {
    var accountstatement = $(this);
    var form_data = JSON.stringify(accountstatement.serializeObject());
    var member_id = $('#member_statement_id').val();
    console.log(member_id);
    accountstatementDoc()
    async function accountstatementDoc(){
    $.ajax({
      url: base + "statement/" + member_id,
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
      },
      type: "POST",
      contentType: 'application/json',
      cache: false,
      data: form_data,
      success: function(response) {
            console.log(response);
            accountstatement[0].reset()
            $("#rangemembermodal").modal('hide');
            // $('#deposit_transaction').innerHTML("");
            $("#deposit_transaction > tr").remove();
            $("#accountstatementmodal").modal('show');
            $("#saccologo").html('<img src="'+localStorage.logo+'" height="70px">');
            $("#startdate").html(response.data.statement[0].mindate);
            $("#enddate").html(response.data.statement[0].maxdate);
            $("#accountname").html(response.data.statement[0].accountfname +' '+response.data.statement[0].accountlname);
            $("#accountnumber").html(response.data.statement[0].accountnumber);
            $("#accountcontact").html('256'+response.data.statement[0].accountcontact);
            $("#accountaddress").html(response.data.statement[0].accountaddress);
            $("#sacconame_statememt").html(response.data.statement[0].name);
            $("#saccocontact_statement").html('256'+response.data.statement[0].contact);
            $("#saccoemail_statement").html(response.data.statement[0].email);
            $("#saccoaddress_statement").html(response.data.statement[0].address);
            $("#accountbalance").html('UGX '+numberWithCommas(response.data.statement[0].accountbalance));
            $("#accountbottom").html('UGX '+numberWithCommas(response.data.statement[0].accountbalance));
            $("#accountbottomword").html(toWordsconver(numberWithCommas(response.data.statement[0].accountbalance))+ 'Shillings');

            depositnums = response.data.statement[0].deposits.length;
            withdrawnums = response.data.statement[0].withdraws.length;

          for (let i = 0; i < depositnums ; i++) {
            var deposit_transaction= "";
                deposit_transaction += '<tr>';
                deposit_transaction += '<td>'+response.data.statement[0].deposits[i].date+'</td>';
                deposit_transaction += '<td>'+response.data.statement[0].deposits[i].notes+'</td>'
                deposit_transaction += '<td>-</td>';
                deposit_transaction += '<td>'+numberWithCommas(response.data.statement[0].deposits[i].amount)+'</td>';
                deposit_transaction += '<td>'+response.data.statement[0].deposits[i].method+'</td>';
                deposit_transaction += '<td>'+response.data.statement[0].deposits[i].reference+'</td>';
                deposit_transaction += '<td>'+numberWithCommas(response.data.statement[0].deposits[i].charge)+'</td>';
                deposit_transaction += '<td>'+numberWithCommas(response.data.statement[0].deposits[i].balance)+'</td>';
                deposit_transaction += '</tr>';
            $('#deposit_transaction').append(deposit_transaction);
            }
            for (let i = 0; i < withdrawnums; i++) {
            var withdraw_transaction= "";
                withdraw_transaction += '<tr>';
                withdraw_transaction += '<td>'+response.data.statement[0].withdraws[i].date+'</td>';
                withdraw_transaction += '<td>'+response.data.statement[0].withdraws[i].notes+'</td>'
                withdraw_transaction += '<td>'+numberWithCommas(response.data.statement[0].withdraws[i].amount)+'</td>';
                withdraw_transaction += '<td>-</td>';
                withdraw_transaction += '<td>'+response.data.statement[0].withdraws[i].method+'</td>';
                withdraw_transaction += '<td>'+response.data.statement[0].withdraws[i].reference+'</td>';
                withdraw_transaction += '<td>'+numberWithCommas(response.data.statement[0].withdraws[i].charge)+'</td>';
                withdraw_transaction += '<td>'+numberWithCommas(response.data.statement[0].withdraws[i].balance)+'</td>';
                withdraw_transaction += '</tr>';
            $('#deposit_transaction').append(withdraw_transaction);
            }
           $('#statement-table-sorter').tablesorter(
                          {sortList: [[0,1]]}
           );

      },
      error: function(error,xhr,status){
          if (xhr.status === 401) {
              authchecker(accountstatementDoc);
          }
      }
    })
  }

  }

})

});

//member account
$(document).ready(function(){

  // retrive data for the select fields

      getAccounts();

      async function getAccounts() {
          $.ajax({
              url: base+"getsaccoaccounts",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "GET",
              success: function(response) {
                  nums = response.data.rows_returned;
                  for (var i = 0; i < nums; i++) {
                      var acoounts = '';
                      acoounts += '<option value=' + response.data.accounts[i].id + '>' + response.data.accounts[i].account + ' | '+response.data.accounts[i].code +'</option>';
                      $('#multipleSelect').append(acoounts);
                  }

                  VirtualSelect.init({
                  ele: '#multipleSelect'
                });
              },
              error: function(xhr){
                      if (xhr.status == '401') {
                          getAccounts()
                      }
              }
          });
      }


      getMembersonly();

      async function getMembersonly() {
          $.ajax({
              url: base+"members",
              headers: {
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
              },
              type: "GET",
              success: function(response) {

                  nums = response.data.rows_returned;
                  for (var i = 0; i < nums; i++) {
                      var members = '';
                      members += '<option value=' + response.data.members[i].id + '>' + response.data.members[i].firstname + '  '+response.data.members[i].midlename +'  '+response.data.members[i].lastname +'</option>';
                      $('#member').append(members);
                  }
                  $('#member').select2({
                            theme: 'bootstrap5',
                            width: 'resolve',
                        });


              },
              error: function(xhr){
                      if (xhr.status == '401') {
                          getMembersonly();
                      }
              }
          });
      }

      $('#member').on('change', function(){
          var memberid = $(this).val();
          if(memberid){
            memberData();
            async function memberData() {

              $.ajax({
                url: base+"members/"+memberid,
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "GET",
                success: function(response) {
                  $('#customername').val(response.data.members[0].firstname +' '+response.data.members[0].midlename+' '+response.data.members[0].lastname);
                  $('#accoountnumber').val(response.data.members[0].account);

                  var image = response.data.members[0].images.length;
                if (image >0) {
                $('#image').attr('src', response.data.members[0].images[0].imageurl);
                }
                else {
                  $('#image').attr('src', 'https://live.irembofinance.com/assets/img/placeholder.png');
                }

                },
                error: function(xhr){
                        if (xhr.status == '401') {
                            memberData();
                        }
                }
            });
            }
        }
      })



      // attach account to member
      $('#attachAccounts').submit(function(event) {
            event.preventDefault();
            if ($('#attachAccounts').valid()) {

            var member= $("#member").val();
            var date = $("#dateofreg").val();
            var accounts= $("#multipleSelect").val();
            var accountsarr= accounts.toString().split(',');

            var formData=JSON.stringify({"doj":date,"member_id":member,"accounts_attached":accountsarr});
                //   cancelIdleCallback
                attachAccount();
                // submit form data to api
                async function attachAccount() {
                    // start ajax loader
                    $.ajax({
                        url: base+"memberaccounts",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "POST",
                        contentType: 'application/json',
                        data: formData,
                        success: function(response) {
                            // attachAccounts[0].reset();

                            var icon = 'success';
                            var message = 'Account(s) Attached!';
                            sweetalert(icon, message);
                            return;
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status === 401) {
                                authchecker(attachAccount);
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
        // end attach account to member
      });