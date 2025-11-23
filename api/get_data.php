<?php
// Configura le credenziali del database Aiven
$dbhost = 'pg-f9ef4a9-pgdunk-2799.e.aivencloud.com';
$dbport = 13578;
$dbname = 'defaultdb';
$dbuser = 'avnadmin';
$dbpass = 'AVNS_dP-S1FX9jwwx5uKFUFB';

// Connessione al database PostgreSQL con SSL
$conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass sslmode=require");

if (!$conn) {
    die("Connessione al database fallita: " . pg_last_error());
}

// Esegui una query di esempio per testare la connessione
$query = 'SELECT * FROM "Green Spaces";';
$result = pg_query($conn, $query);

if (!$result) {
    die("Errore nella query: " . pg_last_error());
}

// Estrai i risultati e stampali
$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

// Chiudi la connessione
pg_close($conn);
?>


