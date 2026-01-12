<?php
require_once "direct/config.php";

$pageTitle = "Done & Canceled";

$user_id = intval($_SESSION['user_id'] ?? 0);
$role_id = intval($_SESSION['role'] ?? 0);

/**
 * ==========================
 * QUERY BASE (STATUS TERAKHIR)
 * ==========================
 */
$baseSql = "
SELECT 
    r.id,
    u.username AS peminta,
    r.user_toko,
    r.jenis_kendala,
    r.level_urgensi,
    r.id_staff,
    stf.username AS staff_name,
    s.status AS status_kode,
    s.nama AS status_nama,
    ts.change_datetime,
    r.deskripsi_kendala
FROM transaksi_request r
JOIN users u ON u.id = r.user_request
LEFT JOIN users stf ON stf.id = r.id_staff
JOIN transaksi_status ts 
    ON ts.request_id = r.id
JOIN status s 
    ON s.id = ts.current_status
WHERE ts.change_datetime = (
    SELECT MAX(ts2.change_datetime)
    FROM transaksi_status ts2
    WHERE ts2.request_id = r.id
)
";

/**
 * ==========================
 * DONE DATA
 * ==========================
 */
$sqlDone = $baseSql . " AND s.status = 'D' ORDER BY ts.change_datetime DESC";
$resDone = $conn->query($sqlDone);
$doneData = $resDone ? $resDone->fetch_all(MYSQLI_ASSOC) : [];

/**
 * ==========================
 * CANCELED DATA
 * ==========================
 */
$sqlCanceled = $baseSql . " AND s.status = 'C' ORDER BY ts.change_datetime DESC";
$resCanceled = $conn->query($sqlCanceled);
$canceledData = $resCanceled ? $resCanceled->fetch_all(MYSQLI_ASSOC) : [];

ob_start();
?>

<link rel="stylesheet" href="dist/css/done_cancelled.css">

<div class="container">
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

    <!-- FILTER PANEL -->
    <div class="filter-panel" id="filterPanel">
        <div class="filter-grid">
            <div class="filter-group">
                <label>Toko</label>
                <select id="filter-toko">
                    <option value="">Semua</option>
                    <option value="">TM</option>
                    <option value="">HK</option>
                    <option value="">RJ</option>
                    <?php
                    // Ambil toko unik dari data
                    $allData = array_merge($doneData, $canceledData);
                    $uniqueTokos = array_unique(array_column($allData, 'user_toko'));
                    foreach ($uniqueTokos as $toko): 
                        if (!empty($toko)):
                    ?>
                    <option value="<?= htmlspecialchars($toko) ?>"><?= htmlspecialchars($toko) ?></option>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Penerima</label>
                <select id="filter-peminta">
                    <option value="">Semua</option>
                    <?php
                    $uniquePeminta = array_unique(array_column($allData, 'peminta'));
                    foreach ($uniquePeminta as $peminta):
                        if (!empty($peminta)):
                    ?>
                    <option value="<?= htmlspecialchars($peminta) ?>"><?= htmlspecialchars($peminta) ?></option>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </select>
            </div>


            <div class="filter-group">
                <label>Dari Tanggal</label>
                <input type="date" id="filter-start-date">
            </div>

            <div class="filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" id="filter-end-date">
            </div>
        </div>
        
        <div class="filter-actions">
            <button class="btn-filter btn-reset" id="btnResetFilter">
                Reset
            </button>
            <button class="btn-filter btn-terapkan" id="btnApplyFilter">
                Terapkan
            </button>
        </div>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab-button active" onclick="switchTab('done')" id="tab-done">
            <i class="fas fa-check-circle"></i> Done
        </button>
        <button class="tab-button" onclick="switchTab('canceled')" id="tab-canceled">
            <i class="fas fa-times-circle"></i> Canceled
        </button>
    </div>

    <!-- DONE TABLE -->
    <div class="table-container" id="done-table">
        <?php if (empty($doneData)): ?>
            <div class="empty-state" id="empty-done">
                <div class="empty-icon"><i class="fas fa-check-circle"></i></div>
                <h3>Tidak Ada Data Done</h3>
                <p>Belum ada request yang selesai.</p>
            </div>
        <?php else: ?>
        <table id="doneTable" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Toko</th>
                    <th>Peminta</th>
                    <th>Jenis Kendala</th>
                    <th>Urgensi</th>
                    <th>Tanggal Selesai</th>
                    <th>Handler</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($doneData as $i => $r): 
                $urgensiClass = '';
                switch($r['level_urgensi']) {
                    case 'Sangat Tinggi': $urgensiClass = 'very-high'; break;
                    case 'Tinggi': $urgensiClass = 'high'; break;
                    case 'Sedang': $urgensiClass = 'medium'; break;
                    case 'Rendah': $urgensiClass = 'low'; break;
                    default: $urgensiClass = 'medium';
                }
            ?>
                <tr data-id="<?= $r['id'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($r['user_toko']) ?></td>
                    <td><?= htmlspecialchars($r['peminta']) ?></td>
                    <td><?= htmlspecialchars($r['jenis_kendala']) ?></td>
                    <td class="urgency-cell">
                        <div class="urgency-card">
                            <span class="urgency-code"><?= $r['jenis_kendala'] == 'Software' ? 'S' : 'H' ?></span>
                            <span class="urgency-badge <?= $urgensiClass ?>">
                                <?= htmlspecialchars($r['level_urgensi']) ?>
                            </span>
                        </div>
                    </td>
                    <td><?= date('d M Y H:i', strtotime($r['change_datetime'])) ?></td>
                    <td><?= htmlspecialchars($r['staff_name'] ?? '-') ?></td>
                    <td>
                        <button class="detail-button btn-detail" data-id="<?= $r['id'] ?>">
                            Detail
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- CANCELED TABLE -->
    <div class="table-container" id="canceled-table" style="display:none;">
        <?php if (empty($canceledData)): ?>
            <div class="empty-state" id="empty-canceled">
                <div class="empty-icon"><i class="fas fa-times-circle"></i></div>
                <h3>Tidak Ada Data Canceled</h3>
                <p>Tidak ada request yang dibatalkan.</p>
            </div>
        <?php else: ?>
        <table id="canceledTable" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Toko</th>
                    <th>Peminta</th>
                    <th>Jenis Kendala</th>
                    <th>Urgensi</th>
                    <th>Tanggal Batal</th>
                    <th>Handler</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($canceledData as $i => $r): 
                $urgensiClass = '';
                switch($r['level_urgensi']) {
                    case 'Sangat Tinggi': $urgensiClass = 'very-high'; break;
                    case 'Tinggi': $urgensiClass = 'high'; break;
                    case 'Sedang': $urgensiClass = 'medium'; break;
                    case 'Rendah': $urgensiClass = 'low'; break;
                    default: $urgensiClass = 'medium';
                }
            ?>
                <tr data-id="<?= $r['id'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($r['user_toko']) ?></td>
                    <td><?= htmlspecialchars($r['peminta']) ?></td>
                    <td><?= htmlspecialchars($r['jenis_kendala']) ?></td>
                    <td class="urgency-cell">
                        <div class="urgency-card">
                            <span class="urgency-code"><?= $r['jenis_kendala'] == 'Software' ? 'S' : 'H' ?></span>
                            <span class="urgency-badge <?= $urgensiClass ?>">
                                <?= htmlspecialchars($r['level_urgensi']) ?>
                            </span>
                        </div>
                    </td>
                    <td><?= date('d M Y H:i', strtotime($r['change_datetime'])) ?></td>
                    <td><?= htmlspecialchars($r['staff_name'] ?? '-') ?></td>
                    <td>
                        <button class="detail-button btn-detail" data-id="<?= $r['id'] ?>">
                            Detail
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal" id="detailModal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <div class="modal-header">
            <h2>Detail Request</h2>
        </div>
        <div class="modal-body" id="modalDetailBody">
            <!-- Content akan diisi via AJAX -->
            <div class="loading-spinner" style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Memuat data...</p>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('detailModal')">Tutup</button>
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