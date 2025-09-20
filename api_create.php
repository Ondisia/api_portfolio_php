<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "koneksi.php";

// CREATE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // cek Content-Type
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    if (strpos($contentType, "application/json") !== false) {
        // request JSON biasa
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) send_response("error", "Input JSON tidak valid");

        $foto = $input['foto'] ?? "";
        $deskripsi = $input['deskripsi'] ?? "";
        $file_resume = $input['file_resume'] ?? "";
        
        // simpan ke database
        $stmt = $koneksi->prepare("INSERT INTO aset_website (foto, deskripsi, file_resume) VALUES (?, ?, ?)");
        if (!$stmt) send_response("error", "prepare statement gagal: " . $koneksi->error);

        $stmt->bind_param("sss", $foto, $deskripsi, $file_resume);

        if ($stmt->execute()) {
            send_response("success", "Data berhasil disimpan", [
                "id" => $stmt->insert_id,
                "foto" => $foto,
                "deskripsi" => $deskripsi,
                "file_resume" => $file_resume
            ]);
        } else {
            send_response("error", "Gagal menyimpan data: " . $stmt->error);
        }
        $stmt->close();
    } else {
        send_response("error", "Content-Type tidak dikenali: $contentType");
    }
} else {
    send_response("error", "Method tidak diizinkan");
}

$koneksi->close();
