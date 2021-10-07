<?php
session_start();
?>
<?php
include "head.php";
include "database.php";
if (!isset($_SESSION['usertest'])) {
	echo "<script>window.location.href='login.php';</script>";
} else {
?>
	<?php
	if (isset($_GET['section'])) {
		$section  = $_GET['section'];
		$db_section = "section_" . $section;
		$query = "SELECT * FROM `{$db_section}`";
		$results = $mysqli->query($query) or die($mysqli->error . __LINE__);
		$total = $results->num_rows;

		$query = "SELECT * FROM info_section WHERE name_section = '$section'";
		$results = $mysqli->query($query) or die($mysqli->error . __LINE__);
		$row = $results->fetch_assoc();
		$type = $row['type_questions'];
		$time = $row['time'];
		$instruction = $row['instruction'];
	}
	?>

	<html>

	<body>

		<main>
			<?php
			if (isset($_GET['section'])) {
			?>
				<div class="container">
					<h2>Section <?= ucfirst($_GET['section']) ?></h2>
					<ul>
						<li>Jumlah Pertanyaan: <?php echo $total; ?></li>
						<li>Jenis Pertanyaan: <?php echo $type; ?></li>
						<li>Waktu: <?php echo $time; ?> menit</li>
						<li>Petunjuk Pengerjaan: </li>
					</ul>
					<?php if ($_GET['section'] == 'visual') {
					?>
						<p style="text-align: justify;"></a>Dalam tes ini terdapat dua macam pertanyaan, tipe pertanyaan pertama
							kamu harus menentukan objek mana yang paling “berbeda” dari semua objek yang
							ditampilkan. Objek yang paling “berbeda” adalah jawaban dari tipe pertama.</p>
						<b>Contoh 1 : Cari yang paling berbeda</b>
						<img src="images/instructions/visual-1.png" />
						<p style="text-align: justify;"></a>Jika kamu memilih jawaban B maka kamu benar. Mengapa? Karena B
							merupakan objek berbentuk lingkaran tanpa garis lurus yang menyertainya.</p>
						<p style="text-align: justify;"></a>Dalam tipe pertanyaan kedua kamu akan diberikan urutan dari objek atau
							bentuk. Tugasmu adalah memilih objek yang akan datang selanjutnya dari alternatif
							yang diberikan. Isi bagian kosong yang tertanda dengan tanda tanya pada tipe
							pertanyaan ini.</p>
						<h4>Contoh 2 : Cari bentuk selanjutnya</h4>
						<img src="images/instructions/visual-2.png" />
						<p style="text-align: justify;"></a>Jawaban untuk tipe ini adalah A, Mengapa? Jika diperhatikan bentuk
							pertama adalah lingkaran yang berubah seiring dengan urutan sehingga menjadi
							lingkaran penuh di bentuk ke-4 maka jawaban yang benar adalah A karena setelah
							lingkaran penuh pola yang benar adalah berubah menjadi lebih kecil. Kamu
							memiliki waktu 10 menit untuk mengerjakan soal sebisa mungkin. Untuk
							menjawab kamu cukup memilih pilihan yang tepat yang sudah disediakan.</p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'numerical') {
					?>
						<p style="text-align: justify;"></a>Pada tes ini kamu diberikan berbagai angka yang terhubung satu sama
							lainnya mereka terhubung dalam barisan yang sama, tetapi juga terdapat hubungan
							antar kolom. Terkadang ada angka yang hilang diganti dengan tanda strip (-) dan
							angka yang diganti dengan tanda tanya (?), dari semua informasi yang ada kamu
							harus mencari angka yang diganti dengan tanda tanya (?).</p>
						<h4>Contoh :</h4>
						<img src="images/instructions/numerical.png" />
						<p style="text-align: justify;"></a>Jawaban yang paling logis adalah empat (4) mengapa 4? Karena 4
							merupakan angka yang sesuai dengan urutan yaitu : 1, 2, 3, 4 dan 4 merupakan
							setengah dari 8 sebagaimana halnya 3 yang merupakan setengah dari 6. Jadi dengan
							menjawab 4 menjadikan angka terhubung secara logis. Kamu memiliki waktu 8
							menit untuk mengisi semua soal kerjakan sebisa mungkin. Untuk menjawab kamu
							cukup mengetik angka di dalam kolom jawaban yang sudah disediakan.</p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'verbal') {
					?>
						<p style="text-align: justify;"></a>Dalam tes ini kami ingin melihat sejauh mana kamu dapat mengambil
							kesimpulan yang logis dari informasi yang sudah diberikan. Selalu ada informasi
							yang cukup untuk menarik kesimpulan, baca dengan teliti untuk mendapatkan
							jawaban yang benar.</p>
						<h4>Contoh :</h4>
						<p>Kota <i>Newport</i> lebih jauh ke barat daripada kota <i>Flatpeak</i>, meskipun tidak sejauh
							kota <i>Daybridge</i>.</p>
						<p>Kota mana yang paling jauh ke timur?</p>
						<a>A) <i>Newport</i></a><br>
						<a>B) <i>Flatpeak</i></a><br>
						<a>C) <i>Daybridge</i></a><br>
						<p style="text-align: justify;"></a>Jika kamu menjawab Newport (A) maka jawabanmu benar, karena kota
							Daybridge merupakan kota yang paling jauh ke barat sedangkan Flatpeak
							merupakan kota yang terjauh kebarat kedua setelah Daybridge. Kamu punya waktu
							10 menit untuk melakukan semua tes, lakukan sebisa mungkin. Untuk menjawab
							pertanyaan cukup pilih jawaban yang paling benar dari yang sudah disediakan.</p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'sequential') {
					?>
						<p style="text-align: justify;"></a>Kamu akan diberikan urutan dari berbagai macam bentuk, dalam urutan
							tersebut terdapat dua bentuk yang hilang dan telah diganti dengan angka 1 dan 2.
							Pilih jawaban yang benar dengan petunjuk yang telah diberikan pada halaman.
							Tulis abjad yang tertera pada petunjuk dalam kolom jawaban untuk menjawab.</p>
						<h4>Contoh :</h4>
						<img src="images/instructions/sequential.png" />
						<p style="text-align: justify;"></a>Jawaban untuk pertanyaan diatas adalah 1 = E dan 2 = D, karena E dan D
							merupakan bentuk paling logis untuk melengkapi pertanyaan diatas, dengan bentuk
							E dan D maka urutan bentuk akan menjadi lengkap dan logis. Kamu punya 15 menit
							untuk menjawab semua soal sebisa mungkin, untuk menjawab cukup ketik abjad
							yang diperlukan dalam kolom jawaban dan <b>KETIK ABJAD DENGAN HURUF
							KAPITAL.</b></p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'spatial') {
					?>
						<p style="text-align: justify;"></a>Tes ini mengeksplorasi seberapa mudah kamu melihat objek terbalik dalam
							ruang, kamu akan diberikan sebuah objek dibawah objek ini akan ada lima objek
							berbeda setiap objek akan diberikan nomor. Kamu harus menentukan apakah objek
							dibawah merupakan alternatif dari objek diatas atau tidak.</p>
						<p style="text-align: justify;"></a>Objek dapat dikatakan alternatif apabila objek tersebut hanya terbalik atau
							terputar, sedangkan objek dikatakan tidak identik apabila proporsi atau bagian dari
							objek tersebut telah diubah. Kamu akan menjawab setiap pertanyaan dengan abjad
							‘Y’ untuk Ya dan ‘N’ untuk Tidak.</p>
						<h4>Contoh :</h4>
						<img src="images/instructions/spatial.png" />
						<a><br>(Jawaban untuk pertanyaan diatas : 1 = N 2 = N 3 = Y 4 = Y 5 = N)</br></a>
						<p style="text-align: justify;"></a>Kamu memiliki waktu untuk menjawab sebanyak 10 menit sebisa mungkin. Untuk
							menjawab cukub berikan abjad yang sesuai dalam kolom jawaban <b>ABJAD
							DIKETIK DENGAN HURUF KAPITAL.</b></p>
					<?php
					} ?>
					<?php if ($_GET['section'] == '3d') {
					?>
						<p style="text-align: justify;"></a>Dalam tes ini kamu akan harus mencari sisi yang tersembunyi dari sebuah
							objek. Kamu akan diberikan tumpukan balok, setiap balok memiliki ukuran yang
							sama. Kamu bisa melihat sisi depan dan atas, tapi kamu tidak dapat melihat sisi
							yang tersembunyi dari kamu dan juga sisi bawah. Bagaimanapun kamu bisa
							menggunakan imajenasi-mu untuk memberikan gambaran terhadap sisi yang
							tersembunyi.</p>
						<h4>Contoh :</h4>
						<img src="images/instructions/3d.png" />
						<p style="text-align: justify;"></a>Jawaban untuk sisi A adalah iii, sisi B adalah i, dan sisi C adalah v. Kamu
							memiliki waktu lima menit untuk menjawab semua soal sebisa mungkin. <b>Untuk
							menjawab pertanyaan cukup ketik abjad yang tertera (misal : iii, ii, i) KETIK
							DALAM HURUF NON-KAPITAL.</b></p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'system') {
					?>
						<p style="text-align: justify;"></a>Dalam tes ini kamu diberikan beberapa huruf dan simbol, setiap huruf
							memiliki simbolnya tersendiri. Tugasmu adalah menulis huruf sesuai dengan
							simbolnya.</p>
						<h4>Contoh :</h4>
						<img src="images/instructions/system.png" />
						<p style="text-align: justify;"></a>Jawaban untuk nomor 1 sampai 12 secara berurutan adalah : G, F, H, K, K, B, J, D,
							L, G, A, C.</p>
						<p style="text-align: justify;"></a>Kamu memiliki waktu empat menit untuk menjawab semua pertanyaan
							yang ada sebisa mungkin. Untuk menjawab cukup ketik abjad yang ada ke dalam
							kolom jawaban, <b>KETIK ABJAD DENGAN HURUF KAPITAL.</b></p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'vocabulary') {
					?>
						<p style="text-align: justify;"></a>Tes ini dilakukan untuk mengetahui seberapa jauh pemahamanmu tentang
							kata. Kamu akan diberikan kata dan alternatif yang memiliki makna yang
							mendekati, pilih makna yang paling mendekati untuk kata yang diberikan.</p>
						<h4>Contoh :</h4>
						<a>Memiliki</a><br><br>
						<a>A) Menguasai</a><br>
						<a>B) Membayar</a><br>
						<a>C) Merelakan</a><br>
						<a>D) Melepas</a><br>
						<p style="text-align: justify;"></a>Jika kamu memilih Menguasai (a) maka kamu memilih jawaban yang benar,
							kamu memiliki waktu yang tidak terbatas untuk tes ini, tapi mungkin akan percuma
							jika kamu mengerjakan lebih dari lima menit, karena kamu akan mengetahui
							apakah kamu paham dengan kata-katanya atau tidak. Untuk menjawab pertanyaan
							pilih saja pilihan yang tepat dari pilihan yang sudah diberikan. Jika memilih untuk
							mengerjakan hanya dalam lima menit tolong sediakan timer sendiri.</p>
					<?php
					} ?>
					<?php if ($_GET['section'] == 'figurework') {
					?>
						<p style="text-align: justify;"></a>Tes ini akan memberikan kamu berbagai macam pertanyaan mulai dari
							penjumlahan hingga perhitungan. Tes ini memiliki perhitungan aritmatik dan akan
							menguji pemahamanmu dalam desimal, persentase, dan pecahan. Kamu tidak
							diperbolehkan menggunakan kalkulator dalam tes.</p>
						<h4>Contoh :</h4>
						<a>Berapa hasil dari 1/5 ?</a><br><br>
						<a>Jawaban : 0.20</a>
						<p style="text-align: justify;"></a><b>Untuk menjawab cukup masukkan jawaban dalam kolom jawaban. Untuk pengerjaan tes ini ada beberapa aturan yang harus dilakukan antara lain :</p>
						<p style="margin-left: 2rem;">1. Desimal diketik dengan titik (.) bukan koma (,) seperti contoh diatas.</p>
						<p style="margin-left: 2rem;">2. Angka desimal diketik MINIMAL dua (2) angka dibelakang koma seperti
							contoh diatas.</p>
						<p style="margin-left: 2rem;">3. Jangan menulis simbol yang tidak diperlukan dalam pengerjaan seperti : $,
							£, €, %, dan lain-lain.</p>
						<p style="margin-left: 2rem;">4. Jika diminta pengerjaan dengan pecahan, jawab dengan menuliskan
							pecahan tersebut. Misal : 1/4, 2/3, 5/5</p>
						<p style="margin-left: 2rem;">5. Jika tidak diminta pengerjaan dengan pecahan maka jawab dengan desimal.</p></b>
					<?php
					} ?>
					<a href="question.php?section=<?= $_GET['section'] ?>&n=1" class="start" style="background-color:#23a0f1;color:white;font-size:.9rem;width:20%;border-radius:20px;text-align:center;margin-left:70%">Lanjutkan</a>
				</div>
			<?php
			} else {
			?>
				<div class="container" style="flex-direction: row; display:flex;justify-content:space-between">
					<div style="display:flex;flex:1;padding:100px;padding-top:50px;padding-bottom:50px"><img src="images/logowhite.png" style="width: 100%;height:fit-content"></div>
					<div style="display:flex;flex:1;margin-top:3%;margin-bottom:3%;">
						<ul style="width: 100%;">
							<li style="margin-bottom: 5%;">Terdapat 9 bagian :</li>
							<li>Visual</li>
							<li>Numerical</li>
							<li>Verbal</li>
							<li>Sequential</li>
							<li>Spatial</li>
							<li>3D</li>
							<li>System</li>
							<li>Vocabulary</li>
							<li>Figurework</li>

							<form action="./process.php" method="post">
								<input type="submit" class="start" name="starttest" value="Mulai" style="background-color:#23a0f1;color:white;font-size:.9rem;width:40%;border-radius:20px;text-align:center;margin-top:5%">
							</form>
						</ul>
					</div>
					



				</div>
				<div style="text-align:center;margin-bottom:3%">
						<a href="index.php" class="start" style="border-radius: 20px;padding-left:5%;padding-right:5%">Kembali</a>
					</div>
				<script>
					document.cookie = "visual_timer=false;";
					document.cookie = "numerical_timer=false;";
					document.cookie = "verbal_timer=false;";
					document.cookie = "sequential_timer=false;";
					document.cookie = "spatial_timer=false;";
					document.cookie = "3d_timer=false;";
					document.cookie = "system_timer=false;";
					document.cookie = "vocabulary_timer=false;";
					document.cookie = "figurework_timer=false;";
				</script>
			<?php

			} ?>
		</main>


		<?php
		include "footer.php";
		?>

	</body>

	</html>

<?php
}
?>