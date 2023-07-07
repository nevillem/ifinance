$(document).ready(function () {
          getDashboard()
        async function getDashboard(){
         $.ajax({
        url: base+"dashboard",
        headers: {
            'Authorization': localStorage.token,
            'Content-Type': 'application/json'
     },
        type : "GET",
        success: function(response) {
            $("#branches").html(response.data.branches);
            $("#members").html(response.data.members);
            $("#users").html(response.data.users);
          },
          error: function(xhr, status, error){
            if(xhr.status == '401'){
              getDashboard()
            }
          }
    })
  }
});