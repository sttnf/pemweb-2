<?php
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if it's an update operation
        if (isset($_POST['id'])) {
            $stmt = $dbh->prepare("UPDATE prodi SET kode = ?, nama = ?, kaprodi = ? WHERE id = ?");
            $stmt->execute([
                $_POST['kode'],
                $_POST['nama'],
                $_POST['kaprodi'],
                $_POST['id']
            ]);
            $message = "Data berhasil diperbarui!";
        } else {
            // Create new prodi
            $stmt = $dbh->prepare("INSERT INTO prodi (kode, nama, kaprodi) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['kode'],
                $_POST['nama'],
                $_POST['kaprodi']
            ]);
            $message = "Data berhasil disimpan!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $dbh->prepare("DELETE FROM prodi WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $message = "Data berhasil dihapus!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Edit request (to load data into form)
$editMode = false;
$editData = [
    'id' => '',
    'kode' => '',
    'nama' => '',
    'kaprodi' => ''
];

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $dbh->prepare("SELECT * FROM prodi WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($editData) {
            $editMode = true;
        } else {
            $error = "Data tidak ditemukan!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Display existing prodi data
$prodiList = $dbh->query("SELECT * FROM prodi")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Prodi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-center">Form Prodi</h1>

        <?php if (isset($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $editMode ? 'Edit Prodi' : 'Tambah Prodi'; ?>
            </h2>
            <form method="POST" action="">
                <?php if ($editMode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                        <input type="text" id="kode" name="kode" required
                            value="<?php echo htmlspecialchars($editData['kode']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" id="nama" name="nama" required
                            value="<?php echo htmlspecialchars($editData['nama']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="kaprodi" class="block text-sm font-medium text-gray-700 mb-1">Kaprodi</label>
                        <input type="text" id="kaprodi" name="kaprodi" required
                            value="<?php echo htmlspecialchars($editData['kaprodi']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php echo $editMode ? 'Update' : 'Simpan'; ?>
                        </button>

                        <?php if ($editMode): ?>
                            <a href="app.php"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Daftar Prodi</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Kode</th>
                            <th class="py-2 px-4 border-b text-left">Nama</th>
                            <th class="py-2 px-4 border-b text-left">Kaprodi</th>
                            <th class="py-2 px-4 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodiList as $prodi): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($prodi['kode']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($prodi['nama']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($prodi['kaprodi']); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $prodi['id']; ?>"
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded text-sm">
                                            Edit
                                        </a>
                                        <a href="?action=delete&id=<?php echo $prodi['id']; ?>"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                           class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-sm">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($prodiList)): ?>
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">Tidak ada data prodi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>