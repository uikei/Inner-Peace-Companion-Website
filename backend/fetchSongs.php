<?php
header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "innerpeacecomp_web";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

//Added song_cover
$sql = "SELECT song_id, song_title, song_artist, song_filename, song_cover FROM songs ORDER BY song_id ASC";
$res = $conn->query($sql);

$songs = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // Build full URLs relative to RelaxMode.php location
        $songs[] = [
            'song' => (int) $row['song_id'],
            'title' => $row['song_title'],
            'artist' => $row['song_artist'],
            'file' => "../src/music/" . $row['song_filename'],   // adjust path
            'cover' => "../src/banner/" . $row['song_cover']
        ];
    }
}

echo json_encode(['songs' => $songs]);
$conn->close();
