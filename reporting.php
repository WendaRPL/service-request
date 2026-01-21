<?php
require_once 'direct/config.php';

$pageTitle = "Reporting";

$sql = "
SELECT 
    r.id,
    COALESCE(t.nama_toko, '-') AS nama_toko,
    IFNULL(u_req.username, '-') AS peminta,
    IFNULL(u_pen.username, '-') AS penerima,
    IFNULL(CONCAT(UPPER(LEFT(r.tipe_kendala, 1)), LOWER(SUBSTRING(r.tipe_kendala, 2))), '-') AS sh_code, 
    IFNULL(r.level_urgensi, '-') AS level_urgensi,
    IFNULL(r.jenis_kendala, '-') AS jenis_kendala,
    IFNULL(s.status, '-') AS status_nama,
    r.status AS id_status,
    IFNULL(stf.username, '-') AS staff_name,
    IFNULL(r.description, '-') AS description,
    IFNULL(r.ketidaksesuaian_detail, '-') AS ketidaksesuaian_detail, 
    IFNULL(r.input_datetime, '-') AS input_datetime,
    IFNULL(r.upload, '-') AS upload,
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
WHERE r.status IN (2, 3, 4, 5) 
ORDER BY ts_last.change_datetime DESC";

$result = mysqli_query($conn, $sql);
$requests = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
}

// Ambil data dinamis untuk Filter
$list_toko = mysqli_query($conn, "SELECT nama_toko FROM master_toko ORDER BY nama_toko ASC");
$list_staff = mysqli_query($conn, "SELECT username FROM users WHERE role = 1 ORDER BY username ASC");
$list_penerima = mysqli_query($conn, "SELECT DISTINCT u.username FROM transaksi_request r JOIN users u ON r.user_penerima = u.id ORDER BY u.username ASC");

ob_start();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link rel="stylesheet" href="dist/css/reporting.css">

<!-- main content isi disini -->
<main class="dashboard-content">

    <div class="border-queue report-header">
        <button class="btn-filter-toggle" id="btnToggleReportFilter">
            Filter ▼
        </button>
    </div>

    <!-- FILTER PANEL -->
    <div class="filter-panel" id="reportFilterPanel">
        <div class="filter-grid">

            <div class="filter-group">
                <label>Toko</label>
                <select id="report-filter-toko" class="live-report-filter">
                    <option value="">Semua Toko</option>
                    <?php 
                    // Menggunakan variabel $list_toko yang sudah Anda buat di atas
                    mysqli_data_seek($list_toko, 0); // Reset pointer query
                    while($t = mysqli_fetch_assoc($list_toko)): ?>
                        <option value="<?= htmlspecialchars($t['nama_toko']) ?>"><?= htmlspecialchars($t['nama_toko']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Penerima</label>
                <select id="report-filter-penerima" class="live-report-filter">
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
                <select id="report-filter-handler" class="live-report-filter">
                    <option value="">Semua IT</option>
                    <?php while($s = mysqli_fetch_assoc($list_staff)): ?>
                        <option value="<?= htmlspecialchars($s['username']) ?>"><?= htmlspecialchars($s['username']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Status</label>
                <select id="report-filter-status" class="live-report-filter">
                    <option value="">Semua Status</option>
                    <option value="On Process">On Process</option>
                    <option value="On Repair">On Repair</option>
                    <option value="Done">Done</option>
                    <option value="Canceled">Canceled</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Tipe Input User</label>
                <select id="report-filter-wrong-input" class="live-report-filter">
                    <option value="">Semua Data</option>
                    <option value="salah-input">User Yang Salah Input</option>
                    <option value="normal">Input Normal (Kosong)</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Dari Tanggal</label>
                <input type="date" id="report-filter-start-date" class="live-report-filter">
            </div>

            <div class="filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" id="report-filter-end-date" class="live-report-filter">
            </div>
        </div>
    </div>

    <div class="all-request-table">

        <div class="export-buttons">
            <button class="export-btn" onclick="copyTable()">Copy</button>
            <button class="export-btn" onclick="exportTable('excel')">Export Excel</button>
            <button class="export-btn" onclick="exportTable('csv')">Export CSV</button>
            <button class="export-btn" onclick="exportTable('pdf')">Export PDF</button>
            <button class="export-btn" onclick="printSemua()">Print Semua</button>
        </div>

        <div class="table-container">
            <table id="reportingTable" class="display responsive nowrap">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>No.</th>
                        <th>Toko</th>
                        <th>Peminta</th>
                        <th>Penerima</th>
                        <th>Tipe Kendala (Urgensi)</th>
                        <th>Jenis Kendala</th>
                        <th>Status</th>
                        <th>Handling By</th>
                        <th>User Salah Input</th>
                        <th>Tanggal Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php $no = 1; foreach ($requests as $row): ?>
                            <tr data-request-id="<?= $row['id'] ?>" 
                                data-toko="<?= htmlspecialchars($row['nama_toko'] ?? '-') ?>"
                                data-peminta="<?= htmlspecialchars($row['peminta'] ?? '-') ?>"
                                data-penerima="<?= htmlspecialchars($row['penerima'] ?? '-') ?>"
                                data-sh="<?= htmlspecialchars($row['sh_code'] ?? '-') ?>"
                                data-urgensi="<?= htmlspecialchars($row['level_urgensi'] ?? '-') ?>"
                                data-jenis="<?= htmlspecialchars($row['jenis_kendala'] ?? '-') ?>"
                                data-status="<?= htmlspecialchars($row['id_status'] ?? '-') ?>"
                                data-handler="<?= htmlspecialchars($row['staff_name'] ?? '-') ?>"
                                data-tanggal="<?= ($row['change_datetime'] !== '-') ? date('d M Y H:i', strtotime($row['change_datetime'])) : '-' ?>"
                                data-wrong="<?= htmlspecialchars($row['ketidaksesuaian_detail'] ?? '-') ?>"
                                data-desc="<?= htmlspecialchars($row['description'] ?? '-') ?>" 
                                data-img="<?= htmlspecialchars($row['upload'] ?? '') ?>">
                                
                                <td><input type="checkbox" class="row-check" value="<?= $row['id'] ?>"></td>
                                <td><?= $no++ ?>.</td>
                                <td><?= htmlspecialchars($row['nama_toko'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['peminta'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['penerima'] ?? '-') ?></td>
                                <td class="urgency-cell">
                                    <span class="urgency-code"><?= htmlspecialchars($row['sh_code'] ?? '-') ?></span>
                                    <span class="urgency-badge <?= strtolower($row['level_urgensi'] ?? '') ?>">
                                        <?= htmlspecialchars($row['level_urgensi'] ?? '-') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['jenis_kendala'] ?? '-') ?></td>
                                <td>
                                    <div class="status-card status-<?= strtolower(str_replace(' ', '-', $row['status_nama'] ?? 'default')) ?>">
                                        <?= htmlspecialchars($row['status_nama'] ?? '-') ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['staff_name'] ?? '-') ?></td>
                                <td class="truncate-text"><?= htmlspecialchars($row['ketidaksesuaian_detail'] ?? '-') ?></td>
                                <td><?= ($row['change_datetime'] !== '-') ? date('d M Y H:i', strtotime($row['change_datetime'])) : '-' ?></td>
                                
                                <td>
                                    <button class="btn-detail">Open Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Detail Reporting -->
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
                        <label class="field-label">User</label>
                        <input type="text" id="d-user" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Peminta</label>
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

                    <div class="field-item full-width-field">
                        <label class="field-label">User Salah Input</label>
                        <textarea id="d-wrong" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>

                    <!-- Field full width untuk deskripsi -->
                    <div class="field-item full-width-field">
                        <label class="field-label">Deskripsi Kendala</label>
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

<!-- Modal Detail Reporting -->
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
                        <label class="field-label">User</label>
                        <input type="text" id="d-user" class="field-value" readonly>
                    </div>

                    <div class="field-item">
                        <label class="field-label">Peminta</label>
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

                    <div class="field-item full-width-field">
                        <label class="field-label">User Salah Input</label>
                        <textarea id="d-wrong" class="field-value textarea-value" readonly style="resize: vertical;"></textarea>
                    </div>

                    <!-- Field full width untuk deskripsi -->
                    <div class="field-item full-width-field">
                        <label class="field-label">Deskripsi Kendala</label>
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

            <div class="modal-actions">
                <button class="btn-cancel">Tutup</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="dist/js/reporting.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>