<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Array</title>
</head>
<body>
    <?php
    $buah = ['apel', 'jeruk', 'mangga', 'anggur', 'melon'];

    echo "buah ke 2 adalah $buah[1]";
    
    echo "jumlah buah adalah " . count($buah);

    echo "<ol>" . PHP_EOL;
    for($i = 0; $i < count($buah); $i++) {
        echo "<li>$buah[$i]</li>" . PHP_EOL;
    }
    echo "</ol>" . PHP_EOL;

    // Tambah buah
    $buah[] = 'pepaya';
    echo "<li>$buah[5]</li>" . PHP_EOL;

    // Hapus buah index ke 3
    unset($buah[3]);

    // Ubah buah index ke 4
    $buah[4] = 'semangka';

    // Cetak seluruh buah dengan index
    echo "<ul>" . PHP_EOL;
    foreach($buah as $index => $b) {
        echo "<li>index $index - nama buah $b</li>" . PHP_EOL;
    }
    echo "</ul>" . PHP_EOL;
    ?>
</body>
</html>