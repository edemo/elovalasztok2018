
/**
 * Adatom.hu kijelentkezés AJAX lekérdezéssel
 */
function doAdaLogout() {
	document.getElementById("errorNotLoggedIn").style.display = "none";
	document.getElementById("errorParse").style.display = "none";
	document.getElementById("errorHost").style.display = "none";

	var xhttp;
	if (window.XMLHttpRequest) {
		xhttp = new XMLHttpRequest();
	} else {
		xhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4) {
			if (this.status == 200) {
				var result;
				try {
					result = JSON.parse(this.responseText);
					if (typeof result.message !== undefined) {
						document.getElementById("adalogout").disabled = true;
						document.getElementById("logoutDone").style.display = "block";
					} else {
						for (var i in result.errors) {
							if (result.errors[i] == "not logged in") {
								console.error("You're not logged in!");
								document.getElementById("errorNotLoggedIn").style.display = "block";
							}
						}
					}
			    } catch(e) {
			    	console.error(e);
					document.getElementById("errorParse").style.display = "block";
			    }
			} else {
				console.error("Host unreachable (code: "+this.status+")");
				document.getElementById("errorHost").style.display = "block";
			}
		}
	};
	xhttp.open("GET", "https://adatom.hu/ada/v1/logout", true);
	xhttp.send();
}
