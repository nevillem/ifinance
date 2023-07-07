$(document).ready(function () {
    $("body").children().first().before($(".modal"));
    $('.dropify').dropify();
    async function getMembers() {
        $.ajax({
            url: base + "members",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function (response) {
                nums = response.data.rows_returned;
                for (var i = 0; i < nums; i++) {
                    var accounts = '';
                    accounts += '<option value=' + response.data.members[i].id + '>' + response.data.members[i].account+" "+response.data.members[i].firstname+" "+response.data.members[i].midlename  +" "+response.data.members[i].lastname  + '</option>';
                    $('.member-select-input').append(accounts);
                }
                $('.member-select-input').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#addnextofkinform")
                });
            },
            error: function (xhr) {
                if (xhr.status == '401') {
                    getMembers()
                }
            }
        })
    }
    getMembers()

    getNextOfKin()
    async function getNextOfKin() {
        $.ajax({
            url: base + "kin",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function (response) {
                var nums = response.data.rows_returned;
                $('#numofmembers').html(nums);
                var no = 0
                for (var i = 0; i < nums; i++) {
                    no++
                    var kin = '';
                    kin += '<tr>';
                    kin += '<td></td>';
                    // member += '<td>' + kin_id + '</td>';
                    kin += '<td>' + no + '</td>';
                    kin += '<td>' + response.data.nKin[i].firstname + '</td>';
                    kin += '<td>' + response.data.nKin[i].lastname + '</td>';
                    kin += '<td>' + response.data.nKin[i].relationship + '</td>';
                    kin += '<td>' +  response.data.nKin[i].inheritance + '</td>';
                    kin += '<td>' +  response.data.nKin[i].contact + '</td>';
                    kin += '<td>' +  response.data.nKin[i].address + '</td>';
                    kin += '<td>' + response.data.nKin[i].dob + '</td>';
                    // member += '<td>' + response.data.rows[i].members_member_id + '</td>';
                    // member += status;
                    kin += '<td class="fw-60 text-center"><a href="#">' +
                        '<a href="#" data-toggle="modal" class=\'nextofkin\' data-target=\'#editnKinmodal\' id=' + response.data.nKin[i].id +
                        ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a></td>';
                    kin += '</tr>';
                    $('#kin_table').append(kin);
                }
                $('#dataTables-kin').DataTable({
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
                    }, language: {

                    },
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
                    ],
                    "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
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
            error: function (xhr, status, error) {
              console.log(xhr);
                if (xhr.status == '401') {
                    getNextOfKin()
                }
            }
        })
    }


    // $('#contactinfo').hide()
    $('#finalinfo').hide()
    $(".next").click(function () {
        var form = $("#addnextofkinform");
        form.validate({
            errorElement: 'span',
            errorClass: 'invalid-feedback',
            highlight: function (element, errorClass, validClass) {
                $(element).closest('.form-group').addClass("has-error");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).closest('.form-group').removeClass("has-error");
            },
            rules: {


            },
            messages: {

            }
        })
        if (form.valid() === true) {
            if ($('#biodata').is(":visible")) {
                current_fs = $('#biodata')
                next_fs = $('#contactinfo')
            }
            else if ($('#contactinfo').is(":visible")) {
                current_fs = $('#contactinfo')
                next_fs = $('#finalinfo')
            }
            else if ($('#finalinfo').is(":visible")) {
                current_fs = $('#finalinfo')
                next_fs = $('#documentsinfo')
            }
            current_fs.hide()
            next_fs.show();
        }
    })

    $('#previous').click(() => {
        if ($('#contactinfo').is(":visible")) {
            current_fs = $('#contactinfo');
            next_fs = $('#biodata');
        }
        else if ($('#finalinfo').is(":visible")) {
            current_fs = $('#finalinfo');
            next_fs = $('#contactinfo');
        }
        next_fs.show();
        current_fs.hide();
    })

    // POST: ENROLL_MEMBERS
    $('#addnextofkinform').submit(function (event) {
        event.preventDefault();
        if ($('#addnextofkinform').valid()) {
            var addnextofkinform = $(this);
            var form_data = JSON.stringify(addnextofkinform.serializeObject());
            // console.log(":Submit was called_________", form_data)
            addNkin()
            async function addNkin() {
                $.ajax({
                    url: base + "kin",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function (response) {
                        $('#previous').click();
                        $("#addnextofkinmodal").modal('hide')
                        addnextofkinform[0].reset();
                        $("#dataTables-kin").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'Success, Next of kin uploaded';
                        sweetalert(icon, message)
                        getNextOfKin()
                        return;
                    },
                    error: function (xhr, status, error) {
                        console.log( xhr);
                        if (xhr.status === 401) {
                            authchecker(addNkin);
                        }
                        var icon = 'warning';
                        var message = xhr.responseJSON.messages[0];
                        sweetalert(icon, message);
                    }

                });
                return false;
            }
        }
    })
    // delete
    $(document).delegate('.deletemember', 'click', function (event) {
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
        async function deletemember() {
            $.ajax({
                url: base + "members/" + id,
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "DELETE",
                success: function () {
                    $("#dataTables-members").DataTable().clear().destroy();
                    var icon = 'warning'
                    var message = 'Account deleted'
                    sweetalert(icon, message)
                    getNextOfKin()
                },
                error: function (xhr) {
                  console.log(xhr);
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

    $(document).delegate('.nextofkin', 'click', function (event) {
        event.preventDefault();
        var id = $(this).attr('id')
        geteditmember()
        async function geteditmember() {
            $.ajax({
                url: base + "kin/" + id,
                method: "GET",
                dataType: "json",
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                success: function (response) {
                    console.log(response);
                    $('#id_update').val(response.data.nKin[0].id);
                    $('#firstname_update').val(response.data.nKin[0].firstname);
                    $('#lastname_update').val(response.data.nKin[0].lastname);
                    $('#midlename_update').val(response.data.nKin[0].midlename);
                    $('#email_update').val(response.data.nKin[0].email);
                    $('#status_update').val(response.data.nKin[0].status);
                    $('#contact_update').val('0' + response.data.nKin[0].contact);
                    $('#gender_update').val(response.data.nKin[0].gender);
                    $('#address_update').val(response.data.nKin[0].address);
                    $('#dob_update').val(response.data.nKin[0].dob);
                    $('#relationship_update').val(response.data.nKin[0].relationship);
                    $('#identification_update').val(response.data.nKin[0].identification);
                    $('#inheritance_update').val(response.data.nKin[0].inheritance);
                    $('#editnKinmodal').modal('show');
                },
                error: function (xhr) {
                    if (xhr.status == '401') {
                        authchecker(geteditmember)
                    }

                }
            })
        }
        $(".next_one").click(function () {
            var form = $("#editmemberform");
            form.validate({
                errorElement: 'span',
                errorClass: 'invalid-feedback',
                highlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass("has-error");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').removeClass("has-error");
                },
                rules: {


                },
                messages: {

                }
            })
            if (form.valid() === true) {
                if ($('#biodata_update').is(":visible")) {
                    current_fs = $('#biodata_update')
                    next_fs = $('#contactinfo_update')
                } else if ($('#contactinfo_update').is(":visible")) {
                    current_fs = $('#contactinfo_update')
                    next_fs = $('#documentsinfo_update')
                }


                next_fs.show()
                current_fs.hide()
            }
        })

        $('#previous_one').click(function () {
            if ($('#contactinfo_update').is(":visible")) {
                current_fs = $('#contactinfo_update');
                next_fs = $('#biodata_update');
            }
            next_fs.show()
            current_fs.hide()
        })

        $('#editmemberform').submit(function (event) {
            event.preventDefault();
            if ($('#editmemberform').valid()) {
                var editmemberform = $(this);
                var form_data = JSON.stringify(editmemberform.serializeObject());
                var nextofkin = $('#id_update').val();
                editMembers();
                async function editMembers() {
                    // start ajax loader
                    $.ajax({
                        url: base + "kin/" + nextofkin,
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "PATCH",
                        contentType: 'application/json',
                        cache: false,
                        data: form_data,
                        success: function (response) {
                            $("#editmembermodal").modal('hide')
                            editmemberform[0].reset()
                            $('#previous_one').click()
                            $("#dataTables-kin").DataTable().clear().destroy()
                            var icon = 'success'
                            var message = 'edit successful'
                            sweetalert(icon, message)
                            getNextOfKin()

                        },
                        error: function (xhr, status, error) {
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



    // pick the modal member id
    $(document).delegate('.rangemember', 'click', function (event) {
        event.preventDefault();
        var id = $(this).attr('id')
        // console.log(id)
        $('#member_statement_id').val(id);
    })

    // please don't touch
    $('#accountstatement').submit(function (event) {
        event.preventDefault();
        if ($('#accountstatement').valid()) {
            var accountstatement = $(this);
            var form_data = JSON.stringify(accountstatement.serializeObject());
            var member_id = $('#member_statement_id').val();
            console.log(member_id);
            accountstatementDoc()
            async function accountstatementDoc() {
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
                    success: function (response) {
                        //console.log(response);
                        accountstatement[0].reset()
                        $("#rangemembermodal").modal('hide');
                        // $('#deposit_transaction').innerHTML("");
                        $("#deposit_transaction > tr").remove();
                        $("#accountstatementmodal").modal('show');
                        $("#saccologo").html('<img src="' + localStorage.logo + '" height="70px">');
                        $("#startdate").html(response.data.statement[0].mindate);
                        $("#enddate").html(response.data.statement[0].maxdate);
                        $("#accountname").html(response.data.statement[0].accountfname + ' ' + response.data.statement[0].accountlname);
                        $("#accountnumber").html(response.data.statement[0].accountnumber);
                        $("#accountcontact").html('256' + response.data.statement[0].accountcontact);
                        $("#accountaddress").html(response.data.statement[0].accountaddress);
                        $("#sacconame_statememt").html(response.data.statement[0].name);
                        $("#saccocontact_statement").html('256' + response.data.statement[0].contact);
                        $("#saccoemail_statement").html(response.data.statement[0].email);
                        $("#saccoaddress_statement").html(response.data.statement[0].address);
                        $("#accountbalance").html('UGX ' + numberWithCommas(response.data.statement[0].accountbalance));
                        $("#accountbottom").html('UGX ' + numberWithCommas(response.data.statement[0].accountbalance));
                        $("#accountbottomword").html(toWordsconver(numberWithCommas(response.data.statement[0].accountbalance)) + 'Shillings');

                        depositnums = response.data.statement[0].deposits.length;
                        withdrawnums = response.data.statement[0].withdraws.length;

                        for (let i = 0; i < depositnums; i++) {
                            var deposit_transaction = "";
                            deposit_transaction += '<tr>';
                            deposit_transaction += '<td>' + response.data.statement[0].deposits[i].date + '</td>';
                            deposit_transaction += '<td>' + response.data.statement[0].deposits[i].notes + '</td>'
                            deposit_transaction += '<td>-</td>';
                            deposit_transaction += '<td>' + numberWithCommas(response.data.statement[0].deposits[i].amount) + '</td>';
                            deposit_transaction += '<td>' + response.data.statement[0].deposits[i].method + '</td>';
                            deposit_transaction += '<td>' + response.data.statement[0].deposits[i].reference + '</td>';
                            deposit_transaction += '<td>' + numberWithCommas(response.data.statement[0].deposits[i].charge) + '</td>';
                            deposit_transaction += '<td>' + numberWithCommas(response.data.statement[0].deposits[i].balance) + '</td>';
                            deposit_transaction += '</tr>';
                            $('#deposit_transaction').append(deposit_transaction);
                        }
                        for (let i = 0; i < withdrawnums; i++) {
                            var withdraw_transaction = "";
                            withdraw_transaction += '<tr>';
                            withdraw_transaction += '<td>' + response.data.statement[0].withdraws[i].date + '</td>';
                            withdraw_transaction += '<td>' + response.data.statement[0].withdraws[i].notes + '</td>'
                            withdraw_transaction += '<td>' + numberWithCommas(response.data.statement[0].withdraws[i].amount) + '</td>';
                            withdraw_transaction += '<td>-</td>';
                            withdraw_transaction += '<td>' + response.data.statement[0].withdraws[i].method + '</td>';
                            withdraw_transaction += '<td>' + response.data.statement[0].withdraws[i].reference + '</td>';
                            withdraw_transaction += '<td>' + numberWithCommas(response.data.statement[0].withdraws[i].charge) + '</td>';
                            withdraw_transaction += '<td>' + numberWithCommas(response.data.statement[0].withdraws[i].balance) + '</td>';
                            withdraw_transaction += '</tr>';
                            $('#deposit_transaction').append(withdraw_transaction);
                        }
                        $('#statement-table-sorter').tablesorter(
                            { sortList: [[0, 1]] }
                        );

                    },
                    error: function (error, xhr, status) {
                        if (xhr.status === 401) {
                            authchecker(accountstatementDoc);
                        }
                    }
                })
            }

        }

    })

})
