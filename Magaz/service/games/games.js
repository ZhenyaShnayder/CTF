function proverka(){
	if(confirm("Подтвердить, что хочу выйти")){
		window.location.href="/";
		return true;
	}
	return false;
}
