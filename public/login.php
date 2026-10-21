<?php
require __DIR__ . '/../src/app.php';

if (current_user() !== null) {
    redirect('index.php');
}
render_header('login');
?>
<main class="auth-page">
	<div class="container split">
		<div class="split-logo"><img src="images/logo.png" alt="CEAT"></div>
		<div class="auth-card">
			<form action="auth_process.php" method="post">
				<?= csrf_field() ?>
				<h1>Masuk</h1>
				<label for="nim">NIM</label>
				<input id="nim" type="text" inputmode="numeric" pattern="[0-9]{1,20}" name="nim" required autocomplete="username">
				<br><br>
				<label for="password">Kata Sandi</label>
				<input id="password" type="password" name="password" required autocomplete="current-password">
				<br><br>
				<button type="submit" class="start primary wide" name="action" value="login">Masuk</button>
				<div class="switch">Belum punya akun? <a href="regis.php">Daftar</a></div>
			</form>
		</div>
	</div>
</main>
<?php render_footer();
