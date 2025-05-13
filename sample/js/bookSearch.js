document.addEventListener("DOMContentLoaded", () => {
    const genresDropdown = document.getElementById("genres");

    genresDropdown.addEventListener("change", async function () {
        var selectedGenre = genresDropdown.value;
        console.log(selectedGenre);
        getUpcomingBooks(selectedGenre);

    })
})

document.getElementById("searchButton").addEventListener("click", getBooks);


async function getBooks() {
    var title = document.getElementById("searchBar").value;
    console.log(title);
    document.getElementById("output").innerHTML = "";
    try{
    let response = await fetch("/php/bookService.php", {
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        method: "POST",
        body: "type=OLBookSearch&title=" +
            title
    });

    if (response.ok) {
        const responseText = await response.text();
        console.log(responseText)
        console.log(JSON.parse(responseText));
        bookJson = JSON.parse(responseText);
        sessionStorage.setItem("bookDetails", JSON.stringify(bookJson));
        console.log(sessionStorage.getItem("bookDetails"));
        for (var i = 0; i < bookJson.bookKeys.length; i++) {
            document.getElementById("output").innerHTML += "<h2> <a href=/html/bookDetails.html?id=" + bookJson.bookKeys[i] + ">" + bookJson.bookTitles[i] + "</a> <img src=https://covers.openlibrary.org/b/isbn/" + bookJson.bookKeys[i] + "-S.jpg></h2>";
        }
    }}catch(error){
        console.error(error())
    }

}

let bookID;
async function getUpcomingBooks(genre) {
    let response = await fetch("/php/bookService.php", {
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        method: "POST",
        body: "type=OLBookSearch&title=" +
            title
    });
        data = await response.json();
        displayUpcomingBooks(data.items);
        console.log(bookID);

    

}

function displayUpcomingBooks(books) {

    const outputDiv = document.getElementById("upcomingBooks");
    outputDiv.innerHTML = "";
    const currentDay = new Date();
    console.log(books);
    books.forEach(book => {
        var bookID = book.id;
        var title = book.volumeInfo.title || "no book data avialable";
        var authors = book.volumeInfo.authors ? book.volumeInfo.authors.join(",") : "Unknown author";
        var publishDate = book.volumeInfo.publishedDate;
        var publishDateObject = new Date(publishDate);

        if (publishDateObject > currentDay) {
            const bookElement = document.createElement("div");
            bookElement.innerHTML = `<p class="bookTitle">${title}</p> <p> by: ${authors}</p> <p class="bookReleaseDate"> ${publishDate} </p>`;
            const watchlistButton = document.createElement("button");
            watchlistButton.textContent = "Add this book to watchlist"
            watchlistButton.addEventListener("click", function(){
    
             addBookToWatchlist(bookElement,bookID)
            });
            bookElement.appendChild(watchlistButton);
            outputDiv.appendChild(bookElement);

        }

       
        });

    }



async function addBookToWatchlist(bookElement, bookID) {
    var cookie = JSON.parse(sessionStorage.getItem("cookie"));
    var userID = cookie.userID;
    var title = bookElement.querySelector(".bookTitle").textContent;
    var publishDate = bookElement.querySelector(".bookReleaseDate").textContent;
    console.log(bookID);
    let response = await fetch("/php/bookService.php", {
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        method: "POST",
        body: "type=addwatchlist&googleBookID="+bookID+"&userID=" + userID + "&title=" + title + "&bookReleaseDate=" + publishDate
    });
    if (response.ok) {
        console.log("added book to watchlist");


    }
}

