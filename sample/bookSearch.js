document.getElementById("searchButton").addEventListener("click", getBooks);
async function getBooks(){
    document.getElementById("output").innerHTML = "";
        var searchResult = document.getElementById("searchBar").value;
        fetch("http://openlibrary.org/search.json?q="+searchResult)
        .then(a => a.json())
        .then(response => {
            console.log(response.docs)
            for(var i = 0; i <10; i++){
                document.getElementById("output").innerHTML += "<h2>" + response.docs[i].title + "<h2>"+ response.docs[i].author_name + "<img src=https://covers.openlibrary.org/b/id/"+response.docs[i].cover_i+"-M.jpg><br>";
            }
        })

}