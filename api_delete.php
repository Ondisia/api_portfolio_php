<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "koneksi.php";

// DELETE (DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!$input || !isset($input['id'])) send_response("error", "Input JSON tidak valid atau id tidak ada");

    $id = intval($input['id']);
    $stmt = $koneksi->prepare("DELETE FROM aset_website WHERE id = ?");
    if (!$stmt) send_response("error", "prepare statement gagal: " . $koneksi->error);

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        send_response("success", "Data berhasil dihapus");
    } else {
        send_response("error", "Gagal menghapus data: " . $stmt->error);
    }
    $stmt->close();
}

// METHOD NOT ALLOWED
else {
    send_response("error", "Method tidak diizinkan");
}

$koneksi->close();

?>
