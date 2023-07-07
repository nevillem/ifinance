// groupS
$(document).ready(function(){
  // save groups
  $('#saveGroupsForm').submit(function(event) {
        event.preventDefault();
        if ($('#saveGroupsForm').valid()) {

            var saveGroupsForm = $(this);
            var form_data = JSON.stringify(saveGroupsForm.serializeObject());
            //   cancelIdleCallback
            addGroup();
            // submit form data to api
            async function addGroup() {
                // start ajax loader
                $.ajax({
                    url: base+"groups",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        saveGroupsForm[0].reset();
                        var icon = 'success';
                        $("#dataTables-groups").DataTable().clear().destroy();
                        getGroups();
                        var message = 'Group Created';
                        sweetalert(icon, message);
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addGroup);
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
    // end save  groups


    // geteditdata
    $(document).delegate('.groups-edit', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id');
        geteditdata();
        async function geteditdata() {
            $.ajax({
                url: base + "groups/" + id,
                method: "GET",
                dataType: "json",
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                success: function(response) {

                    $('#editgroupName').val(response.data.group[0].groupname);
                    $('#editgroupChair').val(response.data.group[0].chairperson);
                    $('#editcontactNumber').val(response.data.group[0].contact);
                    $('#editemail').val(response.data.group[0].email);
                    $('#editadress').val(response.data.group[0].address);
                    $('#editregistrationDate').val(response.data.group[0].doj);
                    $('#editgroupStatus').val(response.data.group[0].status);
                    $('#editidentification').val(response.data.group[0].identification);
                    $('#editId').val(response.data.group[0].id);
                    $('#editeditGroupmodal').modal('show');

                },
                error: function(xhr, status, error) {
                    if (xhr.status == '401') {
                        authchecker(geteditdata);
                    }
                }
            })
        }
    });
// end geteditdata


 // update groups
    $('#updateGroupsForm').submit(function(event) {
        event.preventDefault();
        if ($('#updateGroupsForm').valid()) {

            var updateGroupsForm = $(this);
            var id=$('#editId').val();
            var form_data = JSON.stringify(updateGroupsForm.serializeObject());
            //   cancelIdleCallback
            updateGroup();
            // submit form data to api
            async function updateGroup() {
                // start ajax loader
                $.ajax({
                    url: base+"groups/"+id,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        updateGroupsForm[0].reset();
                        $("#dataTables-groups").DataTable().clear().destroy();
                        getGroups();
                        var icon = 'success';
                        $("#dataTables-groups").DataTable().clear().destroy();
                        getGroups();
                        var message = 'Group Updated';
                        sweetalert(icon, message);
                        return;
                    },
                    error: function(xhr, status, error) {
                        // console.log(xhr.status);
                        if (xhr.status === 401) {
                            authchecker(updateGroup);
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
    // end update  groups

    // get data
    getGroups();
    async function getGroups() {
      $.ajax({
        url: base+"groups",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                $('#numofgroups').html(nums);

                // var nums = response.data.groups.length;
                var no = 0
                for (var i = 0; i < nums; i++) {
                    no++
                    var groups = '';
                    groups += '<tr>';
                    groups += '<td> </td>';
                    groups += '<td>' +no+ '</td>';
                    groups += '<td>' +response.data.groups[i].groupname+ '</td>';
                    groups += '<td>' +response.data.groups[i].account+ '</td>';
                    groups += '<td>' +response.data.groups[i].chairperson+ '</td>';
                    groups += '<td>' +response.data.groups[i].contact+ '</td>';
                    groups += '<td>' +response.data.groups[i].branch+ '</td>';
                    groups += '<td>' +response.data.groups[i].doj+ '</td>';
                    groups += '<td class="fw-60 text-center"><a href="#" data-toggle="modal" class=\'groups-delete\' data-target=\'#deletegroupsmodal\' id=' + response.data.groups[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-info fa fa-eye fa-1.5x"></i></a>&nbsp; <a href="#" data-toggle="modal" class=\'groups-edit\' data-target=\'#editGroupmodal\' id=' + response.data.groups[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> </td>';
                    groups += '</tr>';

                    $("#group_table").append(groups);
                }

                $('#dataTables-groups').DataTable({
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
                    getGroups();
                }
            }
        })
    }
});


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
                              $('#saccoAcountName').append(acoounts);
                          }

                          VirtualSelect.init({
                          ele: '#saccoAcountName'
                        });
                      },
                      error: function(xhr){
                              if (xhr.status == '401') {
                                  getAccounts()
                              }
                      }
                  });
              }


              getGroups();

              async function getGroups() {
                  $.ajax({
                      url: base+"groups",
                      headers: {
                          'Authorization': localStorage.token,
                          'Content-Type': 'application/json'
                      },
                      type: "GET",
                      success: function(response) {

                          nums = response.data.rows_returned;
                          for (var i = 0; i < nums; i++) {
                              var groups = '';
                              groups += '<option value=' + response.data.groups[i].id + '>' + response.data.groups[i].groupname + ' | '+response.data.groups[i].account +' | '+response.data.groups[i].status +'</option>';
                              $('#group').append(groups);
                          }
                          $('#group').select2({
                                    theme: 'bootstrap5',
                                    width: 'resolve',
                                });
                      },
                      error: function(xhr){
                              if (xhr.status == '401') {
                                  getGroups();
                              }
                      }
                  });
              }

              $('#group').on('change', function(){
                  var groupid = $(this).val();
                  if(groupid){
                    groupData();
                    async function groupData() {

                      $.ajax({
                        url: base+"groups/"+groupid,
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                          $('#gName').val(response.data.group[0].groupname +' - '+response.data.group[0].branch+', '+response.data.group[0].status);
                          $('#aNumber').val(response.data.group[0].account);

                        },
                        error: function(xhr){
                                if (xhr.status == '401') {
                                    groupData();
                                }
                        }
                    });
                    }
                }
              })



              // attach account to group
              $('#attachGroups').submit(function(event) {
                    event.preventDefault();
                    if ($('#attachGroups').valid()) {
                      attachGroups=$(this);
                      var group= $("#group").val();
                    var date = $("#dateofreg").val();
                    var accounts= $("#saccoAcountName").val();
                    var accountsarr= accounts.toString().split(',');

                    var formData=JSON.stringify({"doj":date,"group_id":group,"accounts_attached":accountsarr});
                    //   cancelIdleCallback
                        attachAccount();
                        // submit form data to api
                        async function attachAccount() {
                            // start ajax loader
                            $.ajax({
                                url: base+"groupaccounts",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: formData,
                                success: function(response) {
                                    attachGroups[0].reset();

                                    var icon = 'success';
                                    var message = 'Group(s) Attached!';
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
                // end attach account to group
              });
