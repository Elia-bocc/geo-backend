<?php
header('Content-Type: application/json');

// Aiven details
$dbhost = 'pg-f9ef4a9-pgdunk-2799.e.aivencloud.com';
$dbport = 13578;
$dbname = 'defaultdb';
$dbuser = 'avnadmin';
$dbpass = 'AVNS_dP-S1FX9jwwx5uKFUFB';

// Conncection
$db_conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass sslmode=require");

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
