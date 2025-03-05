<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Array Sort</title>
</head>
<body>
    <?php
    $buah = ["a" => 'apel', "b" => 'jeruk', "c" => 'mangga', "d" => 'anggur', "e" => 'melon'];
    sort($buah);

    echo "<ol>" . PHP_EOL;
    foreach($buah as $index => $b) {
        echo "<li>index $index - nama buah $b</li>" . PHP_EOL;
    }
    echo "</ol>" . PHP_EOL;
    ?>
</body>
</html>