<?php session_start(); ?>
<?php
include "head.php";
include 'database.php';
$id_user = $_SESSION['id_user'];
if (isset($_GET['waktu'])) {
	$waktu = $_GET['waktu'];
	$tanggal = $_GET['tanggal'];
} else {
	$tanggal =  $_SESSION['tanggal'];
	$waktu = $_SESSION['waktu'];
}
$query_user = "SELECT * FROM result WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
$result_user = $mysqli->query($query_user) or die($mysqli->error . __LINE__);
$row_user = $result_user->fetch_assoc();

$query = "SELECT * FROM user WHERE id_user='$id_user' ";
$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
$row = $result->fetch_assoc();
$age = $row['age'];

// Skor
$result_visual = 0;
$result_numerical = 0;
$result_verbal = 0;
$result_sequential = 0;
$result_spatial = 0;
$result_3d = 0;
$result_system = 0;
$result_vocabulary = 0;
$result_figurework = 0;

for ($i = 0; $i < 32; $i++) {
	if (($row_user['result_visual'][$i]) == "B") {
		$result_visual++;
	}
}
if ($result_visual == 32) {
	$result_visual = $result_visual + 3;
}
for ($i = 0; $i < 20; $i++) {
	if (($row_user['result_numerical'][$i]) == "B") {
		$result_numerical++;
	}
}
if ($result_numerical == 20) {
	$result_numerical = $result_numerical + 3;
}
for ($i = 0; $i < 33; $i++) {
	if (($row_user['result_verbal'][$i]) == "B") {
		$result_verbal++;
	}
}
if ($result_verbal == 33) {
	$result_verbal = $result_verbal + 2;
}
for ($i = 0; $i < 32; $i++) {
	if (($row_user['result_sequential'][$i]) == "B") {
		$result_sequential++;
	}
}
if ($result_sequential == 32) {
	$result_sequential = $result_sequential + 3;
}
for ($i = 0; $i < 70; $i++) {
	if (($row_user['result_spatial'][$i]) == "B") {
		$result_spatial++;
	}
}
for ($i = 0; $i < 30; $i++) {
	if (($row_user['result_3d'][$i]) == "B") {
		$result_3d++;
	}
}
if ($result_3d == 30) {
	$result_3d = $result_3d + 3;
}
for ($i = 0; $i < 216; $i++) {
	if (($row_user['result_system'][$i]) == "B") {
		$result_system++;
	}
}
for ($i = 0; $i < 40; $i++) {
	if (($row_user['result_vocabulary'][$i]) == "B") {
		$result_vocabulary++;
	}
}
if ($result_vocabulary == 40) {
	if ($age < 16) {
		$result_vocabulary = $result_vocabulary + 2;
	} else if ($age >= 16 && $age < 20) {
		$result_vocabulary = $result_vocabulary + 1;
	}
}
for ($i = 0; $i < 30; $i++) {
	if (($row_user['result_figurework'][$i]) == "B") {
		$result_figurework++;
	}
}
if ($result_figurework == 30) {
	if ($age <= 15) {
		$result_figurework = $result_figurework + 2;
	} else if ($age > 15 && $age < 19) {
		$result_figurework = $result_figurework + 1;
	}
}

// Grafik Progress
$result_visual_progress = 0;
$result_numerical_progress = 0;
$result_verbal_progress = 0;
$result_sequential_progress = 0;
$result_spatial_progress = 0;
$result_3d_progress = 0;
$result_system_progress = 0;
$result_vocabulary_progress = 0;
$result_figurework_progress = 0;

if ($result_visual >= 32) {
	$result_visual_progress = 10;
} else {
	$result_visual_progress = $result_visual * 10 / 32;
}

if ($result_numerical >= 20) {
	$result_numerical_progress = 10;
} else {
	$result_numerical_progress = $result_numerical * 10 / 20;
}

if ($result_verbal >= 33) {
	$result_verbal_progress = 10;
} else {
	$result_verbal_progress = $result_verbal * 10 / 33;
}

if ($result_sequential >= 32) {
	$result_sequential_progress = 10;
} else {
	$result_sequential_progress = $result_sequential * 10 / 32;
}

if ($result_spatial >= 70) {
	$result_spatial_progress = 10;
} else {
	$result_spatial_progress = $result_spatial * 10 / 70;
}

if ($result_3d >= 30) {
	$result_3d_progress = 10;
} else {
	$result_3d_progress = $result_3d * 10 / 30;
}

if ($result_system >= 216) {
	$result_system_progress = 10;
} else {
	$result_system_progress = $result_system * 10 / 216;
}

if ($result_vocabulary >= 40) {
	$result_vocabulary_progress = 10;
} else {
	$result_vocabulary_progress = $result_vocabulary * 10 / 40;
}

if ($result_figurework >= 30) {
	$result_figurework_progress = 10;
} else {
	$result_figurework_progress = $result_figurework * 10 / 30;
}

// Grafik Potensi
$result_visual_potensi = 0;
$result_numerical_potensi = 0;
$result_verbal_potensi = 0;
$result_sequential_potensi = 0;
$result_spatial_potensi = 0;
$result_3d_potensi = 0;
$result_system_potensi = 0;
$result_vocabulary_potensi = 0;
$result_figurework_potensi = 0;

$result_visual_temp = $result_visual * 2.86;
if (0 < $result_visual_temp && $result_visual_temp <= 2.86) {
	$result_visual_potensi = 1;
} else if (2.87 <= $result_visual_temp && $result_visual_temp <= 5.72) {
	$result_visual_potensi = 2;
} else if (5.73 <= $result_visual_temp && $result_visual_temp <= 14.30) {
	$result_visual_potensi = 3;
} else if (14.31 <= $result_visual_temp && $result_visual_temp <= 20.00) {
	$result_visual_potensi = 4;
} else if (20.01 <= $result_visual_temp && $result_visual_temp <= 28.60) {
	$result_visual_potensi = 5;
} else if (28.61 <= $result_visual_temp && $result_visual_temp <= 37.14) {
	$result_visual_potensi = 6;
} else if (37.15 <= $result_visual_temp && $result_visual_temp <= 48.62) {
	$result_visual_potensi = 7;
} else if (48.63 <= $result_visual_temp && $result_visual_temp <= 57.14) {
	$result_visual_potensi = 8;
} else if (57.15 <= $result_visual_temp && $result_visual_temp <= 80.08) {
	$result_visual_potensi = 9;
} else if ((80.09 <= $result_visual_temp && $result_visual_temp <= 100) || $result_visual_temp > 100) {
	$result_visual_potensi = 10;
}

$result_numerical_temp = $result_numerical * 4.55;
if (0 < $result_numerical_temp && $result_numerical_temp <= 4.55) {
	$result_numerical_potensi = 1;
} else if (4.56 <= $result_numerical_temp && $result_numerical_temp <= 9.10) {
	$result_numerical_potensi = 2;
} else if (9.11 <= $result_numerical_temp && $result_numerical_temp <= 18.20) {
	$result_numerical_potensi = 3;
} else if (18.21 <= $result_numerical_temp && $result_numerical_temp <= 22.75) {
	$result_numerical_potensi = 4;
} else if (22.76 <= $result_numerical_temp && $result_numerical_temp <= 31.85) {
	$result_numerical_potensi = 5;
} else if (31.86 <= $result_numerical_temp && $result_numerical_temp <= 36.40) {
	$result_numerical_potensi = 6;
} else if (36.41 <= $result_numerical_temp && $result_numerical_temp <= 50.05) {
	$result_numerical_potensi = 7;
} else if (50.06 <= $result_numerical_temp && $result_numerical_temp <= 59.15) {
	$result_numerical_potensi = 8;
} else if (59.16 <= $result_numerical_temp && $result_numerical_temp <= 81.90) {
	$result_numerical_potensi = 9;
} else if ((81.91 <= $result_numerical_temp && $result_numerical_temp <= 100) || $result_numerical_temp > 100) {
	$result_numerical_potensi = 10;
}

$result_verbal_temp = $result_verbal * 2.86;
if (0 < $result_verbal_temp && $result_verbal_temp <= 2.86) {
	$result_verbal_potensi = 1;
} else if (2.87 <= $result_verbal_temp && $result_verbal_temp <= 8.58) {
	$result_verbal_potensi = 2;
} else if (8.59 <= $result_verbal_temp && $result_verbal_temp <= 17.16) {
	$result_verbal_potensi = 3;
} else if (17.17 <= $result_verbal_temp && $result_verbal_temp <= 22.88) {
	$result_verbal_potensi = 4;
} else if (22.89 <= $result_verbal_temp && $result_verbal_temp <= 28.60) {
	$result_verbal_potensi = 5;
} else if (28.61 <= $result_verbal_temp && $result_verbal_temp <= 34.32) {
	$result_verbal_potensi = 6;
} else if (34.33 <= $result_verbal_temp && $result_verbal_temp <= 39.90) {
	$result_verbal_potensi = 7;
} else if (39.91 <= $result_verbal_temp && $result_verbal_temp <= 45.62) {
	$result_verbal_potensi = 8;
} else if (45.61 <= $result_verbal_temp && $result_verbal_temp <= 73.35) {
	$result_verbal_potensi = 9;
} else if ((73.36 <= $result_verbal_temp && $result_verbal_temp <= 100) || $result_verbal_temp > 100) {
	$result_verbal_potensi = 10;
}

$result_sequential_temp = $result_sequential * 2.86;
if (0 < $result_sequential_temp && $result_sequential_temp <= 2.86) {
	$result_sequential_potensi = 1;
} else if (2.87 <= $result_sequential_temp && $result_sequential_temp <= 8.58) {
	$result_sequential_potensi = 2;
} else if (8.59 <= $result_sequential_temp && $result_sequential_temp <= 17.16) {
	$result_sequential_potensi = 3;
} else if (17.17 <= $result_sequential_temp && $result_sequential_temp <= 22.88) {
	$result_sequential_potensi = 4;
} else if (22.89 <= $result_sequential_temp && $result_sequential_temp <= 28.60) {
	$result_sequential_potensi = 5;
} else if (28.61 <= $result_sequential_temp && $result_sequential_temp <= 34.32) {
	$result_sequential_potensi = 6;
} else if (34.33 <= $result_sequential_temp && $result_sequential_temp <= 45.76) {
	$result_sequential_potensi = 7;
} else if (45.77 <= $result_sequential_temp && $result_sequential_temp <= 54.34) {
	$result_sequential_potensi = 8;
} else if (54.35 <= $result_sequential_temp && $result_sequential_temp <= 77.22) {
	$result_sequential_potensi = 9;
} else if ((77.23 <= $result_sequential_temp && $result_sequential_temp <= 100) || $result_sequential_temp > 100) {
	$result_sequential_potensi = 10;
}

$result_spatial_temp = $result_spatial * 1.43;
if (0 < $result_spatial_temp && $result_spatial_temp <= 5.72) {
	$result_spatial_potensi = 1;
} else if (5.73 <= $result_spatial_temp && $result_spatial_temp <= 12.87) {
	$result_spatial_potensi = 2;
} else if (12.88 <= $result_spatial_temp && $result_spatial_temp <= 25.74) {
	$result_spatial_potensi = 3;
} else if (25.75 <= $result_spatial_temp && $result_spatial_temp <= 37.18) {
	$result_spatial_potensi = 4;
} else if (37.19 <= $result_spatial_temp && $result_spatial_temp <= 42.90) {
	$result_spatial_potensi = 5;
} else if (42.91 <= $result_spatial_temp && $result_spatial_temp <= 48.62) {
	$result_spatial_potensi = 6;
} else if (48.63 <= $result_spatial_temp && $result_spatial_temp <= 54.34) {
	$result_spatial_potensi = 7;
} else if (54.35 <= $result_spatial_temp && $result_spatial_temp <= 60.06) {
	$result_spatial_potensi = 8;
} else if (60.07 <= $result_spatial_temp && $result_spatial_temp <= 80.08) {
	$result_spatial_potensi = 9;
} else if ((80.09 <= $result_spatial_temp && $result_spatial_temp <= 100) || $result_spatial_temp > 100) {
	$result_spatial_potensi = 10;
}

$result_3d_temp = $result_3d * 3.03;
if (0 < $result_3d_temp && $result_3d_temp <= 9.09) {
	$result_3d_potensi = 1;
} else if (9.10 <= $result_3d_temp && $result_3d_temp <= 18.18) {
	$result_3d_potensi = 2;
} else if (18.19 <= $result_3d_temp && $result_3d_temp <= 27.27) {
	$result_3d_potensi = 3;
} else if (27.28 <= $result_3d_temp && $result_3d_temp <= 36.36) {
	$result_3d_potensi = 4;
} else if (36.37 <= $result_3d_temp && $result_3d_temp <= 42.42) {
	$result_3d_potensi = 5;
} else if (42.43 <= $result_3d_temp && $result_3d_temp <= 48.48) {
	$result_3d_potensi = 6;
} else if (48.49 <= $result_3d_temp && $result_3d_temp <= 57.57) {
	$result_3d_potensi = 7;
} else if (57.58 <= $result_3d_temp && $result_3d_temp <= 63.63) {
	$result_3d_potensi = 8;
} else if (63.64 <= $result_3d_temp && $result_3d_temp <= 81.81) {
	$result_3d_potensi = 9;
} else if ((81.82 <= $result_3d_temp && $result_3d_temp <= 100) || $result_3d_temp > 100) {
	$result_3d_potensi = 10;
}

$result_system_temp = $result_system * 0.46;
if (0 < $result_system_temp && $result_system_temp <= 6.90) {
	$result_system_potensi = 1;
} else if (6.91 <= $result_system_temp && $result_system_temp <= 13.80) {
	$result_system_potensi = 2;
} else if (13.81 <= $result_system_temp && $result_system_temp <= 29.90) {
	$result_system_potensi = 3;
} else if (29.91 <= $result_system_temp && $result_system_temp <= 46.00) {
	$result_system_potensi = 4;
} else if (46.01 <= $result_system_temp && $result_system_temp <= 53.36) {
	$result_system_potensi = 5;
} else if (53.37 <= $result_system_temp && $result_system_temp <= 60.72) {
	$result_system_potensi = 6;
} else if (60.73 <= $result_system_temp && $result_system_temp <= 68.54) {
	$result_system_potensi = 7;
} else if (68.55 <= $result_system_temp && $result_system_temp <= 75.91) {
	$result_system_potensi = 8;
} else if (75.92 <= $result_system_temp && $result_system_temp <= 87.86) {
	$result_system_potensi = 9;
} else if ((87.87 <= $result_system_temp && $result_system_temp <= 100) || $result_system_temp > 100) {
	$result_system_potensi = 10;
}

if ($age < 15) {
	$result_vocabulary_temp = $result_vocabulary * 2.27;
	$a1 = 0;
	$a2 = 6.81;
	$a3 = 6.82;
	$a4 = 13.62;
	$a5 = 13.63;
	$a6 = 29.51;
	$a7 = 29.52;
	$a8 = 41.13;
	$a9 = 41.14;
	$a10 = 49.94;
	$a11 = 49.95;
	$a12 = 56.75;
	$a13 = 56.76;
	$a14 = 64.56;
	$a15 = 64.57;
	$a16 = 68.10;
	$a17 = 68.11;
	$a18 = 83.99;
	$a19 = 84.00;
	$a20 = 100;
} else if ($age >= 15 && $age < 18) {
	$result_vocabulary_temp = $result_vocabulary * 2.38;
	$a1 = 0;
	$a2 = 6.84;
	$a3 = 6.85;
	$a4 = 14.28;
	$a5 = 14.29;
	$a6 = 29.64;
	$a7 = 29.65;
	$a8 = 42.22;
	$a9 = 42.23;
	$a10 = 50.16;
	$a11 = 50.17;
	$a12 = 59.50;
	$a13 = 59.51;
	$a14 = 63.84;
	$a15 = 63.85;
	$a16 = 71.40;
	$a17 = 71.41;
	$a18 = 82.08;
	$a19 = 82.09;
	$a20 = 100;
} else {
	$result_vocabulary_temp = $result_vocabulary * 2.50;
	$a1 = 0;
	$a2 = 7.50;
	$a3 = 7.51;
	$a4 = 15.00;
	$a5 = 15.01;
	$a6 = 32.50;
	$a7 = 32.51;
	$a8 = 47.50;
	$a9 = 47.51;
	$a10 = 55.00;
	$a11 = 55.01;
	$a12 = 62.50;
	$a13 = 61.51;
	$a14 = 70.00;
	$a15 = 70.01;
	$a16 = 75.00;
	$a17 = 75.01;
	$a18 = 87.50;
	$a19 = 87.51;
	$a20 = 100;
}

if ($a1 < $result_vocabulary_temp && $result_vocabulary_temp <= $a2) {
	$result_vocabulary_potensi = 1;
} else if ($a3 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a4) {
	$result_vocabulary_potensi = 2;
} else if ($a5 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a6) {
	$result_vocabulary_potensi = 3;
} else if ($a7 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a8) {
	$result_vocabulary_potensi = 4;
} else if ($a9 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a10) {
	$result_vocabulary_potensi = 5;
} else if ($a11 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a12) {
	$result_vocabulary_potensi = 6;
} else if ($a13 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a14) {
	$result_vocabulary_potensi = 7;
} else if ($a15 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a16) {
	$result_vocabulary_potensi = 8;
} else if ($a17 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a18) {
	$result_vocabulary_potensi = 9;
} else if (($a19 <= $result_vocabulary_temp && $result_vocabulary_temp <= $a20) || $result_vocabulary_temp > 100) {
	$result_vocabulary_potensi = 10;
}

if ($age < 15) {
	$result_figurework_temp = $result_figurework * 3.13;
	$a1 = 0;
	$a2 = 3.13;
	$a3 = 3.14;
	$a4 = 9.39;
	$a5 = 9.40;
	$a6 = 15.64;
	$a7 = 15.65;
	$a8 = 18.78;
	$a9 = 18.79;
	$a10 = 28.17;
	$a11 = 28.18;
	$a12 = 37.56;
	$a13 = 37.57;
	$a14 = 46.95;
	$a15 = 46.97;
	$a16 = 56.34;
	$a17 = 56.35;
	$a18 = 78.25;
	$a19 = 78.26;
	$a20 = 100;
} else if ($age >= 15 && $age < 19) {
	$result_figurework_temp = $result_figurework * 3.23;
	$a1 = 0;
	$a2 = 3.23;
	$a3 = 3.24;
	$a4 = 9.69;
	$a5 = 9.70;
	$a6 = 16.15;
	$a7 = 16.16;
	$a8 = 19.38;
	$a9 = 19.39;
	$a10 = 29.07;
	$a11 = 29.08;
	$a12 = 38.76;
	$a13 = 38.77;
	$a14 = 48.45;
	$a15 = 48.46;
	$a16 = 58.14;
	$a17 = 58.15;
	$a18 = 80.75;
	$a19 = 80.76;
	$a20 = 100;
} else {
	$result_figurework_temp = $result_figurework * 3.33;
	$a1 = 0;
	$a2 = 3.33;
	$a3 = 3.34;
	$a4 = 9.99;
	$a5 = 10;
	$a6 = 16.65;
	$a7 = 16.66;
	$a8 = 19.98;
	$a9 = 19.99;
	$a10 = 29.97;
	$a11 = 29.98;
	$a12 = 39.96;
	$a13 = 39.97;
	$a14 = 49.95;
	$a15 = 49.96;
	$a16 = 59.94;
	$a17 = 59.95;
	$a18 = 79.92;
	$a19 = 79.93;
	$a20 = 100;
}

if ($a1 < $result_figurework_temp && $result_figurework_temp <= $a2) {
	$result_figurework_potensi = 1;
} else if ($a3 <= $result_figurework_temp && $result_figurework_temp <= $a4) {
	$result_figurework_potensi = 2;
} else if ($a5 <= $result_figurework_temp && $result_figurework_temp <= $a6) {
	$result_figurework_potensi = 3;
} else if ($a7 <= $result_figurework_temp && $result_figurework_temp <= $a8) {
	$result_figurework_potensi = 4;
} else if ($a9 <= $result_figurework_temp && $result_figurework_temp <= $a10) {
	$result_figurework_potensi = 5;
} else if ($a11 <= $result_figurework_temp && $result_figurework_temp <= $a12) {
	$result_figurework_potensi = 6;
} else if ($a13 <= $result_figurework_temp && $result_figurework_temp <= $a14) {
	$result_figurework_potensi = 7;
} else if ($a15 <= $result_figurework_temp && $result_figurework_temp <= $a16) {
	$result_figurework_potensi = 8;
} else if ($a17 <= $result_figurework_temp && $result_figurework_temp <= $a18) {
	$result_figurework_potensi = 9;
} else if (($a19 <= $result_figurework_temp && $result_figurework_temp <= $a20) || $result_figurework_temp > 100) {
	$result_figurework_potensi = 10;
}

function color($value)
{
	if ($value == 10) {
		return '\'rgba(0, 99, 132, 0.6)\'';
	} else if ($value == 9) {
		return '\'rgba(30, 99, 132, 0.6)\'';
	} else if ($value == 8) {
		return '\'rgba(60, 99, 132, 0.6)\'';
	} else if ($value == 7) {
		return '\'rgba(90, 99, 132, 0.6)\'';
	} else if ($value == 6) {
		return '\'rgba(120, 99, 132, 0.6)\'';
	} else if ($value == 5) {
		return '\'rgba(150, 99, 132, 0.6)\'';
	} else if ($value == 4) {
		return '\'rgba(180, 99, 132, 0.6)\'';
	} else if ($value == 3) {
		return '\'rgba(210, 99, 132, 0.6)\'';
	} else if ($value == 2) {
		return '\'rgba(240, 99, 132, 0.6)\'';
	} else if ($value == 1) {
		return '\'rgba(270, 99, 132, 0.6)\'';
	}
}
?>


<body>
	<main>
		<div class="container">
			<h2>-- Result --</h2>

			<table style="width:50%;margin-top:5%">
				<tr>
					<th>Waktu</th>
					<th>Tanggal</th>
				</tr>
				<tr>
					<td style="text-align: center;"><?= $waktu ?></td>
					<td style="text-align: center;"><?= $tanggal ?></td>
				</tr>
			</table>

			<table style="width:100%;margin-top:5%">
				<tr>
					<th>Visual</th>
					<th>Numerical</th>
					<th>Verbal</th>
					<th>Sequential</th>
					<th>Spatial</th>
					<th>3D</th>
					<th>System</th>
					<th>Vocabulary</th>
					<th>Figurework</th>
				</tr>
				<tr>
					<td><?= $result_visual ?></td>
					<td><?= $result_numerical ?></td>
					<td><?= $result_verbal ?></td>
					<td><?= $result_sequential ?></td>
					<td><?= $result_spatial ?></td>
					<td><?= $result_3d ?></td>
					<td><?= $result_system ?></td>
					<td><?= $result_vocabulary ?></td>
					<td><?= $result_figurework ?></td>
				</tr>
			</table>
		</div>
		<div class="container" style="margin-top: 2%;width:50%">
			<h2>Progress</h2>
			<canvas style="margin-top: 2%;" id="myChart"></canvas>
		</div>
		<div style="display:flex;flex-direction:row;margin-left:5%;margin-right:5%">
			<div class="container" style="margin-top: 2%;">
				<h2>Potensi</h2>
				<canvas style="margin-top: 2%;" id="myChart1"></canvas>
			</div>
			<div style="margin-left: 2%;"></div>
			<div class="container" style="margin-top: 2%;">
				<h2>Computer dan IT</h2>
				<canvas style="margin-top: 2%;" id="myChart2"></canvas>
			</div>
		</div>

		<div class="container">
			<table style="width:100%;margin-top:5%;margin-left:auto;margin-right:auto">
				<tr>
					<th>Hasil Akhir</th>
				</tr>
				<tr>
					<td style="text-align: center;" id="hasil1">

					</td>
				</tr>
			</table>
		</div>


		<div style="text-align:center;margin-top:3%;margin-bottom:3%">

			<a href="indexresult.php" class="start" style="border-radius: 20px;padding-left:5%;padding-right:5%">Kembali</a>
		</div>

	</main>
	<?php
	include "footer.php";
	?>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>
		var scoreData = {
			label: 'Grafik Progress',
			data: [<?= $result_visual_progress ?>, <?= $result_numerical_progress ?>, <?= $result_verbal_progress ?>, <?= $result_sequential_progress ?>, <?= $result_spatial_progress ?>, <?= $result_3d_progress ?>, <?= $result_system_progress ?>, <?= $result_vocabulary_progress ?>, <?= $result_figurework_progress ?>],
			backgroundColor: [
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)'
			],
			borderColor: [
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)'
			],
			borderWidth: 2,
			hoverBorderWidth: 0
		};

		var chartOptions = {
			indexAxis: 'y',
			scales: {
				yAxes: [{
					barPercentage: 0.5
				}]
			},
			elements: {
				rectangle: {
					borderSkipped: 'left',
				}
			}
		};

		var barChart = new Chart(myChart, {
			type: 'bar',
			data: {
				labels: ["Visual", "Numerical", "Verbal", "Sequential", "Spatial", "3D", "System", "Vocabulary", "Figurework"],
				datasets: [scoreData],
			},
			options: chartOptions
		});
	</script>
	<script>
		var scoreData = {
			label: 'Grafik Potensi',
			data: [<?= $result_visual_potensi ?>, <?= $result_numerical_potensi ?>, <?= $result_verbal_potensi ?>, <?= $result_sequential_potensi ?>, <?= $result_spatial_potensi ?>, <?= $result_3d_potensi ?>, <?= $result_system_potensi ?>, <?= $result_vocabulary_potensi ?>, <?= $result_figurework_potensi ?>],
			backgroundColor: [
				<?= color($result_visual_potensi) ?>,
				<?= color($result_numerical_potensi) ?>,
				<?= color($result_verbal_potensi) ?>,
				<?= color($result_sequential_potensi) ?>,
				<?= color($result_spatial_potensi) ?>,
				<?= color($result_3d_potensi) ?>,
				<?= color($result_system_potensi) ?>,
				<?= color($result_vocabulary_potensi) ?>,
				<?= color($result_figurework_potensi) ?>,
			],

			hoverBorderWidth: 0
		};

		var chartOptions = {
			indexAxis: 'y',
			scales: {
				yAxes: [{
					barPercentage: 0.5,
				}]
			},
			elements: {
				rectangle: {
					borderSkipped: 'left',
				}
			}
		};

		var barChart = new Chart(myChart1, {
			type: 'bar',
			data: {
				labels: ["Visual", "Numerical", "Verbal", "Sequential", "Spatial", "3D", "System", "Vocabulary", "Figurework"],
				datasets: [scoreData],
			},
			options: chartOptions
		});
	</script>
	<script>
		var scoreData = {
			label: 'Grafik Potensi',
			data: [<?= $result_visual_potensi ?>, <?= $result_numerical_potensi ?>, <?= $result_verbal_potensi ?>, <?= $result_sequential_potensi ?>, <?= $result_spatial_potensi ?>, <?= $result_3d_potensi ?>, <?= $result_system_potensi ?>, <?= $result_vocabulary_potensi ?>, <?= $result_figurework_potensi ?>],
			backgroundColor: [
				<?= color($result_visual_potensi) ?>,
				<?= color($result_numerical_potensi) ?>,
				<?= color($result_verbal_potensi) ?>,
				<?= color($result_sequential_potensi) ?>,
				<?= color($result_spatial_potensi) ?>,
				<?= color($result_3d_potensi) ?>,
				<?= color($result_system_potensi) ?>,
				<?= color($result_vocabulary_potensi) ?>,
				<?= color($result_figurework_potensi) ?>,
			],

			hoverBorderWidth: 0
		};

		var chartOptions = {
			indexAxis: 'y',
			scales: {
				yAxes: [{
					barPercentage: 0.5,
				}]
			},
			elements: {
				rectangle: {
					borderSkipped: 'left',
				}
			}
		};

		var barChart = new Chart(myChart1, {
			type: 'bar',
			data: {
				labels: ["Visual", "Numerical", "Verbal", "Sequential", "Spatial", "3D", "System", "Vocabulary", "Figurework"],
				datasets: [scoreData],
			},
			options: chartOptions
		});
	</script>
	<script>
		var scoreData = {
			label: 'Computer dan IT',
			data: [8, 8, 4, 8, 8, 5, 6, 4, 6],
			backgroundColor: [
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)',
				'rgba(0, 99, 132, 0.6)'
			],
			borderColor: [
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)',
				'rgba(0, 99, 132, 1)'
			],

			hoverBorderWidth: 0
		};

		var chartOptions = {
			indexAxis: 'y',
			scales: {
				yAxes: [{
					barPercentage: 0.5,
				}]
			},
			elements: {
				rectangle: {
					borderSkipped: 'left',
				}
			}
		};

		var barChart = new Chart(myChart2, {
			type: 'bar',
			data: {
				labels: ["Visual", "Numerical", "Verbal", "Sequential", "Spatial", "3D", "System", "Vocabulary", "Figurework"],
				datasets: [scoreData],
			},
			options: chartOptions
		});
	</script>

</body>

</html>
<?php
include 'fuzzyche.php';
include 'fuzzycns.php';
include 'fuzzycp.php';
include 'fuzzycsa.php';
include 'fuzzyda.php';

$resultFuzzyche = fuzzyche($result_visual_potensi, $result_numerical_potensi, $result_verbal_potensi, $result_sequential_potensi, $result_spatial_potensi, $result_3d_potensi, $result_system_potensi, $result_vocabulary_potensi, $result_figurework_potensi);
//$resultFuzzyche = fuzzyche(3, 5, 3, 8, 5, 5, 8, 8, 8);
$resultFuzzycns = fuzzycns($result_visual_potensi, $result_numerical_potensi, $result_verbal_potensi, $result_sequential_potensi, $result_spatial_potensi, $result_3d_potensi, $result_system_potensi, $result_vocabulary_potensi, $result_figurework_potensi);
//$resultFuzzycns = fuzzycns(8, 8, 8, 8, 8, 8, 8, 8, 8);
$resultFuzzycp = fuzzycp($result_visual_potensi, $result_numerical_potensi, $result_verbal_potensi, $result_sequential_potensi, $result_spatial_potensi, $result_3d_potensi, $result_system_potensi, $result_vocabulary_potensi, $result_figurework_potensi);
//$resultFuzzycp = fuzzycp(8, 8, 8, 8, 8, 8, 8, 8, 8);
$resultFuzzycsa = fuzzycsa($result_visual_potensi, $result_numerical_potensi, $result_verbal_potensi, $result_sequential_potensi, $result_spatial_potensi, $result_3d_potensi, $result_system_potensi, $result_vocabulary_potensi, $result_figurework_potensi);
//$resultFuzzycsa = fuzzycsa(8, 8, 8, 8, 8, 8, 8, 8, 8);
$resultFuzzycda = fuzzyda($result_visual_potensi, $result_numerical_potensi, $result_verbal_potensi, $result_sequential_potensi, $result_spatial_potensi, $result_3d_potensi, $result_system_potensi, $result_vocabulary_potensi, $result_figurework_potensi);
//$resultFuzzycda = fuzzyda(3, 8, 4, 8, 5, 8, 8, 8, 5);

$listHasil = array("CHE" => $resultFuzzyche, "CNS" => $resultFuzzycns, "CP" => $resultFuzzycp, "CSA" => $resultFuzzycsa, "DA" => $resultFuzzycda);
arsort($listHasil);
$index = 0;
$jenis = '';
$nilai = '';
foreach ($listHasil as $x => $x_value) {
	if ($index == 0) {
		$jenis = $x;
		$nilai = $x_value;
	}
	$index++;
}
if(($result_visual_potensi == 0) && ($result_numerical_potensi == 0) && ($result_verbal_potensi == 0) && ($result_sequential_potensi == 0) && ($result_spatial_potensi == 0) &&($result_3d_potensi == 0) && ($result_system_potensi == 0) && ($result_vocabulary_potensi == 0) && ($result_figurework_potensi == 0)) {
    $jenis="0";
}else if (($resultFuzzycda == 30) && ($resultFuzzyche == 30) && ($resultFuzzycp == 30) && ($resultFuzzycns == 30) && ($resultFuzzycsa == 30)){
    $jenis="1";
    }else if($jenis=="CHE"){
	$jenis="Computer Hardware Engineer";
}else if($jenis=="CNS"){
	$jenis="Computer Network Specialist";
}else if($jenis=="CP"){
	$jenis="Computer Programmer";
}else if($jenis=="CSA"){
	$jenis="Computer System Analyst";
}else if($jenis=="CDA"){
	$jenis="Database Administrator";
}
?>
<script>
    var jenis = "<?php echo $jenis?>";
    if(jenis == "0" || jenis == "1"){
        var hasil1 = "<?php echo "Jawaban tidak lengkap, tidak dapat menarik kesimpulan"; ?>"
    }else{
        var hasil1 = "<?php echo "Kamu cocok menjadi '".$jenis."'. Kamu memiliki nilai kecocokan sebesar : ".$nilai; ?>";
    }

	var hasilDiv1 = document.getElementById("hasil1");
	hasilDiv1.innerHTML = hasil1;
</script>
<?
?>