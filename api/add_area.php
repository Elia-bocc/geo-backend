<?php
header("Access-Control-Allow-Origin: *");  // Permette richieste da qualsiasi dominio
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-Type: application/json');

// Aiven details
$dbhost = 'pg-f9ef4a9-pgdunk-2799.e.aivencloud.com';
$dbport = 13578;
$dbname = 'defaultdb';
$dbuser = 'avnadmin';
$dbpass = 'AVNS_dP-S1FX9jwwx5uKFUFB';
$table_name = "Green Spaces";
$geom_column = "geom";
$name_column = '"NOME"';

// Conncection
$db_conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass sslmode=require");

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


