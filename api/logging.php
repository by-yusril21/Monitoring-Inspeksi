<?php
include "../config/database.php";

$webhookResponse = json_decode(file_get_contents('php://input'), true);
$topic = $webhookResponse["topic"];
$payload = $webhookResponse["payload"];

$topicExplode = explode("/", $topic);
$serialNumber = $topicExplode[1];
$name = $topicExplode[2];

if ($topicExplode[2] == "suhu" || $topicExplode[2] == "kelembaban" || $topicExplode[2] == "volt" || $topicExplode[2] == "hz" || $topicExplode[2] == "arus" || $topicExplode[2] == "power") {
    $type = "sensor";
} else {
    $type = "relay1";
}

// Query untuk mendapatkan nilai terakhir dari database
$sqlCheck = "SELECT value FROM data_sensor WHERE serial_number='$serialNumber' AND sensor_relay1='$type' AND name='$name' ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sqlCheck);

// Periksa jika ada hasil
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $lastValue = $row['value'];

    // Bandingkan nilai baru dengan nilai terakhir
    if ($payload != $lastValue) {
        // Jika nilai berubah, lakukan INSERT
        $sqlInsert = "INSERT INTO data_sensor (serial_number, sensor_relay1, value, name, mqtt_topic)
                      VALUES ('$serialNumber', '$type', '$payload', '$name', '$topic')";
        mysqli_query($conn, $sqlInsert);
    }
} else {
    // Jika tidak ada nilai sebelumnya (data baru), lakukan INSERT
    $sqlInsert = "INSERT INTO data_sensor (serial_number, sensor_relay1, value, name, mqtt_topic)
                  VALUES ('$serialNumber', '$type', '$payload', '$name', '$topic')";
    mysqli_query($conn, $sqlInsert);
}

// Tutup koneksi database
mysqli_close($conn);
