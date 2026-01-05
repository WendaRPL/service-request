<?php
require_once "direct/config.php";


$role_id   = intval($_SESSION['role'] ?? 0);
$user_id   = intval($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['name'] ?? '';
$pageTitle = "Done & Canceled";
ob_start();
?>
<link rel="stylesheet" href="dist/css/done_cancelled.css">

<div class="container">
        <div class="nav-home" onclick="goToDashboard()">
            <i class="fas fa-home"></i> Home
        </div>

        <div class="tabs">
            <button class="tab-button active" onclick="switchTab('done')" id="tab-done">
                <i class="fas fa-check-circle"></i> Done
            </button>
            <button class="tab-button" onclick="switchTab('canceled')" id="tab-canceled">
                <i class="fas fa-times-circle"></i> Canceled
            </button>
        </div>

        <!-- Done Table -->
        <div class="table-container" id="done-table">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Peminta</th>
                        <th>S/H</th>
                        <th>Jenis Kendala</th>
                        <th>Tanggal Selesai</th>
                        <th>Handler</th>
                        <th>Aksi</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody id="done-tbody">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Canceled Table -->
        <div class="table-container" id="canceled-table" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Peminta</th>
                        <th>S/H</th>
                        <th>Jenis Kendala</th>
                        <th>Tanggal Dibatalkan</th>
                        <th>Handler</th>
                        <th>Aksi</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody id="canceled-tbody">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Empty State Messages -->
        <div class="empty-state" id="empty-done" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Tidak Ada Data Done</h3>
            <p>Belum ada request yang selesai diproses.</p>
        </div>

        <div class="empty-state" id="empty-canceled" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h3>Tidak Ada Data Canceled</h3>
            <p>Tidak ada request yang dibatalkan.</p>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Request</h3>
                <button class="modal-close" onclick="closeDetailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Detail akan diisi oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeDetailModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script src="dist/js/done_cancelled.js"></script>

<?php
$content = ob_get_clean();
require_once "modules/layout/template.php";
?>
