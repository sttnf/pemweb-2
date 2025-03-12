<?php
require_once "lingkaran.php";

echo 'Nilai PHI adalah ' . Lingkaran::phi. '<br>';
$lingkaran1 = new Lingkaran(10);
echo 'Jari-jari lingkaran = ' . $lingkaran1->jari . '<br>';
$lingkaran1->cetak();

echo "<br>";

$lingkaran2 = new Lingkaran(20);
echo 'Jari-jari lingkaran = ' . $lingkaran1->jari . '<br>';
$lingkaran2->cetak();
?>