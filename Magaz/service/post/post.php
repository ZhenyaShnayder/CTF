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
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$secret_word = $_POST['secret_word'] ?? null;
			$number = $_POST['number'] ?? null;

			if (!$secret_word || !$number) {
				echo "<!DOCTYPE html>
				<html>
				<body>
				<h1>Ошибка: Все поля обязательны для заполнения.</h1>
				</body>
				</html>";
				$stmt->close();
				$db->close();
				exit;
			}

			$query = "INSERT INTO secrets (user_id, secret_word, number) VALUES (?, ?, ?)";
			$stmt = $db->prepare($query);
			if (!$stmt) {
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
			$stmt->bind_param("iss", $db_user_id, $secret_word, $number);
			if (!$stmt->execute()) {
				echo "<!DOCTYPE html>
				<html>
				<body>
				<h1>Ошибка при добавлении данных: " . $insertStmt->error . "</h1>
				</body>
				</html>";
			}
			$stmt->close();
		}
		
	} else {
		$stmt->close();
		$db->close();
		header("Location: /");
		exit;
	}
	
	$stmt->close();
	$db->close();
?>
