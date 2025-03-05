<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Array Pop</title>
</head>
<body>
    <?php
    $buah = ['apel', 'jeruk', 'mangga', 'anggur', 'melon'];
    echo "jumlah buah adalah " . count($buah);
    print_r($buah);
    echo "<br>";

    // Hapus buah index terakhir
    $buah_pop = array_pop($buah);

    echo "jumlah buah adalah " . count($buah);

    print_r($buah);
    ?>
</body>
</html>