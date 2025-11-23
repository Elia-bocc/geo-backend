<?php
header("Access-Control-Allow-Origin: *");  // Permette richieste da qualsiasi dominio
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// I have a postGis database with two tables: Air Pollution and Green Spaces.
// This script allow the leflet map to take de data from this DB through XAMPP.


header('Content-Type: application/json');

// Aiven details
$dbhost = 'pg-f9ef4a9-pgdunk-2799.e.aivencloud.com';
$dbport = 13578;
$dbname = 'defaultdb';
$dbuser = 'avnadmin';
$dbpass = 'AVNS_dP-S1FX9jwwx5uKFUFB';

// Conncection
$conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass sslmode=require");

if (!$conn) {
    die("Connessione al database fallita: " . pg_last_error());
}

// Query for "Green Spaces"
$query_green_spaces = "
SELECT jsonb_build_object(
  'type',     'FeatureCollection',
  'features', jsonb_agg(ST_AsGeoJSON(t.*)::jsonb)
)
FROM \"Green Spaces\" t;
";

// Excecution Green Spaces query
$result_green_spaces = pg_query($conn, $query_green_spaces);
$row_green_spaces = pg_fetch_row($result_green_spaces);

// Query for "Air Pollution"
$query_air_pollution = "
SELECT jsonb_build_object(
  'type',     'FeatureCollection',
  'features', jsonb_agg(ST_AsGeoJSON(t.*)::jsonb)
)
FROM \"Air Pollution\" t;
";

// Excecution Air Pollution query
$result_air_pollution = pg_query($conn, $query_air_pollution);
$row_air_pollution = pg_fetch_row($result_air_pollution);

// Combine
$data = [
    'green_spaces' => json_decode($row_green_spaces[0]), 
    'air_pollution' => json_decode($row_air_pollution[0]) 
];

// Returning json data
echo json_encode($data);

// Closing connection
pg_close($conn);
?>


