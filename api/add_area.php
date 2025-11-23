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
$geom_column = "geom";
$name_column = '"NOME"';

$db_conn = pg_connect($conn_string);

if (!$db_conn) {
    echo json_encode(['status' => 'error', 'message' => 'Connection to database failed']);
    exit;
}

$geojson_str = $_POST['geojson'] ?? null;

if (!$geojson_str) {
    echo json_encode(['status' => 'error', 'message' => 'No GeoJSON data received']);
    exit;
}

$geojson_obj = json_decode($geojson_str);
$nome = $geojson_obj->properties->NOME ?? 'Unknown Area';

$sql = "INSERT INTO \"$table_name\" ($name_column, $geom_column) 
        VALUES ($1, ST_SetSRID(ST_GeomFromGeoJSON($2), 4326))";

$geometry_str = json_encode($geojson_obj->geometry);

$result = pg_query_params($db_conn, $sql, [$nome, $geometry_str]);

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'Area added']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Errore SQL: ' . pg_last_error($db_conn)]);
}

pg_close($db_conn);
?>