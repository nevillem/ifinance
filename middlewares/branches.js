$(document).ready(function() {
    $("body").children().first().before($(".modal"));
    getBranches();
    async function getBranches() {
        $.ajax({
            url: base + "branches",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                // console.log(response);
                $('#numofbranches').html(response.data.rows_returned);
                var nums = response.data.branches.length;
                for (var i = 0; i < nums; i++) {
                    var branch =
                        '<div class="col-lg-4 mt-4 col-md-4 col-sm-4 col-xs-12 p-0 mb-lg-0">' +
                        '<div class="card shadow-lg mr-4 bg-white rounded">' +
                        '<div class="box-part text-center">' +
                        '<div class="title row">' +
                        '<h6 class="col-lg-6">Branch:</h6>' +
                        '<h6 class="col-lg-6">' + response.data.branches[i].name + '</h6>' +
                        '</div>' +
                        '<div class="text row">' +
                        '<span class="col-lg-6">' +
                        '<h6>Code: </h6><h6>Address: </h6><h6>Status: </h6>' +
                        ' </span>' +
                        ' <span class="col-lg-6"><h6>' + response.data.branches[i].code + ' </h6><h6 >' + response.data.branches[i].address + ' </h6><h6 >' + response.data.branches[i].status + '</h6></span>' +
                        '</div>' +
                        '<a href="#" data-toggle="modal" class=\'edit\' data-target=\'#editbranchmodal\' id=' + response.data.branches[i].id + ' data-backdrop="static" data-keyboard="false"><i class="fa fa-edit text-warning p-2" aria-hidden="true"></i></a>' +
                        '<a href="#" class=\'delete\' id=' + response.data.branches[i].id + '><i class="fa fa-trash text-danger p-2" aria-hidden="true"></i></a>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    $(".branches").append(branch);
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status == '401') {
                    getBranches()
                }
            }
        })
    }

    $('#branchform').validate({
        rules: {
            name: {
                required: true
            },
            code: {
                required: true,
                number: true,
                maxlength: 4
            },
            address: {
                required: true
            }
        },
        messages: {
            name: {
                required: "please enter branch name"
            },
            code: {
                required: "please enter a branch code e.g. 111 or 1",
                number: "this accepts digits only",
                maxlength: "branch code should be max 4 characters"
            },
            address: {
                required: "please enter address"
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

    $('#branchform').submit(function(event) {
        event.preventDefault();
        if ($('#branchform').valid()) {

            var branchform = $(this);
            var form_data = JSON.stringify(branchform.serializeObject());
            //   cancelIdleCallback
            addbranch();
            // submit form data to api
            async function addbranch() {
                // start ajax loader
                $.ajax({
                    url: base + "branches",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addbranch").modal('hide');
                        branchform[0].reset();
                        $(".branches").html("");
                        getBranches();
                        var icon = 'success';
                        var message = 'new branch added';
                        sweetalert(icon, message);
                        return;
                    },
                    error: function(xhr, status, error) {
                        // console.log(xhr.status);
                        if (xhr.status === 401) {
                            authchecker(addbranch);
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
    // delete the branch 
    $(document).delegate('.delete', 'click', function(event) {
        event.preventDefault();
        //delete branch
        var id = $(this).attr('id');
        deletebranch()
        async function deletebranch() {
            $.ajax({
                url: base + "branches/" + id,
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "DELETE",
                success: function() {
                    $(".branches").html("")
                    getBranches()
                    var icon = 'info';
                    var message = 'branch deleted';
                    sweetalert(icon, message);
                },
                error: function(xhr) {
                    if (xhr.status == '401') {
                        authchecker(deletebranch);
                    }
                    var icon = 'warning';
                    var message = 'cannot delete branch';
                    sweetalert(icon, message);
                }
            })
        }
    });
    // get the branch data 
    $(document).delegate('.edit', 'click', function(event) {
            event.preventDefault();
            var id = $(this).attr('id');
            geteditdata();
            async function geteditdata() {
                $.ajax({
                    url: base + "branches/" + id,
                    method: "GET",
                    dataType: "json",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    success: function(response) {
                        // console.log(response);
                        $('#name_update').val(response.data.branch[0].name);
                        $('#address_update').val(response.data.branch[0].address);
                        $('#code_update').val(response.data.branch[0].code);
                        $('#status_update').val(response.data.branch[0].status);
                        $('#branch_update_id').val(response.data.branch[0].id);
                        $('#editbranchmodal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status == '401') {
                            authchecker(geteditdata);
                        }
                    }
                })
            }
        })
        //  edit branch edit
    $('#editbranchform').validate({
        rules: {
            name: {
                required: true
            },
            code: {
                required: true,
                number: true,
                maxlength: 4
            },
            address: {
                required: true
            },
            status: {
                required: true
            }
        },
        messages: {
            name: {
                required: "please enter branch name"
            },
            code: {
                required: "please enter a branch code e.g. 111 or 1",
                number: "this accepts digits only",
                maxlength: "branch code should be max 4 characters"
            },
            address: {
                required: "please enter address"
            },
            status: {
                required: "status is required"
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

    $('#editbranchform').submit(function(event) {
        event.preventDefault();
        if ($('#editbranchform').valid()) {
            var editbranchform = $(this);
            var form_data = JSON.stringify(editbranchform.serializeObject());
            var branch_id = $('#branch_update_id').val();
            editbranch();
            async function editbranch() {
                $.ajax({
                    url: base + "branches/" + branch_id,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    cache: false,
                    data: form_data,
                    success: function(response) {
                        // console.log(branch_id);
                        $("#editbranchmodal").modal('hide');
                        editbranchform[0].reset();
                        $(".branches").html("");
                        var icon = 'success';
                        var message = 'branch edited';
                        sweetalert(icon, message);
                        getBranches();

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(editbranch);
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
});