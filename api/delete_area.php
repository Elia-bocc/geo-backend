<?php
header('Content-Type: application/json');

// Load local config if exists, otherwise fallback to defaults
$defaultConfig = require __DIR__ . '/config/config.default.php';
$localConfigFile = __DIR__ . '/config/config.local.php';

if (file_exists($localConfigFile)) {
    $config = array_merge($defaultConfig, require $localConfigFile);
} else {
    $config = $defaultConfig;
}

// Build connection string
$conn_string = sprintf(
    "host=%s port=%s dbname=%s user=%s password=%s",
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['user'],
    $config['password']
);
$table_name = "Green Spaces";
$id_column = "id";

$db_conn = pg_connect($conn_string);

if (!$db_conn) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to database']);
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    exit;
}

$sql = "DELETE FROM \"$table_name\" WHERE $id_column = $1";

$result = pg_query_params($db_conn, $sql, [$id]);

if ($result) {
    if (pg_affected_rows($result) > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Area deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . pg_last_error($db_conn)]);
}

pg_close($db_conn);
?>