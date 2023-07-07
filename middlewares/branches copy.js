$(document).ready(function () {
  $.ajaxSetup({
    beforeSend: function(jqXHR, options) {
    }
});
    //dashboard request
    function sleep (ms) {
        return new Promise(resolve => setTimeout(resolve, ms))
      } 

    async function getBranches(){
            await sleep(1);
         $.ajax({
        url: "http://localhost/irembo_version_control/API/branches",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
     },
        type : "GET",
        success: function(response) {
            $(".loading").hide();
            $(".login").show();
            // console.log(response);
            $('#numofbranches').html(response.data.rows_returned) ;
            var nums = response.data.branches.length;
            for (var i = 0; i < nums; i++){
                var branch = 
                '<div class="col-lg-4 mt-4 col-md-4 col-sm-4 col-xs-12 p-0 mb-lg-0">'+
                '<div class="card shadow-lg mr-4 bg-white rounded">'+
                '<div class="box-part text-center">'+
                '<div class="title row">'+
                    '<h6 class="col-lg-6">Branch:' + response.data.branches[i].id + '</h6>'+
                    '<h6 class="col-lg-6">'+ response.data.branches[i].name +'</h6>'+
                '</div>'+
                '<div class="text row">'+
                    '<span class="col-lg-6">'+
                        '<h6>Code: </h6><h6>Address: </h6><h6>Status: </h6>'+
                   ' </span>'+
                   ' <span class="col-lg-6"><h6>'+ response.data.branches[i].code +' </h6><h6 >'+ response.data.branches[i].address +' </h6><h6 >'+ response.data.branches[i].status +'</h6></span>'+
                '</div>' +
                '<a href=""><i class="fa fa-eye text-success p-2" aria-hidden="true"></i></a>' +
                '<a href=""><i class="fa fa-edit text-warning p-2" aria-hidden="true"></i></a>' +
                '<a href="" class=\'delete\' id=' + response.data.branches[i].id + '><i class="fa fa-trash text-danger p-2" aria-hidden="true"></i></a>' +
             '</div>' +
             '</div>' +
             '</div>'
             ;
                $(".branches").append(branch);
                // console.clear();
            }
          },
          error: function(){
            getBranches();
          }
       })
    }
    getBranches();
    
    //post new branch
$('#branchform').validate({
      rules: {
        name: {
          required: true
        },
        code: {
            required: true,
            number: true,
            maxlength:4
          },
          address:{
              required: true
          }
      },
      messages: {
                name:{
                required: "please enter branch name"
              },
                code: {
                  required: "please enter a branch code e.g. 111 or 1",
                  number: "this accepts digits only",
                  maxlength: "branch code should be max 4 characters"
                },
                address:{
                required: "please enter address"
                }
            },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
      },
      highlight: function (element, _errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
    })

  $('#branchform').submit(function(event){
    event.preventDefault();
    if($('#branchform').valid()){
      
            // function to make form values to json format
          $.fn.serializeObject = function(){
            var o = {};
            var a = this.serializeArray();
            $.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
          };
         // get form data
      var branchform = $(this);
      var form_data = JSON.stringify(branchform.serializeObject());
        //   cancelIdleCallback
            addbranch();
        // submit form data to api
    async function addbranch(){
          // start ajax loader
        $(document).ajaxStart(function () {
            $(".login").css("background-color", "#73b41a");
            $(".loading").show();
            $(".login").hide();
          });
      $.ajax({
          url: "http://localhost/irembo_version_control/API/branches",
          headers:{
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
          },
          type : "POST",
          contentType : 'application/json',
          data : form_data,
            success: function(response) {
            $(".loading").hide();
            $(".login").show();
            $("#addbranch").modal('hide');     
            branchform[0].reset();
            $(".branches").html("");                
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                  toast.addEventListener('mouseenter', Swal.stopTimer)
                  toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
                })
                Toast.fire({
                icon: 'success',
                title: 'new branch added',
              });
              getBranches();

            },
            error: function(xhr, status, error){
            // console.log(xhr.status);
            if(xhr.status === 401){
                authcheck();
            }else{
            const Toast = Swal.mixin({
              toast: true,
              position: 'center',
              showConfirmButton: false,
              timer: 1000,
              timerProgressBar: true,
              didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
              }
              })
              Toast.fire({
              icon: 'warning',
              title: xhr.responseJSON.messages,
            });
          }
        }

        });
      return false;
     }
     async function authcheck(){
        $.ajax({
            url: "http://localhost/irembo_version_control/API/sessions/"+localStorage.id,
            headers: { 
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
         },
            type : "PATCH",
            dataType: 'json',
            data: JSON.stringify({
                   "refresh_token": localStorage.refreshToken
                }),
            success: function(response){
                localStorage.setItem("token", response.data.access_token);
                localStorage.setItem("refreshToken", response.data.refresh_token);
                addbranch();
            },
            error: function(xhr,status,error){
                // console.log(xhr);
                localStorage.clear();
                window.location.href="auth";
            }

        })
    }                                                                                              
    }
  })
  
  $(document).delegate('.delete', 'click', function(event){
        event.preventDefault();
          //delete branch
          var id = $(this).attr('id');
          // console.log(id);
          $.ajax({
            url: "http://localhost/irembo_version_control/API/branches/"+id,
            headers: {
                'Authorization': localStorage.token,
                'Content-Type': 'application/json'
                    },
            type : "DELETE",
            success: function(){
              $(".branches").html("");                
              const Toast = Swal.mixin({
                  toast: true,
                  position: 'bottom',
                  showConfirmButton: false,
                  timer: 1500,
                  timerProgressBar: true,
                  didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                  }
                  })
                  Toast.fire({
                  icon: 'success',
                  title: 'branch deleted',
                });
                getBranches();
            },
            error: function(){ 
            const Toast = Swal.mixin({
              toast: true,
              position: 'bottom',
              showConfirmButton: false,
              timer: 1000,
              timerProgressBar: true,
              didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
              }
              })
              Toast.fire({
              icon: 'error',
              title: 'error deleting branch',
            });
          }
    })
    
  })
});
