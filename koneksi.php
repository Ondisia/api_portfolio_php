<?php
$host = 'localhost';
$user = 'root';
$password = 'Domon123@';
$dbname = 'portfolio_db';

$koneksi = mysqli_connect($host, $user, $password, $dbname);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}