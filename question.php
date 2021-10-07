<?php session_start(); ?>
<?php
include "head.php";
include "database.php";
if (!isset($_SESSION['usertest'])) {
	echo "<script>window.location.href='login.php';</script>";
} else {
?>
	<?php

	$section = $_GET['section'];

	$number = (int) $_GET['n'];
	$db_section = "section_" . $section;

	$query = "SELECT * FROM `{$db_section}`";
	$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
	$total = $result->num_rows;

	$query = "SELECT * FROM `{$db_section}` WHERE question_number = $number";
	$result = $mysqli->query($query) or die($mysqli->error . __LINE__);

	$question = $result->fetch_assoc();


	?>

	<body>
		<main>
			<div class="container">
				<div style="margin-top:3%;">
					<div style="background-color:#23a0f1;display:table;padding: 2px 10px 2px 10px">
						<a style="color:white">Timer : </a>
						<a style="color:white" id="demo"></a>
					</div>

				</div>

				<div class="current">Question <?php echo $question['question_number']; ?> of <?php echo $total; ?> <?php echo "(" . ucfirst($section) . ")"; ?></div>
				<p class="question">
					<?php
					$text_question = $question['question_text']; //text from a database (with line breaks escaped)
					stripslashes(nl2br($text_question));
					echo $text_question; ?>
				</p>
				<?php if (($section == 'numerical') || ($section == 'sequential') || ($section == 'spatial') || ($section == '3d') || ($section == 'system')) {
				?>
					<img width="400" src=<?= "images/" . $section . "/" . $question['question_number'] . ".jpg"  ?> />
				<?php } ?>

				<?php if (($section == 'visual') && file_exists("images/" . $section . "/" . $question['question_number'] . ".jpg")) {
				?>
					<img width="400" src=<?= "images/" . $section . "/" . $question['question_number'] . ".jpg"  ?> />
				<?php } ?>

				<p>Jawaban</p>

				<form method="post" action="process.php">
					<?php if (($section == 'visual')) {
					?>
						<div class="choices-section">
							<div>
								<input name="choice" type="radio" value="1" /><br>
								<img width="100" src=<?= "images/visual/" . $question['question_number'] . "_1.jpg"?> />
							</div>
							<div>
								<input name="choice" type="radio" value="2" /><br>
								<img width="100" src=<?= "images/visual/" . $question['question_number'] . "_2.jpg"  ?> />
							</div>
							<div>
								<input name="choice" type="radio" value="3" /><br>
								<img width="100" src=<?= "images/visual/" . $question['question_number'] . "_3.jpg"  ?> />
							</div>
							<div>
								<input name="choice" type="radio" value="4" /><br>
								<img width="100" src=<?= "images/visual/" . $question['question_number'] . "_4.jpg"  ?> />
							</div>

						</div>


					<?php } ?>

					<?php if (($section == 'numerical')) {
					?>
						<div class="choices-input">
							<input type="number" name="choice" autocomplete="off" />
						</div>
					<?php } ?>

					<?php if ($section == 'sequential') {
					?>
						<div class="choices-input">
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>1</span></div><input type="text" autocomplete="off" name="choice1" />
							</div>
							<br>
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>2</span></div><input type="text" autocomplete="off" name="choice2" />
							</div>

						</div>
					<?php } ?>

					<?php if ($section == 'spatial') {
					?>
						<div class="choices-input">
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>1</span></div><input type="text" autocomplete="off" name="choice1" />
							</div>
							<br>
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>2</span></div><input type="text" autocomplete="off" name="choice2" />
							</div>
							<br>
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>3</span></div><input type="text" autocomplete="off" name="choice3" />
							</div>
							<br>
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>4</span></div><input type="text" autocomplete="off" name="choice4" />
							</div>
							<br>
							<div style="flex-direction: row;display:flex">
								<div style="width: 1rem;"><span>5</span></div><input type="text" autocomplete="off" name="choice5" />
							</div>

						</div>
					<?php } ?>

					<?php if (($section == '3d') || ($section == 'figurework')) {
					?>
						<div class="choices-input">
							<input type="text" autocomplete="off" name="choice" />
						</div>
					<?php } ?>

					<?php if (($section == 'system')) {
					?>
						<div class="row">
							<div class="center">
								<a>1</a>
								<input name="choice1" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>2</a>
								<input name="choice2" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>3</a>
								<input name="choice3" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>4</a>
								<input name="choice4" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>5</a>
								<input name="choice5" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>6</a>
								<input name="choice6" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>7</a>
								<input name="choice7" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>8</a>
								<input name="choice8" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>9</a>
								<input name="choice9" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>10</a>
								<input name="choice10" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>11</a>
								<input name="choice11" type="text" autocomplete="off" class="input-system" />
							</div>
							<div class="center">
								<a>12</a>
								<input name="choice12" type="text" autocomplete="off" class="input-system" />
							</div>

						</div>
					<?php } ?>

					<?php if (($section == 'verbal')) {
					?>
						<div class="choices-section">
							<div>
								<input name="choice" type="radio" value="1" /><?php echo $question['choices_1']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="2" /><?php echo $question['choices_2']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="3" /><?php echo $question['choices_3']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="4" /><?php echo $question['choices_4']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="5" /><?php echo $question['choices_5']; ?>
							</div>


						</div>


					<?php } ?>

					<?php if (($section == 'vocabulary')) {
					?>
						<div class="choices-section">
							<div>
								<input name="choice" type="radio" value="1" /><?php echo $question['choices_1']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="2" /><?php echo $question['choices_2']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="3" /><?php echo $question['choices_3']; ?>
							</div>
							<div>
								<input name="choice" type="radio" value="4" /><?php echo $question['choices_4']; ?>
							</div>

						</div>


					<?php } ?>

					<input type="hidden" name="number" value="<?= $number; ?>" />
					<input type="hidden" name="section" value="<?= $section; ?>" />
					<input type="submit" class="start" name="submitquestion" value="Submit" />
				</form>

			</div>
		</main>
		<?php
		include "footer.php";
		?>
		<script>
			const queryString = window.location.search;
			const urlParams = new URLSearchParams(queryString);
			const section = urlParams.get('section')
			var name_timer = section + "_timer";

			function getCookie(cname) {
				var name = cname + "=";
				var decodedCookie = decodeURIComponent(document.cookie);
				var ca = decodedCookie.split(';');
				for (var i = 0; i < ca.length; i++) {
					var c = ca[i];
					while (c.charAt(0) == ' ') {
						c = c.substring(1);
					}
					if (c.indexOf(name) == 0) {
						return c.substring(name.length, c.length);
					}
				}
				return "false";
			}

			function getCookieName(name) {
				var match = document.cookie.match(RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
				return match ? match[1] : false;
			}

			var timerObject = function() {
				this.startTime = 60; //in Minutes
				this.doneClass = "done"; //optional styling applied to text when timer is done
				this.space = '       ';

				return this;
			};

			timerObject.prototype.startTimer = function(duration) {
				var me = this,
					timer = duration,
					minutes, seconds;
				var intervalLoop = setInterval(function() {
					minutes = parseInt(timer / 60, 10)
					seconds = parseInt(timer % 60, 10);
					minutes = minutes < 10 ? "0" + minutes : minutes;
					seconds = seconds < 10 ? "0" + seconds : seconds;
					textContent = "00" + me.space + " : " + minutes + " : " + me.space + seconds;
					document.getElementById("demo").innerHTML = textContent;
					document.cookie = name_timer + "=" + timer;
					if (--timer < 0) {
						document.cookie = name_timer + "=end";
						if (section == 'visual') {
							location.href = "indextest.php?section=numerical";
						} else if (section == 'numerical') {
							location.href = "indextest.php?section=verbal";
						} else if (section == 'verbal') {
							location.href = "indextest.php?section=sequential";
						} else if (section == 'sequential') {
							location.href = "indextest.php?section=spatial";
						} else if (section == 'spatial') {
							location.href = "indextest.php?section=3d";
						} else if (section == '3d') {
							location.href = "indextest.php?section=system";
						} else if (section == 'system') {
							location.href = "indextest.php?section=vocabulary";
						} else if (section == 'vocabulary') {
							location.href = "indextest.php?section=figurework";
						} else if (section == 'figurework') {
							location.href = "final.php";
						}
						clearInterval(intervalLoop);

					}
				}, 1000);
			}
			var rest = getCookie(name_timer);
			var restName = getCookieName(name_timer);
			if (rest == 'end') {
				if (section == 'visual') {
					location.href = "indextest.php?section=numerical";
				} else if (section == 'numerical') {
					location.href = "indextest.php?section=verbal";
				} else if (section == 'verbal') {
					location.href = "indextest.php?section=sequential";
				} else if (section == 'sequential') {
					location.href = "indextest.php?section=spatial";
				} else if (section == 'spatial') {
					location.href = "indextest.php?section=3d";
				} else if (section == '3d') {
					location.href = "indextest.php?section=system";
				} else if (section == 'system') {
					location.href = "indextest.php?section=vocabulary";
				} else if (section == 'vocabulary') {
					location.href = "indextest.php?section=figurework";
				} else if (section == 'figurework') {
					location.href = "final.php";
				}
			} else if (rest == 'false' || restName == false) {
				var t1 = new timerObject();
				var t = 0;
				if (section == "visual") {
					t = 600;
				} else if (section == "numerical") {
					t = 480;
				} else if (section == "verbal") {
					t = 600;
				} else if (section == "sequential") {
					t = 900;
				} else if (section == "spatial") {
					t = 600;
				} else if (section == "3d") {
					t = 300;
				} else if (section == "system") {
					t = 240;
				} else if (section == "vocabulary") {
					t = 300;
				} else if (section == "figurework") {
					t = 600;
				}
				t1.startTimer(t);
			} else {
				if (rest > 0) {
					var t1 = new timerObject();
					t1.startTimer(rest - 1);
				}
			}
		</script>
	</body>

	</html>

<?php
}
?>