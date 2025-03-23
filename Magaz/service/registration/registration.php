<?php
	session_start();
	if (isset($_SESSION['message'])) {
		$message = $_SESSION['message'];
		unset($_SESSION['message']);
	}else {
	    $message = "";
	}
	
	$db = new mysqli('mysql', 'root', 'rootpassword', 'mysql_db');
	if (!$db) {
		echo "<!DOCTYPE html>
		<html>
		<body>
		<h1>Connection to DB failed. Errno:" . $db->connect_errno . "</h1>
		</body>
		</html>"; 
		exit();
	}

	$db->set_charset("utf8");
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$email = $_POST['email'] ?? null;
    		$password = $_POST['password'] ?? null;
    		$password2 = $_POST['password2'] ?? null;
    		if($password != $password2){
    			$_SESSION['message'] ='Пароли не совпадают';
    			echo json_encode('Passwords are not similar');
			$db->close();
			//header("Location: /registration/");
			exit;
    		}
    		if (!$email || !$password) {
			$_SESSION['message'] ='Invalid username or password';
			$db->close();
			echo json_encode('Invalid username or password');
			//header("Location: /registration/");
			exit;
		}
    		if(strlen($password) < 5){
    			$_SESSION['message'] = 'Пароль должен содержать не менее 5 символов';
    			$db->close();
    			echo json_encode('Password must be at least 5 characters long');
			//header("Location: /registration/");
			exit;
    		}
    		if (!preg_match('/[A-Z]/', $password)) {
    			$_SESSION['message'] = 'Пароль должен содержать хотя бы одну заглавную букву';
    			$db->close();
    			echo json_encode('The password must contain at least one capital letter');
			//header("Location: /registration/");
			exit;
		}
		if (!preg_match('/[0-9]/', $password)) {
			$_SESSION['message'] = 'The password must contain at least one digit';
			$db->close();
			echo json_encode('The password must contain at least one digit');
			//header("Location: /registration/");
			exit;
		}
		if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
			$_SESSION['message'] = 'The password must contain at least one special character';
			$db->close();
			echo json_encode('The password must contain at least one special character');
			//header("Location: /registration/");
			exit;
		}
		if (strpos($email, '@') === false) {
			$_SESSION['message'] = 'Email некорректный';
			$db->close();
			echo json_encode('Email is not correct');
			//header("Location: /registration/");
			exit;
		}
		$domain = explode('@', $email)[1] ?? '';
		if (empty($domain) || !checkdnsrr($domain, 'MX')) {
			$_SESSION['message'] = 'Email is not correct';
			$db->close();
			echo json_encode('Email is not correct');
			//header("Location: /registration/");
			exit;
		}
		
		$query = $db->prepare('SELECT id FROM users WHERE email = ?');
		$query->bind_param('s', $email);
		$query->execute();
		$result = $query->get_result();

		if ($result->num_rows > 0) {
			$_SESSION['message'] = 'Пользователь с таким email уже существует';
			$db->close();
			echo json_encode('A user with this email already exists');
			//header("Location: /registration/");
			exit;
		}
		
		$userId = $query->insert_id;
		 
		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		$authToken = bin2hex(random_bytes(32));
		$cookieName = "session";
	    	$expiryTime = time() + (86400 * 30);
		setcookie($cookieName, $authToken, $expiryTime, "/", "", true, true);
		
		$query = $db->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
		$query->bind_param('ss', $email, $hashedPassword);
		$query->execute();
		$userId = $query->insert_id;	
			
		$query = $db->prepare('INSERT INTO cookie (user_id, cookie) VALUES (?, ?)');
		$query->bind_param('is', $userId, $authToken);
		$query->execute();
		$_SESSION['user_id'] = $userId;
		header("Location: /games/");
	}
	$db->close();
?>
