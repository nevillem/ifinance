$(document).ready(function () {
            getData()              
         async function getData(){
            $.ajax({
                url: base+"saccos",
                headers: { 
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
             },
                type : "GET",
                success: function(response) {
                    // console.log(response);
                    $("#sacconame").html(response.data.sacconame);
                    $("#saccocode").html(response.data.saccocode);
                    $("#saccoemail").html(response.data.saccoemail);
                  },
                  error: function(){
                    authchecker(getData)
                  }
           
                
            })
        }
    });
    $(document).ready(function () {
      $("#logout").click(function() {
        logOut()              
        async function logOut(){
           $.ajax({
               url: base+"sessions/"+localStorage.id,
               headers: { 
                   'Authorization': localStorage.token,
                   'Content-Type': 'application/json'
            },
               type : "DELETE",
               success: function(response) {
                //  console.log(response)
                   localStorage.clear()
                   window.location.href="auth";
                 },
                 error: function(xhr){
                   if (xhr.status == '401') {
                     logOut()
                   }
                 }
           })
       }
      })
     
});