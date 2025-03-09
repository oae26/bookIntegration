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
            bookJson = JSON.parse(responseText);
            sessionStorage.setItem("bookDetails", JSON.stringify(bookJson));
            console.log(sessionStorage.getItem("bookDetails"));
            for(var i = 0; i < bookJson.bookKeys.length; i++){        
            document.getElementById("output").innerHTML += "<h2> <a href=./bookDetails.html?id="+bookJson.bookKeys[i]+">"+ bookJson.bookTitles[i]+"</a> <img src=https://covers.openlibrary.org/b/isbn/"+bookJson.bookKeys[i]+"-S.jpg></h2>";
            }
        }
    }

