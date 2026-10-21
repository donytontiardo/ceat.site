<?php /** @var ?array $user  @var string $page */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CEAT</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
</head>
<body>
<header class="topbar">
	<div class="container topbar-inner">
		<h1 class="brand"><a href="index.php">CEAT</a></h1>
		<div class="topbar-actions">
			<?php if ($user !== null): ?>
				<h2 class="username"><?= e($user['name']) ?></h2>
				<span class="divider"></span>
				<form action="auth_process.php" method="post">
					<?= csrf_field() ?>
					<button type="submit" class="start" name="action" value="logout">Keluar</button>
				</form>
			<?php elseif ($page === 'index'): ?>
				<a href="regis.php" class="start primary">Daftar</a>
				<span class="divider"></span>
				<a href="login.php" class="start primary">Masuk</a>
			<?php endif; ?>
		</div>
	</div>
</header>
<?php foreach (take_flashes() as $message): ?>
	<p class="flash container"><?= e($message) ?></p>
<?php endforeach; ?>
