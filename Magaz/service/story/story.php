<?php
	session_start();
	if (!isset($_COOKIE["session"])) {
	    header("Location: /");
	    exit;
	}
	$db = new mysqli('db', 'root', 'rootpassword', 'vkurse_db'); // Подключение к базе данных
	if (!$db) {
		echo "<!DOCTYPE html>
		<html>
		<body>
		<h1>Ошибка подключения к базе данных. Код ошибки: " . $db->connect_errno . "</h1>
		</body>
		</html>";
		exit();
	}

	$db->set_charset("utf8");
	
	$cookieValue = $_COOKIE["session"];
	$query = "SELECT user_id FROM cookie WHERE cookie = ?";
	$stmt = $db->prepare($query);
	if (!$stmt) {
		echo "<!DOCTYPE html>
		<html>
		<body>
		<h1>Ошибка подготовки запроса: " . $db->error . "</h1>
		</body>
		</html>";
		exit();
	}
	$stmt->bind_param("s", $cookieValue);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		if($_SESSION['user_id'] != $db_user_id){
			$stmt->close();
			$db->close();
			header("Location: /");
			exit;
		}
		//ЛОГИКА
		$query = "SELECT secret_number, secret_word FROM secrets WHERE id_user = ?";
		$stmt_secrets = $db->prepare($query);
		if (!$stmt_secrets) {
			echo "<!DOCTYPE html>
			<html>
			<body>
			<h1>Ошибка подготовки запроса: " . $db->error . "</h1>
			</body>
			</html>";
			$stmt->close();
			$db->close();
			exit;
		}
		$stmt_secrets->bind_param("i", $db_user_id);
		$stmt_secrets->execute();
		$stmt_secrets->store_result();
		$stmt_secrets->bind_result($secret_number, $secret_word);
		
		$secrets = [];
		while ($stmt_secrets->fetch()) {
		$secrets[] = [
		    'secret_number' => $secret_number,
		    'secret_word' => $secret_word,
		];
		}
		$stmt_secrets->close();
	} else {
		$stmt->close();
		$db->close();
		header("Location: /");
		exit;
	}
	
	$stmt->close();
	$db->close();
?>
