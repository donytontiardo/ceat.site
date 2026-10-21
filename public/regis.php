<?php
require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/accounts.php';

if (current_user() !== null) {
    redirect('index.php');
}
render_header('register');
?>
<main class="auth-page">
	<div class="container split">
		<div class="split-logo"><img src="images/logo.png" alt="CEAT"></div>
		<div class="auth-card">
			<form action="auth_process.php" method="post">
				<?= csrf_field() ?>
				<h1>Buat Akun</h1>
				<label for="name">Nama Lengkap</label>
				<input id="name" type="text" name="name" required maxlength="100" autocomplete="name">
				<br><br>
				<label for="nim">NIM</label>
				<input id="nim" type="text" inputmode="numeric" pattern="[0-9]{1,20}" name="nim" required autocomplete="username">
				<br><br>
				<label for="age">Usia</label>
				<input id="age" type="number" name="age" min="<?= MIN_AGE ?>" max="<?= MAX_AGE ?>" required>
				<br><br>
				<label for="password">Kata Sandi (minimal <?= MIN_PASSWORD_LENGTH ?> karakter)</label>
				<input id="password" type="password" name="password" required minlength="<?= MIN_PASSWORD_LENGTH ?>"
				       maxlength="<?= MAX_PASSWORD_BYTES ?>" autocomplete="new-password">
				<br><br>
				<button type="submit" class="start primary wide" name="action" value="register">Daftar</button>
				<div class="switch">Sudah punya akun? <a href="login.php">Masuk</a></div>
			</form>
		</div>
	</div>
</main>
<?php render_footer();
