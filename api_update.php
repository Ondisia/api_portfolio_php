<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "koneksi.php";

// UPDATE (PUT)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if (!$input || !isset($input['id'])) send_response("error", "Input JSON tidak valid atau id tidak ada");

    $id          = intval($input['id']);
    $foto        = isset($input['foto']) ? $input['foto'] : "";
    $deskripsi   = isset($input['deskripsi']) ? $input['deskripsi'] : "";
    $file_resume = isset($input['file_resume']) ? $input['file_resume'] : "";

    $stmt = $koneksi->prepare("UPDATE aset_website SET foto = ?, deskripsi = ?, file_resume = ? WHERE id = ?");
    if (!$stmt) send_response("error", "Prepare statement gagal: " . $koneksi->error);

    $stmt->bind_param("sssi", $foto, $deskripsi, $file_resume, $id);

    if ($stmt->execute()) {
        send_response("success", "Data berhasil diupdate", [
            "id" => $id,
            "foto" => $foto,
            "deskripsi" => $deskripsi,
            "file_resume" => $file_resume
        ]);
    } else {
        send_response("error", "Gagal mengupdate data: " . $stmt->error);
    }
    $stmt->close();
}
