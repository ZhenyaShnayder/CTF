function proverka(){
	if(confirm("Подтвердить, что хочу выйти")){
		window.location.href="/";
		return true;
	}
	return false;
}

function story(){
	window.location.href="/story";
}

function post(){
	window.location.href="/post";
}
