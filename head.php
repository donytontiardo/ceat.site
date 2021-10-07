<?php
function current_url()
{
    $url      = $_SERVER['REQUEST_URI'];
    $validURL = str_replace("&", "&amp", $url);
    return $validURL;
}
//echo "page URL is : ".current_url();

$offer_url = current_url();
$path = explode("/", $offer_url); // splitting the path
$last = end($path); 
?>
<!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CEAT</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
</head>
</body>
<header style="background-color:black">
	<div style="flex-direction: row; display:flex;justify-content:space-between" class="container">
		<dvi>
			<h1 style="color:white">CEAT</h1>
		</dvi>
		<div style="width:50%;flex-direction: row; display:flex;justify-content:flex-end; align-items:center">
			<h2 style="margin-right: 2%;color:white"><?php if (isset($_SESSION['username'])) {
															echo $_SESSION['username'];
														} ?></h2>
			<?php if (isset($_SESSION['username'])) {
			?>
				<span style="border-left:2px solid white;height:80%;margin-right: 2%;"></span>
				<form action="./auth_process.php" method="post">
					<input type="submit" class="start" name="logout" value="Keluar">
				</form>
			<?php
			} ?>
			<?php if (!isset($_SESSION['username'])&&$last=="index.php") {
			?>
				<a href="regis.php" class="start" style="background-color:#23a0f1;color:white;font-size:1rem">Daftar</a>
				<span style="border-left:2px solid white;height:80%;margin-right: 2%;margin-left: 2%;"></span>
				<a href="login.php" class="start" style="background-color:#23a0f1;color:white;font-size:1rem">Masuk</a>
			<?php
			} ?>

		</div>

	</div>
</header>

</html>