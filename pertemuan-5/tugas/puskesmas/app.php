<?php
global $dbh;
require "config.php";

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

function getTableIcon($table)
{
    $icons = [
        'pasien' => 'fa-user',
        'kelurahan' => 'fa-map-marker-alt',
        'paramedik' => 'fa-user-md',
        'unit_kerja' => 'fa-hospital',
        'periksa' => 'fa-stethoscope'
    ];
    return $icons[$table] ?? 'fa-table';
}

function getFieldLabel($field)
{
    $labels = [
        'id' => 'ID', 'nama' => 'Nama', 'tmp_lahir' => 'Tempat Lahir', 'tgl_lahir' => 'Tanggal Lahir',
        'gender' => 'Jenis Kelamin', 'email' => 'Email', 'alamat' => 'Alamat', 'kelurahan_id' => 'Kelurahan',
        'kategori' => 'Kategori', 'telpon' => 'Telepon', 'unit_kerja_id' => 'Unit Kerja', 'tanggal' => 'Tanggal Periksa',
        'berat' => 'Berat (kg)', 'tinggi' => 'Tinggi (cm)', 'tensi' => 'Tensi', 'keterangan' => 'Keterangan',
        'pasien_id' => 'Pasien', 'dokter_id' => 'Dokter', 'kec_id' => 'Kecamatan'
    ];
    return $labels[$field] ?? ucfirst($field);
}

function getRelatedDisplayValue($dbh, $table, $column, $id)
{
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
    <title>Puskesmas System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar"
           class="bg-blue-700 text-white w-64 flex-shrink-0 transition-all duration-300 ease-in-out transform -translate-x-full md:translate-x-0 fixed md:static h-full z-40">
        <div class="p-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl font-semibold">Puskesmas</h1>
                <button id="close-sidebar" class="md:hidden text-white focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="space-y-1">
                <?php
                $tables = ['pasien', 'kelurahan', 'paramedik', 'unit_kerja', 'periksa'];
                foreach ($tables as $table): ?>
                    <a href="app.php?table=<?= $table ?>"
                       class="flex items-center px-3 py-2.5 rounded-md transition-colors <?= $currentTable === $table ? 'bg-blue-800 text-white' : 'text-blue-100 hover:bg-blue-800 hover:text-white' ?>">
                        <i class="fas <?= getTableIcon($table) ?> w-5 mr-3 text-center"></i>
                        <span><?= ucfirst($table) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="absolute bottom-4 left-0 right-0 px-4 text-center text-xs text-blue-200">
                &copy; 2025 Puskesmas System
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navbar -->
        <header class="bg-white shadow-sm z-30">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center">
                    <button id="toggle-sidebar" class="md:hidden mr-3 text-gray-600 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800">Sistem Informasi Puskesmas</h1>
                </div>
                <div class="text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span id="current-date"><?= date('d M Y') ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4">
            <div class="container mx-auto max-w-6xl">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800"><?= ucfirst($currentTable) ?> Management</h2>
                    <p class="text-gray-600 text-sm">Manage your <?= $currentTable ?> records</p>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div id="alert"
                         class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                        <div class="flex">
                            <i class="fas fa-check-circle mt-0.5 mr-2"></i>
                            <span>Record successfully <?= htmlspecialchars($_GET['msg']) ?>!</span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
                    <div class="border-b border-gray-200 px-5 py-3 flex items-center">
                        <i class="fas <?= $editId ? 'fa-edit' : 'fa-plus' ?> mr-2 text-blue-600"></i>
                        <h3 class="font-medium text-gray-700"><?= $editId ? 'Edit' : 'Add New' ?> <?= ucfirst($currentTable) ?></h3>
                    </div>

                    <div class="p-6 bg-white rounded-xl shadow-md">
                        <form method="POST" action="app.php" class="space-y-6">
                            <input type="hidden" name="table" value="<?= htmlspecialchars($currentTable) ?>">
                            <input type="hidden" name="action" value="<?= $editId ? 'update' : 'add' ?>">
                            <?php if ($editId): ?>
                                <input type="hidden" name="id" value="<?= htmlspecialchars($editId) ?>">
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach ($tableStructure as $column): ?>
                                    <?php
                                    $field = $column['Field'];
                                    if ($field === 'id' && !$editId) continue;

                                    $value = $editData[$field] ?? '';
                                    $isRequired = $column['Null'] === 'NO' && $column['Extra'] !== 'auto_increment';
                                    $fieldType = str_contains($column['Type'], 'date') ? 'date' : 'text';
                                    $enumValues = [];

                                    if (str_contains($column['Type'], 'enum')) {
                                        preg_match("/enum\('(.+?)'\)/", $column['Type'], $matches);
                                        $enumValues = explode("','", $matches[1]);
                                    }

                                    $label = getFieldLabel($field);
                                    ?>

                                    <div>
                                        <label for="<?= $field ?>"
                                               class="block text-sm font-semibold text-gray-800 mb-1">
                                            <?= $label ?> <?= $isRequired ? '<span class="text-red-500">*</span>' : '' ?>
                                        </label>

                                        <?php if (!empty($enumValues)): ?>
                                            <select id="<?= $field ?>" name="<?= $field ?>"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?= $isRequired ? 'required' : '' ?>>
                                                <option value="">-- Select <?= $label ?> --</option>
                                                <?php foreach ($enumValues as $enumValue): ?>
                                                    <option value="<?= $enumValue ?>" <?= $value === $enumValue ? 'selected' : '' ?>>
                                                        <?= ucfirst($enumValue) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                        <?php elseif ($field === 'gender'): ?>
                                            <div class="flex gap-6 mt-1">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="gender"
                                                           value="L" <?= $value === 'L' ? 'checked' : '' ?>
                                                           class="text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="gender"
                                                           value="P" <?= $value === 'P' ? 'checked' : '' ?>
                                                           class="text-pink-600 focus:ring-pink-500">
                                                    <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                                                </label>
                                            </div>

                                        <?php elseif (in_array($field, ['alamat', 'keterangan'])): ?>
                                            <textarea id="<?= $field ?>" name="<?= $field ?>" rows="3"
                                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            <?= $isRequired ? 'required' : '' ?>><?= htmlspecialchars($value) ?></textarea>

                                        <?php elseif (str_contains($field, 'email')): ?>
                                            <input type="email" id="<?= $field ?>" name="<?= $field ?>"
                                                   value="<?= htmlspecialchars($value) ?>"
                                                   placeholder="email@example.com"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?= $isRequired ? 'required' : '' ?>>

                                        <?php elseif ($field === 'telpon'): ?>
                                            <input type="tel" id="<?= $field ?>" name="<?= $field ?>"
                                                   value="<?= htmlspecialchars($value) ?>"
                                                   placeholder="0812345678"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?= $isRequired ? 'required' : '' ?>>

                                        <?php elseif (isset($relationships[$currentTable][$field])): ?>
                                            <select id="<?= $field ?>" name="<?= $field ?>"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?= $isRequired ? 'required' : '' ?>>
                                                <option value="">-- Select <?= $label ?> --</option>
                                                <?php foreach ($relatedData[$field] as $item): ?>
                                                    <option value="<?= $item['id'] ?>" <?= $value == $item['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($item[$relationships[$currentTable][$field]['display']]) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                        <?php else: ?>
                                            <input type="<?= $fieldType ?>" id="<?= $field ?>" name="<?= $field ?>"
                                                   value="<?= htmlspecialchars($value) ?>"
                                                   placeholder="Enter <?= strtolower($label) ?>"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?= $isRequired ? 'required' : '' ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                <button type="reset"
                                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400">
                                    Reset
                                </button>
                                <button type="submit"
                                        class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                    <?= $editId ? 'Update' : 'Save' ?>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-800"><?= ucfirst($currentTable) ?> Data</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wide">
                            <tr>
                                <?php foreach ($tableStructure as $column): ?>
                                    <th class="px-6 py-3 text-left"><?= getFieldLabel($column['Field']) ?></th>
                                <?php endforeach; ?>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                            <?php if (empty($tableData)): ?>
                                <tr>
                                    <td colspan="<?= count($tableStructure) + 1 ?>"
                                        class="px-6 py-5 text-center text-gray-500">
                                        No data available.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tableData as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <?php foreach ($tableStructure as $column): ?>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= isset($relationships[$currentTable][$column['Field']]) && !empty($row[$column['Field']])
                                                    ? htmlspecialchars(getRelatedDisplayValue($dbh, $currentTable, $column['Field'], $row[$column['Field']]))
                                                    : ($column['Field'] === 'gender'
                                                        ? ($row[$column['Field']] === 'L' ? 'Laki-laki' : 'Perempuan')
                                                        : htmlspecialchars($row[$column['Field']] ?? 'N/A')) ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <a href="app.php?table=<?= $currentTable ?>&edit=<?= $row['id'] ?>"
                                               class="inline-flex items-center text-blue-600 hover:text-blue-800 transition">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                            <form method="POST" action="app.php" class="inline">
                                                <input type="hidden" name="table"
                                                       value="<?= htmlspecialchars($currentTable) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this record?');"
                                                        class="ml-3 inline-flex items-center text-red-600 hover:text-red-800 transition">
                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<!-- Overlay for sidebar on mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle alerts
        const alertBox = document.getElementById('alert');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.opacity = '0';
                alertBox.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => alertBox.remove(), 500);
            }, 3000);
        }

        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleSidebarBtn = document.getElementById('toggle-sidebar');
        const closeSidebarBtn = document.getElementById('close-sidebar');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden', 'md:overflow-auto');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
        }

        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', openSidebar);
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Handle resize events
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                closeSidebar();
            }
        });

        // Add responsive table for small screens
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.classList.add('w-full');
            const wrapper = document.createElement('div');
            wrapper.classList.add('overflow-x-auto');
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    });
</script>
</body>
</html>