function validate_password(password){
	if (password.includes("'") || password.includes('"')) {
		alert("Пароль не должен содержать кавычки");
	 	return false;
	}
	if(password.length < 8){
		alert("Пароль должен быть длиннее");
		return false;
	}
	const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]+$/;
	if (!passwordRegex.test(password)) {
      		alert('Пароль должен содержать буквы, цифры и хотя бы один специальный символ !@#$%^&*)');
      		return false;
      	}
	return true;
}

function submitForm(){
	const password1 = document.getElementById("password1").value;
	const password2 = document.getElementById("password2").value;
	if(!validate_password(password1)){
		return;
	}
	if(password1 != password2){
		alert("Пароли не совпадают");
		return;
	}
}
