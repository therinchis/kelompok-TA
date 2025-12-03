<?php
include 'koneksi.php';

if (isset($_GET['nim'])) {
    $nim = $_GET['nim'];
    $query_user = "SELECT * FROM tbl_users WHERE nim = '$nim'";
} else {
    $query_user = "SELECT * FROM tbl_users ORDER BY id_user DESC LIMIT 1";
}

$result_user = $DB->query($query_user);
$user = $result_user->fetch_assoc();
if (!$user) die("Belum ada data user.");
$nim = $user['nim'];

// Query Data
$biodata      = $DB->query("SELECT * FROM tbl_biodata WHERE nim = '$nim'");
$pendidikan   = $DB->query("SELECT * FROM tbl_pendidikan WHERE nim = '$nim' ORDER BY tahun DESC");
$pengalaman   = $DB->query("SELECT * FROM tbl_pengalaman WHERE nim = '$nim'");
$keahlian     = $DB->query("SELECT * FROM tbl_keahlian WHERE nim = '$nim'");
$aside_items  = $DB->query("SELECT * FROM tbl_aside WHERE nim = '$nim'");
$footer_data  = $DB->query("SELECT * FROM tbl_footer WHERE nim = '$nim'")->fetch_assoc();
$list_users = $DB->query("SELECT nim, nama_lengkap, foto_profil FROM tbl_users ORDER BY id_user DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil - <?= $user['nama_lengkap'] ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
      <?php if (!empty($user['foto_background'])): ?>
      body { background: url('uploads/<?= $user['foto_background'] ?>') no-repeat center/cover !important; background-attachment: fixed !important; }
      <?php endif; ?>
      .user-switcher { position: fixed; top: 20px; right: 20px; background: rgba(255,255,255,0.9); padding: 10px; border-radius: 8px; z-index: 9999; }
  </style>
</head>
<body>

 <div class="user-dropdown">
      <button class="drop-btn">
          <i class="fas fa-user-circle"></i> 
          <span>Ganti User</span>
          <i class="fas fa-chevron-down arrow"></i>
      </button>
      
      <div class="dropdown-content">
          <p class="dropdown-header">Pilih Profil:</p>
          <?php 
          // Kembalikan data user ke baris pertama agar bisa di-loop ulang
          $list_users->data_seek(0); 
          while($u = $list_users->fetch_assoc()): 
          ?>
              <a href="?nim=<?= $u['nim'] ?>" class="<?= ($u['nim'] == $nim) ? 'active-user' : '' ?>">
                  <img src="<?= !empty($u['foto_profil']) ? 'uploads/'.$u['foto_profil'] : 'pp.jpg' ?>" class="mini-pp">
                  <?= $u['nama_lengkap'] ?>
              </a>
          <?php endwhile; ?>
      </div>
  </div>

  <header>
    <div class="profile-image"><img src="<?= !empty($user['foto_profil']) ? "uploads/".$user['foto_profil'] : "pp.jpg" ?>"></div>
    <div class="profile-text"><h1>Profil Mahasiswa</h1><p><?= $user['nama_lengkap'] ?></p></div>
  </header>

  <div class="running-banner"><marquee scrollamount="2"><?= $footer_data['quote'] ?? "Welcome!" ?></marquee></div>

  <main>
    <div class="main-layout">
      <nav>
        <ul>
            <li><a href="#biodata" onclick="showContent('biodata', this)" class="active-link">Biodata</a></li>
            <li><a href="#pendidikan" onclick="showContent('pendidikan', this)">Pendidikan</a></li>
            <li><a href="#pengalaman" onclick="showContent('pengalaman', this)">Pengalaman</a></li>
            <li><a href="#keahlian" onclick="showContent('keahlian', this)">Keahlian</a></li>
        </ul>
      </nav>
      
      <section id="main-content">
        <article id="biodata" class="content-article active">
          <h2>Biodata</h2>
          <p class="data-item">Nama: <?= $user['nama_lengkap'] ?></p>
          <p class="data-item">NIM: <?= $user['nim'] ?></p>
          <?php while($row = $biodata->fetch_assoc()): ?>
             <?php if(in_array($row['judul'], ['Program Studi', 'Universitas'])): ?>
                <p class="data-item"><?= $row['judul'] ?>: <?= $row['isi'] ?></p>
             <?php else: ?>
                <br><p><b><?= $row['judul'] ?></b></p><p><?= $row['isi'] ?></p>
             <?php endif; ?>
          <?php endwhile; ?>
        </article>

        <article id="pendidikan" class="content-article">
            <h2>Pendidikan</h2>
            <div class="timeline-pendidikan">
                <?php while($edu = $pendidikan->fetch_assoc()): ?>
                <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content">
                    <h4><?= $edu['institusi'] ?></h4><p class="timeline-period"><?= $edu['tahun'] ?></p>
                    <?= !empty($edu['jurusan']) ? "<p>Jurusan: {$edu['jurusan']}</p>" : "" ?>
                </div></div>
                <?php endwhile; ?>
            </div>
        </article>

        <article id="pengalaman" class="content-article">
            <h2>Pengalaman</h2>
            <div class="experience-container">
                <?php while($exp = $pengalaman->fetch_assoc()): ?>
                <div class="experience-item">
                    <div class="exp-photo"><img src="<?= !empty($exp['foto']) ? "uploads/".$exp['foto'] : "https://via.placeholder.com/120" ?>"></div>
                    <div class="exp-text"><h3><?= $exp['judul'] ?></h3><p><?= $exp['isi'] ?></p></div>
                </div>
                <?php endwhile; ?>
            </div>
        </article>

        <article id="keahlian" class="content-article">
            <h2>Keahlian</h2>
            <ul><?php while($skill = $keahlian->fetch_assoc()): ?><li><strong><?= $skill['judul'] ?></strong> <?= !empty($skill['isi']) ? "- ".$skill['isi'] : "" ?></li><?php endwhile; ?></ul>
        </article>
      </section>

   <aside>
        <h4>Hobi dan Minat</h4>
        <div class="aside-hobbies">
            
            <?php 
            // Reset variabel player ke default
            $lagu_path = "";
            $judul_lagu_player = "Belum Ada Lagu"; 
            $nama_artis_player = "Silakan Upload";
            
            // Loop semua data dari database
            while($item = $aside_items->fetch_assoc()): 
                
                
                if (!empty($item['lagu']) && strpos($item['lagu'], '.mp3') !== false) {
                     $lagu_path = "uploads/" . $item['lagu'];
                     $judul_lagu_player = !empty($item['judul_lagu']) ? $item['judul_lagu'] : "Judul Tidak Diketahui";
                     $nama_artis_player = !empty($item['keterangan']) ? $item['keterangan'] : "-";
                }
                
               
                if (!empty($item['nama_kegiatan'])):
            ?>
                <div class="hobby-card">
                    <?php if(!empty($item['foto'])): ?>
                        <img src="uploads/<?= $item['foto'] ?>" alt="<?= $item['nama_kegiatan'] ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/150?text=Hobby" alt="No Image">
                    <?php endif; ?>
                    
                    <h5><?= $item['nama_kegiatan'] ?></h5>
                </div>
            <?php 
                endif; // Penutup IF nama_kegiatan
            endwhile; 
            ?>
            
            <div class="hobby-card music-player-card">
                <div class="album-art">
                    <img src="<?= !empty($user['foto_profil']) ? "uploads/".$user['foto_profil'] : "pp.jpg" ?>">
                </div>
                <div class="track-info">
                    <h6 id="track-title"><?= $judul_lagu_player ?></h6>
                    <p class="artist-name" id="artist"><?= $nama_artis_player ?></p>
                    <p class="duration" id="track-duration">--:--</p>
                </div>
                <div class="controls">
                    <button class="play-btn" id="play-pause-btn">▶</button>
                </div>
            </div>
            
        </div>
        
        <audio id="my-audio" src="<?= $lagu_path ?>"></audio>
    </aside>
  </main>
  
  <footer>
    <div class="footer-content">
        <div class="footer-col col-left">
             <div class="social-links">
                <?php if(!empty($footer_data['linkedin'])) echo "<a href='{$footer_data['linkedin']}'><i class='fab fa-linkedin'></i></a>"; ?>
                <?php if(!empty($footer_data['spotify'])) echo "<a href='{$footer_data['spotify']}'><i class='fab fa-spotify'></i></a>"; ?>
                <?php if(!empty($footer_data['instagram'])) echo "<a href='{$footer_data['instagram']}'><i class='fab fa-instagram'></i></a>"; ?>
             </div>
        </div>
        <div class="footer-col col-center"><p><?= $footer_data['copyright'] ?? "© 2024" ?></p></div>
        <div class="footer-col col-right"><p class="nama-web">profile</p><p class="slogan"><?= $footer_data['quote'] ?? "" ?></p></div>
    </div>
  </footer>

  <script>
    function showContent(id, el) {
      document.querySelectorAll('.content-article').forEach(a => a.classList.remove('active'));
      document.querySelectorAll('nav a').forEach(a => a.classList.remove('active-link'));
      document.getElementById(id).classList.add('active');
      el.classList.add('active-link');
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        const audio = document.getElementById('my-audio');
        const btn = document.getElementById('play-pause-btn');
        const dur = document.getElementById('track-duration');
        let playing = false;
        
        if(audio.getAttribute('src') === "") {
            btn.style.display = 'none'; 
        }

        btn.addEventListener('click', () => {
            if (playing) { audio.pause(); btn.textContent = '▶'; playing = false; }
            else { audio.play(); btn.textContent = '⏸'; playing = true; }
        });

        audio.addEventListener('loadedmetadata', () => {
            if (isFinite(audio.duration)) {
                let m = Math.floor(audio.duration / 60);
                let s = Math.floor(audio.duration % 60).toString().padStart(2, '0');
                dur.textContent = `${m}:${s}`;
            }
        });
        audio.addEventListener('ended', () => { btn.textContent = '▶'; playing = false; });
    });
  </script>
</body>
</html>