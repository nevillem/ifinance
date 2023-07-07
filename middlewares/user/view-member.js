$(document).ready(function () {

      getMemberBio();
      async function getMemberBio() {
         var url = window.location.href;
        var params = url.split('?').pop().split('=').pop();
        $.ajax({
          url: base + "members/" + localStorage.memberid1,
          headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
          },
          type: "GET",
          contentType: 'application/json',
          cache: false,
          success: function (response) {
            // console.log(response.data.members[0].images[0].imageurl);
            // $('#deposit_transaction').innerHTML("");
            // $("#saccologo").html('<img src="' + localStorage.logo + '" height="70px">');
            $("#full_name").html(response.data.members[0].firstname +' '+response.data.members[0].midlename +' '+response.data.members[0].lastname);
            $("#gender").html(response.data.members[0].gender);
            $("#dob").html(response.data.members[0].dob);
            $("#member_contact").html('0'+response.data.members[0].contact);
            $("#member_join_date").html(response.data.members[0].doj);
            $("#status").html(response.data.members[0].status);
            $("#member_identification").html(response.data.members[0].identification);
            $("#member_address").html(response.data.members[0].address);
            $("#member_employment_status").html(response.data.members[0].employment_status);
            $("#member_gross_income").html(response.data.members[0].gross_income);
            $("#member_marital_status").html(response.data.members[0].marital_status);
            $("#member_group").html(response.data.members[0].sacco_groups);
            $("#attach").html(response.data.members[0].attach);
            // $("#comments").html(response.data.members[0].member_marital_status);
            var num =response.data.members[0].images.length;

            if(num >0){
            $('.bio-image').attr('src', response.data.members[0].images[0].imageurl);
          }
          var nextofkinnum =response.data.members[0].nextofkin.length;
          if (nextofkinnum) {

            for (var i = 0; i < nextofkinnum; i++) {
              var nextofkin='';
              nextofkin += '<div class="row-container d-flex align-items-center justify-content-between bio-info">';
              nextofkin += '<div>' +response.data.members[0].nextofkin[i].firstname+ '</div>';
              nextofkin += '<div>' +response.data.members[0].nextofkin[i].lastname+ '</div>';
              nextofkin += '<div>' +"0"+ response.data.members[0].nextofkin[i].contact+ '</div>';
              nextofkin += '</div';
              $('#nextofkin').append(nextofkin);

            }
          }
          else {
            var nextofkin='';
            nextofkin += '<div class="row-container d-flex align-items-center justify-content-between bio-info">';
            nextofkin += '<div class="col-12">No Accounts opened yet</div>';
            // accounts += '<div>' +response.data.members[0].accounts[i].dateopened+ '</div>';
            // accounts += '<div>' +"0"+ response.data.members[0].accounts[i].contact+ '</div>';
            nextofkin += '</div';
            $('#nextofkin').append(nextofkin);

          }
          var accountsnum =response.data.members[0].accounts.length;
            var dataa =response.data.members[0].accounts;
            if (accountsnum >0) {
              for (var x = 0; x < accountsnum; x++) {
                console.log(dataa[x].name);
                var accounts='';
                accounts += '<div class="row-container d-flex align-items-center justify-content-between bio-info">';
                accounts += '<div class="bio-heading">' +dataa[x].name+ '</div>';
                accounts += '<div>' +dataa[x].dateopened+ '</div>';
                // accounts += '<div>' +"0"+ response.data.members[0].accounts[i].contact+ '</div>';
                accounts += '</div';
                $('#accounts_info').append(accounts);

              }
            }
            else {
              var accounts='';
              accounts += '<div class="row-container d-flex align-items-center justify-content-between bio-info">';
              accounts += '<div class="col-12">No Accounts opened yet</div>';
              // accounts += '<div>' +response.data.members[0].accounts[i].dateopened+ '</div>';
              // accounts += '<div>' +"0"+ response.data.members[0].accounts[i].contact+ '</div>';
              accounts += '</div';
              $('#accounts_info').append(accounts);

            }
          },
          error: function (error, xhr, status) {
            if (xhr.status === 401) {
              authchecker(getMemberBio);
            }
          }
        })
      }
    });
