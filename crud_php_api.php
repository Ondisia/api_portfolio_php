<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "koneksi.php";

// Helper: send JSON response
function send_response($status, $message, $data = null) {
    $res = ["status" => $status, "message" => $message];
    if ($data !== null) $res["data"] = $data;
    echo json_encode($res);
    exit;
}

// Get method and input
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

// CREATE (POST)
if ($method === 'POST') {
    // cek Content-Type
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";

    if (strpos($contentType, "application/json") !== false) {
        // request JSON biasa
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) send_response("error", "Input JSON tidak valid");

        $foto = $input['foto'] ?? "";
        $deskripsi = $input['deskripsi'] ?? "";
        $file_resume = $input['file_resume'] ?? "";
    } elseif (strpos($contentType, "multipart/form-data") !== false) {
        // request FormData (upload file)
        $deskripsi = $_POST['deskripsi'] ?? "";

        // handle foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_name = uniqid() . "_" . basename($_FILES['foto']['name']);
            $foto_path = "uploads/" . $foto_name;
            move_uploaded_file($foto_tmp, $foto_path);
            $foto = $foto_path;
        } else {
            $foto = "";
        }

        // handle file_resume
        if (isset($_FILES['file_resume']) && $_FILES['file_resume']['error'] === UPLOAD_ERR_OK) {
            $resume_tmp = $_FILES['file_resume']['tmp_name'];
            $resume_name = uniqid() . "_" . basename($_FILES['file_resume']['name']);
            $resume_path = "uploads/" . $resume_name;
            move_uploaded_file($resume_tmp, $resume_path);
            $file_resume = $resume_path;
        } else {
            $file_resume = "";
        }
    } else {
        send_response("error", "Content-Type tidak dikenali: $contentType");
    }

    // simpan ke database
    $stmt = $koneksi->prepare("INSERT INTO aset_website (foto, deskripsi, file_resume) VALUES (?, ?, ?)");
    if (!$stmt) send_response("error", "Prepare statement gagal: " . $koneksi->error);

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
}


// READ (GET)
elseif ($method === 'GET') {
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

// UPDATE (PUT)
elseif ($method === 'PUT') {
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

// DELETE (DELETE)
elseif ($method === 'DELETE') {
    if (!$input || !isset($input['id'])) send_response("error", "Input JSON tidak valid atau id tidak ada");

    $id = intval($input['id']);
    $stmt = $koneksi->prepare("DELETE FROM aset_website WHERE id = ?");
    if (!$stmt) send_response("error", "Prepare statement gagal: " . $koneksi->error);

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
