
window.onload = async function() {
    let data;
    cookie = JSON.parse(sessionStorage.getItem("cookie"));
    let response = await  fetch("./bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=getwatchlist&userID="+cookie.userID
    });
    if (response.ok){		
        console.log("yay");
        const responseText =  await response.text();
        console.log(responseText)
         data = JSON.parse(responseText);
        displayWatchList(data);
       
                  }
        
    }

    function displayWatchList(data) {
        console.log(data)
        let watchlist = document.getElementById("watchList")
   
    let bookNameArray = data.bookNameArray;
    let releaseDateArray = data.releaseDateArray;

  
            for(var i = 0; i < bookNameArray.length; i++){
                let row = document.createElement("tr");
        
         
            row.innerHTML = `
                <td>
                
                ${bookNameArray[i]}</td>
                <td>${releaseDateArray[i]}</td>
               `;
            watchlist.appendChild(row);
           
    }

}