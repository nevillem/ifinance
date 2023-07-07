$(document).ready(function () {
            getData()              
         async function getData(){
            $.ajax({
                url: base+"user",
                headers: { 
                    'Authorization': localStorage.token,
                    'Content-Type': 'application/json'
             },
                type : "GET",
                success: function(response) {
                    // console.log(response);
                    $("#name").html(response.data.user[0].name);
                    $("#email").html(response.data.user[0].username);
                    $("#role").html(response.data.user[0].role);
                    $("#branch").html(response.data.user[0].branch);
                    $("#contact").html(response.data.user[0].contact);
                    localStorage.setItem("branch", response.data.user[0].branch)
                    localStorage.setItem("sacconame", response.data.user[0].branch)
                    localStorage.setItem("role", response.data.user[0].role)
                  },
                  error: function(){
                    authchecker(getData)
                  }
           
                
            })
            // get sacco data
            $.ajax({
              url: base+"information",
              headers: { 
                  'Authorization': localStorage.token,
                  'Content-Type': 'application/json'
           },
              type : "GET",
              success: function(response) {
                // console.log(response)
                  localStorage.setItem("logo", response.data.logo)
                  localStorage.setItem("sacconame", response.data.name)
                  localStorage.setItem("shortname", response.data.shortname)
                  localStorage.setItem("email", response.data.email)
                  localStorage.setItem("contact", response.data.contact)
                },
                error: function(){
                  getData()
                }
         
              
          })
        }
    });
    $(document).ready(function () {
      $("#logout").click(function() {
        logOut()              
        async function logOut(){
           $.ajax({
               url: base+"usersession/"+localStorage.id,
               headers: { 
                   'Authorization': localStorage.token,
                   'Content-Type': 'application/json'
            },
               type : "DELETE",
               success: function(response) {
                //  console.log(response)
                   localStorage.clear()
                   window.location.href="login";
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