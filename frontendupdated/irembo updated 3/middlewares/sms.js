$(document).ready(function () {
    $("body").children().first().before($(".modal"));
        $('#allmembersmsform').submit(function(event) {
            event.preventDefault();
            if ($('#allmembersmsform').valid()) {
                var sendsmsmembers = $(this);
                var form_data = JSON.stringify(sendsmsmembers.serializeObject());
                sendsmsmember()
                async function sendsmsmember() {
                    $.ajax({
                        url: base + "communication/members",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "POST",
                        contentType: 'application/json',
                        data: form_data,
                        success: function(response) {
                            $("#allmembersmsmodal").modal('hide');
                            sendsmsmembers[0].reset();
                            var icon = 'success'
                            var message = 'message sent'
                            sweetalert(icon, message)
                            return;
                        },
                        error: function(xhr, status, error) {
                            // console.log(xhr);
                            if (xhr.status === 401) {
                                authchecker(sendsmsmember);
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

  $(document).ready(function () {
    $("body").children().first().before($(".modal"));
        $('#allstaffsmsform').submit(function(event) {
            event.preventDefault();
            if ($('#allstaffsmsform').valid()) {
                var staffsmsform = $(this);
                var form_data = JSON.stringify(staffsmsform.serializeObject());
                sendsmsstaff()
                async function sendsmsstaff() {
                    $.ajax({
                        url: base + "communication/staff",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "POST",
                        contentType: 'application/json',
                        data: form_data,
                        success: function(response) {
                            $("#allstaffsmsmodal").modal('hide');
                            staffsmsform[0].reset();
                            var icon = 'success'
                            var message = 'message sent'
                            sweetalert(icon, message)
                            return;
                        },
                        error: function(xhr, status, error) {
                            // console.log(xhr);
                            if (xhr.status === 401) {
                                authchecker(sendsmsstaff);
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
  
  $(document).ready(function () {
    $("body").children().first().before($(".modal"));
        $('#allmarketsmsform').submit(function(event) {
            event.preventDefault();
            if ($('#allmarketsmsform').valid()) {
                var marketsmsform = $(this);
                var form_data = JSON.stringify(marketsmsform.serializeObject());
                sendsmsmarket()
                async function sendsmsmarket() {
                    $.ajax({
                        url: base + "communication/market",
                        headers: {
                            'Authorization': localStorage.token,
                            'Content-Type': 'application/json'
                        },
                        type: "POST",
                        contentType: 'application/json',
                        data: form_data,
                        success: function(response) {
                            $("#allmarketsmsmodal").modal('hide');
                            marketsmsform[0].reset();
                            var icon = 'success'
                            var message = 'message sent'
                            sweetalert(icon, message)
                            return;
                        },
                        error: function(xhr, status, error) {
                            // console.log(xhr);
                            if (xhr.status === 401) {
                                authchecker(sendsmsmarket);
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