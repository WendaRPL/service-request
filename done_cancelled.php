<?php
require_once "direct/config.php";

$pageTitle = "Done & Canceled"; 

$sql_base = "
SELECT 
    r.id,
    r.handling_by,
    IFNULL(r.tindakan_it, '') AS tindakan_it,        
    IFNULL(r.hasil_it, '') AS hasil_it,
    IFNULL(r.ketidaksesuaian_detail, '') AS ketidaksesuaian_detail, 
    COALESCE(t.nama_toko, t_alt.nama_toko, '-') AS nama_toko,
    COALESCE(t.kode_toko, t_alt.kode_toko, '-') AS kode_toko,
    IFNULL(u_req.username, 'No Peminta') AS peminta,
    u_req.id AS id_peminta,
    IFNULL(u_pen.username, '') AS penerima, 
    u_pen.id AS id_penerima_user,
    IFNULL(r.terjadi_pada, '') AS terjadi_pada,
    IFNULL(r.tipe_kendala, '') AS sh_code, 
    IFNULL(r.level_urgensi, '') AS level_urgensi,
    IFNULL(r.jenis_kendala, '') AS jenis_kendala,   
    IFNULL(r.kode_hardware, '') AS kode_hardware,
    r.status AS id_status,
    TRIM(IFNULL(s.status, '')) AS status_nama,
    IFNULL(stf.username, '-') AS staff_name, 
    IFNULL(r.description, '') AS description,
    IFNULL(r.upload, '') AS upload,  
    r.finished_at AS change_datetime
FROM transaksi_request r 
LEFT JOIN master_toko t ON r.user_toko = t.id 
LEFT JOIN users u_req ON r.user_request = u_req.id 
LEFT JOIN master_toko t_alt ON u_req.toko_id = t_alt.id 
LEFT JOIN users u_pen ON r.user_penerima = u_pen.id 
LEFT JOIN users stf ON r.handling_by = stf.id
LEFT JOIN status s ON r.status = s.id 
LEFT JOIN (
    SELECT request_id, MAX(change_datetime) as change_datetime 
    FROM transaksi_status 
    GROUP BY request_id
) ts_last ON r.id = ts_last.request_id
";

$currentUserId = (int) $_SESSION['user_id'];
$userRole = (int) ($_SESSION['role'] ?? 0);

$sql_done = $sql_base . " WHERE s.id = 4 ";
if ($userRole !== 1 && $currentUserId !== 1) {
    $sql_done .= " AND (r.user_request = $currentUserId OR r.user_penerima = $currentUserId) ";
}

$sql_done .= " ORDER BY ts_last.change_datetime DESC";
$res_done = mysqli_query($conn, $sql_done);
$doneData = $res_done ? mysqli_fetch_all($res_done, MYSQLI_ASSOC) : [];

$sql_canceled = $sql_base . " WHERE s.id = 5 ";
if ($userRole !== 1 && $currentUserId !== 1) {
    $sql_canceled .= " AND (r.user_request = $currentUserId OR r.user_penerima = $currentUserId) ";
}
$sql_canceled .= " ORDER BY ts_last.change_datetime DESC";
$res_canceled = mysqli_query($conn, $sql_canceled);
$canceledData = $res_canceled ? mysqli_fetch_all($res_canceled, MYSQLI_ASSOC) : [];

$list_handler = mysqli_query($conn, "
    SELECT DISTINCT u.username 
    FROM transaksi_request r 
    JOIN users u ON r.handling_by = u.id 
    ORDER BY u.username ASC
");
$list_toko = mysqli_query($conn, "SELECT nama_toko FROM master_toko ORDER BY nama_toko ASC");
$list_staff = mysqli_query($conn, "SELECT username FROM users WHERE role = 1 ORDER BY username ASC");
$list_penerima = mysqli_query($conn, "SELECT DISTINCT u.username FROM transaksi_request r JOIN users u ON r.user_penerima = u.id ORDER BY u.username ASC");
// Ambil daftar user
$list_user = mysqli_query($conn, "SELECT DISTINCT u.username 
    FROM transaksi_request r 
    JOIN users u ON r.user_request = u.id 
    ORDER BY u.username ASC");

ob_start();
?>

<link rel="stylesheet" href="dist/css/done_cancelled.css">

<div class="dashboard-content">
    <?php if (can('input_request')): ?>
        <div class="nav-home" onclick="goToDashboard()">
            <i class="fas fa-home"></i> Home
        </div>
    <?php endif; ?>

    <div class="border-queue done-header">
        <button class="btn-filter-toggle" id="btnToggleFilter">
            Filter ▼
        </button>
    </div>

    <div class="filter-panel" id="filterPanel">
        <div class="filter-grid">
            <div class="filter-group">
                <label>Toko</label>
                <select id="filter-toko" class="live-filter">
                    <option value="">Semua Toko</option>
                    <?php  
                    mysqli_data_seek($list_toko, 0);  
                    while($t = mysqli_fetch_assoc($list_toko)): ?>
                        <option value="<?= htmlspecialchars($t['nama_toko']) ?>"><?= htmlspecialchars($t['nama_toko']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Penerima</label>
                <select id="filter-penerima" class="live-filter">
                    <option value="">Semua Penerima</option>
                    <?php 
                    mysqli_data_seek($list_penerima, 0);
                    while($p = mysqli_fetch_assoc($list_penerima)): ?>
                        <option value="<?= htmlspecialchars($p['username']) ?>"><?= htmlspecialchars($p['username']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Handling By</label>
                <select id="filter-handler" class="live-filter">
                    <option value="">Semua IT</option>
                    <?php while($s = mysqli_fetch_assoc($list_staff)): ?>
                        <option value="<?= htmlspecialchars($s['username']) ?>"><?= htmlspecialchars($s['username']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Dari Tanggal</label>
                <input type="date" id="filter-start-date" class="live-filter">
            </div>

            <div class="filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" id="filter-end-date" class="live-filter">
            </div>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-button active" onclick="switchTab('done')" id="tab-done">
            <i class="fas fa-check-circle"></i> Done (<?= count($doneData) ?>)
        </button>
        <button class="tab-button" onclick="switchTab('canceled')" id="tab-canceled">
            <i class="fas fa-times-circle"></i> Canceled (<?= count($canceledData) ?>)
        </button>
    </div>

    <div class="all-request-table">
        <div id="done-table" class="table-container">
            <?php if (empty($doneData)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle fa-3x" style="color:#ccc"></i>
                    <h3>Tidak Ada Data Done</h3>
                </div>
            <?php else: ?>
                <table id="doneTable" class="display reportingTable" style="width:100%">
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
                            <th>Tanggal Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        $no=1; foreach ($doneData as $row): 
                        $shCode = ucfirst($row['sh_code']);
                        $urgensi = $row['level_urgensi'];
                        $classUrgensi = strtolower(str_replace(' ', '-', $urgensi));

                        $badgeHTML = $shCode . ' <span class="urgency-badge ' . $classUrgensi . '">' . $urgensi . '</span>';
                        ?>
                        <tr data-request-id="<?= $row['id'] ?>" 
                            data-toko="<?= htmlspecialchars($row['nama_toko'] ?? '-') ?>"
                            data-peminta="<?= htmlspecialchars($row['peminta'] ?? '-') ?>"
                            data-penerima="<?= htmlspecialchars($row['penerima'] ?? '-') ?>"
                            data-sh="<?= htmlspecialchars($row['sh_code'] ?? '-') ?>"
                            data-urgensi="<?= htmlspecialchars($row['level_urgensi'] ?? '-') ?>"
                            data-jenis="<?= htmlspecialchars($row['jenis_kendala'] ?? '-') ?>"
                            data-status="<?= htmlspecialchars($row['status_nama'] ?? '-') ?>"
                            data-handler="<?= htmlspecialchars($row['staff_name'] ?? '-') ?>"
                            data-wrong="<?= htmlspecialchars($row['ketidaksesuaian_detail'] ?? '-') ?>" 
                            data-desc="<?= htmlspecialchars($row['description'] ?? '-') ?>"
                            data-tindakan-it="<?= htmlspecialchars($row['tindakan_it'] ?? '-') ?>"
                            data-hasil-it="<?= htmlspecialchars($row['hasil_it'] ?? '-') ?>"
                            data-kode-hw="<?= htmlspecialchars($row['kode_hardware'] ?? '-') ?>"
                            data-terjadi-pada="<?= htmlspecialchars($row['terjadi_pada'] ?? '-') ?>"
                            data-tanggal="<?= date('Y-m-d H:i', strtotime($row['change_datetime'])) ?>"
                            data-img="<?= htmlspecialchars($row['upload'] ?? '') ?>">
                            
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                            <td><?= htmlspecialchars($row['peminta']) ?></td>
                            <td><?= htmlspecialchars($row['penerima']) ?></td>
                            <td class="urgency-cell">
                                <span class="urgency-code"><?= $row['sh_code'] ?></span>
                                <span class="urgency-badge <?= strtolower(str_replace(' ', '-', $row['level_urgensi'])) ?>">
                                    <?= $row['level_urgensi'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['jenis_kendala']) ?></td>
                            <td><div class="status-card status-done">Done</div></td>
                            <td><?= htmlspecialchars($row['staff_name'] ?? '-') ?></td>
                            <td><?= date('d M Y H:i', strtotime($row['change_datetime'])) ?></td>
                            <td>
                                <button class="btn-detail" 
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
                                    data-tanggal="<?= htmlspecialchars($row['change_datetime']) ?>" 
                                    data-deskripsi="<?= htmlspecialchars($row['description']); ?>">
                                    Open Detail
                                </button>
                                <?php if (can('update_request')): ?>
                                    <button class="btn-report" 
                                        data-id="<?= $row['id']; ?>"
                                        data-toko="<?= htmlspecialchars($row['nama_toko']); ?>"
                                        data-user="<?= htmlspecialchars($row['peminta']); ?>"
                                        data-peminta="<?= htmlspecialchars($row['penerima']); ?>"
                                        data-sh='<?= $badgeHTML ?>'  
                                        data-kendala="<?= htmlspecialchars($row['jenis_kendala']); ?>"
                                        data-kode="<?= htmlspecialchars($row['kode_hardware']); ?>"
                                        data-wrong="<?= htmlspecialchars($row['ketidaksesuaian_detail']); ?>">
                                        Report
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="canceled-table" class="table-container" style="display:none;">
            <?php if (empty($canceledData)): ?>
                <div class="empty-state">
                    <i class="fas fa-times-circle fa-3x" style="color:#ccc"></i>
                    <h3>Tidak Ada Data Canceled</h3>
                </div>
            <?php else: ?>
                <table id="canceledTable" class="display reportingTable" style="width:100%">
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
                            <th>Tanggal Batal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        $no=1; foreach ($canceledData as $row): 
                        $shCode = ucfirst($row['sh_code']);
                        $urgensi = $row['level_urgensi'];
                        $classUrgensi = strtolower(str_replace(' ', '-', $urgensi));

                        $badgeHTML = $shCode . ' <span class="urgency-badge ' . $classUrgensi . '">' . $urgensi . '</span>';
                        ?>
                        <tr data-request-id="<?= $row['id'] ?>" 
                            data-toko="<?= htmlspecialchars($row['nama_toko'] ?? '-') ?>"
                            data-peminta="<?= htmlspecialchars($row['peminta'] ?? '-') ?>"
                            data-penerima="<?= htmlspecialchars($row['penerima'] ?? '-') ?>"
                            data-sh="<?= htmlspecialchars($row['sh_code'] ?? '-') ?>"
                            data-urgensi="<?= htmlspecialchars($row['level_urgensi'] ?? '-') ?>"
                            data-jenis="<?= htmlspecialchars($row['jenis_kendala'] ?? '-') ?>"
                            data-status="<?= htmlspecialchars($row['status_nama'] ?? '-') ?>"
                            data-handler="<?= htmlspecialchars($row['staff_name'] ?? '-') ?>"
                            data-wrong="<?= htmlspecialchars($row['ketidaksesuaian_detail'] ?? '-') ?>" 
                            data-desc="<?= htmlspecialchars($row['description'] ?? '-') ?>"
                            data-tindakan-it="<?= htmlspecialchars($row['tindakan_it'] ?? '-') ?>"
                            data-hasil-it="<?= htmlspecialchars($row['hasil_it'] ?? '-') ?>"
                            data-kode-hw="<?= htmlspecialchars($row['kode_hardware'] ?? '-') ?>"
                            data-terjadi-pada="<?= htmlspecialchars($row['terjadi_pada'] ?? '-') ?>"
                            data-tanggal="<?= date('Y-m-d H:i', strtotime($row['change_datetime'])) ?>"
                            data-img="<?= htmlspecialchars($row['upload'] ?? '') ?>">
                            
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                            <td><?= htmlspecialchars($row['peminta']) ?></td>
                            <td><?= htmlspecialchars($row['penerima']) ?></td>
                            <td class="urgency-cell">
                                <span class="urgency-code"><?= $row['sh_code'] ?></span>
                                <span class="urgency-badge <?= strtolower(str_replace(' ', '-', $row['level_urgensi'])) ?>">
                                    <?= $row['level_urgensi'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['jenis_kendala']) ?></td>
                            <td><div class="status-card status-cancelled">Canceled</div></td>
                            <td><?= htmlspecialchars($row['staff_name'] ?? '-') ?></td>
                            <td><?= date('d M Y H:i', strtotime($row['change_datetime'])) ?></td>
                            <td>
                                <button class="btn-detail" 
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
                                    data-tanggal="<?= htmlspecialchars($row['change_datetime']) ?>" 
                                    data-deskripsi="<?= htmlspecialchars($row['description']); ?>">
                                    Open Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- Modal Detail  -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" data-modal="detailModal">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2>Detail Request</h2>
            </div>

            <div class="modal-body">
                <div class="field-grid"> 
                    <div class="field-item">
                        <div class="field-label">No. Request</div>
                        <div class="field-value" id="detail-no" readonly>...</div>
                    </div>
                     
                    <div class="field-item">
                        <div class="field-label">Toko</div>
                        <div class="field-value" id="detail-toko" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Peminta</div>
                        <div class="field-value" id="detail-penerima" readonly>...</div>
                    </div>
                    
                    <div class="field-item">
                        <div class="field-label">Penerima</div>
                        <div class="field-value" id="detail-peminta" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Terjadi Pada Siapa Saja</div>
                        <div class="field-value" id="detail-terjadi" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Tipe Kendala (Urgensi)</div>
                        <div class="field-value" id="detail-sh" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Jenis Kendala</div>
                        <div class="field-value" id="detail-jenis" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Kode Hardware</div>
                        <div class="field-value" id="detail-kode-hw" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Status</div>
                        <div class="field-value" id="detail-status" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Handling by</div>
                        <div class="field-value" id="detail-staff" readonly>...</div>
                    </div>

                    <div class="field-item">
                        <div class="field-label">Tanggal</div>
                        <div class="field-value" id="detail-tanggal" readonly>...</div>
                    </div>
                     
                    <div class="field-item full-width-field">
                        <div class="field-label">Deskripsi Kendala</div>
                        <textarea id="detail-deskripsi" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>
                     
                    <div class="field-item full-width-field">
                        <div class="field-label">Lampiran</div>
                        <div class="field-value">
                            <div id="attachment-container"></div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>

<!-- Modal Report -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2>Laporan Penyelesaian</h2>
            </div>

            <div class="modal-body">
                <!-- input sebelumnya -->
                    <div class="section-divider">Informasi Sebelumnya <span style="color: #eb2700ff; font-weight:600">(Read Only)</span></div>

                <div class="field-grid">
                    <div class="field-item">
                        <label class="field-label">No. Request</label>
                        <input type="text" id="r-no" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Toko</label>
                        <input type="text" id="r-toko-old" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Peminta</label>
                        <input type="text" id="r-user-old" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Penerima</label>
                        <input type="text" id="r-peminta-old" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Tipe Kendala (Urgensi)</label>
                        <input type="text" id="r-sh-old" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Jenis Kendala</label>
                        <input type="text" id="r-kendala-old" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Kode Hardware</label>
                        <input type="text" id="r-kode-old" class="field-value" readonly>
                    </div>

                    <!-- input user yang salah input -->
                    <div class="field-item full-width-field">
                        <label class="field-label input">Pengisian Untuk User yang Salah Input <span style="color: #eb2700ff;">(Dapat diisi)</span></label>
                        <textarea id="r-user-wrong-input" class="field-value textarea-value" style="resize: vertical;"></textarea>
                    </div>

                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-submit" id="btn-submit-report">
                    Simpan Laporan
                </button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="dist/js/done_cancelled.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>