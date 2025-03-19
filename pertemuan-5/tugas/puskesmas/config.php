<?php
// config.php - Database configuration
$dsn = "mysql:host=localhost;dbname=dbpuskesmas;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $dbh = new PDO($dsn, 'root', '', $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions for database operations
function fetchAll($dbh, $table, $conditions = '') {
    $sql = "SELECT * FROM $table" . ($conditions ? " WHERE $conditions" : "");
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetchById($dbh, $table, $id) {
    $stmt = $dbh->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function delete($dbh, $table, $id) {
    $stmt = $dbh->prepare("DELETE FROM $table WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

function fetchRelatedData($dbh, $relationInfo) {
    $sql = "SELECT id, {$relationInfo['display']} FROM {$relationInfo['table']}";
    if (!empty($relationInfo['condition'])) {
        $sql .= " WHERE {$relationInfo['condition']}";
    }
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($action === 'add' || $action === 'update') {
        $fields = $values = [];
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['action', 'table', 'id'])) {
                $fields[] = $key;
                $values[":$key"] = $value !== '' ? $value : null;
            }
        }

        try {
            if ($action === 'add') {
                $placeholders = array_map(fn($field) => ":$field", $fields);
                $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            } else {
                $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));
                $sql = "UPDATE $table SET $setClause WHERE id = :id";
                $values[':id'] = $id;
            }
            $stmt = $dbh->prepare($sql);
            $stmt->execute($values);
            header("Location: app.php?table=$table&msg=" . ($action === 'add' ? 'added' : 'updated'));
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } elseif ($action === 'delete' && $id) {
        try {
            delete($dbh, $table, $id);
            header("Location: app.php?table=$table&msg=deleted");
            exit;
        } catch (PDOException $e) {
            $error = "Delete failed: " . $e->getMessage();
        }
    }
}

// Fetch data
$currentTable = $_GET['table'] ?? 'pasien';
$editId = $_GET['edit'] ?? null;
$editData = $editId ? fetchById($dbh, $currentTable, $editId) : null;
$tableData = fetchAll($dbh, $currentTable);

// Get table structure
try {
    $tableStructure = $dbh->query("DESCRIBE $currentTable")->fetchAll();
} catch (PDOException $e) {
    die("Error fetching table structure: " . $e->getMessage());
}

// Define relationships for dropdowns
$relationships = [
    'pasien' => ['kelurahan_id' => ['table' => 'kelurahan', 'display' => 'nama']],
    'paramedik' => ['unit_kerja_id' => ['table' => 'unit_kerja', 'display' => 'nama']],
    'periksa' => [
        'pasien_id' => ['table' => 'pasien', 'display' => 'nama'],
        'dokter_id' => ['table' => 'paramedik', 'display' => 'nama', 'condition' => "kategori='dokter'"]
    ]
];

// Get related data for dropdowns
$relatedData = [];
if (isset($relationships[$currentTable])) {
    foreach ($relationships[$currentTable] as $column => $relationInfo) {
        $relatedData[$column] = fetchRelatedData($dbh, $relationInfo);
    }
}

// Get page title based on current table
$pageTitle = ucfirst($currentTable) . ' Management';
?>