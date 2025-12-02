<?php
include("koneksi.php");

// 1. Tentukan Tabel yang Sedang Aktif
$tabel_aktif = isset($_GET['tabel']) ? $_GET['tabel'] : 'tbl_users';

// 2. Daftar Tabel & Kolom (Sesuai Database Terbaru)
$allowed_tables = [
    'tbl_users'      => ['pk' => 'id_user', 'cols' => ['nim', 'nama_lengkap', 'foto_profil']], 
    'tbl_biodata'    => ['pk' => 'id_biodata', 'cols' => ['nim', 'judul', 'isi']],
    'tbl_pendidikan' => ['pk' => 'id_pendidikan', 'cols' => ['nim', 'institusi', 'jurusan', 'tahun']],
    'tbl_pengalaman' => ['pk' => 'id_pengalaman', 'cols' => ['nim', 'judul', 'isi', 'foto']],
    'tbl_keahlian'   => ['pk' => 'id_keahlian', 'cols' => ['nim', 'judul', 'isi']], // Tanpa Foto
    'tbl_aside'      => ['pk' => 'id_aside', 'cols' => ['nim', 'foto', 'nama_kegiatan', 'judul_lagu', 'keterangan', 'lagu']],
    'tbl_footer'     => ['pk' => 'id_footer', 'cols' => ['nim', 'linkedin', 'spotify', 'instagram', 'copyright', 'quote']]
];

// Validasi tabel agar aman
if (!array_key_exists($tabel_aktif, $allowed_tables)) {
    $tabel_aktif = 'tbl_users';
}

$primaryKey = $allowed_tables[$tabel_aktif]['pk'];
$columns    = $allowed_tables[$tabel_aktif]['cols'];

// Variabel Edit
$v_id   = "";
$v_data = [];
$op     = isset($_GET['op']) ? $_GET['op'] : '';

// --- A. LOGIKA HAPUS DATA (Record) ---
if ($op == 'hapus') {
    $id = $_GET['id'];
    $DB->query("DELETE FROM $tabel_aktif WHERE $primaryKey = '$id'");
    echo "<script>alert('Data berhasil dihapus'); document.location='admin_profile.php?tabel=$tabel_aktif';</script>";
}

// --- B. LOGIKA AMBIL DATA (Edit Mode) ---
if ($op == 'edit') {
    $id = $_GET['id'];
    $r = $DB->query("SELECT * FROM $tabel_aktif WHERE $primaryKey = '$id'");
    $v_data = $r->fetch_assoc();
    $v_id = $id;
}

// --- C. LOGIKA SIMPAN (Insert / Update) ---
if (isset($_POST['simpan'])) {
    $id_val = $_POST['id_val']; 
    $set_values = [];
    $insert_cols = [];
    $insert_vals = [];

    foreach ($columns as $col) {
        $val = "";
        $is_file = (strpos($col, 'foto') !== false || $col == 'lagu');

        if ($is_file) {
            // 1. Jika ada upload file baru
            if (isset($_FILES[$col]) && !empty($_FILES[$col]['name'])) {
                $nama_file = $_FILES[$col]['name'];
                $tmp_file = $_FILES[$col]['tmp_name'];
                $nama_baru = time() . "_" . basename($nama_file); // Rename unik
                
                if (move_uploaded_file($tmp_file, "uploads/" . $nama_baru)) {
                    $val = $nama_baru;
                } else {
                    $val = ""; 
                }
            } 
            // 2. Jika user mencentang "Hapus File"
            elseif (isset($_POST['delete_' . $col])) {
                $val = ""; 
            }
            // 3. Jika tidak ada perubahan, pakai file lama
            else {
                if ($id_val != "") {
                    $val = $_POST[$col . '_old'] ?? '';
                }
            }
        } else {
            // Data Teks Biasa
            $val = isset($_POST[$col]) ? $DB->real_escape_string($_POST[$col]) : '';
        }

        $set_values[] = "$col = '$val'";
        $insert_cols[] = $col;
        $insert_vals[] = "'$val'";
    }

    if ($id_val) {
        $sql = "UPDATE $tabel_aktif SET " . implode(", ", $set_values) . " WHERE $primaryKey = '$id_val'";
    } else {
        $sql = "INSERT INTO $tabel_aktif (" . implode(", ", $insert_cols) . ") VALUES (" . implode(", ", $insert_vals) . ")";
    }

    if ($DB->query($sql)) {
        echo "<script>alert('Data berhasil disimpan'); document.location='admin_profile.php?tabel=$tabel_aktif';</script>";
    } else {
        echo "<div style='color:red'>Error: " . $DB->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- TEMA GLOBAL (Sesuai Index) --- */
        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: url('https://i.pinimg.com/1200x/59/c5/3e/59c53e7770ca3e101c78aabc9da77d4f.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            padding: 20px; 
            margin: 0;
        }

        h1 {
            color: #1a5632;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px 20px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            font-weight: 700;
        }

        /* --- NAVIGASI --- */
        .nav-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        
        .nav-tabs a { 
            text-decoration: none; padding: 10px 20px; 
            background: rgba(255, 255, 255, 0.8); 
            color: #1a5632; border-radius: 8px; 
            font-weight: 600; font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .nav-tabs a:hover { background: #fff; transform: translateY(-2px); }
        .nav-tabs a.active { background: #EC8CA6; color: white; box-shadow: 0 4px 10px rgba(236, 140, 166, 0.4); }
        
        /* --- CONTAINER --- */
        .container { display: flex; gap: 20px; flex-direction: column; }
        @media(min-width: 900px) { .container { flex-direction: row; } }
        
        /* --- CARD --- */
        .card { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
            flex: 1; 
            backdrop-filter: blur(5px);
        }

        .card h3 {
            margin-top: 0; color: #1a5632;
            border-bottom: 2px solid #EC8CA6; 
            padding-bottom: 10px; margin-bottom: 20px;
        }
        
        /* --- FORM --- */
        label { display: block; margin-top: 15px; font-weight: 600; font-size: 0.9rem; margin-bottom: 5px; color: #1a5632; }
        input[type="text"], textarea { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px; font-family: 'Poppins', sans-serif; }
        input[type="text"]:focus, textarea:focus { outline: none; border-color: #EC8CA6; }
        input[type="file"] { margin-top: 5px; font-size: 0.9rem; }
        
        .btn-save { 
            background: #1a5632; color: white; 
            padding: 12px 20px; border: none; 
            cursor: pointer; margin-top: 25px; 
            border-radius: 8px; font-weight: 700; 
            width: 100%; font-family: 'Poppins', sans-serif;
            transition: background 0.3s;
        }
        .btn-save:hover { background: #EC8CA6; }

        /* --- PREVIEW FILE --- */
        .form-preview { margin-top: 10px; padding: 10px; background: #fff; border: 2px dashed #ddd; border-radius: 8px; display: inline-block; text-align: center; }
        .form-preview img { max-width: 100px; height: auto; border-radius: 4px; display: block; margin-bottom: 5px; }
        .checkbox-del { font-weight: normal; color: #e74c3c; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 5px; margin-top: 5px; }

        /* --- GROUPING UNTUK ASIDE --- */
        .input-group-custom { 
            border-left: 4px solid #1a5632; padding-left: 20px; 
            background: #f8fcf9; padding: 15px; 
            border-radius: 0 8px 8px 0; margin-bottom: 20px; 
        }
        
        /* --- TABEL --- */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; }
        th { background: #1a5632; color: white; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        tr:hover { background-color: #fcfcfc; }
        .preview { max-width: 60px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        /* Links Edit/Hapus */
        a.act-btn { text-decoration: none; font-weight: 600; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
        a.edit { color: #e67e22; background: #fff5e6; }
        a.hapus { color: #c0392b; background: #fcebe9; }
    </style>
</head>
<body>

    <h1>Admin Dashboard</h1>

    <div class="nav-tabs">
        <?php foreach ($allowed_tables as $tbl => $info): ?>
            <a href="?tabel=<?php echo $tbl; ?>" class="<?php echo ($tabel_aktif == $tbl) ? 'active' : ''; ?>">
                <?php echo strtoupper(str_replace('tbl_', '', $tbl)); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="container">
        
        <div class="card" style="flex: 0 0 400px;">
            <h3>Form <?php echo ($op == 'edit') ? 'Edit' : 'Tambah'; ?> Data</h3>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_val" value="<?php echo $v_id; ?>">

                <?php if($tabel_aktif == 'tbl_aside'): ?>
                    
                    <label>NIM</label>
                    <input type="text" name="nim" value="<?= $v_data['nim'] ?? '' ?>">

                    <div class="input-group-custom">
                        <strong>BAGIAN 1: KARTU HOBI</strong>
                        <label>Nama Kegiatan</label>
                        <input type="text" name="nama_kegiatan" value="<?= $v_data['nama_kegiatan'] ?? '' ?>" placeholder="Contoh: Membaca">
                        
                        <label>Foto Kegiatan</label>
                        <input type="file" name="foto">
                        <input type="hidden" name="foto_old" value="<?= $v_data['foto'] ?? '' ?>">
                        <?php if(!empty($v_data['foto'])): ?>
                            <div class="form-preview">
                                <img src="uploads/<?= $v_data['foto'] ?>">
                                <label class="checkbox-del"><input type="checkbox" name="delete_foto" value="1"> Hapus foto?</label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group-custom" style="border-left-color: #EC8CA6;">
                        <strong style="color: #EC8CA6;">BAGIAN 2: MUSIK</strong>
                        <label>Judul Lagu</label>
                        <input type="text" name="judul_lagu" value="<?= $v_data['judul_lagu'] ?? '' ?>" placeholder="Contoh: Kabar Bahagia">
                        
                        <label>Artis / Keterangan</label>
                        <input type="text" name="keterangan" value="<?= $v_data['keterangan'] ?? '' ?>" placeholder="Contoh: Rumahsakit">
                        
                        <label>File Lagu (.mp3)</label>
                        <input type="file" name="lagu" accept=".mp3,audio/*">
                        <input type="hidden" name="lagu_old" value="<?= $v_data['lagu'] ?? '' ?>">
                        <?php if(!empty($v_data['lagu'])): ?>
                            <div class="form-preview">
                                🎵 <?= $v_data['lagu'] ?>
                                <label class="checkbox-del"><input type="checkbox" name="delete_lagu" value="1"> Hapus lagu?</label>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    
                    <?php foreach ($columns as $col) { 
                        $val = $v_data[$col] ?? '';
                        echo "<label>".strtoupper(str_replace('_',' ',$col))."</label>";

                        if (strpos($col, 'foto') !== false) {
                            // Upload Foto Standard
                            echo "<input type='file' name='$col'>";
                            echo "<input type='hidden' name='{$col}_old' value='$val'>";
                            if (!empty($val)) {
                                echo "<div class='form-preview'><img src='uploads/$val'>";
                                echo "<label class='checkbox-del'><input type='checkbox' name='delete_$col' value='1'> Hapus foto ini?</label></div>";
                            }
                        } elseif ($col == 'isi' || $col == 'quote') {
                            echo "<textarea name='$col' rows='4'>$val</textarea>";
                        } else {
                            echo "<input type='text' name='$col' value='$val'>";
                        }
                    } ?>

                <?php endif; ?>

                <input type="submit" name="simpan" value="SIMPAN DATA" class="btn-save">
                <?php if($op == 'edit'): ?>
                    <a href="?tabel=<?= $tabel_aktif ?>" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none;">Batal Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h3>Data: <?php echo strtoupper(str_replace('tbl_', '', $tabel_aktif)); ?></h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php foreach ($columns as $col) echo "<th>".strtoupper(str_replace('_',' ',$col))."</th>"; ?>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $r = $DB->query("SELECT * FROM $tabel_aktif ORDER BY $primaryKey DESC");
                        $no = 1;
                        while ($d = $r->fetch_assoc()) {
                            echo "<tr><td>".$no++."</td>";
                            foreach ($columns as $col) {
                                $isi = $d[$col] ?? '';
                                
                                if(strpos($col, 'foto') !== false && !empty($isi)) 
                                    echo "<td><img src='uploads/$isi' class='preview'></td>";
                                elseif($col == 'lagu' && !empty($isi))
                                    echo "<td>🎵 Ada</td>";
                                else 
                                    echo "<td>".(strlen($isi)>40 ? substr($isi,0,40)."..." : $isi)."</td>";
                            }
                            echo "<td>
                                <a href='?tabel=$tabel_aktif&op=edit&id={$d[$primaryKey]}' class='act-btn edit'>Edit</a> 
                                <a href='?tabel=$tabel_aktif&op=hapus&id={$d[$primaryKey]}' onclick='return confirm(\"Yakin hapus data ini?\")' class='act-btn hapus'>Hapus</a>
                            </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>