<?php
require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/scoring.php';
require __DIR__ . '/../src/fuzzy.php';

$user = require_login();
$id = filter_var($_GET['id'] ?? (current_test()['result_id'] ?? null), FILTER_VALIDATE_INT);
$result = $id === false ? null : find_result($id, $user['id']);   // only the student's own tests
if ($result === null) {
    not_found();
}

$age = (int) (find_user($user['id'])['age'] ?? 0);
$grades = grade_result($result, $age);
$careers = career_fits(array_column($grades, 'potensi'));
$best = reset($careers['fits']);

// Computer and IT reference profile shown next to the student's, per subject
const REFERENCE_PROFILE = [8, 8, 4, 8, 8, 5, 6, 4, 6];

function potential_color(int $grade): string
{
    return 'rgba(' . min(255, (10 - $grade) * 30) . ', 99, 132, 0.6)';
}

$chartData = [
    'labels' => array_column(SECTIONS, 'label'),
    'progress' => array_column($grades, 'progress'),
    'potential' => array_column($grades, 'potensi'),
    'potentialColors' => array_map('potential_color', array_column($grades, 'potensi')),
    'reference' => REFERENCE_PROFILE,
];

render_header('result');
?>
<main>
	<div class="container">
		<h2>-- Result --</h2>
		<table class="half">
			<tr>
				<th>Waktu</th>
				<th>Tanggal</th>
			</tr>
			<tr>
				<td class="cell-center"><?= e($result['waktu']) ?></td>
				<td class="cell-center"><?= e($result['tanggal']) ?></td>
			</tr>
		</table>
		<table>
			<tr>
				<?php foreach (SECTIONS as $section): ?>
					<th><?= e($section['label']) ?></th>
				<?php endforeach; ?>
			</tr>
			<tr>
				<?php foreach ($grades as $grade): ?>
					<td><?= $grade['score'] ?></td>
				<?php endforeach; ?>
			</tr>
		</table>
	</div>

	<div class="container half chart-box">
		<h2>Progress</h2>
		<canvas id="chart-progress"></canvas>
	</div>
	<div class="charts">
		<div class="container chart-box">
			<h2>Potensi</h2>
			<canvas id="chart-potential"></canvas>
		</div>
		<div class="container chart-box">
			<h2>Computer dan IT</h2>
			<canvas id="chart-reference"></canvas>
		</div>
	</div>

	<div class="container">
		<table>
			<tr>
				<th>Hasil Akhir</th>
			</tr>
			<tr>
				<td class="cell-center">
					<?php if (!$careers['conclusive']): ?>
						Jawaban tidak lengkap, tidak dapat menarik kesimpulan
					<?php else: ?>
						Kamu cocok menjadi '<?= e($best['name']) ?>'. Kamu memiliki nilai kecocokan sebesar :
						<?= number_format($best['score'], 2) ?>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<th>Bidang</th>
				<th>Nilai Kecocokan</th>
			</tr>
			<?php foreach ($careers['fits'] as $fit): ?>
				<tr>
					<td><?= e($fit['name']) ?></td>
					<td class="cell-center"><?= number_format($fit['score'], 2) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>

	<div class="actions">
		<a href="indexresult.php" class="start pill">Kembali</a>
	</div>
</main>
<script type="application/json" id="chart-data"><?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="js/chart-4.4.1.umd.js"></script>
<script src="js/result.js"></script>
<?php render_footer();
