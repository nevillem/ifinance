$(document).ready(function () {
    $('#verify').validate({
      rules: {
        otp: {
          required: true
        }
      },
      messages: {
              otp:{
                required: "Please insert a valid One Time Password"
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

  $('#verify').submit(function(event){
    event.preventDefault();

    if($('#verify').valid()){
      var verify=$(this);
      var form_data=JSON.stringify(verify.serializeObject());
      $.ajax({
          url: base+"verify",
          type : "POST",
          contentType : 'application/json',
          headers: {"Authorization": localStorage.getItem("token")},
          data : form_data,
          statusCode: {
            201: function(response) {
              var icon = 'success'
              var message = 'verified please login'
              sweetalert(icon, message).then(function() {
                      window.location.href = 'login';
                    });
    },
            400: function(xhr){
              var icon = 'warning'
              var message = xhr.responseJSON.messages
              sweetalert(icon, message)
          },
          401 :function (xhr) {
            var icon = 'warning'
            var message = xhr.responseJSON.messages
            sweetalert(icon, message)
          }
        }
        });
      return false;
    }
  });
});

