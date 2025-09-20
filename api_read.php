<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "koneksi.php";

// READ (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if ($id) {
        $stmt = $koneksi->prepare("SELECT * FROM aset_website WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        if ($data) {
            send_response("success", "Data ditemukan", $data);
        } else {
            send_response("error", "Data tidak ditemukan");
        }
        $stmt->close();
    } else {
        $result = $koneksi->query("SELECT * FROM aset_website");
        $data = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;
        send_response("success", "Data aset_website", $data);
    }
}

// METHOD NOT ALLOWED
else {
    send_response("error", "Method tidak diizinkan");
}

$koneksi->close();

?>
