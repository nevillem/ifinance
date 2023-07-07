$(document).ready(function () {
    $("body").children().first().before($(".modal"));
        $('#newpassword').validate({
            rules: {
              newpass: {
                required: true,
                // alphanumeric: true,
                minlength:8
              },
              oldpass: {
                  required: true,
                },
                conpassword:{
                    required: true,
                    equalTo: "#newpass"
                }
            },
            messages: {
                newpass:{
                    required: "please enter new password",
                    minlength:"password should be atleast 8 characters"
                    },
                    oldpass: {
                        required: "please enter old password",
                        
                      },
                      conpassword:{
                      required: "please confirm password",
                      equalTo: "password don't match"
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
          });
  $('#newpassword').submit(function(event){
    event.preventDefault();
    if($('#newpassword').valid()){
      var password = $(this);
      var form_data = JSON.stringify(password.serializeObject());
        changepassword()
      async function changepassword(){
          // start ajax loader
      $.ajax({
          url: base+"saccos/password",
          headers:{
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
          },
          type : "POST",
          contentType : 'application/json',
          data : form_data,
            success: function(response) {
                password[0].reset();                         
                var icon = 'success'
                var message = 'password updated'
                sweetalert(icon, message)
              return;
            },
            error: function(xhr, status, error){
            if(xhr.status === 401){
                authchecker(changepassword);
            }else{
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
});