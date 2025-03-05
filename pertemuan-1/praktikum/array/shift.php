<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Array Shift</title>
</head>
<body>

<?php   
$buah = ['apel', 'jeruk', 'mangga', 'anggur', 'melon'];
echo "jumlah buah adalah " . count($buah);
print_r($buah);

// Hapus buah index pertama
$buah_shift = array_shift($buah);

echo "jumlah buah adalah " . count($buah);

foreach ($buah as $index => $b) {
    echo "<li>index $index - nama buah $b</li>" . PHP_EOL;
}
?>
    
</body>
</html>