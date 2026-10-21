<?php
require __DIR__ . '/../src/app.php';

require_test();
$name = $_GET['section'] ?? '';
$section = is_string($name) ? section($name) : null;
$number = filter_var($_GET['n'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($section === null || $number === false || $number > question_count($name)) {
    not_found();
}
if (seconds_left($name) === 0) {
    redirect(after_section($name));
}
$question = find_question($name, $number);
if ($question === null) {
    not_found();
}
$left = start_section_clock($name);
$image = "images/$name/$number.jpg";

render_header('question');
?>
<main>
	<div class="container">
		<div class="timer">Timer: <span id="timer" data-seconds="<?= $left ?>"
			data-next="<?= e(after_section($name)) ?>"></span></div>

		<div class="current">Question <?= $number ?> of <?= question_count($name) ?> (<?= e($section['label']) ?>)</div>
		<?php /* question content comes from the question tables and may hold HTML (images, emphasis) */ ?>
		<div class="question"><?= $question['question_text'] ?></div>

		<?php if ($section['image'] === 'always'
		          || ($section['image'] === 'if_exists' && is_file(__DIR__ . "/$image"))): ?>
			<img class="question-image" src="<?= e($image) ?>" alt="Soal <?= $number ?>">
		<?php endif; ?>

		<p>Jawaban</p>
		<form method="post" action="process.php">
			<?= csrf_field() ?>
			<input type="hidden" name="section" value="<?= e($name) ?>">
			<input type="hidden" name="number" value="<?= $number ?>">

			<?php if ($section['input'] === 'image_choice'): ?>
				<div class="choices-section">
					<?php for ($i = 1; $i <= $section['choices']; $i++): ?>
						<label class="choice">
							<input name="choice" type="radio" value="<?= $i ?>"><br>
							<img class="choice-image" src="images/<?= e($name) ?>/<?= $number ?>_<?= $i ?>.jpg" alt="Pilihan <?= $i ?>">
						</label>
					<?php endfor; ?>
				</div>

			<?php elseif ($section['input'] === 'text_choice'): ?>
				<div class="choices-section">
					<?php for ($i = 1; $i <= $section['choices']; $i++): ?>
						<label class="choice"><input name="choice" type="radio" value="<?= $i ?>"><?= $question["choices_$i"] ?></label>
					<?php endfor; ?>
				</div>

			<?php elseif ($section['input'] === 'number' || $section['input'] === 'text'): ?>
				<div class="choices-input">
					<?php if ($section['input'] === 'number'): ?>
						<input type="number" name="choice" step="any" autocomplete="off">
					<?php else: ?>
						<input type="text" name="choice" autocomplete="off" maxlength="<?= MAX_ANSWER_LENGTH ?>">
					<?php endif; ?>
				</div>

			<?php elseif ($section['input'] === 'pair' || $name === 'spatial'): ?>
				<div class="choices-input">
					<?php for ($i = 1; $i <= $section['choices']; $i++): ?>
						<div class="field-row"><span><?= $i ?></span>
							<input type="text" name="choice<?= $i ?>" autocomplete="off" maxlength="<?= MAX_ANSWER_LENGTH ?>"></div>
					<?php endfor; ?>
				</div>

			<?php else: /* system: a row of small fields */ ?>
				<div class="row">
					<?php for ($i = 1; $i <= $section['choices']; $i++): ?>
						<div class="center"><span><?= $i ?></span>
							<input name="choice<?= $i ?>" type="text" autocomplete="off" class="input-system"
							       maxlength="<?= MAX_ANSWER_LENGTH ?>"></div>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<button type="submit" class="start" name="action" value="answer">Submit</button>
		</form>
	</div>
</main>
<script src="js/timer.js"></script>
<?php render_footer();
