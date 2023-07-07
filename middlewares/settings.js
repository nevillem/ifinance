$(document).ready(function() {
    $("body").children().first().before($(".modal"));

      var maxField = 10; //Input fields increment limitation
      var addButton = $('#addMore'); //Add button selector
      var wrapper = $('#Directory'); //Input field wrapper
      var fieldHTML = '<div class="person col-md-12 mb-2  form-inline"><input type="text" name="amountfrom" placeholder="amount from" class="amountfrom form-control form-control-sm  mb-2 mr-2" required/><input type="text" name="amountto" placeholder="amount to" class="amountto form-control form-control-sm  mb-2  mr-2" required/><input type="text" name="charge" placeholder="charge" class="charge form-control form-control-sm mb-2  mr-2" required /><select type="text" name="modeofdeduction" class="modeofdeduction border-1 form-control form-control-sm input-text mb-2 mr-2" required><option value=""  disabled selected hidden>select mode of payment</option> <option value="value">Value</option><option value="percentage">Percentage</option></select><a href="javascript:void(0);" class="remove_button"><i class="fas fa-trash-alt center"></i></a></div>'; //New input field html
      var x = 1; //Initial field counter is 1

    //Once add button is clicked
    $(addButton).click(function(){
      //Check maximum number of input fields
      if(x < maxField){
          x++; //Increment field counter
          $(wrapper).append(fieldHTML); //Add field html
      }
  });
  //Once remove button is clicked
$(wrapper).on('click', '.remove_button', function(e){
    e.preventDefault();
    $(this).parent('div').remove(); //Remove field html
    x--; //Decrement field counter
});
    getsacco()
     async function getsacco() {

        $.ajax({
            url: base + "saccos",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                $('#name_sacco').val(response.data.sacconame);
                $('#contact_sacco').val('0' + response.data.saccocontact);
                $('#shortname_sacco').val(response.data.saccoshortname);
                $('#address_sacco').val(response.data.saccoaddress);
            },
            error: function(xhr, status, error) {
                if (xhr.status == '401') {
                    authchecker(getsacco)
                }
            }
        })
    }

    $('#updatesacco').submit(function(event) {
        event.preventDefault();
        if ($('#updatesacco').valid()) {
            var updatesacco = $(this);
            var form_data = JSON.stringify(updatesacco.serializeObject());
            addupdatesacco();
            async function addupdatesacco() {
                $.ajax({
                    url: base + "saccos",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    data: form_data,
                    success: function() {
                        var icon = 'success'
                        var message = 'sacco updated'
                        sweetalert(icon, message)
                        getsacco()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(getsacco)
                        } else {
                            var icon = 'info'
                            var message = xhr.responseJSON.messages
                            sweetalert(icon, message)
                        }
                    }
                });
                return false;
            }
        }
    });

});

$(document).ready(function() {
    getaccount()
    async function getaccount() {
        $.ajax({
            url: base + "settings/accounts",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var account = '';
                    account += '<tr>';
                    account += '<td>' + no + '</td>';
                    account += '<td>' + response.data.accounts[i].name + '</td>';
                    account += '<td>' + numberWithCommas(response.data.accounts[i].charge) + '</td>';
                    account += '<td>' + numberWithCommas(response.data.accounts[i].balance) + '</td>';
                    account += '<td>' + response.data.accounts[i].describe + '</td>';
                    account += '<td class="fw-60"> <a href="#" data-toggle="modal" class=\'edit\' data-target=\'#editaccountmodal\' id=' + response.data.accounts[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'delete\' id=' + response.data.accounts[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
                    account += '</tr>';
                    $('#account_table').append(account);
                }
                $('#dataTables-account').DataTable({
                    pageLength: 5,
                    autoWidth: true
                });
            },
            error: function(xhr, status, error) {
                if (xhr.status == '401') {
                    getaccount()
                }
            }
        })
    }

    $('#accounttypeform').submit(function(event) {
        event.preventDefault();
        if ($('#accounttypeform').valid()) {
            var accounttypeform = $(this);
            var form_data = JSON.stringify(accounttypeform.serializeObject());
            addupdatesacco();
            // submit form data to api
            async function addupdatesacco() {
                // start ajax loader
                $.ajax({
                    url: base + "settings/accounts",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addaccount").modal('hide');
                        accounttypeform[0].reset();
                        $("#dataTables-account").DataTable().clear().destroy();
                        var icon = 'success';
                        var message = 'account type added'
                        sweetalert(icon, message)
                        getaccount();
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(getaccount);
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
    $(document).delegate('.delete', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id');
        deleteaccounts()
        async function deleteaccounts() {
            $.ajax({
                url: base + "settings/accounts/" + id,
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "DELETE",
                success: function(response) {
                    $("#dataTables-account").DataTable().clear().destroy();
                    var icon = 'success'
                    var message = 'account type deleted'
                    sweetalert(icon, message)
                    getaccount()
                },
                error: function(xhr) {
                    if (xhr.status == '401') {
                        authchecker(deleteaccounts)
                    } else {
                        var icon = 'warning'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                    }

                }
            })
        }
    });
    $(document).delegate('.edit', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
        geteditaccounts()
        async function geteditaccounts(){
        $.ajax({
            url: base + "settings/accounts/"+id,
            method: "GET",
            dataType: "json",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                $('#name_update').val(response.data.accounts[0].name);
                $('#id_update').val(response.data.accounts[0].id);
                $('#describe_update').val(response.data.accounts[0].describe);
                $('#charge_update').val(response.data.accounts[0].charge);
                $('#balance_update').val(response.data.accounts[0].balance);
                $('#editaccountmodal').modal('show');
            },
            error: function(xhr){
                if (xhr.responseJSON.messages) {
                    authchecker(geteditaccounts)
                }
            }
        })
        }
    });
    $('#accounttypeformedit').submit(function(event) {
        event.preventDefault();
        if ($('#accounttypeformedit').valid()) {
            var accounttypeformedit = $(this);
            var form_data = JSON.stringify(accounttypeformedit.serializeObject());
            var account_id = $('#id_update').val();
                editaccount();
            async function editaccount() {
                $.ajax({
                    url: base+"settings/accounts/"+account_id,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    cache: false,
                    data: form_data,
                    success: function(response) {
                        $("#editaccountmodal").modal('hide');
                        accounttypeformedit[0].reset();
                        $("#dataTables-account").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'account type edited'
                        sweetalert(icon, message)
                        getaccount()

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(editaccount);
                        } else {
                            var icon = 'warning'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                        }
                    }
                })
                return false;
            }
        }
    });

});

$(document).ready(function() {
    getshare()
    async function getshare() {
        $.ajax({
            url: base + "settings/shares",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var account = '';
                    account += '<tr>';
                    account += '<td>' + no + '</td>';
                    account += '<td>' + response.data.shares[i].name + '</td>';
                    account += '<td>' + numberWithCommas(response.data.shares[i].price) + '</td>';
                    account += '<td>' + response.data.shares[i].limit + '</td>';
                    account += '<td class="fw-60"> <a href="#" data-toggle="modal" class=\'editshare\' data-target=\'#editsharemodal\' id=' + response.data.shares[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'deleteshare\' id=' + response.data.shares[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
                    account += '</tr>';
                    $('#share_table').append(account);
                }
                $('#dataTables-share').DataTable({
                    pageLength: 5,
                    autoWidth: true
                });
            },
            error: function(xhr,status,error) {
                if (xhr.status == '401') {
                    authchecker(getshare)
                }
            }
        })
    }

    $('#addshareform').submit(function(event) {
        event.preventDefault();
        if ($('#addshareform').valid()) {
            var addshareform = $(this);
            var form_data = JSON.stringify(addshareform.serializeObject());
            addshare()
            async function addshare() {
                $.ajax({
                    url: base + "settings/shares",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addshare").modal('hide');
                        addshareform[0].reset();
                        $("#dataTables-share").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'share type added'
                        sweetalert(icon, message)
                        getshare()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addshare);
                        } else {
                        var icon = 'warning'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                        }
                    }
                })
                return false;
            }
        }
    });
    $(document).delegate('.deleteshare', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id');
        deleteshare()
        async function deleteshare(){
        $.ajax({
            url: base + "settings/shares/" + id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "DELETE",
            success: function() {
                $("#dataTables-share").DataTable().clear().destroy();
                var icon = 'warning'
                var message = 'share type deleted'
                sweetalert(icon, message)
                getshare()
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    authchecker(deleteshare)
                }
            }
        })
        }
    });
    $(document).delegate('.editshare', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
        geteditshare()
        async function geteditshare(){
        $.ajax({
            url: base + "settings/shares/" + id,
            method: "GET",
            dataType: "json",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                $('#name_share').val(response.data.share[0].name);
                $('#id_share').val(response.data.share[0].id);
                $('#price_share').val(response.data.share[0].price);
                $('#limit_share').val(response.data.share[0].limit);
                $('#editsharemodal').modal('show');
            },
            error: function(xhr){
                if (xhr.status == '401') {
                    authchecker(geteditshare)
                }
            }
        })
        }
    });
    $('#editshareform').submit(function(event) {
        event.preventDefault();
        if ($('#editshareform').valid()) {
            var editshareform = $(this);
            var form_data = JSON.stringify(editshareform.serializeObject());
            var shareid = $('#id_share').val()
            editshare()
            async function editshare() {
                $.ajax({
                    url: base + "settings/accounts/" + shareid,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    cache: false,
                    data: form_data,
                    success: function(response) {
                        $("#editsharemodal").modal('hide');
                        editshareform[0].reset();
                        $("#dataTables-share").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'share type edited'
                        sweetalert(icon, message)
                        getshare()

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(getshare);
                        } else {
                            var icon = 'success'
                            var message = xhr.responseJSON.messages
                            sweetalert(icon, message)
                        }
                    }

                });
                return false;
            }
        }
    });

});

$(document).ready(function() {
    getLoan()
    async function getLoan() {

        $.ajax({
            url: base + "settings/loans",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var loan = '';
                    loan += '<tr>';
                    loan += '<td>' + no + '</td>';
                    loan += '<td>' + response.data.loans[i].name + '</td>';
                    loan += '<td>' + response.data.loans[i].interest + '</td>';
                    loan += '<td>' + response.data.loans[i].penalty + '</td>';
                    loan += '<td>' + response.data.loans[i].period + '</td>';
                    loan += '<td>' + response.data.loans[i].frequency + '</td>';
                    loan += '<td>' + numberWithCommas(response.data.loans[i].fee) + '</td>';
                    loan += '<td>' + response.data.loans[i].notes + '</td>';
                    loan += '<td class="fw-60"> <a href="#" data-toggle="modal" class=\'editloan\' data-target=\'#editloanmodal\' id=' + response.data.loans[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'deleteloan\' id=' + response.data.loans[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
                    loan += '</tr>';
                    $('#loan_table').append(loan);
                }
                $('#dataTables-loan').DataTable({
                    pageLength: 5,
                    autoWidth: true
                });
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    getLoan()
                }
            }
        })
    }

    $('#addloanform').submit(function(event) {
        event.preventDefault();
        if ($('#addloanform').valid()) {
            var addloanform = $(this);
            var form_data = JSON.stringify(addloanform.serializeObject());
            addloan()
            async function addloan() {
                $.ajax({
                    url: base + "settings/loans",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addloanmodal").modal('hide');
                        addloanform[0].reset();
                        $("#dataTables-loan").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'loan type edited'
                        sweetalert(icon, message)
                        getLoan()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addloan)
                        } else {
                            var icon = 'success'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                        }
                    }

                });
                return false;
            }
        }
    });
    $(document).delegate('.deleteloan', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id');
        deleteloan()
        async function deleteloan(){
        $.ajax({
            url: base + "settings/loans/" + id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "DELETE",
            success: function() {
                $("#dataTables-loan").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'loan type added'
                        sweetalert(icon, message)
                        getLoan()
            },
            error: function(xhr) {
                if (xhr.status) {
                        authchecker(deleteloan)
                }
                var icon = 'success'
                var message = xhr.responseJSON.messages
                sweetalert(icon, message)
            }
            })
        }
    });
    $(document).delegate('.editloan', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
        getLoanedit()
        async function getLoanedit(){
        $.ajax({
            url: base + "settings/loans/" + id,
            method: "GET",
            dataType: "json",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                $('#name_loan').val(response.data.loan[0].name);
                $('#id_loan').val(response.data.loan[0].id);
                $('#interest_loan').val(response.data.loan[0].interest);
                $('#period_loan').val(response.data.loan[0].period);
                $('#penalty_loan').val(response.data.loan[0].penalty);
                $('#frequency_loan').val(response.data.loan[0].frequency);
                $('#fee_loan').val(response.data.loan[0].fee);
                $('#notes_loan').val(response.data.loan[0].notes);
                $('#editloanmodal').modal('show');
            },
            error: function(xhr){
                if (xhr.status == '401') {
                        authchecker(getLoanedit)
                }
            }
        })
    }
    });
    $('#editloanform').submit(function(event) {
        event.preventDefault();
        if ($('#editloanform').valid()) {
            var editloanform = $(this);
            var form_data = JSON.stringify(editloanform.serializeObject());
            var loanid = $('#id_loan').val();
            editLoan();
            async function editLoan() {
                $.ajax({
                    url: base + "settings/loans/" + loanid,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    cache: false,
                    data: form_data,
                    success: function(response) {
                        $("#editloanmodal").modal('hide');
                        editloanform[0].reset();
                        $("#dataTables-loan").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'loan type edited'
                        sweetalert(icon, message)
                        getLoan()
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(editLoan);
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

$(document).ready(function() {
    getCapital()
    async function getCapital() {
        $.ajax({
            url: base + "settings/capital",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var capital = '';
                    capital += '<tr>';
                    capital += '<td>' + no + '</td>';
                    capital += '<td>' + response.data.capital[i].name + '</td>';
                    capital += '<td>' + numberWithCommas(response.data.capital[i].amount) + '</td>';
                    capital += '<td>' + response.data.capital[i].date + '</td>';
                    capital += '</tr>';
                    $('#capital_table').append(capital);
                }
                $('#dataTables-capital').DataTable({
                    pageLength: 5,
                    autoWidth: true
                });
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    getCapital()
                }
            }
        })
    }

    $('#addcapitalform').submit(function(event) {
        event.preventDefault();
        if ($('#addcapitalform').valid()) {
            var addcapitalform = $(this);
            var form_data = JSON.stringify(addcapitalform.serializeObject());
            addcapital()
            async function addcapital() {
                // start ajax loader
                $.ajax({
                    url: base + "settings/capital",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addcapitalmodal").modal('hide');
                        addcapitalform[0].reset();
                        $("#dataTables-capital").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'new capital added'
                        sweetalert(icon, message)
                        getCapital()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addcapital);
                        }
                        var icon = 'success'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                    }

                });
                return false;
            }
        }
    })
});

$(document).ready(function() {
    getIncome()
    async function getIncome() {
        $.ajax({
            url: base + "settings/inpense/income",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var income = '';
                    income += '<tr>';
                    income += '<td>' + no + '</td>';
                    income += '<td>' + response.data.inpenses[i].name + '</td>';
                    income += '<td class="fw-60"> <a href="#" data-toggle="modal" class=\'editincome\' data-target=\'#editincomemodal\' id=' + response.data.inpenses[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'deleteinpense\' id=' + response.data.inpenses[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
                    income += '</tr>';
                    $('#income_table').append(income);
                }
                $('#dataTables-income').DataTable({
                    pageLength: 5,
                    autoWidth: true
                });
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    getIncome()
                }
            }
        })
    }

    $('#addincomeform').submit(function(event) {
        event.preventDefault();
        if ($('#addincomeform').valid()) {
            var addincomeform = $(this);
            var form_data = JSON.stringify(addincomeform.serializeObject());
            addIncome()
            async function addIncome() {
                $.ajax({
                    url: base + "settings/inpense",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addincomemodal").modal('hide');
                        addincomeform[0].reset();
                        $("#dataTables-income").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'saved successfully'
                        sweetalert(icon, message)
                        getIncome()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addIncome);
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
    $(document).delegate('.deleteincome', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id');
        deleteincome()
        async function deleteincome(){
        $.ajax({
            url: base + "settings/inpense/" + id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "DELETE",
            success: function() {
                $("#dataTables-income").DataTable().clear().destroy();
                var icon = 'success'
                var message = 'deleted successfully'
                sweetalert(icon, message)
                getIncome()
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    authchecker(deleteincome)
                }
                var icon = 'error'
                var message = xhr.responseJSON.messages
                sweetalert(icon, message)
            }
        })
    }
    });
    $(document).delegate('.editincome', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('id')
            geteditincome()
        async function geteditincome(){
        $.ajax({
            url: base + "settings/inpense/" + id,
            method: "GET",
            dataType: "json",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            success: function(response) {
                $('#name_income').val(response.data.inpense[0].name);
                $('#id_income').val(response.data.inpense[0].id);
                $('#editincomemodal').modal('show');
            },
            error: function(xhr){
                    if (xhr.responseJSON) {
                        authchecker(geteditincome)
                    }
            }
        })
    }
    });
    $('#editincomeform').submit(function(event) {
        event.preventDefault();
        if ($('#editincomeform').valid()) {
            var editincomeform = $(this);
            var form_data = JSON.stringify(editincomeform.serializeObject());
            var incomeid = $('#id_income').val();
                editIncome()
            async function editIncome() {
                $.ajax({
                    url: base + "settings/inpense/" + incomeid,
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "PATCH",
                    contentType: 'application/json',
                    cache: false,
                    data: form_data,
                    success: function(response) {
                        $("#editincomemodal").modal('hide');
                        editincomeform[0].reset();
                        $("#dataTables-income").DataTable().clear().destroy();
                        var icon = 'success'
                        var message ='edited successfully'
                        sweetalert(icon, message)
                        getIncome();

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(editIncome);
                        }
                        var icon = 'error'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                    }

                });
                return false;
            }
        }
    });

});

$(document).ready(function() {
        getExpense()
    async function getExpense() {
        $.ajax({
            url: base + "settings/inpense/expense",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var expense = '';
                    expense += '<tr>';
                    expense += '<td>' + no + '</td>';
                    expense += '<td>' + response.data.inpenses[i].name + '</td>';
                    expense += '<td class="fw-60"> <a href="#" data-toggle="modal" class=\'editexpense\' data-target=\'#editexpensemodal\' id=' + response.data.inpenses[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'deleteexpense\' id=' + response.data.inpenses[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
                    expense += '</tr>';
                    $('#expense_table').append(expense);
                }
                $('#dataTables-expense').DataTable({
                    pageLength: 5,
                    autoWidth: true
                });
            },
            error: function(xhr) {
                if (xhr.status == '401') {
                    getExpense()
                }
            }
        })
    }
    $('#addexpenseform').submit(function(event) {
        event.preventDefault();
        if ($('#addexpenseform').valid()) {
            var addexpenseform = $(this);
            var form_data = JSON.stringify(addexpenseform.serializeObject());
            addExpense()
            async function addExpense() {
                $.ajax({
                    url: base + "settings/inpense",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        $("#addexpensemodal").modal('hide');
                        addexpenseform[0].reset();
                        $("#dataTables-expense").DataTable().clear().destroy();
                        var icon = 'success'
                        var message = 'expense added'
                        sweetalert(icon, message)
                        getExpense()
                        return;
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            authchecker(addExpense);
                        }
                        var icon = 'warning'
                        var message = xhr.responseJSON.messages
                        sweetalert(icon, message)
                    }
                })
                return false;
            }

        }
    })
});
// image logo functions
$(document).ready(function() {
    getLogo()
async function getLogo() {
    $.ajax({
        url: base + "logos",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
        },
        type: "GET",
        success: function(response) {
            $('#image-logo').append('<img src=' +response.data.logo+ ' height="80px" class="float-right" alt="no logo">')
        },
        error: function(xhr) {
            if (xhr.status == '401') {
                getLogo()
            }
        }
    })
}
$('#logo-upload').submit(function(event) {
    event.preventDefault();
    if ($('#logo-upload').valid()) {
        var formData = new FormData(this)
        addLogo()
        async function addLogo() {
            $.ajax({
                url: base + "logos",
                headers: {'Authorization': localStorage.token},
                type: "POST",
                contentType: false,
                processData: false,
                cache: false,
                data: formData,
                success: function(response) {
                    $('#image-logo').html("");
                    getLogo()
                    var icon = 'success'
                    var message = 'Logo uploaded'
                    sweetalert(icon,message)
                    return;
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        authchecker(addLogo);
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
});


$(document).ready(function() {
  getaccounts();
  async function getaccounts() {
      $.ajax({
          url: base+"accountgroup/accounts",
          headers: {
              'Authorization': localStorage.token,
              'Content-Type': 'application/json'
          },
          type: "GET",
          success: function(response) {
              nums = response.data.rows_returned;
              for (var i = 0; i < nums; i++) {
                  var accounts = '';
                  accounts += '<option value=' + response.data.account[i].id + '>' + response.data.account[i].account+'</option>';
                  $('#saccoaccount').append(accounts);
              }
              $('#saccoaccount').select2({
                  theme: 'bootstrap5',
                  width: 'resolve',
                  dropdownParent: $("#saccowitdrawsettingsform")
              });

          },
          error: function(xhr){
                  if (xhr.status == '401') {
                      getaccounts();
                  }
          }
      })
  }
async function  getWIthdrawSetting(){
  $.ajax({
      url: base + "accountgroup/withdrawsettings",
      headers: {
          'Authorization': localStorage.token,
          'Content-Type': 'application/json'
      },
      type: "GET",
      success: function(response) {
          var nums = response.data.rows_returned;
          var no = 0;
          for (var i = 0; i < nums; i++) {
              no++
              var withdrawsetting = '';
              withdrawsetting += '<tr>';
              withdrawsetting += '<td>' + no + '</td>';
              withdrawsetting += '<td>' + response.data.withdrawsettings[i].account + '</td>';
              withdrawsetting += '<td>' + numberWithCommas(response.data.withdrawsettings[i].minbalance) + '</td>';
              withdrawsetting += '<td>' + numberWithCommas(response.data.withdrawsettings[i].charge) + '</td>';
              withdrawsetting += '<td>' + numberWithCommas(response.data.withdrawsettings[i].amountfrom) + '</td>';
              withdrawsetting += '<td>' + numberWithCommas(response.data.withdrawsettings[i].amountto) + '</td>';
              withdrawsetting += '<td>' +  response.data.withdrawsettings[i].modeofdeduction + '</td>';
              withdrawsetting += '<td class="fw-60"> <a href="#" data-toggle="modal" class=\'edit\' data-target=\'#editaccountmodal\' id=' + response.data.withdrawsettings[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> <a href="#" class=\'delete\' id=' + response.data.withdrawsettings[i].id + '><i class="text-danger fa fa-remove fa-1.5x"></i></a></td>';
              withdrawsetting += '</tr>';
              $('#withdrawsetting_table').append(withdrawsetting);
          }
          $('#dataTables-withdrawsetting').DataTable({
              pageLength: 5,
              autoWidth: true
          });
      },
      error: function(xhr, status, error) {
          if (xhr.status == '401') {
              getaccount()
          }
      }
  });
}
getWIthdrawSetting()
  // var req = "Required.";
  //
  // $("#saccowitdrawsettingsform").validate();
  //
  // $(".amountto").each(function(){
  // $(this).rules("add", {
  // required:true,
  // messages:{
  //   required:req
  // }
  // });
  // });
  $('#saccowitdrawsettingsform').validate({
    rules: {
      minimumbalance: {
        required: true
      },
      account:{
        required: true
      }
    },
    messages: {
      minimumbalance: {
        required: "Please enter minimum balance",
      },
      account: {
     required: "Please select account",
    },
    },
          errorPlacement: function (error, element) {
            if(element.hasClass('select2') && element.next('.select2-container').length) {
                error.insertAfter(element.next('.select2-container'));
            }
          element.closest('.form-group').append(error);
          }
  })

  $('#saccowitdrawsettingsform').submit(function(event) {
    // $("#amountfrom-error").attr("style", "display:none");
      event.preventDefault();
          // adding rules for inputs with class 'required'
          var amounto = '';
          var amountfrom = '';
          var charge = '';
          var mod = '';
          $('.amountfrom').each(function(){
              if($(this).val()){
                amountfrom = '';
                $('.amountfrom').removeClass('.error');

              }else{
                amountfrom = 'not';
                $('.amountfrom').addClass('.error');
              }
          });
          $('.amountto').each(function(){
              if($(this).val()){
                amounto = '';
                $('.amountto').removeClass('.error');

              }else{
                amounto = 'not';
                $('.amountto').addClass('.error');
              }
          });
          $('.charge').each(function(){
              if($(this).val()){
                charge = '';
                $('.charge').removeClass('error');

              }else{
                charge = 'not';
                $('.charge').addClass('error');
              }
          });

          if($('.modeofdeduction').filter(function(){ return !this.value.trim(); }).length){
            mod='not'
           $('.modeofdeduction').addClass('error');
          }
          else
          {
            mod='';
           $('.modeofdeduction').removeClass('error');
          }
          if(amounto != '' || amountfrom != '' || charge != ''|| mod != '')
          {
           return false;
          }
          else {

        if($('#saccowitdrawsettingsform').valid()) {
        var withdrawsetting = $(this);

          var Directory = {};
          Directory.account = $('.title').find(":selected").val();
          Directory.minimumbalance = $("div#Directory input.minimumbalance").val();
          Directory.withdrawsettings = [];
          $("div#Directory div.person").each(function() {
            Directory.withdrawsettings.push({
              amountto:  $(this).children("input.amountto").val(),
              amountfrom: $(this).children("input.amountfrom").val(),
              charge: $(this).children("input.charge").val(),
              modeofdeduction: $(this).children(".modeofdeduction").find(":selected").val()
            });
          });
          var form_data= JSON.stringify(Directory);
          addWithdrawSetting()
          async function addWithdrawSetting() {
            $.ajax({
                url: base + "accountgroup/withdrawsettings",
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "POST",
                contentType: 'application/json',
                data: form_data,
                success: function(response) {
                    withdrawsetting[0].reset();
                    $("#dataTables-withdrawsetting").DataTable().clear().destroy();
                    var icon = 'success';
                    var message = 'withdraw setting saved'
                    sweetalert(icon, message)
                    getWIthdrawSetting();
                    return;
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        authchecker(getaccount);
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
      }
  });
});

$(document).ready(function() {
    getChartsOfAccounts()
    async function getChartsOfAccounts() {
        $.ajax({
            url: base + "chartofaccounts",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                var nums = response.data.rows_returned;
                var no = 0;
                for (var i = 0; i < nums; i++) {
                    no++
                    var accountgroups = '';
                    accountgroups += '<tr style="background:#D9D9D9;">';
                    accountgroups += '<td class="col-md-2">' + response.data.accountgroups[i].accountgroup + '</td>';
                    accountgroups += '<td class="col-md-2">' + response.data.accountgroups[i].accountg_groupcode + '</td>';
                    accountgroups += '<td colspan="8"></td>';
                    accountgroups += '</tr>';
                    var subaccounts =response.data.accountgroups[i].subaccounts.length;
                    var subaccountsdata =response.data.accountgroups[i].subaccounts;
                    for (var x = 0; x < subaccounts; x++) {

                      var accountsCount =subaccountsdata[x].accounts.length;
                      var accountsData =subaccountsdata[x].accounts;
                      var accounts='';
                      for (var y = 0; y < accountsCount; y++) {
                        //
                        // accounts +='<td class="col-md-2">' + accountsData[y].accountname + '</td>';
                        // accounts +='<td class="col-md-2">' + accountsData[y].accountcode + '</td>';
                        // var subaccountsrows='';
                        accountgroups += '<tr style="background:#F2F2F2;">';
                        accountgroups += '<td class="col-md-2"></td>';
                        accountgroups += '<td class="col-md-2"></td>';
                        accountgroups += '<td class="col-md-2">' + subaccountsdata[x].name + '</td>';
                        accountgroups += '<td class="col-md-2">' + subaccountsdata[x].code + '</td>';
                        accountgroups += '<td class="col-md-2">' + accountsData[y].accountname + '</td>';
                        accountgroups += '<td class="col-md-2">' + accountsData[y].accountcode + '</td>';
                        // accountgroups += accounts;
                        // accountgroups += '<td class="col-md-2"></td>';
                        accountgroups += '</tr>';
                      }

                    }
                    $('#datatable').append(accountgroups);
                 }
                // $('#dataTables-account').DataTable({
                //     pageLength: 5,
                //     autoWidth: true
                // });
            },
            error: function(xhr, status, error) {
                if (xhr.status == '401') {
                    getChartsOfAccounts()
                }
            }
        })
    }
  });

//account groups
$(document).ready(function(){
  // get account groups
                accountGroups()
                async function accountGroups() {
                    $.ajax({
                        url: base+"accountgroup/groupaccount",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "GET",
                        success: function(response) {
                            // console.log(response);
                            var nums = response.data.account_group.length;
                            var no = 0
                            for (var i = 0; i < nums; i++) {
                                no++
                                var group_accounts = '';
                                group_accounts += '<tr>';
                                group_accounts += '<td>' +no+ '</td>';
                                group_accounts += '<td>' +response.data.account_group[i].name+ '</td>';
                                group_accounts += '<td>' +response.data.account_group[i].code+ '</td>';
                                group_accounts += '</tr>';

                                $("#group_accountss").append(group_accounts);
                            }
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == '401') {
                                accountGroups()
                            }
                        }
                    })
 }

 // save sacco-account groups
                $('#saveaccountgroup').submit(function(event) {
                    event.preventDefault();
                    if ($('#saveaccountgroup').valid()) {

                        var saveaccountgroup = $(this);
                        var form_data = JSON.stringify(saveaccountgroup.serializeObject());
                        //   cancelIdleCallback
                        addaccountgroup();
                        // submit form data to api
                        async function addaccountgroup() {
                            // start ajax loader
                            $.ajax({
                                url: base+"accountgroup/groupaccount",
                                headers: {
                                    'Authorization': localStorage.token,
                                    'Content-Type': 'application/json'
                                },
                                type: "POST",
                                contentType: 'application/json',
                                data: form_data,
                                success: function(response) {
                                    // console.log(response);
                                    saveaccountgroup[0].reset();
                                    $("#table_group_accounts").DataTable().clear().destroy();
                                    accountGroups();
                                    var icon = 'success';
                                    var message = 'New group account added';
                                    sweetalert(icon, message);
                                    return;
                                },
                                error: function(xhr, status, error) {
                                    // console.log(xhr.status);
                                    if (xhr.status === 401) {
                                        authchecker(addaccountgroup);
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

//sub accounts$
$(document).ready(function(){
  getSaccoBranches()

    async function getSaccoBranches() {
        $.ajax({
            url: base+"branches",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                // console.log(response)
                nums = response.data.rows_returned;
                for (var i = 0; i < nums; i++) {
                    var branches = '';
                    branches += '<option value=' + response.data.branches[i].id + '>' + response.data.branches[i].name + ' | '+response.data.branches[i].address +' | '+response.data.branches[i].code+ ' | 0'+response.data.branches[i].status+'</option>';
                    $('#sacco-branch').append(branches);
                    $('#editsacco-branch').append(branches);
                }
                $('#sacco-branch').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#save_subaccount_group")
                });

                $('#editsacco-branch').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#editsubaccountgroupform")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getSaccoBranches()
                    }
            }
        })
    }

    // sub account groups for accounts
  $("#accountgroup").on("change", function(){
      var account_group= $(this).val();
      // console.log(account_group);
      get_subAccountGroups();
      async function get_subAccountGroups() {
          if(account_group){

              $.ajax({
                  url: base+"accountgroup/getsubaccounts/"+account_group,
                  headers: {
                      'Authorization': localStorage.token,
                      'Content-Type': 'application/json'
                  },
                  type: "GET",
                  success: function(response) {
                      // console.log(response)
                      nums = response.data.rows_returned;
                      for (var i = 0; i < nums; i++) {
                          var sub_accountgroups = '';
                          sub_accountgroups += '<option value=' + response.data.subaccount[i].id + '>' + response.data.subaccount[i].subaccount + ' | '+response.data.subaccount[i].code +' | '+response.data.subaccount[i].accountgroup +' | '+response.data.subaccount[i].saccobranch +' </option>';
                          $('#sub-account-group').append(sub_accountgroups);
                      }
                      $('#sub-account-group').select2({
                          theme: 'bootstrap5',
                          width: 'resolve',
                          dropdownParent: $("#save_accounts_form")
                      });
                  },
                  error: function(xhr){
                          if (xhr.status == '401') {
                              get_subAccountgroups()
                          }
                  }
              })
          }
          else{
          // $("#sub-account-group").html('<option value="">Select account group first</option>');
          $('.subaccountgroup').html('<option value="">Select account group first</option>');
          }
      }
  });
    // account groups for accounts
        getsub_accountgroups();
        async function getsub_accountgroups() {
            $.ajax({
                url: base+"accountgroup/groupaccount",
                headers: {
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
                },
                type: "GET",
                success: function(response) {
                    // console.log(response)
                    nums = response.data.rows_returned;
                    for (var i = 0; i < nums; i++) {
                        var sub_accountgroups = '';
                        sub_accountgroups += '<option value=' + response.data.account_group[i].id + '>' + response.data.account_group[i].name + ' | '+response.data.account_group[i].code +'</option>';
                        $('#accountgroup').append(sub_accountgroups);
                    }
                    $('#accountgroup').select2({
                        theme: 'bootstrap5',
                        width: 'resolve',
                        dropdownParent: $("#save_accounts_form")
                    });
                },
                error: function(xhr){
                        if (xhr.status == '401') {
                            getsub_accountgroups()
                        }
                }
            })
        }
    // account groups for subaccount
    getAccountGroups();
    async function getAccountGroups() {
        $.ajax({
            url: base+"accountgroup/groupaccount",
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
            },
            type: "GET",
            success: function(response) {
                // console.log(response)
                nums = response.data.rows_returned;
                for (var i = 0; i < nums; i++) {
                    var accountgroups = '';
                    accountgroups += '<option value=' + response.data.account_group[i].id + '>' + response.data.account_group[i].name + ' | '+response.data.account_group[i].code +'</option>';
                    $('#account-group').append(accountgroups);
                    $('#editaccount-group').append(accountgroups);
                }
                $('#account-group').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#save_subaccount_group")
                });
                $('#editaccount-group').select2({
                    theme: 'bootstrap5',
                    width: 'resolve',
                    dropdownParent: $("#editsubaccountgroupform")
                });
            },
            error: function(xhr){
                    if (xhr.status == '401') {
                        getAccountGroups()
                    }
            }
        })
    }


  subsub_accountgroups()
  async function subsub_accountgroups() {
      $.ajax({
          url: base+"accountgroup/subaccounts",
          headers: {
              'Authorization': localStorage.token,
              'Content-Type': 'application/json'
          },
          type: "GET",
          success: function(response) {
              // console.log(response);
              var nums = response.data.subaccount.length;
              var no = 0
              for (var i = 0; i < nums; i++) {
                  no++
                  var sub_accounts = '';
                  sub_accounts += '<tr>';
                  sub_accounts += '<td> </td>';
                  sub_accounts += '<td>' +no+ '</td>';
                  sub_accounts += '<td>' +response.data.subaccount[i].code+ '</td>';
                  sub_accounts += '<td>' +response.data.subaccount[i].subaccount+ '</td>';
                  sub_accounts += '<td>' +response.data.subaccount[i].accountgroup+ '</td>';
                  sub_accounts += '<td>' +response.data.subaccount[i].saccobranch+ '</td>';
                  sub_accounts += '<td class="fw-60 text-center"><a href="#" data-toggle="modal" class=\'subaccount-delete\' data-target=\'#deletesubaccountsmodal\' id=' + response.data.subaccount[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-danger fa fa-trash-alt fa-1.5x"></i></a>&nbsp; <a href="#" data-toggle="modal" class=\'subaccount-edit\' data-target=\'#editsubaccountsmodal\' id=' + response.data.subaccount[i].id + ' data-backdrop="static" data-keyboard="false"><i class="text-warning fa fa-edit fa-1.5x"></i></a> </td>';
                  sub_accounts += '</tr>';

                  $("#sub_accountss").append(sub_accounts);
              }

              $('#dataTables-sub-accounts').DataTable({
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
                  subsub_accountgroups()
              }
          }
      })
  }

  // save sub-account groups
    $('#save_subaccount_group').submit(function(event) {
        event.preventDefault();
        if ($('#save_subaccount_group').valid()) {

            var save_subaccount_group = $(this);
            var form_data = JSON.stringify(save_subaccount_group.serializeObject());
            //   cancelIdleCallback
            addsubaccountgroup();
            // submit form data to api
            async function addsubaccountgroup() {
                // start ajax loader
                $.ajax({
                    url: base+"accountgroup/subaccounts",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    type: "POST",
                    contentType: 'application/json',
                    data: form_data,
                    success: function(response) {
                        // console.log(response);
                        save_subaccount_group[0].reset();
                        $("#dataTables-sub-accounts").DataTable().clear().destroy();
                        subsub_accountgroups();
                        var icon = 'success';
                        var message = 'Sub account added';
                        sweetalert(icon, message);
                        return;
                    },
                    error: function(xhr, status, error) {
                        // console.log(xhr.status);
                        if (xhr.status === 401) {
                            authchecker(addsubaccountgroup);
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

//get accounts-Settings
$(document).ready(function(){
  // get account
                  accounts();
                  async function accounts() {
                      $.ajax({
                          url: base+"accountgroup/accounts",
                          headers: {
                              'Authorization': localStorage.token,
                              'Content-Type': 'application/json'
                          },
                          type: "GET",
                          success: function(response) {
                              // console.log(response);
                              var nums = response.data.account.length;
                              var no = 0
                              for (var i = 0; i < nums; i++) {
                                  no++
                                  var accounts = '';
                                  accounts += '<tr>';
                                  accounts += '<td> </td>';
                                  accounts += '<td>' +no+ '</td>';
                                  accounts += '<td>' +response.data.account[i].account+ '</td>';
                                  accounts += '<td>' +response.data.account[i].code+ '</td>';
                                  accounts += '<td>' +response.data.account[i].subaccount+ '</td>';
                                  accounts += '<td>' +response.data.account[i].account_group+ '</td>';
                                  accounts += '<td>' +response.data.account[i].openingbalance+ '</td>';
                                  accounts += '<td>' +response.data.account[i].status+ '</td>';
                                  accounts += '<td class="fw-60 text-center"><a href="#"><i class="text-danger fa fa-trash fa-1.5x"></i></a> <a href="#"><i class="text-warning fa fa-edit fa-1.5x"></i></a></td>';
                                  accounts += '</tr>';

                                  $("#accountss").append(accounts);
                              }
  // inoooooooo
                              $('#dataTables-accounts').DataTable({
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
                                  accounts()
                              }
                          }
                      })
                  }
                  // end get account


                 // save accounts
                  $('#save_accounts_form').submit(function(event) {
                      event.preventDefault();
                      if ($('#save_accounts_form').valid()) {

                          var save_accounts_form = $(this);
                          var form_data = JSON.stringify(save_accounts_form.serializeObject());
                          //   cancelIdleCallback
                          addaccount();
                          // submit form data to api
                          async function addaccount() {
                              // start ajax loader
                              $.ajax({
                                  url: base+"accountgroup/accounts",
                                  headers: {
                                      'Authorization': localStorage.token,
                                      'Content-Type': 'application/json'
                                  },
                                  type: "POST",
                                  contentType: 'application/json',
                                  data: form_data,
                                  success: function(response) {
                                      console.log(response);
                                      save_accounts_form[0].reset();
                                      $("#dataTables-accounts").DataTable().clear().destroy();
                                      accounts();
                                      var icon = 'success';
                                      var message = 'Account added';
                                      sweetalert(icon, message);
                                      return;
                                  },
                                  error: function(xhr, status, error) {
                                      // console.log(xhr.status);
                                      if (xhr.status === 401) {
                                          authchecker(addaccount);
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
                  // end save accounts
})

//fixed deposit setting
$(document).ready(function(){
  // retrive data for the select fields
    getAllAccounts()

  async function getAllAccounts() {
      $.ajax({
          url: base+"accountgroup/accounts",
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
                  accounts += '<option value=' + response.data.account[i].id + '>' + response.data.account[i].account + ' | '+response.data.account[i].code +' | '+response.data.account[i].account_group+ ' | 0'+response.data.account[i].status+'</option>';

                  $('#fixedepositaccount').append(accounts);

                  // console.log(accounts);
              }
              $('#fixedepositaccount').select2({
                  theme: 'bootstrap5',
                  width: 'resolve',
                  dropdownParent: $("#fixeddepositform")
              });

          },
          error: function(xhr){
                  if (xhr.status == '401') {
                      getAllAccounts()
                  }
              }
      })
  }

  // retrive interest payable account

  getinterestAccount();

  async function getinterestAccount() {
      $.ajax({
          url: base+"accountgroup/accounts",
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

                  accounts += '<option value="' + response.data.account[i].account + '">' + response.data.account[i].account + ' | '+response.data.account[i].code +' | '+response.data.account[i].account_group+ ' | 0'+response.data.account[i].status+'</option>';
                  $('#payableAccount').append(accounts);
                  // console.log(accounts);
              }


              $('#payableAccount').select2({
                  theme: 'bootstrap5',
                  width: 'resolve',
                  dropdownParent: $("#fixeddepositform")
              });


          },
          error: function(xhr){
              if (xhr.status == '401') {
                  getinterestAccount()
              }
          }
      })
  }
  // retrive expense accounts

  getexpenseAccount();

  async function getexpenseAccount() {
      $.ajax({
          url: base+"accountgroup/accounts",
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
                  accounts += '<option value="' + response.data.account[i].account + '">' + response.data.account[i].account + ' | '+response.data.account[i].code +' | '+response.data.account[i].account_group+ ' | 0'+response.data.account[i].status+'</option>';
                  $('#exp_interest').append(accounts);
                  // console.log(response.data.account[i].account);
              }


              $('#exp_interest').select2({
                  theme: 'bootstrap5',
                  width: 'resolve',
                  dropdownParent: $("#fixeddepositform")
              });


          },
          error: function(xhr){
                  if (xhr.status == '401') {
                      getinterestAccount()
                  }
          }
      })
  }

              // FIXED DEPOSIT SETTINGS
              $('#fixeddepositform').validate({
                  errorPlacement: function (error, element) {
                        if(element.hasClass('select2') && element.next('.select2-container').length) {
                            error.insertAfter(element.next('.select2-container'));
                        }
                      element.closest('.form-group').append(error);
                      }
              })
                  $('#fixeddepositform').submit(function(event) {
                      event.preventDefault();
                      if ($('#fixeddepositform').valid()) {

                          var fixeddepositform = $(this);
                          // var data = $('#payableAccount').val();
                          // var data =fixeddepositform.serializeObject();
                          var form_data = JSON.stringify(fixeddepositform.serializeObject());
                          //   cancelIdleCallback

                          console.log(form_data);
                          savefixeddeposit();
                          // submit form data to api
                          async function savefixeddeposit() {
                              // start ajax loader
                              $.ajax({
                                  url: base+"accountgroup/fixeddepositsetting",
                                  headers: {
                                      'Authorization': localStorage.token,
                                      'Content-Type': 'application/json'
                                  },
                                  type: "POST",
                                  contentType: 'application/json',
                                  data: form_data,
                                  success: function(response) {
                                      // console.log(response);
                                      fixeddepositform[0].reset();
                                      // $("#table_group_accounts").DataTable().clear().destroy();
                                      // accountGroups();
                                      var icon = 'success';
                                      var message = 'Setting Created';
                                      sweetalert(icon, message);
                                      return;
                                  },
                                  error: function(xhr, status, error) {
                                      // console.log(xhr.status);
                                      if (xhr.status === 401) {
                                          authchecker(savefixeddeposit);
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
                  // end save fixed deposit settings
});
//payment methodz
$(document).ready(function(){
        $('#paymentmethodform').validate({
        rules: {
          payment_method: {
                required: true
            },

        },
        messages: {
          payment_method: {
                required: "Please enter Payement Method!"
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

    $('#paymentmethodform').submit(function(event) {
        event.preventDefault();

        if($('#paymentmethodform').valid()){
          var paymentmethodform = $(this);
          var form_data =JSON.stringify(paymentmethodform.serializeObject()) ;


          addPaymentMethod();
          async function addPaymentMethod(){
            $.ajax({
              url: base+"paymentmethod",
              headers:{
                'Authorization':localStorage.token,
                'Content-Type' : 'application/json'
              },
              type: "POST",
              contentType:'application/json',
              // cache:'false',
              data: form_data,

              success: function(response){




                $('#payment_methods_table').DataTable().clear().destroy();
                paymentmethodform[0].reset();
                var message ="Payment Method Added!";
                var icon ="success";
                getpayments();
                sweetalert(icon,message);
                return;

              },
              error: function(xhr, status, error) {
                      console.log(xhr.status);
                          if (xhr.status === 401) {
                              authchecker(addPaymentMethod);
                            } else {
                             var icon = 'warning';
                            var message = xhr.responseJSON.messages;
                            sweetalert(icon, message);

                            }
                        }
            });

            return false;
          }
          // console.log(form_data);
        }


      });




      // initialise delete payment data
      $(document).delegate('.deletepaymode', 'click', function(event) {
            event.preventDefault();
            var id = $(this).attr('id');
            // console.log(id);
            getdeletedata();
            async function getdeletedata() {
                $.ajax({
                    url: base + "paymentmethod/" + id,
                    method: "GET",
                    dataType: "json",
                    headers: {
                        'Authorization': localStorage.token,
                        'Content-Type': 'application/json'
                    },
                    success: function(response) {
                        // console.log(response.data.paymentmethod[0].id);
                        $('#deletepayid').val(response.data.paymentmethod[0].id);
                        $('#exampleModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status == '401') {
                            authchecker(getdeletedata);
                        }
                    }
                })
            }
        });


        $('#deletepayform').submit(function(event) {
        event.preventDefault();

        if($('#deletepayform').valid()){
          var deletepayform = $(this);
          var delete_id = $('#deletepayid').val();
          // var delete_data =JSON.stringify(deletepayform.serializeObject()) ;
          // console.log(delete_id);

          deletePay();
          async function deletePay(){
            $.ajax({
              url: base+"paymentmethod/"+delete_id,
              headers:{
                'Authorization':localStorage.token,
                'Content-Type' : 'application/json'
              },
              type: "DELETE",
              contentType:'application/json',
              // cache:'false',
              success: function(response){
                $('#payment_methods_table').DataTable().clear().destroy();
                deletepayform[0].reset();
                var message ="Payment Method Deleted!";
                var icon ="success";
                getpayments();
                sweetalert(icon,message);
                $('#exampleModal').modal('hide');

                return;

              },
              error: function(xhr, status, error) {
                      console.log(xhr.status);
                          if (xhr.status === 401) {
                              authchecker(addPaymentMethod);
                            } else {
                             var icon = 'warning';
                            var message = xhr.responseJSON.messages;
                            sweetalert(icon, message);

                            }
                        }
            });

            return false;
          }
          // console.log(form_data);
        }


      });
        // fill payments table
        getpayments();
        async function getpayments(){

          $.ajax({
           url: base+"paymentmethod",
           headers:{
             'Authorization':localStorage.token,
             'Content-Type' : 'Application/json'
           },
           Type: "GET",

           success: function(response){
            //  console.log(response);

             var nums = response.data.paymentmethods.length;
             for(var i = 0; i<nums; i++){
               var paymentmethod = "";

               paymentmethod +='<tr>';
               paymentmethod +='<td>' +response.data.paymentmethods[i].paymentmethod+ '</td>';
               paymentmethod += '<td> <a class="fw-60"> <a href="#" data-toggle="modal" data-target="#exampleModal" class=\'deletepaymode\' id=' + response.data.paymentmethods[i].id + '><i class="text-danger fa fa-trash-alt fa-1.5x"></i></a></td>';
               paymentmethod +='</tr>';
               $('#payment_methods').append(paymentmethod);

             }
           },
           error: function(xhr, status, error) {
                        if (xhr.status == '401') {
                            authchecker(getpayments);
                        }
                    }
          });
        }

      });
