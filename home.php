<?php
require_once 'direct/config.php';

$pageTitle = "Home";

/**
 * ==========================
 * AMBIL DATA REQUEST
 * ==========================
 */

// Subquery status terakhir
$sql = "
SELECT 
    r.id,
    u.username AS peminta,
    r.user_toko,
    r.jenis_kendala,
    r.level_urgensi,
    r.id_staff,
    s.status AS status_kode,
    stf.username AS staff_name
FROM transaksi_request r
JOIN users u ON u.id = r.user_request
LEFT JOIN users stf ON stf.id = r.id_staff
LEFT JOIN transaksi_status ts ON ts.request_id = r.id
LEFT JOIN status s ON s.id = ts.current_status
WHERE ts.change_datetime = (
    SELECT MAX(ts2.change_datetime)
    FROM transaksi_status ts2
    WHERE ts2.request_id = r.id
)
ORDER BY r.input_datetime DESC
";

$result = $conn->query($sql);
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

ob_start();
?>

<link rel="stylesheet" href="dist/css/home.css">

<div class="main-container">

    <!-- =========================
         SIDEBAR USER (LEFT)
    ========================== -->
    <aside class="sidebox">

        <?php if (can('input_request')): ?>
        <div class="sidebar-box request-box clickable-box" onclick="ajukanRequest('baru')">
            <h3>Ajukan Request</h3>
            <p>Laporkan Kendala pada Staff IT</p>
        </div>
        <?php endif; ?>

        <?php if (can('input_request')): ?>
        <div class="sidebar-box done-canceled-box clickable-box" onclick="lihatRiwayat()">
            <h3>Done & Canceled</h3>
            <p>Lihat riwayat request</p>
        </div>
        <?php endif; ?>

        <?php if (can('input_request')): ?>
        <div class="sidebar-box unrated-box clickable-box" onclick="lihatUnrated()">
            <h3>Belum Di-Rating</h3>
            <p>Request selesai tapi belum diberi rating</p>
            <div class="box-badge" id="unratedCount">0</div>
        </div>
        <?php endif; ?>

        <?php if (isStaffIT()): ?>
        <div class="urgency-queue-panel">
            <h2>Open Queue</h2>
            <p class="sort-info">Urut: Urgensi → Waktu</p>

            <div class="queue-items-container">
                <?php
                $hasOpenQueue = false;
                foreach ($requests as $r):
                    if ($r['status_kode'] === 'O'):
                        $hasOpenQueue = true;
                ?>
                    <div class="queue-item">
                        <span>
                            <?= htmlspecialchars($r['peminta']) ?>
                            (<?= htmlspecialchars($r['level_urgensi']) ?>)
                        </span>
                        <button class="open-detail-btn">Open</button>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>

                <?php if (!$hasOpenQueue): ?>
                    <div class="open-empty" colspan="9" style="text-align:center;">
                        <p>Tidak ada request Open saat ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </aside>
    <!-- END SIDEBAR -->

    <!-- =========================
         CONTENT
    ========================== -->
    <div class="content-area">
        <h2 class="all-request-title">All Request</h2>

        <div class="table-responsive">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Peminta</th>
                        <th>Toko</th>
                        <th>Jenis Kendala</th>
                        <th>Status</th>
                        <th>Urgensi</th>
                        <th>Staff IT</th>
                        <th colspan="2">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (!$requests): ?>
                    <tr>
                        <td class="queue-empty" colspan="9" style="text-align:center;">
                            Tidak ada antrean request saat ini
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($requests as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($r['peminta']) ?></td>
                        <td><?= htmlspecialchars($r['user_toko']) ?></td>
                        <td><?= htmlspecialchars($r['jenis_kendala']) ?></td>
                        <td><?= htmlspecialchars($r['status_kode'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['level_urgensi']) ?></td>
                        <td><?= htmlspecialchars($r['staff_name'] ?? '-') ?></td>

                        <!-- Detail -->
                        <td>
                            <button class="btn-detail">
                                Detail
                            </button>
                        </td>

                        <!-- Action -->
                        <td>
                            <?php if (can('cancel_request') && $r['user_request'] == $_SESSION['user_id']): ?>
                                <button class="btn-cancel">Cancel</button>
                            <?php endif; ?>

                            <?php if (can('handling_request')): ?>
                                <button class="btn-transfer">Transfer</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk Pengajuan Request --> 
 <div id="requestModal" class="modal-overlay"> 
    <div class="modal-content"> 
        <div class="modal-header"> 
            <h2>Ajukan Request</h2> 
            <span class="modal-close-btn" onclick="closeModal()"> 
                <i class="fas fa-times"></i> 
            </span> 
        </div> 
        <form class="request-form" onsubmit="submitRequestForm(event)"> 
            <h3>Form Pengajuan Service Request</h3> 
            <div class="form-grid"> <div class="form-column"> 
                <div class="form-group"> 
                    <label for="toko">Toko:</label> 
                    <select id="toko" required> 
                        <option value="">-- Pilih --</option> 
                        <option value="toko_a">TM</option> 
                        <option value="toko_b">HK</option> 
                        <option value="toko_c">RJ</option> 
                    </select> 
                </div> 
                <div class="form-group radio-group"> 
                    <label>Untuk siapa:</label> 
                    <label>
                        <input type="radio" name="untuk_siapa" value="diri_sendiri" checked onclick="toggleNamaLain(false)"> Diri Sendiri
                    </label> 
                    <label>
                        <input type="radio" name="untuk_siapa" value="orang_lain" onclick="toggleNamaLain(true)"> Orang Lain
                    </label> 
                </div> 
                <div class="form-group" id="namaLainGroup" style="display: none;"> 
                    <label for="nama_lain">Nama orang lain:
                    </label> 
                    <input type="text" id="nama_lain" placeholder="Nama orang lain..">
                 </div> 
                 <div class="form-group radio-group type-kendala-group"> 
                    <label>Tipe Kendala:  
                    </label> 
                    <label>
                        <input type="radio" name="tipe_kendala" value="Software" checked onclick="toggleHardwareFields(false)"> Software
                    </label> 
                    <label>
                        <input type="radio" name="tipe_kendala" value="Hardware" onclick="toggleHardwareFields(true)"> Hardware
                    </label> 
                </div> 
                <div class="form-group hardware-group" id="jenisHardwareGroup" style="display: none;"> 
                    <label for="jenis_hardware">Jenis Hardware:  
                    </label> <select id="jenis_hardware"> 
                        <option value="">-- Pilih --</option> 
                        <option value="printer">Printer</option> 
                        <option value="monitor">Monitor</option> 
                        <option value="keyboard">Keyboard</option> 
                        <option value="mouse">Mouse</option> 
                        <option value="pc">PC/Desktop</option> 
                        <option value="laptop">Laptop</option> 
                    </select> 
                </div> 
                <div class="form-group hardware-group" id="kodeHardwareGroup" style="display: none;"> 
                    <label for="kode_hardware">Kode Hardware (Opsional):
                    </label> 
                    <input type="text" id="kode_hardware" placeholder="Contoh: PC-001, PRN-002"> 
                </div> 
            </div> 
            <div class="form-column"> 
                <div class="form-group"> 
                    <label for="jenis_kendala">Jenis Kendala:
                    </label> 
                    <select id="jenis_kendala" required> 
                        <option value="">-- Pilih --</option> 
                        <option value="app_error">Aplikasi Error</option> 
                        <option value="koneksi">Koneksi Internet</option> 
                        <option value="software">Software Installation</option> 
                        <option value="email">Email Configuration</option> 
                        <option value="printer">Printer Issues</option> 
                        <option value="hardware">Hardware Failure</option> 
                    </select> 
                </div> 
                <div class="form-group"> 
                    <label for="terjadi_di">Terjadi di siapa saja:</label> 
                    <select id="terjadi_di" required> <option value="">-- Pilih --</option> 
                    <option value="semua">Semua Orang</option> 
                    <option value="beberapa">Beberapa Orang</option> 
                    <option value="satu">Satu Orang Saja</option> 
                </select> </div> <div class="form-group"> 
                    <label for="deskripsi">Deskripsi Kendala:</label>
                    <textarea id="deskripsi" placeholder="Jelaskan kendala secara detail.." required></textarea> 
                </div> 
                <div class="form-group"> 
                    <label>Add Foto:</label> 
                    <div class="upload-box" onclick="triggerFileUpload()"> 
                        <i class="fas fa-image"></i> 
                        <input type="file" id="fileUpload" style="display: none;" accept="image/*" onchange="handleFileSelect(event)"> 
                        <span id="fileName">Masukkan SS/Image</span> 
                    </div> 
                </div> 
                <div class="form-group urgency-info"> 
                    <label>Level Urgensi:</label> 
                    <p>*Level urgensi ditentukan otomatis berdasarkan berapa banyak yang terdampak kendala</p> 
                </div> 
            </div> 
        </div> 
        <div class="modal-footer"> 
            <button type="submit" class="btn btn-submit"> 
                <i class="fas fa-paper-plane"></i> Ajukan Request 
            </button> 
        </div> 
    </form> 
</div> 
</div> 

<!-- Modal untuk Request Belum Di-Rating - DI LUAR modal request --> 
 <div id="unratedModal" class="modal-overlay"> 
    <div class="modal-content"> 
        <div class="modal-header"> 
            <h2>Request Belum Di-Rating</h2> 
            <span class="modal-close-btn" onclick="closeUnratedModal()"> 
                <i class="fas fa-times"></i> 
            </span> 
        </div> 
        <div class="unrated-content request-form"> 
            <div class="unrated-header"> 
                <h3>Request yang Telah Selesai</h3> 
                <p>Berikan rating untuk pelayanan dari IT Staff</p> 
            </div> 
            <div id="unratedList" class="unrated-list">
            </div> 
            <div class="modal-footer"> 
            </div> 
        </div> 
    </div> 
 /a></div>

<script src="dist/js/home.js"></script>

<?php
$content = ob_get_clean();
require_once 'modules/layout/template.php';
?>
