

let cookie;
const loginForm = document.getElementById("loginForm");
const registrationForm = document.getElementById("registrationForm");
const logoutButton = document.getElementById("logoutButton");
const validateButton = document.getElementById("validateButton");
if (loginForm) {
    loginForm.addEventListener('submit', SendLoginRequest);
}

if (registrationForm) {
    registrationForm.addEventListener('submit', SendRegisterRequest);
}

if (logoutButton) {
    logoutButton.addEventListener('click', SendLogoutRequest);
}

if (validateButton) {
    validateButton.addEventListener('click', SendValidateRequest);
}

async function SendLoginRequest(event)
{
	var username = document.getElementById("username").value;
	var password = document.getElementById("password").value;
	event.preventDefault();
	let response = await fetch('./authentication.php',{
	headers:{"Content-Type":"application/x-www-form-urlencoded"},
	method:"POST",
	body:"type=login&username="+
	username+"&password="+
	password
})
	
		if (response.ok)
		{
			const responseText = await response.text();
			HandleLoginResponse(responseText);
			

		}		

	}

    async function SendRegisterRequest(event)
{	
	
	var username = document.getElementById("username2").value;
	var password = document.getElementById("password2").value;
	event.preventDefault();
	let response = await fetch('./authentication.php',{
	headers:{"Content-Type":"application/x-www-form-urlencoded"},
	method:"POST",
	body:"type=register&username="+
	username+"&password="+
	password
})
	
		if (response.ok)
		{
			const responseText = await response.text();
			HandleRegisterResponse(responseText);
		

		}		
}

async function SendLogoutRequest(event)
{		let cookie = JSON.parse(sessionStorage.getItem("cookie"));


	var username = cookie.username;
	var ID = cookie.sessionKey;
	event.preventDefault();
	let response = await fetch("./authentication.php",{
		headers:{"Content-Type":"application/x-www-form-urlencoded"},
		method:"POST",
		body:"type=logout&username="+
		username+"&sessionKey="+
		ID
	});
	
	if(response.ok){

		const responseText = await response.text();
		handleLogoutResponse(responseText);
	}
	else{

		alert("Incorrect credentials, please try again");
	}


}
async function SendValidateRequest(event)
{	
	
	let cookie = JSON.parse(sessionStorage.getItem("cookie"));

	var username = cookie.username;
	var ID = cookie.sessionKey;
	event.preventDefault();
	let response = await fetch("./authentication.php",{
		headers:{"Content-Type":"application/x-www-form-urlencoded"},
		method:"POST",
		body:"type=validate_session&username="+
		username+"&sessionKey="+
		ID
	});
		
			if (response.ok){		
			const responseText = await response.text();
			handleValidateResponse(responseText);
		

		}
		else{
			alert("Not Validated, redirecting to home page");
			window.location.href="./index.html";
		}
}

async function HandleLoginResponse(response)
{
	try{
    let decodedResponse;
	console.log(response);	
    decodedResponse = jwt_decode(response);
    console.log(decodedResponse);
    sessionStorage.setItem("cookie", decodedResponse);
	window.location.href="./home.html";
	sessionStorage.setItem("cookie", JSON.stringify(decodedResponse));
	console.log(JSON.parse(sessionStorage.getItem("cookie")));
}catch{
	alert("Invalid Login Credentials");

}}
	// else{
	// 	document.getElementById("textResponse").innerHTML = "response: invalide credentials <p>";
		
	// }


async function HandleRegisterResponse(response){
	response = JSON.parse(response);
	console.log(response);
	if(response.returnCode == "0"){
		document.getElementById("textResponse").innerHTML = "we're regiistered";
}	
else{
	document.getElementById("textResponse").innerHTML = "user already exists";
}
}
	
async function handleValidateResponse(response){
	console.log(response);
	response = JSON.parse(response);
	if(response.returnCode == "0"){
		document.getElementById("textResponse").innerHTML = "we're validated";
}	else{
	alert("you are not validated, redirecting to home page");
    window.location.href="./index.html";
	
}
}
async function handleLogoutResponse(response){
	console.log(response);
    window.location.href="./index.html";
    localStorage.removeItem("cookie");
}

window.onload = function(){
	let cookie = JSON.parse(sessionStorage.getItem("cookie"));

	console.log(window.location.pathname);
	console.log(cookie);
	if(window.location.pathname !== "/index.html" && cookie == null){
		alert("not logged in, redirecting you to the home page.");
		window.location.href='./index.html'
	}
}
