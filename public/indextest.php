<?php
require __DIR__ . '/../src/app.php';

require_login();
$name = $_GET['section'] ?? null;

if ($name !== null) {
    $section = is_string($name) ? section($name) : null;
    if ($section === null) {
        not_found();
    }
    require_test();
    if (seconds_left($name) === 0) {
        redirect(after_section($name));
    }
    $info = section_info($name);
    render_header('test');
    ?>
	<main>
		<div class="container">
			<h2>Section <?= e($section['label']) ?></h2>
			<ul>
				<li>Jumlah Pertanyaan: <?= question_count($name) ?></li>
				<li>Jenis Pertanyaan: <?= e($info['type_questions']) ?></li>
				<li>Waktu: <?= intdiv(section_seconds($name), 60) ?> menit</li>
				<li>Petunjuk Pengerjaan:</li>
			</ul>
			<?php require __DIR__ . "/../src/views/instructions/$name.php"; ?>
			<p class="continue">
				<a href="question.php?section=<?= e(urlencode($name)) ?>&amp;n=1" class="start primary pill">Lanjutkan</a>
			</p>
		</div>
	</main>
    <?php
    render_footer();
    exit;
}

render_header('test');
?>
<main>
	<div class="container split">
		<div class="split-logo"><img src="images/logowhite.png" alt="CEAT"></div>
		<div class="section-list">
			<ul>
				<li>Terdapat <?= count(SECTIONS) ?> bagian:</li>
				<?php foreach (SECTIONS as $section): ?>
					<li><?= e($section['label']) ?></li>
				<?php endforeach; ?>
				<li>
					<form action="process.php" method="post">
						<?= csrf_field() ?>
						<button type="submit" class="start primary pill" name="action" value="start">Mulai</button>
					</form>
				</li>
			</ul>
		</div>
	</div>
	<div class="actions">
		<a href="index.php" class="start pill">Kembali</a>
	</div>
</main>
<?php render_footer();
