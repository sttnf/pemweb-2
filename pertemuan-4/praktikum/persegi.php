<?php

class PersegiPanjang {
    public $panjang;
    public $lebar;

    public function __construct($panjang, $lebar)
    {
        $this->panjang = $panjang;
        $this->lebar = $lebar;
    }

    public function luas()
    {
        return $this->panjang * $this->lebar;
    }

    public function keliling()
    {
        return 2 * ($this->panjang + $this->lebar);
    }

    public function cetak()
    {
        echo "Panjang: " . $this->panjang . "<br>";
        echo "Lebar: " . $this->lebar . "<br>";
        echo "Luas: " . $this->luas() . "<br>";
        echo "Keliling: " . $this->keliling() . "<br>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Persegi Panjang</title>
</head>

<body>
    <h1>Persegi Panjang</h1>
    <form action="" method="post">
        <label for="panjang">Panjang</label>
        <input type="number" name="panjang" id="panjang" required>
        <br>
        <label for="lebar">Lebar</label>
        <input type="number" name="lebar" id="lebar" required>
        <br>
        <button type="submit" name="hitung">Hitung</button>
    </form>
    <br>
    <?php if (isset($_POST["hitung"])) : ?>
        <?php
        $panjang = $_POST["panjang"];
        $lebar = $_POST["lebar"];
        $persegiPanjang = new PersegiPanjang($panjang, $lebar);
        $persegiPanjang->cetak();
        ?>
    <?php endif; ?>
</body>
</html>
