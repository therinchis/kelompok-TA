<?php 
    // Menggunakan user 'root' dan password kosong (default XAMPP)
    // Database yang dipilih adalah 'db_profile'
    $DB = new mysqli("localhost", "root", "", "db_profile");

    // Cek jika terjadi error
    if(mysqli_connect_errno()) {
        echo("Gagal koneksi, pesan kesalahan: " . mysqli_connect_error());
        exit();
    }
?>