<?php
require_once "direct/config.php";

$pageTitle = "Done & Canceled"; 

$sql_base = "
SELECT 
    r.id,
    COALESCE(t.nama_toko, '-') AS nama_toko,
    IFNULL(u_req.username, '-') AS peminta,
    IFNULL(u_pen.username, '-') AS penerima,
    IFNULL(r.tipe_kendala, '-') AS sh_code,
    IFNULL(r.level_urgensi, '-') AS level_urgensi,
    IFNULL(r.jenis_kendala, '-') AS jenis_kendala,
    IFNULL(r.ketidaksesuaian_detail, '-') AS ketidaksesuaian_detail,
    IFNULL(r.description, '-') AS description,
    IFNULL(s.status, '-') AS status_nama,
    IFNULL(stf.username, '-') AS staff_name,
    IFNULL(ts_last.change_datetime, '-') AS change_datetime
FROM transaksi_request r
LEFT JOIN master_toko t ON r.user_toko = t.id
LEFT JOIN users u_req ON r.user_request = u_req.id
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
 
$sql_done = $sql_base . "
WHERE s.id = 4
AND (
    r.user_request  = $currentUserId
    OR r.user_penerima = $currentUserId
)
ORDER BY ts_last.change_datetime DESC
";

$res_done = mysqli_query($conn, $sql_done);
$doneData = $res_done ? mysqli_fetch_all($res_done, MYSQLI_ASSOC) : [];

$sql_canceled = $sql_base . "
WHERE s.id = 5
AND (
    r.user_request  = $currentUserId
    OR r.user_penerima = $currentUserId
)
ORDER BY ts_last.change_datetime DESC
";

$res_canceled = mysqli_query($conn, $sql_canceled);
$canceledData = $res_canceled ? mysqli_fetch_all($res_canceled, MYSQLI_ASSOC) : [];

$list_handler = mysqli_query($conn, "
    SELECT DISTINCT u.username 
    FROM transaksi_request r 
    JOIN users u ON r.handling_by = u.id 
    ORDER BY u.username ASC
");
$list_penerima = mysqli_query($conn, "SELECT DISTINCT u.username FROM transaksi_request r JOIN users u ON r.user_penerima = u.id");
// Ambil daftar toko
$list_toko = mysqli_query($conn, "SELECT id, nama_toko FROM master_toko ORDER BY nama_toko ASC");
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
                <select id="filter-toko" class="select2-multi" multiple="multiple" style="width: 100%">
                    <?php 
                    $list_toko_query = mysqli_query($conn, "SELECT * FROM master_toko ORDER BY nama_toko ASC");
                    while($t = mysqli_fetch_assoc($list_toko_query)): ?>
                        <option value="<?= htmlspecialchars($t['nama_toko']) ?>"><?= htmlspecialchars($t['nama_toko']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Peminta</label>
                <select id="filter-peminta" class="live-filter">
                    <option value="">Semua Peminta</option>
                    <?php 
                    $list_user_query = mysqli_query($conn, "SELECT username FROM users ORDER BY username ASC");
                    while($u = mysqli_fetch_assoc($list_user_query)): ?>
                        <option value="<?= htmlspecialchars($u['username']) ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Handling By</label>
                <select id="filter-handler" class="live-filter">
                    <option value="">Semua IT</option>
                    <?php while($h = mysqli_fetch_assoc($list_handler)): ?>
                        <option value="<?= htmlspecialchars($h['username']) ?>"><?= htmlspecialchars($h['username']) ?></option>
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
                <table id="doneTable" class="display responsive nowrap reportingTable" style="width:100%">
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
                    <?php $no=1; foreach ($doneData as $row): ?>
                        <tr data-request-id="<?= $row['id'] ?>" 
                            data-toko="<?= htmlspecialchars($row['nama_toko']) ?>"
                            data-peminta="<?= htmlspecialchars($row['peminta']) ?>"
                            data-penerima="<?= htmlspecialchars($row['penerima']) ?>"
                            data-sh="<?= htmlspecialchars($row['sh_code']) ?>"
                            data-urgensi="<?= htmlspecialchars($row['level_urgensi']) ?>"
                            data-jenis="<?= htmlspecialchars($row['jenis_kendala']) ?>"
                            data-status="<?= htmlspecialchars($row['status_nama']) ?>"
                            data-handler="<?= htmlspecialchars($row['staff_name']) ?>"
                            data-wrong="<?= htmlspecialchars($row['ketidaksesuaian_detail']) ?>" 
                            data-desc="<?= htmlspecialchars($row['description']) ?>"
                            data-tanggal="<?= date('Y-m-d', strtotime($row['change_datetime'])) ?>"
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
                                <button class="btn-detail">Open Details</button>
                                <?php if (can('input_request')): ?>
                                    <button class="btn-report">Report</button>
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
                <table id="canceledTable" class="display responsive nowrap reportingTable" style="width:100%">
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
                    <?php $no=1; foreach ($canceledData as $row): ?>
                        <tr data-request-id="<?= $row['id'] ?>" 
                            data-toko="<?= htmlspecialchars($row['nama_toko']) ?>"
                            data-peminta="<?= htmlspecialchars($row['peminta']) ?>"
                            data-penerima="<?= htmlspecialchars($row['penerima']) ?>"
                            data-sh="<?= htmlspecialchars($row['sh_code']) ?>"
                            data-urgensi="<?= htmlspecialchars($row['level_urgensi']) ?>"
                            data-jenis="<?= htmlspecialchars($row['jenis_kendala']) ?>"
                            data-status="<?= htmlspecialchars($row['status_nama']) ?>"
                            data-handler="<?= htmlspecialchars($row['staff_name']) ?>"
                            data-wrong="<?= htmlspecialchars($row['ketidaksesuaian_detail']) ?>" 
                            data-desc="<?= htmlspecialchars($row['description']) ?>"
                            data-tanggal="<?= date('Y-m-d', strtotime($row['change_datetime'])) ?>"
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
                            <td><button class="btn-detail">Open Details</button></td>
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
        <span class="close-btn">&times;</span>

        <div class="modal-detail">
            <div class="modal-header">
                <h2>Detail Request</h2>
            </div>

            <div class="modal-body">
                <div class="field-grid">

                    <div class="field-item">
                        <div class="field-label">No. Request</div>
                        <div class="field-value" id="d-no" class="field-value" readonly></div>
                    </div>
                    
                    <div class="field-item">
                        <label class="field-label">Toko</label>
                        <input type="text" id="d-toko" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Peminta</label>
                        <input type="text" id="d-user" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Penerima</label>
                        <input type="text" id="d-peminta" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">S/H</label>
                        <input type="text" id="d-urgensi" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Jenis Kendala</label>
                        <input type="text" id="d-kendala" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Status</label>
                        <input type="text" id="d-status" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Handling By</label>
                        <input type="text" id="d-handler" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Tanggal</label>
                        <input type="text" id="d-tanggal" class="field-value" readonly>
                    </div>

                    <!-- Field full width untuk deskripsi -->
                    <div class="field-item full-width-field">
                        <div class="field-label">Deskripsi Kendala</div>
                        <textarea id="detail-deskripsi" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>
                    
                    <!-- Field full width untuk foto -->
                    <div class="field-item full-width-field">
                        <div class="field-label">Lampiran Foto</div>
                        <div class="field-value">
                            <div class="foto-preview">
                                <div class="foto-placeholder">
                                    <i>📷</i>
                                    <span>Tidak ada lampiran foto</span>
                                </div>
                            </div>
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
                <div class="field-grid">

                    <!-- input sebelumnya -->
                    <div class="section-divider">Informasi Sebelumnya <span style="color: #eb2700ff; font-weight:600">(Read Only)</span></div>

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
                        <label class="field-label">S/H</label>
                        <input type="text" id="r-sh-old" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Jenis Kendala</label>
                        <input type="text" id="r-kendala-old" class="field-value" readonly>
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