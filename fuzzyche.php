<?php



function fuzzyche($n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9)
{
    // Data Inputan
    //$inputNilai = [$result_visual_potensi, $result_numerical_potensi, $result_verbal_potensi, $result_sequential_potensi, $result_spatial_potensi, $result_3d_potensi, $result_system_potensi, $result_vocabulary_potensi, $result_figurework_potensi];

    $inputNilai = [$n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9];
    //Fuzzifikasi
    //Anggota Himpunan Samar
    //Anggota Visual
    $visualburuk = 0;
    $visualcukup = 0;
    $visualbagus = 0;
    //Anggota Numerical
    $numericalburuk = 0;
    $numericalcukup = 0;
    $numericalbagus = 0;
    //Anggota Verbal
    $verbalburuk = 0;
    $verbalcukup = 0;
    $verbalbagus = 0;
    //Anggota Sequences
    $sequenceburuk = 0;
    $sequencecukup = 0;
    $sequencebagus = 0;
    //Anggota Spatial
    $spatialburuk = 0;
    $spatialcukup = 0;
    $spatialbagus = 0;
    //Anggota 3D
    $tdburuk = 0;
    $tdcukup = 0;
    $tdbagus = 0;
    //Anggota system
    $systemburuk = 0;
    $systemcukup = 0;
    $systembagus = 0;
    //Anggota Vocabulary
    $vocabburuk = 0;
    $vocabcukup = 0;
    $vocabbagus = 0;
    //Anggota figurework
    $figburuk = 0;
    $figcukup = 0;
    $figbagus = 0;

// Fuzzifikasi Visual
if (($inputNilai[0] <= 2) && ($inputNilai[0] >= 0)) {
    $visualburuk = 1;
} else if ($inputNilai[0] < 4) {
    $visualburuk = ((-$inputNilai[0] + 4) / 2);
} else $visualburuk = 0;

if (($inputNilai[0] <= 2) || ($inputNilai[0] >= 8)) {
    $visualcukup = 0;
} else if ($inputNilai[0] > 2 && $inputNilai[0] < 4) {
    $visualcukup = (($inputNilai[0] - 2) / 2);
} else if ($inputNilai[0] > 6 && $inputNilai[0] < 8) {
    $visualcukup = ((-$inputNilai[0] + 8) / 2);
} else if ($inputNilai[0] >= 4 && $inputNilai[0] <= 6) {
    $visualcukup = 1;
};

if ($inputNilai[0] <= 6) {
    $visualbagus = 0;
} else if ($inputNilai[0] > 6 && $inputNilai[0] < 8) {
    $visualbagus = (($inputNilai[0] - 6) / 2);
} else $visualbagus = 1;

// Fuzzifikasi Numerical
if (($inputNilai[1] <= 2) && ($inputNilai[1] >= 0)) {
    $numericalburuk = 1;
} else if ($inputNilai[1] < 4) {
    $numericalburuk = ((-$inputNilai[1] + 4) / 2);
} else $numericalburuk = 0;

if (($inputNilai[1] <= 2) || ($inputNilai[1] >= 8)) {
    $numericalcukup = 0;
} else if ($inputNilai[1] > 2 && $inputNilai[1] < 4) {
    $numericalcukup = (($inputNilai[1] - 2) / 2);
} else if ($inputNilai[1] > 6 && $inputNilai[1] < 8) {
    $numericalcukup = ((-$inputNilai[1] + 8) / 2);
} else if ($inputNilai[1] >= 4 && $inputNilai[1] <= 6) {
    $numericalcukup = 1;
};

if ($inputNilai[1] <= 6) {
    $numericalbagus = 0;
} else if ($inputNilai[1] > 6 && $inputNilai[1] < 8) {
    $numericalbagus = (($inputNilai[1] - 6) / 2);
} else $numericalbagus = 1;

// Fuzzifikasi Verbal
if (($inputNilai[2] <= 2) && ($inputNilai[2] >= 0)) {
    $verbalburuk = 1;
} else if ($inputNilai[2] < 3) {
    $verbalburuk = ((-$inputNilai[2] + 3) / 1);
} else $verbalburuk = 0;

if (($inputNilai[2] <= 2) || ($inputNilai[2] >= 6)) {
    $verbalcukup = 0;
} else if ($inputNilai[2] > 2 && $inputNilai[2] < 3) {
    $verbalcukup = (($inputNilai[2] - 2) / 1);
} else if ($inputNilai[2] > 4 && $inputNilai[2] < 6) {
    $verbalcukup = ((-$inputNilai[2] + 6) / 2);
} else if ($inputNilai[2] >= 3 && $inputNilai[2] <= 4) {
    $verbalcukup = 1;
};

if ($inputNilai[2] <= 4) {
    $verbalbagus = 0;
} else if ($inputNilai[2] > 4 && $inputNilai[2] < 6) {
    $verbalbagus = (($inputNilai[2] - 4) / 2);
} else $verbalbagus = 1;

// Fuzzifikasi Sequences
if (($inputNilai[3] <= 1) && ($inputNilai[3] >= 0)) {
    $sequenceburuk = 1;
} else if ($inputNilai[3] < 2) {
    $sequenceburuk = ((-$inputNilai[3] + 2) / 1);
} else $sequenceburuk = 0;

if (($inputNilai[3] <= 1) || ($inputNilai[3] >= 4)) {
    $sequencecukup = 0;
} else if ($inputNilai[3] > 1 && $inputNilai[3] < 2) {
    $sequencecukup = (($inputNilai[3] - 1) / 1);
} else if ($inputNilai[3] > 2 && $inputNilai[3] < 3) {
    $sequencecukup = ((-$inputNilai[3] + 3) / 1);
} else if ($inputNilai[3] = 2) {
    $sequencecukup = 1;
};

if ($inputNilai[3] <= 2) {
    $sequencebagus = 0;
} else if ($inputNilai[3] > 2 && $inputNilai[3] < 4) {
    $sequencebagus = (($inputNilai[3] - 2) / 2);
} else $sequencebagus = 1;
// Fuzzifikasi Spatial
if (($inputNilai[4] <= 2) && ($inputNilai[4] >= 0)) {
    $spatialburuk = 1;
} else if ($inputNilai[4] < 4) {
    $spatialburuk = ((-$inputNilai[4] + 4) / 2);
} else $spatialburuk = 0;

if (($inputNilai[4] <= 2) || ($inputNilai[4] >= 8)) {
    $spatialcukup = 0;
} else if ($inputNilai[4] > 2 && $inputNilai[4] < 4) {
    $spatialcukup = (($inputNilai[4] - 2) / 2);
} else if ($inputNilai[4] > 6 && $inputNilai[4] < 8) {
    $spatialcukup = ((-$inputNilai[4] + 8) / 2);
} else if ($inputNilai[4] >= 4 && $inputNilai[4] <= 6) {
    $spatialcukup = 1;
};

if ($inputNilai[4] <= 6) {
    $spatialbagus = 0;
} else if ($inputNilai[4] > 6 && $inputNilai[4] < 8) {
    $spatialbagus = (($inputNilai[4] - 6) / 2);
} else $spatialbagus = 1;

// Fuzzifikasi 3D
if (($inputNilai[5] <= 2) && ($inputNilai[5] >= 0)) {
    $tdburuk = 1;
} else if ($inputNilai[5] < 4) {
    $tdburuk = ((-$inputNilai[5] + 4) / 2);
} else $tdburuk = 0;

if (($inputNilai[5] <= 2) || ($inputNilai[5] >= 8)) {
    $tdcukup = 0;
} else if ($inputNilai[5] > 2 && $inputNilai[5] < 4) {
    $tdcukup = (($inputNilai[5] - 2) / 2);
} else if ($inputNilai[5] > 6 && $inputNilai[5] < 8) {
    $tdcukup = ((-$inputNilai[5] + 8) / 2);
} else if ($inputNilai[5] >= 4 && $inputNilai[5] <= 6) {
    $tdcukup = 1;
};

if ($inputNilai[5] <= 6) {
    $tdbagus = 0;
} else if ($inputNilai[5] > 6 && $inputNilai[5] < 8) {
    $tdbagus = (($inputNilai[5] - 6) / 2);
} else $tdbagus = 1;

// Fuzzifikasi System
if (($inputNilai[6] <= 2) && ($inputNilai[6] >= 0)) {
    $systemburuk = 1;
} else if ($inputNilai[6] < 4) {
    $systemburuk = ((-$inputNilai[6] + 4) / 2);
} else $systemburuk = 0;

if (($inputNilai[6] <= 2) || ($inputNilai[6] >= 8)) {
    $systemcukup = 0;
} else if ($inputNilai[6] > 2 && $inputNilai[6] < 4) {
    $systemcukup = (($inputNilai[6] - 2) / 2);
} else if ($inputNilai[6] > 6 && $inputNilai[6] < 8) {
    $systemcukup = ((-$inputNilai[6] + 8) / 2);
} else if ($inputNilai[6] >= 4 && $inputNilai[6] <= 6) {
    $systemcukup = 1;
};

if ($inputNilai[6] <= 6) {
    $systembagus = 0;
} else if ($inputNilai[6] > 6 && $inputNilai[6] < 8) {
    $systembagus = (($inputNilai[6] - 6) / 2);
} else $systembagus = 1;

// Fuzzifikasi Vocabulary
if (($inputNilai[7] <= 2) && ($inputNilai[7] >= 0)) {
    $vocabburuk = 1;
} else if ($inputNilai[7] < 3) {
    $vocabburuk = ((-$inputNilai[7] + 3) / 1);
} else $vocabburuk = 0;

if (($inputNilai[7] <= 2) || ($inputNilai[7] >= 6)) {
    $vocabcukup = 0;
} else if ($inputNilai[7] > 2 && $inputNilai[7] < 3) {
    $vocabcukup = (($inputNilai[7] - 2) / 1);
} else if ($inputNilai[7] > 4 && $inputNilai[7] < 6) {
    $vocabcukup = ((-$inputNilai[7] + 6) / 2);
} else if ($inputNilai[7] >= 3 && $inputNilai[7] <= 4) {
    $vocabcukup = 1;
};

if ($inputNilai[7] <= 4) {
    $vocabbagus = 0;
} else if ($inputNilai[7] > 4 && $inputNilai[7] < 6) {
    $vocabbagus = (($inputNilai[7] - 4) / 2);
} else $vocabbagus = 1;

// Fuzzifikasi Figurework

if (($inputNilai[8] <= 2) && ($inputNilai[8] >= 0)) {
    $figburuk = 1;
} else if ($inputNilai[8] < 3) {
    $figburuk = ((-$inputNilai[8] + 3) / 1);
} else $figburuk = 0;

if (($inputNilai[8] <= 2) || ($inputNilai[8] >= 6)) {
    $figcukup = 0;
} else if ($inputNilai[8] > 2 && $inputNilai[8] < 3) {
    $figcukup = (($inputNilai[8] - 2) / 1);
} else if ($inputNilai[8] > 4 && $inputNilai[8] < 6) {
    $figcukup = ((-$inputNilai[8] + 6) / 2);
} else if ($inputNilai[8] >= 3 && $inputNilai[8] <= 4) {
    $figcukup = 1;
};

if ($inputNilai[8] <= 4) {
    $figbagus = 0;
} else if ($inputNilai[8] > 4 && $inputNilai[8] < 6) {
    $figbagus = (($inputNilai[8] - 4) / 2);
} else $figbagus = 1;



    //Inferensi
    // Anggota Inferensi
    //Visual
    $nilaivisual[0] = $visualburuk;
    $nilaivisual[1] = $visualcukup;
    $nilaivisual[2] = $visualbagus;
    //Numerical    
    $nilainumerical[0] = $numericalburuk;
    $nilainumerical[1] = $numericalcukup;
    $nilainumerical[2] = $numericalbagus;
    //Verbal
    $nilaiverbal[0] = $verbalburuk;
    $nilaiverbal[1] = $verbalcukup;
    $nilaiverbal[2] = $verbalbagus;
    //Sequence
    $nilaisequence[0] = $sequenceburuk;
    $nilaisequence[1] = $sequencecukup;
    $nilaisequence[2] = $sequencebagus;
    //Spatial
    $nilaispatial[0] = $spatialburuk;
    $nilaispatial[1] = $spatialcukup;
    $nilaispatial[2] = $spatialbagus;
    //Three Dimensions
    $nilaitd[0] = $tdburuk;
    $nilaitd[1] = $tdcukup;
    $nilaitd[2] = $tdbagus;
    //System
    $nilaisystem[0] = $systemburuk;
    $nilaisystem[1] = $systemcukup;
    $nilaisystem[2] = $systembagus;
    //Vocabulary
    $nilaivocab[0] = $vocabburuk;
    $nilaivocab[1] = $vocabcukup;
    $nilaivocab[2] = $vocabbagus;
    //Figurework
    $nilaifig[0] = $figburuk;
    $nilaifig[1] = $figcukup;
    $nilaifig[2] = $figbagus;

    //Nilai Kondisi
    $k = 0;
    $nilaiKondisi=[];
    $kondisi=[];

    //Nilai Inferensi
    $terbesarX = 0;
    $terbesarY = 0;



    // Menentukan Rules dan Nilai Minimum
   for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            for ($l = 0; $l < 3; $l++) {
                for ($m = 0; $m < 3; $m++) {
                    for ($n = 0; $n < 3; $n++) {
                        for ($o = 0; $o < 3; $o++) {
                            for ($p = 0; $p < 3; $p++) {
                                for ($q = 0; $q < 3; $q++) {
                                    for ($r = 0; $r < 3; $r++) {
                                        if (($nilaivisual[$i] > 0) && ($nilainumerical[$j] > 0) && ($nilaiverbal[$l] > 0) && ($nilaisequence[$m] > 0) && ($nilaispatial[$n] > 0) && ($nilaitd[$o] > 0) && ($nilaisystem[$p] > 0) && ($nilaivocab[$q] > 0) && ($nilaifig[$r] > 0)) {
                                            if (($nilaivisual[$i] < $nilainumerical[$j]) && ($nilaivisual[$i] < $nilaiverbal[$l]) && ($nilaivisual[$i] < $nilaisequence[$m]) && ($nilaivisual[$i] < $nilaispatial[$n]) && ($nilaivisual[$i] < $nilaitd[$o]) && ($nilaivisual[$i] < $nilaisystem[$p]) && ($nilaivisual[$i] < $nilaivocab[$q]) && ($nilaivisual[$i] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaivisual[$i];
                                            } else if (($nilainumerical[$j] < $nilaivisual[$i]) && ($nilainumerical[$j] < $nilaiverbal[$l]) && ($nilainumerical[$j] < $nilaisequence[$m]) && ($nilainumerical[$j] < $nilaispatial[$n]) && ($nilainumerical[$j] < $nilaitd[$o]) && ($nilainumerical[$j] < $nilaisystem[$p]) && ($nilainumerical[$j] < $nilaivocab[$q]) && ($nilainumerical[$j] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilainumerical[$j];
                                            } else if (($nilaiverbal[$l] < $nilaivisual[$i]) && ($nilaiverbal[$l] < $nilainumerical[$j]) && ($nilaiverbal[$l] < $nilaisequence[$m]) && ($nilaiverbal[$l] < $nilaispatial[$n]) && ($nilaiverbal[$l] < $nilaitd[$o]) && ($nilaiverbal[$l] < $nilaisystem[$p]) && ($nilaiverbal[$l] < $nilaivocab[$q]) && ($nilaiverbal[$l] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaiverbal[$l];
                                            } else if (($nilaisequence[$m] < $nilaivisual[$i]) && ($nilaisequence[$m] < $nilainumerical[$j]) && ($nilaisequence[$m] < $nilaiverbal[$l]) && ($nilaisequence[$m] < $nilaispatial[$n]) && ($nilaisequence[$m] < $nilaitd[$o]) && ($nilaisequence[$m] < $nilaisystem[$p]) && ($nilaisequence[$m] < $nilaivocab[$q]) && ($nilaisequence[$m] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaisequence[$m];
                                            } else if (($nilaispatial[$n] < $nilaivisual[$i]) && ($nilaispatial[$n] < $nilainumerical[$j]) && ($nilaispatial[$n] < $nilaiverbal[$l]) && ($nilaispatial[$n] < $nilaisequence[$m]) && ($nilaispatial[$n] < $nilaitd[$o]) && ($nilaispatial[$n] < $nilaisystem[$p]) && ($nilaispatial[$n] < $nilaivocab[$q]) && ($nilaispatial[$n] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaispatial[$n];
                                            } else if (($nilaitd[$o] < $nilaivisual[$i]) && ($nilaitd[$o] < $nilainumerical[$j]) && ($nilaitd[$o] < $nilaiverbal[$l]) && ($nilaitd[$o] < $nilaisequence[$m]) && ($nilaitd[$o] < $nilaispatial[$n]) && ($nilaitd[$o] < $nilaisystem[$p]) && ($nilaitd[$o] < $nilaivocab[$q]) && ($nilaitd[$o] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaitd[$o];
                                            } else if (($nilaisystem[$p] < $nilaivisual[$i]) && ($nilaisystem[$p] < $nilainumerical[$j]) && ($nilaisystem[$p] < $nilaiverbal[$l]) && ($nilaisystem[$p] < $nilaisequence[$m]) && ($nilaisystem[$p] < $nilaispatial[$n]) && ($nilaisystem[$p] < $nilaitd[$o]) && ($nilaisystem[$p] < $nilaivocab[$q]) && ($nilaisystem[$p] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaisystem[$p];
                                            } else if (($nilaivocab[$q] < $nilaivisual[$i]) && ($nilaivocab[$q] < $nilainumerical[$j]) && ($nilaivocab[$q] < $nilaiverbal[$l]) && ($nilaivocab[$q] < $nilaisequence[$m]) && ($nilaivocab[$q] < $nilaispatial[$n]) && ($nilaivocab[$q] < $nilaitd[$o]) && ($nilaivocab[$q] < $nilaisystem[$p]) && ($nilaivocab[$q] < $nilaifig[$r])) {
                                                $nilaiKondisi[$k] = $nilaivocab[$q];
                                            } else $nilaiKondisi[$k] = $nilaifig[$r];
                                            if (($i == 2) && ($j == 2) && ($n == 2) && ($o == 2) && ($p == 2)) {
                                                $kondisi[$k] = "TINGGI";
                                            } else if ((($i >= 1) && ($j == 2) && ($p == 2) && ($o == 2) && ($n == 2)) || (($i == 2) && ($j >= 1) && ($o == 2) && ($p == 2) && ($n == 2)) || (($i == 2) && ($j == 2) && ($p >= 1) && ($o == 2) && ($n == 2)) || (($i == 2) && ($j == 2) && ($p == 2) && ($o == 2) && ($n >= 1)) || (($i == 2) && ($j == 2) && ($p == 2) && ($o >= 1) && ($n == 2))) {
                                                $kondisi[$k] = "TINGGI";
                                            } else if (($i >= 1) && ($j >= 1) && ($l == 2) && ($p >= 1) && ($n >= 1) && ($o >= 1) && ($m == 2) && ($q == 2) && ($r == 2)) {
                                                $kondisi[$k] = "TINGGI";
                                            } else {
                                                $kondisi[$k] = "RENDAH";
                                            }
                                            $rule[$k] = "Nilai Visual " . $nilaivisual[$i] . ", Numerik " . $nilainumerical[$j] . ", Verbal " . $nilaiverbal[$l] . ", Sequence " . $nilaisequence[$m] . ", spatial " . $nilaispatial[$n] . ", 3D " . $nilaitd[$o] . ", System " . $nilaisystem[$p] . ", Vocabulary " . $nilaivocab[$q] . ", dan Figurework " . $nilaifig[$r] . " maka siswa memiliki kecocokan " . $kondisi[$k] . " dengan nilai = " . $nilaiKondisi[$k] . "<br>";                            
                                            $k = $k + 1;;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $all_rule = "";
    foreach ($rule as $key => $value) {
        $all_rule = $all_rule . $rule[$key] . "<br>";
    }

    $sum_rule = count($rule);
    $rules = "";
    if ($sum_rule == 1) {
        $rules = $rule[0];
    } else {
        for ($i = 0; $i < $sum_rule; $i++) {
            $rules = $rules . $rule[$i];
        }
    }
    $rules = "\"" . $rules . "\"";

    // Menentukan Nilai Max
    for ($i = 0; $i < $k; $i++) {
        if ($kondisi[$i] == "RENDAH") {
            if ($i == 0) {
                $terbesarX = $nilaiKondisi[$i];
            } else {
                if ($nilaiKondisi[$i] > $terbesarX) {
                    $terbesarX = $nilaiKondisi[$i];
                }
            }
        } else {
            if ($i == 0) {
                $terbesarY = $nilaiKondisi[$i];
            } else {
                if ($nilaiKondisi[$i] > $terbesarY) {
                    $terbesarY = $nilaiKondisi[$i];
                }
            }
        }
    }

    $nilai_inferensi1 = "";
    $nilai_inferensi2 = "";
    if ($terbesarX > 0) {
        //$nilai_inferensi1 = "<br><br>" . "Nilai ketidakcocokan mahasiswa = " . $terbesarX . "<br>";
        $nilai_inferensi1 = $terbesarX;
    }
    if ($terbesarY > 0) {
        //$nilai_inferensi2 = "<br><br>" . "Nilai kecocokan mahasiswa = " . $terbesarY . "<br>";
        $nilai_inferensi2 = $terbesarY;
    }
    $nilai_inferensi = $nilai_inferensi1 . $nilai_inferensi2;
    $nilai_inferensi = "\"" . $nilai_inferensi . "\"";


    // Defuzzifikasi
    $sampel = 10;
    $hasilPembilang = 0;
    $hasilPenyebut = 0;
    $hasildeffuzzifikasi = 0;
    $titik_sampel = 0;
    $jumlah_sampelX = 0;
    $jumlah_sampelY = 0;
    $pengaliZ = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    $titik_sampelZ = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    $delta = 0;
    $k = 0;
    $pengaliX = $terbesarX;
    $pengaliY = $terbesarY;
    $delta = 100 / $sampel;
    $titik_sampel += $delta;
    for ($i = 1; $i <= $sampel; $i++) {
        if ($titik_sampel <= 50) {
            $hasilPembilang += $titik_sampel * $pengaliX;
            $jumlah_sampelX += 1;
        } else if ($titik_sampel >= 80) {
            $hasilPembilang += $titik_sampel * $pengaliY;
            $jumlah_sampelY += 1;
        } else if (($titik_sampel > 50) && ($titik_sampel < 80)) {
            if ($pengaliX > $pengaliY) {
                $titik_sampelZ[$k] = (((80 - $titik_sampelZ[$k]) / 30) * 1000);
                $pengaliZ[$k] = ($pengaliZ[$k] / 1000);
                $hasilPembilang += $titik_sampelZ[$k] * $pengaliZ[$k];
            } else {
                $titik_sampelZ[$k] = $titik_sampel;
                $pengaliZ[$k] = ((($titik_sampelZ[$k] - 50) / 30) * 1000);
                $pengaliZ[$k] = ($pengaliZ[$k] / 1000);
                $hasilPembilang += $titik_sampelZ[$k] * $pengaliZ[$k];
            }
            $k += 1;
        }
        $titik_sampel += $delta;
    }
    $hasilPenyebut = ($jumlah_sampelX * $pengaliX) + ($jumlah_sampelY * $pengaliY);
    for ($i = 0; $i < $k; $i++) {
        $hasilPenyebut += $pengaliZ[$i];
    }
    $hasildeffuzzifikasi = $hasilPembilang / $hasilPenyebut;
    //return $infodef = "Kamu cocok menjadi 'Computer Hardware Engineer' Kamu memiliki nilai kecocokan sebesar : " . $hasildeffuzzifikasi;
    return $hasildeffuzzifikasi;
}
