<?php
        class TorenAir {
            public $tinggi, $diameter;

            public function __construct($tinggi, $diameter) {
                $this->tinggi = $tinggi;
                $this->diameter = $diameter;
            }

            public function volume() {
                $radius = $this->diameter / 2;
                return 3.14 * pow($radius, 2) * $this->tinggi;
            }

            public function luasPermukaan() {
                $radius = $this->diameter / 2;
                return 2 * 3.14 * $radius * ($radius + $this->tinggi);
            }

            public function getHasil() {
                return [
                    'tinggi' => $this->tinggi,
                    'diameter' => $this->diameter,
                    'volume' => number_format($this->volume(), 2),
                    'luasPermukaan' => number_format($this->luasPermukaan(), 2)
                ];
            }
        }
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Perhitungan Toren Air</title>
            <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
        </head>
        <body class="min-h-screen py-8 px-4  bg-gradient-to-r from-purple-500 via-pink-500 to-blue-500">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-blue-600 p-4">
                    <h1 class="text-2xl font-bold text-white text-center">Kalkulator Toren Air</h1>
                </div>

                <div class="p-6">
                    <form action="" method="post" class="space-y-4">
                        <div>
                            <label for="tinggi" class="block text-sm font-medium text-gray-700">Tinggi (m)</label>
                            <input type="number" name="tinggi" id="tinggi" step="0.01" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>

                        <div>
                            <label for="diameter" class="block text-sm font-medium text-gray-700">Diameter (m)</label>
                            <input type="number" name="diameter" id="diameter" step="0.01" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>

                        <button type="submit" name="hitung"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Hitung
                        </button>
                    </form>

                    <?php if (isset($_POST["hitung"])): ?>
                        <?php
                        $torenAir = new TorenAir(floatval($_POST["tinggi"]), floatval($_POST["diameter"]));
                        $hasil = $torenAir->getHasil();
                        ?>
                        <div class="mt-6 bg-gray-50 rounded-md p-4">
                            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">Hasil Perhitungan:</h2>
                            <div class="grid grid-cols-2 gap-y-2">
                                <div class="text-gray-600">Tinggi:</div>
                                <div><?= $hasil['tinggi'] ?> m</div>

                                <div class="text-gray-600">Diameter:</div>
                                <div><?= $hasil['diameter'] ?> m</div>

                                <div class="text-gray-600">Volume:</div>
                                <div><?= $hasil['volume'] ?> m³</div>

                                <div class="text-gray-600">Luas Permukaan:</div>
                                <div><?= $hasil['luasPermukaan'] ?> m²</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>