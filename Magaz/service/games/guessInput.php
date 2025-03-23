<?php
	session_start();
	if (!isset($_COOKIE["session"])) {
	    header("Location: /");
	    exit;
	}
	$db = new mysqli('mysql', 'root', 'rootpassword', 'mysql_db');
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
		$stmt->bind_result($db_user_id);
		$stmt->fetch();
		if($_SESSION['user_id'] != $db_user_id){
			$stmt->close();
			$db->close();
			header("Location: /");
			exit;
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$number = $_POST['number'] ?? null;
			$post_id = $_POST['post_id'] ?? null;
			if($post_id && $number){
				$query = "SELECT secret_number FROM secrets WHERE id = ?";
				$stmt_secret = $db->prepare($query);
				if (!$stmt_secret) {
					echo "<!DOCTYPE html>
					<html>
					<body>
					<h1>Ошибка подготовки запроса: " . $db->error . "</h1>
					</body>
					</html>";
					exit;
				}
				$stmt_secret->bind_param("i", $post_id);
				$stmt_secret->execute();
				$stmt_secret->store_result();
				$stmt_secret->bind_result($secret_number);
				if ($stmt_secret->fetch()) {
					if ($number == $secret_number) {
						echo "Win";
					} else {
						echo "Lose";
					}
				}
			}
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
