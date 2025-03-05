<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>
        Kalkulator
    </h1>

    <form action="operator.php" method="post">
        <label for="a">Nilai A</label>
        <input type="text" name="a" id="a">
        <br>

        <label for="b">Nilai B</label>
        <input type="text" name="b" id="b">
        <br>

        <label>Operator</label>
        <select name="operator" id="operator">
            <option value="+">+</option>
            <option value="-">-</option>
            <option value="*">*</option>
            <option value="/">/</option>
        </select>
        <br>
        <button type="submit">Hitung</button>
    </form>

        <?php

        $hasil = 0;

        if(isset($_POST['a']) && isset($_POST['b']) && isset($_POST['operator'])) {
            $a = $_POST['a'];
            $b = $_POST['b'];
            $operator = $_POST['operator'];


            if($operator == '+') {
                $hasil = $a + $b;
            } else if($operator == '-') {
                $hasil = $a - $b;
            } else if($operator == '*') {
                $hasil = $a * $b;
            } else if($operator == '/') {
                $hasil = $a / $b;
            }
        }
    ?>

    <p>
        Hasil: <?php echo $hasil; ?>
    </p>
    
</body>
</html>