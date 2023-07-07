$(document).ready(function () {
    $('#registerForm').validate({
      rules: {
        email: {
          required: true,
          email: true
        },
        name: {
          required: true,
          minlength: 5
        },
        password: {
            required: true
          },
          copassword:{
                 required: true,
                 equalTo: "#password"
             },
             terms:{
               required: true
             }
      },
      messages: {
              email:{
                required: "A valid e-mail address is required",
                email: "Please enter a valid e-mail address e.g. ibige****@gm***.com"
              },
              name: {
                required: "Please enter a valid SACCO name",
                minlength: "A valid SACCO name is accepted"
              },
              password: {
                  required: "Password should include special character, capitial letter, number",
                  // alphanumeric: "Password should include special character, capitial letter, number"
                },
                copassword:{
                       required: "Please re-enter password",
                       equalTo: "Please passwords don't match"
                   },
                   terms:{
                     required: "Please accept the terms and conditions"
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

  $('#registerForm').submit(function(event){
    event.preventDefault();

    if($('#registerForm').valid()){
      var registerForm=$(this);
      var form_data=JSON.stringify(registerForm.serializeObject());
      // submit form data to api
      $.ajax({
          url: base+"signup",
          type : "POST",
          contentType : 'application/json',
          data : form_data,
          statusCode: {
            201: function(response) {
              localStorage.setItem("token", response.data.access_token);
              localStorage.setItem("refreshToken", response.data.refresh_token);
              var icon = 'warning'
              var message = 'sign up success'
              sweetalert(icon, message).then(function() {
                      window.location.href = 'verification';
                    });
    },
            400: function(){
              var icon = 'info'
            var message = 'error in submitted information'
            sweetalert(icon, message)
          },
          409 :function () {
            var icon = 'warning'
            var message = 'sacco already exists'
            sweetalert(icon, message)
          }
        }
       
        });
      return false;
    }
  });
});
