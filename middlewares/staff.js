$(document).ready(function() {
    $("body").children().first().before($(".modal"));
    
    getUsers()
    async function getUsers() {
        $.ajax({
            url: base + "users",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                $('#numofstaff').html(nums);
                var no = 0;

                for (var i = 0; i < nums; i++) {
                    no++
                    var staff = '';
                    staff += '<tr>';
                    staff += '<td></td>';
                    staff += '<td>' + no + '</td>';
                    staff += '<td>' + response.data.users[i].name + '</td>';
                    staff += '<td>' + response.data.users[i].username + '</td>';
                    staff += '<td> +256' + response.data.users[i].contact + '</td>';
                    staff += '<td>' + response.data.users[i].status + '</td>';
                    staff += '<td>' + response.data.users[i].branch + '</td>';
                    staff += '<td>' + response.data.users[i].role + '</td>';
                    staff += '<td class="fw-60"><a href="#" data-toggle="modal" class=\'edit\' data-target=\'#editstaffmodal\' id=' + response.data.users[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'delete\' id=' + response.data.users[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
                    staff += '</tr>';
                    $('#staff_table').append(staff);
                }
                $('#dataTables-staff').DataTable({
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
                    getUsers()
                }
            }
        })
    }

    async function getBranches() {
        $.ajax({
            url: base + "branches",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                nums = response.data.branches.length;
                for (var i = 0; i < nums; i++) {
                    var branches = '';
                    branches += '<option value=' + response.data.branches[i].id + '>' + response.data.branches[i].name + '</option>';
                    $('#branchid').append(branches);
                }
                $('#branchid').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#addstaff")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                            getBranches()
                    }
            }
        })
    }
    getBranches()
    $('#staffform').validate({
        rules: {
            name: {
                required: true
            },
            username: {
                required: true
            },
            status: {
                required: true
            },
            contact: {
                required: true,
                number: true,
                maxlength: 10,
                minlength: 10,
                phoneUK: true
            },
            role: {
                required: true,
            },
            branchid: {
                required: true
            }
        },
        messages: {
            contact: {
                number: "Please insert a valid phonenumber",
                maxlength: "Please insert a valid phonenumber",
                minlength: "Please insert a valid phonenumber",
                phoneUK: "Please insert a valid phonenumber"
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

    $('#staffform').submit(function(event) {
        event.preventDefault();
        if ($('#staffform').valid()) {
            var staffform = $(this);
            var form_data = JSON.stringify(staffform.serializeObject());
            addUser();
            async function addUser() {
                $.ajax({
                    url: base + "users",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addstaff").modal('hide');
                        staffform[0].reset();
                        $("#dataTables-staff").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'staff added'
                        sweetalert(icon,message)
                        getUsers()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addUser);
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
    $(document).delegate('.delete', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
        deletestaff();
        async function deletestaff(){
        $.ajax({
            url: base + "users/" + id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "DELETE",
            success: function() {
                $("#dataTables-staff").DataTable().clear().destroy();
                var icon = 'warning'
                var message = 'staff deleted'
                sweetalert(icon, message)
                getUsers()
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    authchecker(deletestaff)
                }
                        var icon = 'warning'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
            }
        })
        }
    });

    $(document).delegate('.edit', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
        geteditstaff()
    async function geteditstaff(){
        $.ajax({
            url: base + "users/" + id,
            method: "GET",
            dataType: "json",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                // console.log(response);
                $('#name_update').val(response.data.user[0].name);
                $('#id_update').val(response.data.user[0].id);
                $('#username_update').val(response.data.user[0].username);
                $('#status_update').val(response.data.user[0].status);
                $('#contact_update').val('0' + response.data.user[0].contact);
                $('#role_update').val(response.data.user[0].role);
                $('#branchid_updated').val(response.data.user[0].branchid);
                $('#branch_updated').val(response.data.user[0].branch);
                $('#editstaffmodal').modal('show');
            },
            error: function (xhr){
                if (xhr.status == '401') {
                    authchecker(geteditstaff)
                }

            }
        })
    }
    });
    //  staff edit
    $('#editstaffform').validate({
        rules: {
            name: {
                required: true
            },
            status: {
                required: true
            },
            contact: {
                required: true,
                number: true,
                maxlength: 10,
                minlength: 10,
                phoneUK: true
            },
            role: {
                required: true,
            }
        },
        messages: {
            contact: {
                number: "Please insert a valid phonenumber",
                maxlength: "Please insert a valid phonenumber",
                minlength: "Please insert a valid phonenumber",
                phoneUK: "Please insert a valid phonenumber"
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

    $('#editstaffform').submit(function(event) {
        event.preventDefault();
        if ($('#editstaffform').valid()) {
            var editstaffform = $(this);
            var form_data = JSON.stringify(editstaffform.serializeObject());
            var staff_id = $('#id_update').val();
            editStaff();
            async function editStaff() {
                // start ajax loader
                $.ajax({
                    url: base + "users/" + staff_id,
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
                        $("#editstaffmodal").modal('hide');
                        editstaffform[0].reset();
                        $("#dataTables-staff").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'edit successful'
                        sweetalert(icon, message)
                        getUsers()

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(editStaff);
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


});