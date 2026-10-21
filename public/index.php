<?php
require __DIR__ . '/../src/app.php';

render_header('index');
?>
<main>
	<div class="container hero">
		<img src="images/logowhite.png" alt="CEAT">
		<div class="actions-row">
			<?php if (current_user() === null): ?>
				<a href="login.php" class="start pill">Take The Test</a>
			<?php else: ?>
				<a href="indextest.php" class="start pill">Take The Test</a>
				<a href="indexresult.php" class="start pill">See Your Result</a>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php render_footer();
