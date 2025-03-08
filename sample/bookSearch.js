document.getElementById("searchButton").addEventListener("click", getBooks);
async function getBooks(){
    var title = document.getElementById("searchBar").value;
    console.log(title);
    document.getElementById("output").innerHTML = "";
    let response = await fetch("./bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=booksearch&title="+
        title
    });
        
            if (response.ok){		
            const responseText = await response.text();
            console.log(responseText)
             console.log(JSON.parse(responseText));
            json = JSON.parse(responseText);
            document.getElementById("output").innerHTML += "<h2> "+ json.bookTitles[0];
    
        }
    }

