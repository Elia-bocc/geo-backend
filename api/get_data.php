<?php
// To manage the limit access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
header('Content-Type: application/json');

// Aiven details
$dbhost = 'pg-f9ef4a9-pgdunk-2799.e.aivencloud.com';
$dbport = 13578;
$dbname = 'defaultdb';
$dbuser = 'avnadmin';
$dbpass = 'AVNS_dP-S1FX9jwwx5uKFUFB';

// Connection
$conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass sslmode=require");
if (!$conn) {
    die("Connessione al database fallita: " . pg_last_error());
}

// Green Spaces
$result_green = pg_query($conn, 'SELECT ST_AsGeoJSON(t.*) AS feature FROM "public"."Green Spaces" t');
$features_green = [];
while ($row = pg_fetch_assoc($result_green)) {
    $features_green[] = json_decode($row['feature']);
}
$geojson_green = [
    'type' => 'FeatureCollection',
    'features' => $features_green
];

// Air Pollution
$result_air = pg_query($conn, 'SELECT ST_AsGeoJSON(t.*) AS feature FROM "public"."Air Pollution" t');
$features_air = [];
while ($row = pg_fetch_assoc($result_air)) {
    $features_air[] = json_decode($row['feature']);
}
$geojson_air = [
    'type' => 'FeatureCollection',
    'features' => $features_air
];

// Combine
$data = [
    'green_spaces' => $geojson_green,
    'air_pollution' => $geojson_air
];

// Output
echo json_encode($data);

// Close connection
pg_close($conn);
?>


