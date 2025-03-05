<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $uas = isset($_POST['uas']) ? (float) $_POST['uas'] : 0;
    $uts = isset($_POST['uts']) ? (float) $_POST['uts'] : 0;
    $tugas = isset($_POST['tugas']) ? (float) $_POST['tugas'] : 0;


    $final_score = ($uts * 0.30) + ($uas * 0.35) + ($tugas * 0.35);

    $status = $final_score >= 60 ? "Lulus cuyyy" : "NT dah";
    $status_grade = "I";

    switch (true) {
        case $final_score >= 85:
            $grade = "A";
            break;
        case $final_score >= 70:
            $grade = "B";
            break;
        case $final_score >= 56:
            $grade = "C";
            break;
        case $final_score >= 36:
            $grade = "D";
            break;
        default:
            $grade = "E";
            break;
    }

    switch ($grade){
        case "A":
            $status_grade = "Sangat Memuaskan";
            break;
        case "B":
            $status_grade = "Memuaskan";
            break;
        case "C":
            $status_grade = "Cukup";
            break;
        case "D":
            $status_grade = "Kurang";
            break;
        case "E":
            $status_grade = "Sangat Kurang";
            break;
          default:
            $status_grade = "Tidak Ada";
            break;
        }


    $_SESSION["result"] = [
        "name" => $name,
        "subject" => $subject,
        "uas" => $uas,
        "uts" => $uts,
        "tugas" => $tugas,
        "final_score" => number_format($final_score, 2),
        "status" => $status,
        "grade" => $grade,
        "status_grade" => $status_grade
    ];

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// Ambil data hasil dari session & hapus setelah ditampilkan
$result = $_SESSION["result"] ?? null;
$error = $_SESSION["error"] ?? null;
unset($_SESSION["result"], $_SESSION["error"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penilaian Mahasiswa</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <form method="POST" action="" class="w-full max-w-md p-6 bg-white rounded-lg shadow-md space-y-4">
        <h2 class="text-xl font-semibold text-center">Sistem Penilaian Mahasiswa</h2>

        <?php if ($error) : ?>
            <div class="p-3 bg-red-100 text-red-700 rounded-md">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nama Mahasiswa</label>
            <input type="text" name="name" required placeholder="Masukkan Nama"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
            <input type="text" name="subject" required placeholder="Masukkan Mata Kuliah"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nilai UAS (35%)</label>
            <input type="number" name="uas" step="0.1" required placeholder="Masukkan Nilai UAS"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nilai UTS (30%)</label>
            <input type="number" name="uts" step="0.1" required placeholder="Masukkan Nilai UTS"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nilai TUGAS (35%)</label>
            <input type="number" name="tugas" step="0.1" required placeholder="Masukkan Nilai Tugas"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit" class="w-full p-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Hitung Nilai</button>

        <?php if ($result) : ?>
            <div class="mt-4 p-4 bg-gray-200 text-sm rounded-md">
                <h3 class="font-semibold text-lg">Hasil Perhitungan:</h3>
                <p><strong>Nama:</strong> <?= htmlspecialchars($result["name"]) ?></p>
                <p><strong>Mata Kuliah:</strong> <?= htmlspecialchars($result["subject"]) ?></p>
                <p><strong>Nilai UAS:</strong> <?= $result["uas"] ?></p>
                <p><strong>Nilai UTS:</strong> <?= $result["uts"] ?></p>
                <p><strong>Nilai Tugas:</strong> <?= $result["tugas"] ?></p>
                <p><strong>Nilai Akhir:</strong> <?= $result["final_score"] ?></p>
                <p><strong>Status:</strong> <?= $result["status"] ?></p>
                <p><strong>Predikat:</strong> <?= $result["grade"] ?></p>
                <p><strong>Predikat Status:</strong> <?= $result["status_grade"] ?></p>
            </div>
        <?php endif; ?>
    </form>
</body>
</html>
