<?php

// I have a postGis database with two tables: Air Pollution and Green Spaces.
// This script allow the leflet map to take de data from this DB through XAMPP.


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
    "host=%s dbname=%s user=%s password=%s",
    $config['host'],
    $config['dbname'],
    $config['user'],
    $config['password']
);

// Connection to database
$conn = pg_connect($conn_string);

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
