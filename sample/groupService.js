document.getElementById("groupCreation").addEventListener("click", function(event) {
    event.preventDefault();  
    createGroup();
});
let cookie = JSON.parse(sessionStorage.getItem("cookie"));

async function createGroup(){
    var ownerID = cookie.userID;
    console.log(ownerID);
    groupName = document.getElementById("groupName").value

    let response = await fetch("./bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=creategroup&ownerID="+ownerID +"&groupName="+groupName
    });
        
            if (response.ok){		
            const responseText = await response.text();
            console.log(responseText)
             console.log(JSON.parse(responseText));
                      }
        }

