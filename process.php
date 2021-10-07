<?php session_start(); ?>
<?php include 'database.php'; ?>
<?php

//Check to see if score is set_error_handler

if (!isset($_SESSION['visual_score'])) {
	$_SESSION['visual_score'] = 0;
}
if (!isset($_SESSION['numerical_score'])) {
	$_SESSION['numerical_score'] = 0;
}
if (!isset($_SESSION['verbal_score'])) {
	$_SESSION['verbal_score'] = 0;
}
if (!isset($_SESSION['sequential_score'])) {
	$_SESSION['sequential_score'] = 0;
}
if (!isset($_SESSION['spatial_score'])) {
	$_SESSION['spatial_score'] = 0;
}
if (!isset($_SESSION['3d_score'])) {
	$_SESSION['3d_score'] = 0;
}
if (!isset($_SESSION['system_score'])) {
	$_SESSION['system_score'] = 0;
}
if (!isset($_SESSION['vocabulary_score'])) {
	$_SESSION['vocabulary_score'] = 0;
}
if (!isset($_SESSION['figurework_score'])) {
	$_SESSION['figurework_score'] = 0;
}

if (isset($_POST['submitquestion'])) {
	$section  = $_POST['section'];
	$db_section = "section_" . $section;
	$nim = $_SESSION['nim'];
	$column_result = "result_" . $section;

	$number  = $_POST['number'];
	$next = $number + 1;

	$id_user = $_SESSION['id_user'];
	$tanggal =  $_SESSION['tanggal'];
	$waktu = $_SESSION['waktu'];

	$query_user = "SELECT * FROM result WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
	$result_user = $mysqli->query($query_user) or die($mysqli->error . __LINE__);
	$row_user = $result_user->fetch_assoc();
	$result_score = $row_user[$column_result];

	$query = "SELECT * FROM `{$db_section}`";
	$results = $mysqli->query($query) or die($mysqli->error . __LINE__);
	$total = $results->num_rows;

	$query = "SELECT * FROM `{$db_section}` WHERE question_number = $number";
	$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
	$row = $result->fetch_assoc();

	$correct_choice = $row['answer'];

	

	if ($section == 'visual') {
		$selected_choice = $_POST['choice'];

		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}

		$query_result = "UPDATE result SET $column_result = '$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=numerical");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'numerical') {
		$selected_choice = $_POST['choice'];
		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=verbal");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'verbal') {
		$selected_choice = $_POST['choice'];
		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}
		//echo $result_score;
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=sequential");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'sequential') {
		$selected_choice1 = $_POST['choice1'];
		$selected_choice2 = $_POST['choice2'];
		$selected_choice = $selected_choice1 . $selected_choice2;
		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=spatial");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'spatial') {
		$batch = ($number - 1) * 5;
		$choice[0] = $_POST['choice1'];
		$choice[1] = $_POST['choice2'];
		$choice[2] = $_POST['choice3'];
		$choice[3] = $_POST['choice4'];
		$choice[4] = $_POST['choice5'];
		for ($i = 0; $i < 5; $i++) {
			$selected_choice = $choice[$i];
			if ($selected_choice == '') {
				$result_score[$i + $batch] = "K";
			} else if ($correct_choice[$i] == $selected_choice) {
				$result_score[$i + $batch] = "B";
			} else if ($correct_choice[$i] != $selected_choice) {
				$result_score[$i + $batch] = "S";
			}
		}
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=3d");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == '3d') {
		$selected_choice = $_POST['choice'];
		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=system");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'system') {
		$batch = ($number - 1) * 12;
		$choice[0] = $_POST['choice1'];
		$choice[1] = $_POST['choice2'];
		$choice[2] = $_POST['choice3'];
		$choice[3] = $_POST['choice4'];
		$choice[4] = $_POST['choice5'];
		$choice[5] = $_POST['choice6'];
		$choice[6] = $_POST['choice7'];
		$choice[7] = $_POST['choice8'];
		$choice[8] = $_POST['choice9'];
		$choice[9] = $_POST['choice10'];
		$choice[10] = $_POST['choice11'];
		$choice[11] = $_POST['choice12'];

		for ($i = 0; $i < 12; $i++) {
			$selected_choice = $choice[$i];
			if ($selected_choice == '') {
				$result_score[$i + $batch] = "K";
			} else if ($correct_choice[$i] == $selected_choice) {
				$result_score[$i + $batch] = "B";
			} else if ($correct_choice[$i] != $selected_choice) {
				$result_score[$i + $batch] = "S";
			}
		}
		//echo $result_score;
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=vocabulary");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'vocabulary') {
		$selected_choice = $_POST['choice'];
		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: indextest.php?section=figurework");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
	if ($section == 'figurework') {
		$selected_choice = $_POST['choice'];
		if ($selected_choice == '') {
			$result_score[$_POST['number'] - 1] = "K";
		} else if ($correct_choice == $selected_choice) {
			$result_score[$_POST['number'] - 1] = "B";
		} else if ($correct_choice != $selected_choice) {
			$result_score[$_POST['number'] - 1] = "S";
		}
		$query_result = "UPDATE result SET $column_result='$result_score' WHERE id_user='$id_user' AND waktu='$waktu' AND tanggal='$tanggal'";
		$result = $mysqli->query($query_result) or die($mysqli->error . __LINE__);

		if ($number == $total) {
			header("Location: final.php");
			exit();
		} else {
			header("Location: question.php?section=" . $section . "&n=" . $next);
		}
	}
}

if (isset($_POST['starttest'])) {
	if ($_POST['starttest']) {

		date_default_timezone_set("Asia/Bangkok");
		$tanggal =  date("d/m/Y");
		$waktu = date("h:i:s");

		$id_user = $_SESSION['id_user'];

		$result_visual = "";
		for ($i = 0; $i < 32; $i++) {
			$result_visual .= "K";
		}
		$result_numerical = "";
		for ($i = 0; $i < 20; $i++) {
			$result_numerical .= "K";
		}
		$result_verbal = "";
		for ($i = 0; $i < 33; $i++) {
			$result_verbal .= "K";
		}
		$result_sequential = "";
		for ($i = 0; $i < 32; $i++) {
			$result_sequential .= "K";
		}
		$result_spatial = "";
		for ($i = 0; $i < 70; $i++) {
			$result_spatial .= "K";
		}
		$result_3d = "";
		for ($i = 0; $i < 30; $i++) {
			$result_3d .= "K";
		}
		$result_system = "";
		for ($i = 0; $i < 216; $i++) {
			$result_system .= "K";
		}
		$result_vocabulary = "";
		for ($i = 0; $i < 40; $i++) {
			$result_vocabulary .= "K";
		}
		$result_figurework = "";
		for ($i = 0; $i < 30; $i++) {
			$result_figurework .= "K";
		}
		$query = "INSERT INTO result (id_user,waktu,tanggal,result_visual,result_numerical,result_verbal,result_sequential,result_spatial,result_3d,result_system,result_vocabulary,result_figurework) VALUES ('$id_user','$waktu','$tanggal','$result_visual','$result_numerical','$result_verbal','$result_sequential','$result_spatial','$result_3d','$result_system','$result_vocabulary','$result_figurework')";
		$results = $mysqli->query($query);
		$_SESSION['waktu'] = $waktu;
		$_SESSION['tanggal'] = $tanggal;
		echo "<script>window.location.href='indextest.php?section=visual';</script>";
	}
}
?>