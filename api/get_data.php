<?php

$conn = pg_connect("host=PG_HOST
                    port=PG_PORT
                    dbname=DB_NAME
                    user=DB_USER
                    password=DB_PASSWORD
                    sslmode=require");

$result = pg_query($conn, "SELECT * FROM poligoni");
$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
