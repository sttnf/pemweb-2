<?php
session_start();

class ListsNilai
{
    private $data;

    public function __construct()
    {
        if (!isset($_SESSION['nilai'])) {
            $_SESSION['nilai'] = [];
        }
        $this->data = &$_SESSION['nilai'];
    }

    public function getAllData()
    {
        return $this->data;
    }

    public function addData($nama, $mataKuliah, $tugas, $uts, $uas)
    {
        $nilaiAkhir = ($tugas * 0.3) + ($uts * 0.35) + ($uas * 0.35);
        $status = $nilaiAkhir >= 60 ? "Lulus" : "Tidak Lulus";
        $grade = $this->calculateGrade($nilaiAkhir);
        $statusGrade = $this->calculateStatusGrade($grade);

        $this->data[] = [
            'id' => count($this->data) + 1,
            'nama' => htmlspecialchars($nama),
            'mataKuliah' => htmlspecialchars($mataKuliah),
            'tugas' => floatval($tugas),
            'uts' => floatval($uts),
            'uas' => floatval($uas),
            'nilaiAkhir' => number_format($nilaiAkhir, 2),
            'status' => $status,
            'grade' => $grade,
            'statusGrade' => $statusGrade
        ];
    }

    private function calculateGrade($nilai)
    {
        if ($nilai >= 85) return "A";
        if ($nilai >= 70) return "B";
        if ($nilai >= 56) return "C";
        if ($nilai >= 36) return "D";
        return "E";
    }

    private function calculateStatusGrade($grade)
    {
        return match ($grade) {
            "A" => "Sangat Memuaskan",
            "B" => "Memuaskan",
            "C" => "Cukup",
            "D" => "Kurang",
            "E" => "Sangat Kurang",
            default => "Tidak Ada",
        };
    }

    public function renderTableRows()
    {

        $rows = "";
        foreach ($this->getAllData() as $index => $row) {
            $bgClass = $index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
            $statusClass = $row['status'] === 'Lulus' ? 'text-green-600 font-medium' : 'text-red-600 font-medium';

            $rows .= "<tr class='$bgClass hover:bg-gray-100 transition-colors'>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200'>{$row['id']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200 font-medium'>{$row['nama']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200'>{$row['mataKuliah']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200'>{$row['tugas']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200'>{$row['uts']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200'>{$row['uas']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200 font-medium'>{$row['nilaiAkhir']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200 $statusClass'>{$row['status']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200 font-bold'>{$row['grade']}</td>";
            $rows .= "<td class='py-3 px-4 border-b border-gray-200'>{$row['statusGrade']}</td>";
            $rows .= "</tr>";
        }
        return $rows;
    }
}

$listsNilai = new ListsNilai();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $mataKuliah = trim($_POST["mataKuliah"] ?? "");
    $tugas = floatval($_POST["tugas"] ?? 0);
    $uts = floatval($_POST["uts"] ?? 0);
    $uas = floatval($_POST["uas"] ?? 0);

    if (!empty($nama) && !empty($mataKuliah) && $tugas >= 0 && $uts >= 0 && $uas >= 0) {
        $listsNilai->addData($nama, $mataKuliah, $tugas, $uts, $uas);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penilaian Mahasiswa</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen py-8 px-4">
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-8 tracking-tight">
        Sistem Penilaian Mahasiswa
    </h1>

    <div class="mb-10 bg-white shadow-lg rounded-xl p-6 border border-gray-100">
        <h2 class="text-xl font-semibold mb-6 text-gray-700 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Input Nilai
        </h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Nama Mahasiswa</label>
                <input type="text" name="nama" placeholder="Masukkan Nama" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                <input type="text" name="mataKuliah" placeholder="Masukkan Mata Kuliah" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Nilai Tugas</label>
                <input type="number" name="tugas" placeholder="Masukkan Nilai Tugas" step="0.01" min="0" max="100"
                       required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                <input type="number" name="uts" placeholder="Masukkan Nilai UTS" step="0.01" min="0" max="100" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                <input type="number" name="uas" placeholder="Masukkan Nilai UAS" step="0.01" min="0" max="100" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            <div class="md:col-span-2">
                <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <div class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Simpan Data
                    </div>
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl shadow-lg border border-gray-100">
        <table class="w-full bg-white">
            <thead>
            <tr class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
                <th class="py-3 px-4 text-left font-semibold">No</th>
                <th class="py-3 px-4 text-left font-semibold">Nama</th>
                <th class="py-3 px-4 text-left font-semibold">Mata Kuliah</th>
                <th class="py-3 px-4 text-left font-semibold">Nilai Tugas</th>
                <th class="py-3 px-4 text-left font-semibold">Nilai UTS</th>
                <th class="py-3 px-4 text-left font-semibold">Nilai UAS</th>
                <th class="py-3 px-4 text-left font-semibold">Nilai Akhir</th>
                <th class="py-3 px-4 text-left font-semibold">Status</th>
                <th class="py-3 px-4 text-left font-semibold">Grade</th>
                <th class="py-3 px-4 text-left font-semibold">Status Grade</th>
            </tr>
            </thead>
            <tbody>
            <?= $listsNilai->renderTableRows() ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>