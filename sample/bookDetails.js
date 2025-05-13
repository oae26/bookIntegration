const urlParams = new URLSearchParams(window.location.search);
const bookKey = urlParams.get('id');

console.log('Book Key:', bookKey);


const bookDetails= JSON.parse(sessionStorage.getItem("bookDetails"));
const userData = JSON.parse(sessionStorage.getItem("cookie"));
console.log(userData.userID);

document.getElementById("bookTitle").innerHTML = bookDetails.bookTitles;
document.getElementById("year").innerHTML = bookDetails.bookYears;
document.getElementById("bookAuthor").innerHTML = bookDetails.bookAuthors;
const reviewText = document.getElementById("review");
var ratings = 0;
const stars = document.querySelectorAll(".stars i");
stars.forEach((star, index1) => {
        star.addEventListener("click", () =>{
            ratings = parseInt(star.getAttribute("value"));
            console.log("User rating is:" + ratings);
            stars.forEach((star, index2)=>{
                index1 >= index2 ? star.classList.add("active") : star.classList.remove("active");
            })
        })
});
document.getElementById("reviewButton").addEventListener("click", submitReview);
document.getElementById("ratingButton").addEventListener("click", submitRating);
async function submitReview(){
    console.log(bookKey);
    console.log(reviewText.value);
    console.log(ratings);
    let response = await fetch("./bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=review&review="+reviewText.value+"&userID="+parseInt(userData.userID)+"&bookID="+bookKey
    });
        
            if (response.ok){		
            const responseText = await response.text();
            console.log(responseText)
             console.log(JSON.parse(responseText));
            json = JSON.parse(responseText);
            document.getElementById("bookReviews").innerHTML += "<p>"+ reviewText.value;
    
        }   
    document.getElementById("bookReviews").innerHTML += "<p>"+ reviewText.value +"<br>";
    
}
async function submitRating(){
        console.log(bookKey);
        console.log(ratings);
    let response = await fetch("./bookService.php",{
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        method:"POST",
        body:"type=rate&rating="+ratings+"&bookID="+bookKey
    });
        
            if (response.ok){		
            const responseText = await response.text();
            console.log(responseText)
             console.log(JSON.parse(responseText));
            json = JSON.parse(responseText);
            document.getElementById("bookReviews").innerHTML += "<p>"+ reviewText.value;
    
        }   
    document.getElementById("bookReviews").innerHTML += "<p>"+ reviewText.value +"<br>";
    
}