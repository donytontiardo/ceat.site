<?php
require __DIR__ . '/../src/app.php';

$user = require_login();
$results = results_of($user['id']);
render_header('results');
?>
<main>
	<div class="container">
		<table>
			<tr>
				<th>No</th>
				<th>Waktu</th>
				<th>Tanggal</th>
				<th>Aksi</th>
			</tr>
			<?php foreach ($results as $i => $result): ?>
				<tr>
					<td><?= $i + 1 ?></td>
					<td><?= e($result['waktu']) ?></td>
					<td><?= e($result['tanggal']) ?></td>
					<td class="cell-center">
						<a href="final.php?id=<?= (int) $result['id_result'] ?>" class="start primary pill">Lihat</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<div class="actions">
		<a href="index.php" class="start pill">Kembali</a>
	</div>
</main>
<?php render_footer();
