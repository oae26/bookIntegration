document.getElementById("groupCreation").addEventListener("click", function(event) {
    event.preventDefault();  
    createGroup();
});

document.getElementById("addUser").addEventListener("click", function(event) {
    event.preventDefault();  
    recruitUser();
});

let cookie = JSON.parse(sessionStorage.getItem("cookie"));
console.log(cookie);
console.log(cookie.userID);

window.onload = async function() {
    let data;
    let response = await  fetch("/php/bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=getgroups&userID="+cookie.userID
    });
    if (response.ok){		
        const responseText =  await response.text();
        console.log(responseText)
         data = JSON.parse(responseText);
         displayGroups(data)
       
                  }
        
    }
async function createGroup(){
    var ownerID = cookie.userID;
    console.log(ownerID);
    groupName = document.getElementById("groupName").value

    let response = await fetch("/php/bookService.php",{
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

        function displayGroups(data) {
            console.log(data)
            let groupTable = document.getElementById("groupTable")
        let groupNameArray = data.groupNameArray;
        let groupIDArray = data.groupIDArray;
        let ownerIDArray = data.ownerIDArray;
        let bookIDArray = data.bookIDArray;
        let readPageArray = data.readPageArray;
        let dueMonthArray = data.dueMonthArray;
        let dueDayArray = data.dueDayArray;
        console.log(ownerIDArray[0]);
        let isOwner;
        let ownerID;
                for(var i = 0; i < ownerIDArray.length; i++){
                    let row = document.createElement("tr");
                    if(cookie.userID == ownerIDArray[i]){
                        isOwner = true;
                        ownerID = ownerIDArray[i];
                    }
            
             
                row.innerHTML = `
                    <td id="groupID">
                    
                    ${groupIDArray[i]}</td>
                    <td>${groupNameArray[i]}</td>
                    <td>${ownerIDArray[i]}</td>
                    <td><input type=text id="bookID"placeholder="${bookIDArray[i]}"><button class=editBookID style="display: ${isOwner ? 'inline-block' : 'none'}">Edit ID</button></td>
                    <td><input id="pageRead"  type=text placeholder="${readPageArray[i]}"></td>
                     <td><input type=text id="dueMonth" placeholder="${dueMonthArray[i]}"></td>
                     <td><input type=text  id="dueDay" placeholder="${dueDayArray[i]}">
                     </td>
                     <input type=text ><button class=editDueDate  style="display: ${isOwner ? 'inline-block' : 'none'}">Edit DueDate</button></td>
                `;
                groupTable.appendChild(row);
                document.querySelectorAll(".editBookID").forEach(button => {
                    button.addEventListener("click", editBookID);
                });
            
                document.querySelectorAll(".editDueDate").forEach(button => {
                    button.addEventListener("click", editDueDate);
                });
               
        }
   
    }

async function editDueDate(event){
    event.preventDefault();
    console.log(document.getElementById("groupID").value);
    
   var groupID = parseInt(document.getElementById("groupID").textContent);
   console.log(groupID); 
   var dueMonth = document.getElementById("dueMonth").value; 
    var dueDay = document.getElementById("dueDay").value;
    var readPage = document.getElementById("pageRead").value;
    let response = await fetch("./bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=editduedetails&groupID="+groupID+"&readPage="+readPage+"&dueMonth="+dueMonth+"&dueDay="+dueDay
    });
        
            if (response.ok){		
            const responseText = await response.text();
            console.log(responseText)
             console.log(JSON.parse(responseText));
                      }
        }

        async function editBookID(event){
            event.preventDefault();
            var groupID = parseInt(document.getElementById("groupID").textContent);
            var bookID = document.getElementById("bookID").value;
            console.log(bookID);
            // to do, make it so that the data.groupIDArRAY fields are equal to the input types. 
            let response = await fetch("/php/bookService.php",{
                headers:{"Content-Type":"application/x-www-form-urlencoded"},
                method:"POST",
                body:"type=editbookid&groupID="+groupID+"&newBookID="+bookID
            });
                
                    if (response.ok){		
                    const responseText = await response.text();
                    console.log(responseText)
                     console.log(JSON.parse(responseText));
                              }
                }
                async function addUser(){
                    var ownerID = cookie.userID;
                    console.log(ownerID);
                    groupName = document.getElementById("groupName").value
                
                    let response = await fetch("./bookService.php",{
                        headers:{"Content-Type":"application/x-www-form-urlencoded"},
                        method:"POST",
                        body:"type=recruituser&addUserID=1&newBookID=1"
                    });
                        
                            if (response.ok){		
                            const responseText = await response.text();
                            console.log(responseText)
                             console.log(JSON.parse(responseText));
                                      }
                        }

async function recruitUser(){
   let  groupID = parseInt(document.getElementById("groupID").textContent);
    console.log(groupID);
    let groupName = document.getElementById("groupName").value;
    let username = document.getElementById("recruitUser").value;
    console.log(groupName);
    let response = await fetch("/php/bookService.php",{
    headers:{"Content-Type":"application/x-www-form-urlencoded"},
    method:"POST",
    body:"type=recruituser&groupID="+groupID+"&username="+username+"&groupname="+groupName
                        });
                            
                                if (response.ok){		
                                const responseText = await response.text();
                                console.log(responseText)
                                 console.log(JSON.parse(responseText));
                                          }
                            }
                    