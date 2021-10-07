<?php session_start(); ?>

<?php
include "head.php";
include "database.php";
?>
<html>

<body>

	<main>
		<div class="container" style="text-align:center">
			<img src="images/logowhite.png" style="width: 50%;height:auto;">
			<div style="flex-direction: row;display:flex;align-items:center;justify-content:center">
				<?php
				if (!isset($_SESSION['usertest'])) {
				?>
					<a href="login.php" class="start" style="border-radius: 20px;padding-left:5%;padding-right:5%">Take The Test</a>
				<?php
				} else {

				?>
					<a href="indextest.php" class="start" style="border-radius: 20px;padding-left:5%;padding-right:5%">Take The Test</a>
					<div style="margin-left: 5%;"></div>
					<a href="indexresult.php" class="start" style="border-radius: 20px;padding-left:5%;padding-right:5%">See Your Result</a>
				<?php

				} ?>

			</div>
		</div>
	</main>


	<?php
	include "footer.php";
	?>

</body>

</html>