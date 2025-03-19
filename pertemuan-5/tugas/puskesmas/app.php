<?php
    require "config.php";

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $table = $_POST['table'] ?? '';
        $id = $_POST['id'] ?? null;

        $fields = $values = [];
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['action', 'table', 'id'])) {
                $fields[] = $key;
                $values[":$key"] = $value;
            }
        }

        try {
            if ($action === 'add') {
                $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', array_keys($values)) . ")";
            } elseif ($action === 'update') {
                $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));
                $sql = "UPDATE $table SET $setClause WHERE id = :id";
                $values[':id'] = $id;
            } elseif ($action === 'delete' && $id) {
                $sql = "DELETE FROM $table WHERE id = :id";
                $values = [':id' => $id];
            }
            $stmt = $dbh->prepare($sql);
            $stmt->execute($values);
            header("Location: app.php?table=$table&msg=" . ($action === 'add' ? 'added' : ($action === 'update' ? 'updated' : 'deleted')));
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }

    // Fetch data
    $currentTable = $_GET['table'] ?? 'pasien';
    $editId = $_GET['edit'] ?? null;
    $editData = $editId ? fetchById($dbh, $currentTable, $editId) : null;
    $tableData = fetchAll($dbh, $currentTable);
    $tableStructure = $dbh->query("DESCRIBE $currentTable")->fetchAll();
    $relationships = [
        'pasien' => ['kelurahan_id' => ['table' => 'kelurahan', 'display' => 'nama']],
        'paramedik' => ['unit_kerja_id' => ['table' => 'unit_kerja', 'display' => 'nama']],
        'periksa' => [
            'pasien_id' => ['table' => 'pasien', 'display' => 'nama'],
            'dokter_id' => ['table' => 'paramedik', 'display' => 'nama', 'condition' => "kategori='dokter'"]
        ]
    ];
    $relatedData = [];
    foreach ($relationships[$currentTable] ?? [] as $column => $relationInfo) {
        $relatedData[$column] = fetchRelatedData($dbh, $relationInfo);
    }

    function renderTableOptions() {
        $tables = ['pasien', 'kelurahan', 'paramedik', 'unit_kerja', 'periksa'];
        $currentTable = $_GET['table'] ?? 'pasien';
        return array_reduce($tables, fn($html, $table) => $html . "<option value=\"$table\"" . ($table === $currentTable ? ' selected' : '') . ">" . ucfirst($table) . "</option>", '');
    }

    function getFieldLabel($field) {
        $labels = [
            'id' => 'ID', 'nama' => 'Nama', 'tmp_lahir' => 'Tempat Lahir', 'tgl_lahir' => 'Tanggal Lahir',
            'gender' => 'Jenis Kelamin', 'email' => 'Email', 'alamat' => 'Alamat', 'kelurahan_id' => 'Kelurahan',
            'kategori' => 'Kategori', 'telpon' => 'Telepon', 'unit_kerja_id' => 'Unit Kerja', 'tanggal' => 'Tanggal Periksa',
            'berat' => 'Berat (kg)', 'tinggi' => 'Tinggi (cm)', 'tensi' => 'Tensi', 'keterangan' => 'Keterangan',
            'pasien_id' => 'Pasien', 'dokter_id' => 'Dokter', 'kec_id' => 'Kecamatan'
        ];
        return $labels[$field] ?? ucfirst($field);
    }

    function getRelatedDisplayValue($dbh, $table, $column, $id) {
        global $relationships;
        if (isset($relationships[$table][$column])) {
            $relationInfo = $relationships[$table][$column];
            $stmt = $dbh->prepare("SELECT {$relationInfo['display']} FROM {$relationInfo['table']} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetchColumn() ?: 'N/A';
        }
        return $id;
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas CRUD System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <header class="mb-10">
        <h1 class="text-3xl font-bold text-center text-blue-800">Sistem Informasi Puskesmas</h1>
        <p class="text-center text-gray-600 mt-2">Manage data for your community health center</p>
    </header>

    <div class="bg-white p-6 rounded-xl shadow-md mb-8">
        <form method="GET" action="app.php" class="flex items-center">
            <label for="table" class="block font-medium text-gray-700 mr-3">Select Menu:</label>
            <select name="table" id="table" class="form-select rounded-lg border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-4 py-2" onchange="this.form.submit()">
                <?= renderTableOptions(); ?>
            </select>
        </form>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-md shadow-sm transition-opacity duration-500" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <p>Record successfully <?= htmlspecialchars($_GET['msg']); ?>!</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-md shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <p><?= htmlspecialchars($error); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-xl shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-6 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas <?= $editId ? 'fa-edit' : 'fa-plus-circle'; ?> mr-2 text-blue-600"></i>
            <?= $editId ? 'Edit' : 'Add New'; ?> <?= ucfirst($currentTable); ?>
        </h2>

        <form method="POST" action="app.php" class="space-y-6">
            <input type="hidden" name="table" value="<?= htmlspecialchars($currentTable); ?>">
            <input type="hidden" name="action" value="<?= $editId ? 'update' : 'add'; ?>">
            <?php if ($editId): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($editId); ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($tableStructure as $column): ?>
                    <?php
                    if ($column['Field'] === 'id' && !$editId) continue;
                    $value = $editData[$column['Field']] ?? '';
                    $isRequired = $column['Null'] === 'NO' && $column['Extra'] !== 'auto_increment';
                    $fieldType = strpos($column['Type'], 'date') !== false ? 'date' : 'text';
                    if (strpos($column['Type'], 'enum') !== false) {
                        preg_match("/enum\('(.+?)'\)/", $column['Type'], $matches);
                        $enumValues = explode("','", $matches[1]);
                    }
                    ?>

                    <div class="form-group">
                        <label for="<?= $column['Field']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= getFieldLabel($column['Field']); ?><?= $isRequired ? ' <span class="text-red-500">*</span>' : ''; ?>
                        </label>

                        <?php if (isset($relationships[$currentTable][$column['Field']])): ?>
                            <div class="relative">
                                <select name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" class="appearance-none w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?>>
                                    <option value="">-- Select <?= getFieldLabel($column['Field']); ?> --</option>
                                    <?php foreach ($relatedData[$column['Field']] as $item): ?>
                                        <option value="<?= $item['id']; ?>" <?= ($value == $item['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($item[$relationships[$currentTable][$column['Field']]['display']]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Select from existing <?= strtolower(getFieldLabel($column['Field'])); ?> records</p>

                        <?php elseif (isset($enumValues)): ?>
                            <div class="relative">
                                <select name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" class="appearance-none w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?>>
                                    <option value="">-- Select <?= getFieldLabel($column['Field']); ?> --</option>
                                    <?php foreach ($enumValues as $enumValue): ?>
                                        <option value="<?= $enumValue; ?>" <?= ($value === $enumValue) ? 'selected' : ''; ?>>
                                            <?= ucfirst($enumValue); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>

                        <?php elseif ($column['Field'] === 'gender'): ?>
                            <div class="mt-1 flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="L" <?= ($value === 'L') ? 'checked' : ''; ?> class="form-radio text-blue-600 h-5 w-5">
                                    <span class="ml-2">Laki-laki</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="P" <?= ($value === 'P') ? 'checked' : ''; ?> class="form-radio text-pink-600 h-5 w-5">
                                    <span class="ml-2">Perempuan</span>
                                </label>
                            </div>

                        <?php elseif (in_array($column['Field'], ['alamat', 'keterangan'])): ?>
                            <div class="relative">
                                <textarea name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150 ease-in-out resize-none" <?= $isRequired ? 'required' : ''; ?>><?= htmlspecialchars($value); ?></textarea>
                            </div>
                            <p class="mt-1 text-xs text-gray-500"><?= $column['Field'] === 'alamat' ? 'Full address including street and number' : 'Additional information' ?></p>

                        <?php elseif (strpos($column['Field'], 'email') !== false): ?>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <input type="email" name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" value="<?= htmlspecialchars($value); ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?> placeholder="email@example.com">
                            </div>

                        <?php elseif (strpos($column['Field'], 'telpon') !== false || strpos($column['Field'], 'phone') !== false): ?>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <input type="tel" name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" value="<?= htmlspecialchars($value); ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?> placeholder="0812345678">
                            </div>

                        <?php elseif ($fieldType === 'date'): ?>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <input type="date" name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" value="<?= htmlspecialchars($value); ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?>>
                            </div>

                        <?php elseif (strpos($column['Field'], 'nama') !== false || strpos($column['Field'], 'name') !== false): ?>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                    <i class="fas fa-user"></i>
                                </div>
                                <input type="text" name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" value="<?= htmlspecialchars($value); ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?> placeholder="Enter <?= strtolower(getFieldLabel($column['Field'])); ?>">
                            </div>

                        <?php else: ?>
                            <input type="<?= $fieldType; ?>" name="<?= $column['Field']; ?>" id="<?= $column['Field']; ?>" value="<?= htmlspecialchars($value); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150 ease-in-out" <?= $isRequired ? 'required' : ''; ?> placeholder="Enter <?= strtolower(getFieldLabel($column['Field'])); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100">
                <button type="reset" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
                <button type="submit" class="px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    <i class="fas <?= $editId ? 'fa-save' : 'fa-plus-circle'; ?> mr-1"></i> <?= $editId ? 'Update' : 'Save'; ?>
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <h2 class="text-xl font-semibold p-4 bg-gray-50 border-b"><?= ucfirst($currentTable); ?> Data</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <?php foreach ($tableStructure as $column): ?>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= getFieldLabel($column['Field']); ?></th>
                    <?php endforeach; ?>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($tableData)): ?>
                    <tr>
                        <td colspan="<?= count($tableStructure) + 1; ?>" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tableData as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <?php foreach ($tableStructure as $column): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= isset($relationships[$currentTable][$column['Field']]) && !empty($row[$column['Field']]) ? htmlspecialchars(getRelatedDisplayValue($dbh, $currentTable, $column['Field'], $row[$column['Field']])) : ($column['Field'] === 'gender' ? ($row[$column['Field']] === 'L' ? 'Laki-laki' : 'Perempuan') : htmlspecialchars($row[$column['Field']] ?? 'N/A')); ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="app.php?table=<?= $currentTable; ?>&edit=<?= $row['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <form method="POST" action="app.php" class="inline">
                                    <input type="hidden" name="table" value="<?= htmlspecialchars($currentTable); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this record?');" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="mt-10 text-center text-gray-600 text-sm">
        <p>&copy; 2025 Puskesmas Management System</p>
    </footer>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alertBox = document.querySelector('.bg-green-100');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.opacity = 0;
                setTimeout(() => alertBox.remove(), 1000);
            }, 3000);
        }
    });
</script>
</body>
</html>