<?php
session_start();
?>
<?php
include "head.php";
include "database.php";
if (!isset($_SESSION['usertest'])) {
	echo "<script>window.location.href='login.php';</script>";
} else {
	$id_user = $_SESSION['id_user'];
	$query_user = "SELECT * FROM result WHERE id_user='$id_user'";
	$result_user = $mysqli->query($query_user) or die($mysqli->error . __LINE__);
?>
	<html>

	<body>

		<main>
			<div class="container">
				<table style="width:100%;margin-top:5%">
					<tr>
						<th>No</th>
						<th>Waktu</th>
						<th>Tanggal</th>
						<th>Aksi</th>
					</tr>

					<?php

					foreach ($result_user as $key => $value) {

					?>
						<tr>
							<td><?= $key + 1 ?></td>
							<td><?= $value['waktu'] ?></td>
							<td><?= $value['tanggal'] ?></td>
							<td style="text-align: center;"><a href="final.php?waktu=<?= $value['waktu']; ?>&tanggal=<?= $value['tanggal']; ?>" class="start" style="background-color:#23a0f1;color:white;font-size:1rem;border-radius:30px;padding:2px 15px 2px 15px">Lihat</a></td>
						</tr>
					<?php
					}
					?>
				</table>

			</div>

			<div style="text-align:center;margin-top:3%;margin-bottom:3%">

				<a href="index.php" class="start" style="border-radius: 20px;padding-left:5%;padding-right:5%">Kembali</a>
			</div>
		</main>


		<?php
		include "footer.php";
		?>

	</body>

	</html>

<?php
}
?>