-- Two questions per section, enough to walk through every kind of answer.
INSERT INTO info_section VALUES
  ('visual', 'Pilihan gambar', 10, ''), ('numerical', 'Isian angka', 8, ''), ('verbal', 'Pilihan ganda', 10, ''),
  ('sequential', 'Isian huruf', 15, ''), ('spatial', 'Y/N', 10, ''), ('3d', 'Isian', 5, ''),
  ('system', 'Isian huruf', 4, ''), ('vocabulary', 'Pilihan ganda', 5, ''), ('figurework', 'Isian angka', 10, '');

INSERT INTO section_visual VALUES (1, 'Cari yang paling <i>berbeda</i>', '2'), (2, 'Bentuk selanjutnya?', '1');
INSERT INTO section_numerical VALUES (1, 'Angka yang hilang?', '4'), (2, 'Angka yang hilang?', '12');
INSERT INTO section_verbal VALUES
  (1, 'Kota mana yang paling jauh ke timur?', '1', 'Newport', 'Flatpeak', 'Daybridge', NULL, NULL),
  (2, 'Siapa yang paling tinggi?', '3', 'A', 'B', 'C', 'D', 'E');
INSERT INTO section_sequential VALUES (1, 'Isi 1 dan 2', 'ED'), (2, 'Isi 1 dan 2', 'AB');
INSERT INTO section_spatial VALUES (1, 'Alternatif?', 'NNYYN'), (2, 'Alternatif?', 'YYYYY');
INSERT INTO section_3d VALUES (1, 'Sisi A?', 'iii'), (2, 'Sisi B?', 'i');
INSERT INTO section_system VALUES (1, 'Tulis hurufnya', 'GFHKKBJDLGAC'), (2, 'Tulis hurufnya', 'ABCDEFGHIJKL');
INSERT INTO section_vocabulary VALUES
  (1, 'Memiliki', '1', 'Menguasai', 'Membayar', 'Merelakan', 'Melepas', NULL),
  (2, 'Membeli', '2', 'Menjual', 'Membayar', 'Meminjam', 'Memberi', NULL);
INSERT INTO section_figurework VALUES (1, 'Berapa hasil dari 1/5?', '0.20'), (2, 'Berapa 1/4?', '0.25');
