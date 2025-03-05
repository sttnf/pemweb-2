<?php

function hitungNilaiAkhir($nilai_tugas, $nilai_uts, $nilai_uas) {
    define(
        'NILAI_TUGAS', 0.45
    );

    define(
        'NILAI_UTS', 0.30
    );

    define(
        'NILAI_UAS', 0.25
    );

    return ($nilai_tugas * NILAI_TUGAS) + ($nilai_uts * NILAI_UTS) + ($nilai_uas * NILAI_UAS);

}

function gradeNilai($nilai_akhir) {
    if ($nilai_akhir >= 85) {
        return 'A';
    } elseif ($nilai_akhir >= 70) {
        return 'B';
    } elseif ($nilai_akhir >= 60) {
        return 'C';
    } elseif ($nilai_akhir >= 50) {
        return 'D';
    } else {
        return 'E';
    }
};

