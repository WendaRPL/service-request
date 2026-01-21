<?php
require_once 'direct/config.php';

$pageTitle = "Home";

/**
 * ==========================
 * REQUEST BELUM DI-RATING
 * ==========================
 */
$sqlUnrated = "
SELECT 
    r.id,
    r.input_datetime,
    IFNULL(r.jenis_kendala,'') AS jenis_kendala,
    IFNULL(r.hasil_it,'') AS hasil_it,
    IFNULL(stf.username,'-') AS staff_name
FROM transaksi_request r
LEFT JOIN users stf ON r.handling_by = stf.id
WHERE 
    r.user_request = ?
    AND r.status = 4
    AND (r.rating IS NULL OR r.rating = '')
ORDER BY r.input_datetime DESC
";

$stmt = $conn->prepare($sqlUnrated);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resUnrated = $stmt->get_result();
$unratedRequests = $resUnrated->fetch_all(MYSQLI_ASSOC);    

/**
 * ==========================
 * AMBIL DATA REQUEST (QUEUE)
 * ==========================
 */

$sql = "
SELECT 
    r.id,
    r.handling_by,
    IFNULL(r.tindakan_it, '') AS tindakan_it,        
    IFNULL(r.hasil_it, '') AS hasil_it,
    IFNULL(r.ketidaksesuaian_detail, '') AS ketidaksesuaian_detail,
    COALESCE(t.nama_toko, t_alt.nama_toko, '') AS nama_toko,
    COALESCE(t.kode_toko, t_alt.kode_toko, '') AS kode_toko,
    IFNULL(u_req.username, 'No Peminta') AS peminta,
    u_req.id AS id_peminta,
    IFNULL(u_penerima.username, '') AS penerima,
    u_penerima.id AS id_penerima_user,
    IFNULL(r.terjadi_pada, '') AS terjadi_pada,
    IFNULL(r.tipe_kendala, '') AS sh_code, 
    IFNULL(r.level_urgensi, '') AS level_urgensi,
    IFNULL(r.jenis_kendala, '') AS jenis_kendala,  
    IFNULL(r.kode_hardware, '') AS kode_hardware,
    r.status AS id_status,
    TRIM(IFNULL(s.status, '')) AS status_nama,
    IFNULL(stf.username, '-') AS staff_name, 
    IFNULL(r.description, '') AS description,
    IFNULL (r.upload, '') AS upload, 
    r.input_datetime
FROM transaksi_request r
LEFT JOIN master_toko t ON r.user_toko = t.id
LEFT JOIN users u_req ON r.user_request = u_req.id
LEFT JOIN master_toko t_alt ON u_req.toko_id = t_alt.id
LEFT JOIN users u_penerima ON r.user_penerima = u_penerima.id
LEFT JOIN users stf ON r.handling_by = stf.id AND stf.role = 1
LEFT JOIN status s ON r.status = s.id
ORDER BY 
    CASE WHEN r.level_urgensi = 'High' THEN 1 ELSE 2 END,
    r.input_datetime DESC
";

$result = $conn->query($sql);
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
// Ambil ID staff yang sedang login dari session
$myId = $_SESSION['user_id'];

$sqlUsers = "SELECT id, username FROM users WHERE role != 1 ORDER BY username ASC";
$resUsers = $conn->query($sqlUsers);
$allUsers = $resUsers ? $resUsers->fetch_all(MYSQLI_ASSOC) : [];

$usersForSearch = $allUsers;  

// Query hanya ambil role Staff IT (role id = 1) dan bukan dirinya sendiri
$sqlAllUsers = "SELECT id, username FROM users WHERE role != 1 ORDER BY username ASC";
$resAllUsers = $conn->query($sqlAllUsers);
$allUsers = $resAllUsers ? $resAllUsers->fetch_all(MYSQLI_ASSOC) : [];
$myTokoId = $userData['toko_id'] ?? '';

// Ambil data sesuai struktur tabel Anda (id, tipe_kendala, nama_kendala)
$sql_master = "SELECT id, tipe_kendala, nama_kendala FROM master_jenis_kendala ORDER BY nama_kendala ASC";
$result_master = mysqli_query($conn, $sql_master);

// Ambil semua data toko untuk pilihan
$sqlToko = "SELECT id, nama_toko, kode_toko FROM master_toko";
$resToko = $conn->query($sqlToko);
$allToko = [];
while($t = $resToko->fetch_assoc()) {
    $allToko[] = $t;
}

$data_kendala_db = [
    'Software' => [],  
    'Hardware' => []
];

if ($result_master) {
    while ($row = mysqli_fetch_assoc($result_master)) { 
        $tipe = ucfirst(strtolower($row['tipe_kendala'])); 
        
        if (isset($data_kendala_db[$tipe])) {
            $data_kendala_db[$tipe][] = [
                'id' => $row['id'],
                'nama' => $row['nama_kendala']
            ];
        }
    }
}

ob_start();
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="dist/css/home.css">

<!-- main content -->
<div class="main-layout">
    <!-- box card ajukan req, done & canceled, dan belum di rating (role user) -->
    <div class="main-container">
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
        </aside>
    </div>
        
    <!-- card open -->
    <?php if(can('handling_request')): ?>
    <div class="urgency-queue-panel">
        <h2>Open</h2>
        <p class="sort-info">Urutan berdasarkan Urgensi (High→Low) > Waktu</p>

        <div class="select-all-row">
            <input type="checkbox" id="select-all">
            <label for="select-all">Pilih semua</label>
            
            <div class="action-dropdown-wrapper">
                <button class="action-btn" id="actionDropdownBtn">Aksi ▼</button>  
                <div class="action-dropdown-menu" id="actionDropdown">  
                    <div class="dropdown-item" data-action="accept">Accept</div>
                    <div class="dropdown-item" data-action="change-urgency">Ganti Level Urgensi</div>
                </div>
            </div>
        </div>

        <div class="queue-items-container">
            <?php foreach ($requests as $row): 
                    if ((int)$row['id_status'] === 1): 
                        // --- badge urgensi ---
                        $shCode = ucfirst($row['sh_code']);  
                        $urgensi = $row['level_urgensi'];    
                        $classUrgensi = strtolower($urgensi);  
                        
                        // Gabungkan menjadi format: Software <span class="urgency-badge high">High</span>
                        $badgeHTML = $shCode . ' <span class="urgency-badge ' . $classUrgensi . '">' . $urgensi . '</span>';
            ?>
                <div class="queue-item">
                    <input type="checkbox" id="user<?= $row['id'] ?>" value="<?= $row['id'] ?>">
                    <label for="user<?= $row['id'] ?>">
                        <span class="queue-name"><?= htmlspecialchars($row['peminta']) ?></span>
                        <abbr class="queue-store"><?= htmlspecialchars($row['kode_toko']) ?></abbr>
                        <span class="urgency-badge <?= strtolower($row['level_urgensi']) ?>">
                            <?= htmlspecialchars($row['level_urgensi']) ?>
                        </span>
                        <div class="queue-datetime">
                            <span class="date"><?= date('d/m/Y', strtotime($row['input_datetime'])) ?></span>
                            <span class="time"><?= date('H:i', strtotime($row['input_datetime'])) ?></span>
                        </div>
                    </label>
                    <button class="open-detail-btn" 
                        data-id="<?= $row['id'] ?>"
                        data-toko="<?= htmlspecialchars($row['nama_toko']) ?>"
                        data-peminta="<?= htmlspecialchars($row['peminta']) ?>"
                        data-penerima="<?= htmlspecialchars($row['penerima']) ?>"
                        data-sh='<?= $badgeHTML ?>'  
                        data-jenis="<?= htmlspecialchars($row['jenis_kendala']) ?>"
                        data-status="<?= htmlspecialchars($row['status_nama']) ?>"
                        data-staff="<?= htmlspecialchars($row['staff_name']) ?>"
                        data-desc="<?= htmlspecialchars($row['description']) ?>"
                        data-kode-hw="<?= htmlspecialchars($row['kode_hardware']) ?>"
                        data-terjadi="<?= htmlspecialchars($row['terjadi_pada']) ?>"
                        data-upload="<?= htmlspecialchars($row['upload']) ?>" 
                        onclick="openQueueDetail(this)"> Open Details
                    </button>
                </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- card all request -->
    <div class="all-request-table">
        <div class="border-queue">
            <h2>All Request (Queue)</h2>
        </div>
        
        <div class="table-container">
            <table id="requestTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Toko</th>
                        <th>Peminta</th>
                        <th>Penerima</th>
                        <th>Tipe Kendala (Urgensi)</th>
                        <th>Jenis Kendala</th>
                        <th>Status</th>
                        <th>Handling By</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php $no = 1; foreach ($requests as $row): ?>
                            <?php 
                                $idStatus = (int)$row['id_status'];
                                $handlerId = (int)$row['handling_by'];
                                $myIdInt = (int)$myId;

                                // 1. Cek apakah status sudah selesai (Done=4 atau Canceled=5)
                                $isFinalStatus = ($idStatus === 4 || $idStatus === 5);

                                // 2. Cek apakah tiket masih Open (1)
                                $isOpen = ($idStatus === 1);

                                // 3. Cek apakah ini tiket milik saya
                                $isMyJob = ($handlerId === $myIdInt);

                                if ($isFinalStatus) {
                                    $disabledAttr = 'disabled';
                                } elseif (!$isOpen && !$isMyJob) {
                                    $disabledAttr = 'disabled';
                                } else {
                                    $disabledAttr = '';
                                }
                            ?>
                            <tr data-request-id="<?= $row['id'] ?>"
                                data-penerima-id="<?= $row['id_penerima_user'] ?>" 
                                data-id-peminta="<?= $row['id_peminta'] ?>"
                                data-id-status="<?= $row['id_status'] ?>"
                                data-tindakan-it="<?= htmlspecialchars($row['tindakan_it'] ?? '') ?>"
                                data-hasil-it="<?= htmlspecialchars($row['hasil_it'] ?? '') ?>"
                                data-deskripsi="<?= htmlspecialchars($row['description']) ?>"
                                data-kode-hw="<?= htmlspecialchars($row['kode_hardware'] ?? '')?>"
                                data-terjadi-pada="<?= htmlspecialchars($row['terjadi_pada'] ?? '') ?>"
                                data-ketidaksesuaian-it="<?= htmlspecialchars($row['ketidaksesuaian_detail'] ?? '') ?>">
                                
                                <td><?= $no++ ?>.</td>
                                <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                                <td><?= htmlspecialchars($row['peminta']) ?></td>
                                <td><?= htmlspecialchars($row['penerima']) ?></td>
                                <td class="urgency-cell">
                                    <span class="urgency-code"><?= htmlspecialchars($row['sh_code']) ?></span>
                                    <span class="urgency-badge <?= strtolower($row['level_urgensi']) ?>">
                                        <?= htmlspecialchars($row['level_urgensi']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['jenis_kendala']) ?></td>
                                <td class="status-column">
                                    <div class="status-card status-<?= strtolower(str_replace(' ', '-', $row['status_nama'])) ?>">
                                        <?= htmlspecialchars($row['status_nama']) ?>
                                    </div>
                                </td>
                                <td class="staff-column">
                                    <?= htmlspecialchars($row['staff_name']) ?>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn-detail" 
                                        onclick="event.stopPropagation(); openDetailRequest(<?= $row['id'] ?>)">Open Details
                                    </button>

                                    <?php if ($row['id_peminta'] == $_SESSION['user_id'] && $row['id_status'] == 1): ?>
                                            <button class="btn-reject" onclick="event.stopPropagation(); cancelRequest(<?= $row['id'] ?>)">
                                                Cancel Request
                                            </button>
                                        <?php endif; ?>

                                    <?php if(can('handling_request')): ?>
                                    <button class="btn-transfer" 
                                        onclick="event.stopPropagation(); openTransferHandler(<?= $row['id'] ?>)" 
                                        <?= $disabledAttr ?>>Transfer Handler
                                    </button>

                                    <button class="btn-status" 
                                        onclick="event.stopPropagation(); openUbahStatus(<?= $row['id'] ?>)" 
                                        <?= $disabledAttr ?>>Ubah Status
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk Pengajuan Request (ROLE USER)--> 
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
                    <label>Toko (Pilih satu atau lebih):</label>
                    <div class="checkbox-list-container">
                        <?php foreach($allToko as $t): ?>
                            <label class="toko-item" for="toko_<?= $t['id'] ?>">
                                <input type="checkbox"  
                                    class="toko-checkbox"   name="toko_id[]" 
                                    value="<?= $t['id'] ?>" 
                                    id="toko_<?= $t['id'] ?>"
                                    <?= ($t['id'] == $_SESSION['toko_id']) ? 'checked' : '' ?>>
                                
                                <span class="toko-label-text">
                                    <?= $t['kode_toko'] ?> - <?= $t['nama_toko'] ?>
                                    <?php if($t['id'] == $_SESSION['toko_id']): ?>
                                        <span class="toko-badge">(Toko Anda)</span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">*Toko asal Anda sudah dicentang otomatis. Anda bisa mencentang toko lain jika kendala terjadi di banyak tempat.</small>
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
                <div class="form-group" id="namaLainGroup" style="display: none; position: relative;">
                    <label for="user_search">Cari User (Nama):</label>
                    
                    <input type="text" 
                        id="user_search" 
                        class="form-control" 
                        placeholder="Ketik nama user..." 
                        autocomplete="off" 
                        onkeyup="searchUser(this.value)">
                    
                    <input type="hidden" name="user_penerima" id="user_penerima_id">
                    
                    <div id="search_results" class="search-dropdown" style="display: none;"></div>
                </div>
                <div class="form-group radio-group type-kendala-group"> 
                    <label>Tipe Kendala:</label> 
                    <label>
                        <input type="radio" name="tipe_kendala" value="Software" checked onclick="updateJenisKendala('software'); toggleHardwareFields(false)"> Software
                    </label> 
                    <label>
                        <input type="radio" name="tipe_kendala" value="Hardware" onclick="updateJenisKendala('hardware'); toggleHardwareFields(true)"> Hardware
                    </label> 
                </div>
                <div class="form-group"> 
                    <label for="jenis_kendala">Jenis Kendala:</label> 
                    <select id="jenis_kendala" name="jenis_kendala" required> 
                        <option value="">-- Pilih Tipe Terlebih Dahulu --</option> 
                    </select> 
                </div>
                <div class="form-group hardware-group" id="kodeHardwareGroup" style="display: none;"> 
                    <label for="kode_hardware">Kode Hardware (Opsional):</label> 
                    <input type="text" id="kode_hardware" name="kode_hardware" placeholder="Contoh: PC-001, PRN-002"> 
                </div>
            </div> 
            <div class="form-column"> 
                <div class="form-group"> 
                    <label for="terjadi_di">Terjadi pada siapa saja:</label> 
                    <select id="terjadi_di" required> 
                        <option value="">-- Pilih --</option> 
                        <option value="semua">Semua Orang</option> 
                        <option value="beberapa">Beberapa Orang</option> 
                        <option value="satu">Satu Orang Saja</option> 
                    </select> 
                </div> 
                <div class="form-group"> 
                    <label for="deskripsi">Deskripsi Kendala: (Opsional)</label>
                    <textarea id="deskripsi" placeholder="Jelaskan kendala secara detail.."></textarea> 
                </div>
                <div class="form-group"> 
                    <label>Add Lampiran (Foto/Dokumen):</label> 
                    <div class="upload-box" onclick="triggerFileUpload()"> 
                        <i class="fas fa-paperclip"></i> <input type="file" id="fileUpload" style="display: none;" 
                            accept="image/*, .pdf, .doc, .docx, .xls, .xlsx" 
                            onchange="handleFileSelect(event)"> 
                        <span id="fileName">Masukkan File Lampiran</span> 
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

<!-- Modal untuk Request Belum Di-Rating --> 
<div id="unratedModal" class="modal-overlay"> 
    <div class="modal-content"> 
        <div class="modal-header"> 
            <h2>Request Belum Di-Rating
                <span id="unratedCount" class="unrated-counter">(0)</span>
            </h2>
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
</div> 

<!-- modal accept dan ganti level urgensi -->
<div id="modalBulkAction" class="modal">
    <div class="modal-content bulk-modal">
        <span class="close-btn" data-modal="modalBulkAction">&times;</span>
        <div class="modal-detail">
            <div class="modal-header">
                <h2 id="bulkModalTitle">Konfirmasi Penyetujuan Service Request</h2>
            </div>
            <div class="modal-body">
                <p class="bulk-desc">Daftar service request yang terpilih:</p>

                <div class="bulk-table-wrapper">
                    <table class="bulk-table">
                        <thead>
                            <tr>
                                <th>Toko</th>
                                <th>Peminta</th>
                                <th>Penerima</th>
                                <th>S/H</th>
                                <th>Jenis Kendala</th>
                            </tr>
                        </thead>
                        <tbody id="bulkRequestList"></tbody>
                    </table>
                </div>

                <div id="urgencyInputContainer" class="bulk-urgency-box" style="display: none;">
                    <select id="bulkUrgencySelect">
                        <option value="Very-High">Very-High</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low" selected>Low</option>
                    </select>
                </div>
                <p id="urgencyErrorMsg">
                    * Ganti urgensi masal hanya bisa dilakukan pada jenis kendala yang sama.
                </p>
            </div>
            <div class="modal-actions">
                <button class="btn-accept" id="btnConfirmBulk">Accept</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: DETAIL REQUEST -->
<div id="requestDetailModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="requestDetailModal">&times;</span>
        <div class="modal-detail">
            <div class="modal-header">
                <h2>Detail Permintaan Layanan</h2>
            </div>
            
            <div class="modal-body">
                <!-- Grid 2 kolom untuk field -->
                <div class="field-grid">
                    <!-- Kolom 1 -->
                    <div class="field-item">
                        <div class="field-label">No. Request</div>
                        <div class="field-value" id="detail-no" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 2 -->
                    <div class="field-item">
                        <div class="field-label">Toko</div>
                        <div class="field-value" id="detail-toko" readonly>...</div>
                    </div>

                    <!-- Kolom 3 -->
                    <div class="field-item">
                        <div class="field-label">Peminta</div>
                        <div class="field-value" id="detail-penerima" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 4 -->
                    <div class="field-item">
                        <div class="field-label">Penerima</div>
                        <div class="field-value" id="detail-peminta" readonly>...</div>
                    </div>

                    <!-- Kolom -->
                    <div class="field-item">
                        <div class="field-label">Terjadi Pada Siapa Saja</div>
                        <div class="field-value" id="detail-terjadi" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 5 -->
                    <div class="field-item">
                        <div class="field-label">Handling by</div>
                        <div class="field-value" id="detail-staff" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 6 -->
                    <div class="field-item">
                        <div class="field-label">Jenis Kendala</div>
                        <div class="field-value" id="detail-jenis" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 7 -->
                    <div class="field-item">
                        <div class="field-label">Tipe Kendala (Urgensi)</div>
                        <div class="field-value" id="detail-sh" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 8 -->
                    <div class="field-item">
                        <div class="field-label">Status</div>
                        <div class="field-value" id="detail-status" readonly>...</div>
                    </div>
                    
                    <!-- Kolom   -->
                    <div class="field-item">
                        <div class="field-label">Kode Hardware</div>
                        <div class="field-value" id="detail-kode-hw" readonly>...</div>
                    </div>
                    
                    <!-- Field full width untuk deskripsi -->
                    <div class="field-item full-width-field">
                        <div class="field-label">Deskripsi Kendala</div>
                        <textarea id="detail-deskripsi" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>
                    
                    <!-- Field full width untuk foto -->
                    <div class="field-item full-width-field">
                        <div class="field-label">Lampiran</div>
                        <div class="field-value">
                            <div id="attachment-container">
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-actions" id="detailModalActions">
                <button id="btnAcceptFromModal" class="btn-lanjut">Accept</button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL 2: TRANSFER HANDLER -->
<div id="transferHandlerModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="transferHandlerModal">&times;</span>
        <div class="modal-detail">
            <div class="modal-header">
                <h2>Ganti Handler</h2>
            </div>
            
            <div class="modal-body">
                <div class="field-grid">
                    <!-- Kolom 1 -->
                    <div class="field-item">
                        <div class="field-label">No. Request</div>
                        <div class="field-value" id="transfer-no" readonly>...</div>
                    </div>

                    <!-- Kolom 2 -->
                    <div class="field-item">
                        <div class="field-label">Toko</div>
                        <div class="field-value" id="transfer-toko" readonly>...</div>
                    </div>

                    <!-- Kolom 3 -->
                    <div class="field-item">
                        <div class="field-label">Peminta</div>
                        <div class="field-value" id="transfer-user" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 4 -->
                    <div class="field-item">
                        <div class="field-label">Penerima</div>
                        <div class="field-value" id="transfer-requester" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 3 -->
                    <div class="field-item">
                        <div class="field-label">Terjadi Pada Siapa Saja</div>
                        <div class="field-value" id="transfer-terjadi" readonly>...</div>
                    </div>
     
                    <!-- Kolom 6 -->
                    <div class="field-item">
                        <div class="field-label">Tipe Kendala (Urgensi)</div>
                        <div class="field-value" id="transfer-tipe" readonly>...</div>
                    </div>
                    
                    <!-- Kolom 5 -->
                    <div class="field-item">
                        <div class="field-label">Jenis Kendala</div>
                        <div class="field-value" id="transfer-jenis" readonly>...</div>
                    </div>

                    <!-- Kolom 5 -->
                    <div class="field-item">
                        <div class="field-label">Kode Hardware</div>
                        <div class="field-value" id="transfer-kode-hw" readonly>...</div>
                    </div>
                    
                    <!-- Field full width untuk deskripsi -->
                    <div class="field-item full-width-field">
                        <div class="field-label">Deskripsi Kendala</div>
                        <textarea id="transfer-deskripsi" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>
                </div>

                <!-- Bagian bawah: Form ganti handler -->
                <div class="form-group handler">
                    <label for="transfer-handler-select">Pilih Handler Baru</label>
                    <select class="form-select" id="transfer-staff-id">
                        <option value="">-- Pilih Staff IT --</option>
                        <?php 
                        $currentStaffId = $_SESSION['user_id']; 
                        $resStaff = $conn->query("SELECT id, username FROM users WHERE role IN (1, 10) AND id != $currentStaffId");
                        
                        while($s = $resStaff->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['username']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="select-hint" style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                        * Pilih staff IT yang akan menangani request ini
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-lanjut" id="btn-submit-transfer">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: UBAH STATUS -->
<div id="ubahStatusModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="ubahStatusModal">&times;</span>
        <div class="modal-detail">
            <div class="modal-header">
                <h2>Update Status Permintaan Layanan</h2>
            </div>
            
            <div class="modal-body">
                <!-- input sebelumnya -->
                <div class="section-divider">Informasi Sebelumnya <span style="color: #eb2700ff; font-weight:600">(Read Only)</span></div>

                <div class="field-grid">

                    <div class="field-item">
                        <div class="field-label">No. Request</div>
                        <div class="field-value" id="ubah-status-req-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Toko</div>
                        <div class="field-value" id="ubah-status-toko-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Peminta</div>
                        <div class="field-value" id="ubah-status-peminta-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Terjadi Pada Siapa Saja</div>
                        <div class="field-value" id="ubah-status-terjadi-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Penerima</div>
                        <div class="field-value" id="ubah-status-penerima-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Jenis Kendala</div>
                        <div class="field-value" id="ubah-status-jenis-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Tipe Kendala (Urgensi)</div>
                        <div class="field-value" id="ubah-status-tipe-val" readonly>...</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Kode Hardware</div>
                        <div class="field-value" id="ubah-status-kode-val" readonly>...</div>
                    </div>
                    <div class="field-item full-width-field">
                        <div class="field-label">Deskripsi Kendala</div>
                        <textarea id="ubah-status-deskripsi-val" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>
                </div>
                
                <!-- input sebelumnya -->
                <div class="section-divide">Form Input Ubah Status <span style="color: #eb2700ff;">(Dapat Diisi)</span></div>

                <!-- Grid 2 kolom -->
                <div class="status-form-grid">
                    <!-- Kolom Kiri -->
                    <div class="form-column">
                        <!-- Tindakan yang dilakukan -->
                        <div class="form-group">
                            <label class="form-label">Tindakan yang dilakukan: (Action)</label>
                            <input type="text" class="form-input" id="ubah-status-tindakan" 
                                placeholder="Masukkan tindakan yang dilakukan...">
                        </div>
                        
                        <!-- Hasil akhirnya -->
                        <div class="form-group">
                            <label class="form-label">Hasil akhirnya: (Result)</label>
                            <textarea class="form-textarea" id="ubah-status-hasil" 
                                    placeholder="Deskripsikan hasil akhir tindakan..."></textarea>
                        </div>

                        <!-- Edit Tipe Kendala -->
                        <div class="form-group">
                            <label class="form-label">Edit Tipe Kendala:</label>
                            <select class="form-select" id="ubah-status-tipe" onchange="updateJenisDropdown(this.value)">
                                <option value="software">Software</option> 
                                <option value="hardware">Hardware</option>
                            </select>
                        </div>
                        
                        <!-- Edit Jenis Kendala -->
                        <div class="form-group">
                            <label class="form-label">Edit Jenis Kendala:</label>
                            <select class="form-select" id="ubah-status-jenis"></select>
                        </div>
                        <!-- kode hardware -->
                        <div class="form-group" id="group-kode-hardware" style="display: none;">
                            <label class="form-label">Kode Hardware:</label>
                            <input type="text" class="form-input" id="ubah-status-kode-hw" placeholder="Masukkan Kode (Contoh: PRN-01)">
                        </div>
                    </div>
                    
                    <!-- Kolom Kanan -->
                    <div class="form-column">              
                        <div class="form-group">
                            <label class="form-label">Edit Penerima Service:</label>
                            <select class="form-select" id="ubah-status-terjadi">
                                <option value="diri_sendiri">Diri Sendiri</option>
                                <option value="orang_lain">Orang Lain</option>
                            </select>
                        </div>

                        <!-- ubah penerima -->
                        <div class="form-group" id="group-penerima-baru" style="display: none;">
                            <label class="form-label">Nama Penerima Service:</label>
                            <select class="form-select" id="ubah-status-penerima">
                                <?php foreach ($allUsers as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- edit terjadi pada siapa saja -->
                        <div class="form-group">
                            <label class="form-label">Dampak Kendala (Terjadi Pada):</label>
                            <select class="form-select" id="ubah-terjadi-siapa" onchange="updateUrgencyAutomaticly()">
                                <option value="">-- Pilih Dampak --</option> 
                                <option value="semua">Semua Orang/Toko</option> 
                                <option value="beberapa">Beberapa Orang</option> 
                                <option value="satu">Hanya Diri Sendiri</option>
                                <option value="opsi_lain">Masih dapat bekerja dengan cara lain</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Level Urgensi (Otomatis):</label>
                            <input type="text" id="tampilan-urgensi" class="form-control" readonly 
                                style="background-color: #e9ecef; font-weight: bold;" placeholder="Pilih dampak dahulu...">
                            <input type="hidden" id="ubah-urgensi" name="level_urgensi">
                        </div>

                        <!-- Ubah Status -->
                        <div class="form-group">
                            <label class="form-label">Ubah Status:</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="status" value="done"> 
                                    <span class="radio-custom"></span>
                                    Done
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="status" value="on-repair">
                                    <span class="radio-custom"></span>
                                    On Repair
                                </label>
                            </div>
                        </div>
                        
                        <!-- Ketidaksesuaian laporan -->
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="ubah-status-ketidaksesuaian">
                                <span class="checkbox-custom"></span>
                                Ketidaksesuaian laporan (opsional)
                            </label>
                            <textarea class="form-textarea" id="ubah-status-ketidaksesuaian-detail" 
                                    placeholder="Deskripsikan ketidaksesuaian..." 
                                    style="margin-top: 8px; display: none;"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-lanjut" id="btn-submit-status">Lanjut</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Permintaan Password -->
<div id="passwordModal" class="modal">
    <div class="modal-content password-modal">
        <span class="close-btn" data-modal="passwordModal">&times;</span>
        <div class="modal-detail">
            <div class="modal-header">
                <h2>User sudah menerima informasi diagnosa & estimasi waktu pelayanan</h2>
            </div>
            
            <div class="modal-body password-modal-body">
                <div class="section-divider">Informasi <span style="color: #eb2700ff; font-weight:600">(Read Only)</span></div>
    
                <div class="field-grid">
                    <div class="field-item">
                        <div class="field-label">Peminta</div>
                        <div class="field-value" id="password-peminta-val">-</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Penerima</div>
                        <div class="field-value" id="password-user-val">-</div>
                    </div>
                    <div class="field-item full-width-field">
                        <div class="field-label">Action (Tindakan)</div>
                        <div class="field-value" id="password-action-val">-</div>
                    </div>
                    <div class="field-item full-width-field">
                        <div class="field-label">Result (Hasil)</div>
                        <div class="field-value" id="password-result-val">-</div>
                    </div>
                    <div class="field-item">
                        <div class="field-label">Status Baru</div>
                        <div class="field-value">
                            <span id="password-status-val" style="text-transform: uppercase; font-weight: bold;">-</span>
                        </div>
                    </div>
                </div>
                
                <!-- konfirmasi password -->
                <div class="section-divide">Form Konfirmasi Password User <span style="color: #eb2700ff;">(Dapat Diisi)</span></div>

                <div class="password-section">
                    <label class="form-label">Masukkan Password:</label>
                    <div class="password-input-group">
                        <input type="password" class="form-input password-input" 
                            id="input-password" placeholder="Ketik password di sini...">
                        <button class="btn-show-password" type="button" id="toggle-password">
                            <i class="eye-icon">👁️</i>
                        </button>
                    </div>
                </div>
                
                <div class="confirmation-checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirm-checkbox">
                        <span class="checkbox-custom"></span>
                        Saya telah mengonfirmasi bahwa user telah menerima informasi
                    </label>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-submit" id="btn-final-submit" disabled>Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Memindahkan data dari PHP ke variabel Global JS
    const dataKendala = <?php echo json_encode($data_kendala_db); ?>;
    const allUsersData = <?php echo json_encode($usersForSearch); ?>;
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const unratedRequestsData = <?= json_encode($unratedRequests); ?>;
</script>
<script src="dist/js/home.js"></script>

<?php
$content = ob_get_clean();
require_once 'modules/layout/template.php';
?>