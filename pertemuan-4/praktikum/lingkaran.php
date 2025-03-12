<?php

class Lingkaran {
    public $jari;
    const phi = 3.14;

    public function __construct($jari)
    {
        $this->jari = $jari;
    }

    public function luas()
    {
        return self::phi * $this->jari * $this->jari;
    }

    public function keliling()
    {
        return 2 * self::phi * $this->jari;
    }

    public function cetak()
    {
        echo "Jari-jari: " . $this->jari ;
        echo "Keliling: " . $this->keliling() ;
    }
}

$lingkaran = new Lingkaran(10);
$lingkaran->cetak();