$(document).ready(function () {
    $('#loginAdminForm').validate({
      rules: {
        username: {
          required: true,
          email: true
        },
        password: {
            required: true
          }
      },
      messages: {
        username:{
                required: "please enter a valid username",
                email: "please enter a valid username"
              },
              password: {
                  required: "please enter a password"
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

  $('#loginAdminForm').submit(function(event){
    event.preventDefault();
    if($('#loginAdminForm').valid()){
      var loginAdminForm=$(this);
      var form_data=JSON.stringify(loginAdminForm.serializeObject());
      $.ajax({
          url: base+"generaladmin",
          type : "POST",
          contentType : 'application/json',
          data : form_data,
            success: function(response) {
              localStorage.setItem("token", response.data.access_token);
              localStorage.setItem("refreshToken", response.data.refresh_token);
              localStorage.setItem("id", response.data.session_id);
              window.location.href="adminall";
             },
            error: function(xhr, status, error){
            var icon = 'warning'
            var message = xhr.responseJSON.messages
            sweetalert(icon, message)
          }

        });
      return false;
    }
  })
});
