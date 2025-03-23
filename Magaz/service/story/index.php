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
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="beauty.css">
    <script src="games.js"></script>
    <title>История игр</title>
</head>
<body>
    <header class="panel">
    	<button class="outside" onclick = "window.location.href='/games'">⇐</button>
        <h1>История игр</h1>
        <div class="Name_out">
            <button class="outside" onclick = "proverka();">Выйти</button>
        </div>
    </header>
	<main>
		<h2>Ваши игры:</h2>
		<table>
			<thead>
				<tr>
					<th>Номер</th>
					<th>Секретное слово</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($secrets)): ?>
				<?php foreach ($secrets as $secret): ?>
					<tr>
						<td><?= htmlspecialchars($secret['secret_number']) ?></td>
						<td><?= htmlspecialchars($secret['secret_word']) ?></td>
					</tr>
					<?php endforeach; ?>
					<?php else: ?>
				<tr>
					<td colspan="2">У вас пока нет игр</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</main>
</body>
</html>
