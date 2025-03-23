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
    		if (!$email || !$password) {
			$db->close();
			header("Location: /");
			exit;
		}
		$query = $db->prepare('SELECT id, password FROM users WHERE email = ?');
		$query->bind_param('s', $email);
		$query->execute();
		$result = $query->get_result();

		if ($result->num_rows === 0) {
			$_SESSION['message'] = "Invalid username or password";
			$db->close();
			echo json_encode($message);
			//header("Location: /");
			exit;
		}
		$user = $result->fetch_assoc();
		if(!$user || !password_verify($password, $user['password'])){
			if (isset($_COOKIE['session'])) {
        			setcookie('session', '', time() - 3600, "/", "", true, true);
        		}
			$_SESSION['message'] = "Invalid username or password";
			$db->close();
			echo json_encode($message);
			//header("Location: /");
			exit;
		}
		$authToken = bin2hex(random_bytes(32));
		$cookieName = "session";
	    	$expiryTime = time() + (86400 * 30);
		setcookie($cookieName, $authToken, $expiryTime, "/", "", true, true);
		
		$query = $db->prepare('SELECT id FROM cookie WHERE user_id = ?');
		$query->bind_param('i', $user['id']);
		$query->execute();
		$cookieResult = $query->get_result();

		if ($cookieResult->num_rows > 0) {
			$query = $db->prepare('UPDATE cookie SET cookie = ? WHERE user_id = ?');
			$query->bind_param('si', $authToken, $user['id']);
		} else {
			$query = $db->prepare('INSERT INTO cookie (user_id, cookie) VALUES (?, ?)');
			$query->bind_param('is', $user['id'], $authToken);
		}

		$query->execute();
    		$_SESSION['user_id'] = $user['id'];
		header("Location: /games/");
	}
	$db->close();
?>
