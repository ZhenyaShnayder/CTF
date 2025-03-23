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
		$query = "SELECT a.id, b.email, a.secret_number, a.secret_word FROM secrets a JOIN users b on a.id_user=b.id";
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
		$stmt_secrets->execute();
		$stmt_secrets->store_result();
		$stmt_secrets->bind_result($id_post, $email_user, $secret_number, $secret_word);
		
		$secrets = [];
		while ($stmt_secrets->fetch()) {
		$secrets[] = [
		    'id_post' => $id_post,
		    'email_user' => $email_user,
		    'secret_number' => $secret_number,
		    'secret_word' => $secret_word,
		    'guessed' => isset($_SESSION['guessed_secrets'][$id_post]) && $_SESSION['guessed_secrets'][$id_post] !== null,
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
    <title>Игры</title>
</head>
<body>
    <header class="panel">
        <h1>Игры</h1>
        <div class="Name_out">
            <button class="outside" onclick = "post()">Создать игру</button>
            <button class="outside" onclick = "story()">История игр</button>
            <button class="outside" onclick = "proverka();">Выйти</button>
        </div>
    </header>
     <main>
        <?php if (empty($secrets)): ?>
            <p>Игр пока нет</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID поста</th>
                        <th>Email пользователя</th>
                        <th>Ваше предположение</th>
                        <th>Выигрыш</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($secrets as $secret): ?>
                        <tr>
                            <td><?= htmlspecialchars($secret['id_post']) ?></td>
                            <td><?= htmlspecialchars($secret['email_user']) ?></td>
                            <td>
				<div class="input-container">
				<form method="POST" action="/guessInput.php">
					<input type="hidden" name="post_id" value="<?= htmlspecialchars($secret['id_post']) ?>">
					<input type="number" name = "number" placeholder="Введите число">
					<button class="submit">Отправить</button>
				</form>
				</div>
                            </td>
			    <td>
				<?php if (isset($_SESSION['guessed_secrets'][$secret['id_post']])): ?>
                                <?= htmlspecialchars($secret['secret_word']) ?>
				<?php else: ?>
					—
				<?php endif; ?>
			</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
