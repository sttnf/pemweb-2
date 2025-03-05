<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Array Push</title>
</head>
<body>
    <?php 
    $buah = ['apel', 'jeruk', 'mangga', 'anggur', 'melon'];
    echo "jumlah buah adalah " . count($buah);
    print_r($buah);
    echo "<br>";

    // Tambah buah index terakhir
    array_push($buah, 'pepaya');
    echo "jumlah buah adalah " . count($buah);

    foreach ($buah as $index => $b) {
        echo "<li>index $index - nama buah $b</li>" . PHP_EOL;
    }
    ?>
    

</body>
</html>