$(document).ready(function () {
$("#logout").click(function() {
logOut();              
async function logOut(){
   $.ajax({
       url: base+"generaladmin/"+localStorage.id,
       headers: { 
           'Authorization': localStorage.token,
           'Content-Type': 'application/json'
    },
       type : "DELETE",
       success: function(response) {
           localStorage.clear()
           window.location.href="admin";
         },
         error: function(xhr){
           if (xhr.status == '401') {
             logOut();
           }
         }
   })
}
});

});