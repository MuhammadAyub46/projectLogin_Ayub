<?php
//session_start();

// =====================
// CEK SESSION
// =====================
if (!isset($_SESSION['transaksi']))  $_SESSION['transaksi']  = [];
if (!isset($_SESSION['pembayaran'])) $_SESSION['pembayaran'] = [];

// =====================
// FILTER
// =====================
$filter_status  = $_GET['status']  ?? '';
$filter_metode  = $_GET['metode']  ?? '';
$filter_dari    = $_GET['dari']    ?? '';
$filter_sampai  = $_GET['sampai']  ?? '';

// Gabungkan data transaksi + info pembayaran
$data_laporan = [];
foreach ($_SESSION['transaksi'] as $trx) {
    $status  = $trx['status'] ?? 'Belum Lunas';
    $metode  = '-';
    $waktu   = '-';
    $id_bayar = '-';

    foreach ($_SESSION['pembayaran'] as $pb) {
        if ($pb['id_transaksi'] == $trx['id_transaksi']) {
            $metode   = $pb['metode'];
            $waktu    = $pb['waktu'];
            $id_bayar = $pb['id_pembayaran'];
            break;
        }
    }

    // Filter status
    if ($filter_status && $status !== $filter_status) continue;

    // Filter metode
    if ($filter_metode && $metode !== $filter_metode) continue;

    // Filter tanggal (format waktu: d/m/Y H:i)
    if ($filter_dari && $waktu !== '-') {
        $tgl = DateTime::createFromFormat('d/m/Y H:i', $waktu);
        $dari = DateTime::createFromFormat('Y-m-d', $filter_dari);
        if ($tgl && $dari && $tgl < $dari) continue;
    }
    if ($filter_sampai && $waktu !== '-') {
        $tgl = DateTime::createFromFormat('d/m/Y H:i', $waktu);
        $sampai = DateTime::createFromFormat('Y-m-d', $filter_sampai);
        if ($tgl && $sampai && $tgl > $sampai->modify('+1 day')) continue;
    }

    $data_laporan[] = array_merge($trx, [
        'status'       => $status,
        'metode'       => $metode,
        'waktu'        => $waktu,
        'id_pembayaran'=> $id_bayar,
    ]);
}

// =====================
// RINGKASAN
// =====================
$total_transaksi  = count($_SESSION['transaksi']);
$total_lunas      = count(array_filter($_SESSION['transaksi'], fn($t) => ($t['status'] ?? 'Belum Lunas') === 'Lunas'));
$total_belum      = $total_transaksi - $total_lunas;
$total_pendapatan = array_sum(array_column(
    array_filter($_SESSION['transaksi'], fn($t) => ($t['status'] ?? 'Belum Lunas') === 'Lunas'),
    'subtotal'
));
$total_pending    = array_sum(array_column(
    array_filter($_SESSION['transaksi'], fn($t) => ($t['status'] ?? 'Belum Lunas') === 'Belum Lunas'),
    'subtotal'
));

// Metode pembayaran terbanyak
$metode_count = [];
foreach ($_SESSION['pembayaran'] as $pb) {
    $metode_count[$pb['metode']] = ($metode_count[$pb['metode']] ?? 0) + 1;
}
arsort($metode_count);
$metode_terbanyak = !empty($metode_count) ? array_key_first($metode_count) : '-';

// Total hasil filter
$total_filter = array_sum(array_column($data_laporan, 'subtotal'));
?>

<style>
.lap-wrapper {
    padding: 20px 30px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}
.lap-wrapper h2 { font-size:24px; font-weight:700; margin-bottom:20px; color:#2c3e50; }

.card {
    background: #fff;
    border-radius: 10px;
    padding: 25px 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}
.card h3 {
    font-size:16px; font-weight:700; margin-bottom:18px;
    color:#2c3e50; border-bottom:2px solid #f0f0f0; padding-bottom:10px;
}

/* ===================== */
/* SUMMARY CARDS         */
/* ===================== */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 25px;
}
.summary-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 16px;
}
.summary-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.icon-blue   { background:#eaf4fc; }
.icon-green  { background:#eafaf1; }
.icon-red    { background:#fdf0f0; }
.icon-purple { background:#f5eef8; }

.summary-card .s-label { font-size:12px; color:#888; font-weight:600; margin-bottom:4px; }
.summary-card .s-value { font-size:18px; font-weight:700; color:#2c3e50; }
.summary-card .s-value.green  { color:#27ae60; }
.summary-card .s-value.red    { color:#e74c3c; }
.summary-card .s-value.blue   { color:#2980b9; }
.summary-card .s-value.purple { color:#8e44ad; }

/* ===================== */
/* FILTER                */
/* ===================== */
.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr auto;
    gap: 14px;
    align-items: end;
}
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-group label { font-size:13px; font-weight:600; color:#555; }
.form-group select,
.form-group input {
    padding:9px 12px; border:1px solid #ddd; border-radius:6px;
    font-size:14px; color:#333; background:#fafafa;
    transition:border-color 0.2s; outline:none;
    width:100%; box-sizing:border-box;
}
.form-group select:focus,
.form-group input:focus { border-color:#3498db; background:#fff; }

.btn-filter {
    padding:10px 20px; background:#2980b9; color:#fff;
    border:none; border-radius:6px; font-size:14px; font-weight:600;
    cursor:pointer; transition:background 0.2s; white-space:nowrap;
}
.btn-filter:hover { background:#1f6390; }
.btn-reset {
    padding:10px 16px; background:#ecf0f1; color:#555;
    border:none; border-radius:6px; font-size:14px; font-weight:600;
    cursor:pointer; text-decoration:none; display:inline-block;
    transition:background 0.2s; white-space:nowrap;
}
.btn-reset:hover { background:#dfe6e9; }
.btn-print-all {
    padding:10px 18px; background:#8e44ad; color:#fff;
    border:none; border-radius:6px; font-size:14px; font-weight:600;
    cursor:pointer; transition:background 0.2s; white-space:nowrap;
}
.btn-print-all:hover { background:#6c3483; }

/* ===================== */
/* TABLE                 */
/* ===================== */
.table-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
.table-header h3 { font-size:16px; font-weight:700; color:#2c3e50; margin:0; border:none; padding:0; }
.table-actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.total-badge {
    background:#eafaf1; color:#27ae60; border:1px solid #a9dfbf;
    border-radius:6px; padding:6px 14px; font-size:14px; font-weight:700;
}
.table-responsive { overflow-x:auto; }
table.lap-table { width:100%; border-collapse:collapse; font-size:14px; }
table.lap-table thead tr { background:#f5f7fa; }
table.lap-table th {
    padding:12px 14px; text-align:left; font-weight:700;
    color:#444; border-bottom:2px solid #e8e8e8; white-space:nowrap;
}
table.lap-table td { padding:11px 14px; border-bottom:1px solid #f0f0f0; color:#555; vertical-align:middle; }
table.lap-table tbody tr:hover { background:#fafcff; }
td.no-data { text-align:center; color:#aaa; padding:30px; font-style:italic; }

/* BADGE */
.badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.badge-lunas  { background:#eafaf1; color:#1e8449; border:1px solid #a9dfbf; }
.badge-belum  { background:#fdf0f0; color:#c0392b; border:1px solid #f5b7b1; }
.badge-metode { background:#eaf4fc; color:#2980b9; border:1px solid #aed6f1; }

/* ===================== */
/* GRAFIK SEDERHANA      */
/* ===================== */
.chart-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}
.chart-card {
    background:#fff; border-radius:10px;
    padding:22px 26px; box-shadow:0 2px 8px rgba(0,0,0,0.08);
}
.chart-card h4 { font-size:14px; font-weight:700; color:#2c3e50; margin-bottom:16px; }
.bar-item { margin-bottom:12px; }
.bar-label { display:flex; justify-content:space-between; font-size:13px; color:#555; margin-bottom:5px; }
.bar-track { background:#f0f0f0; border-radius:20px; height:10px; overflow:hidden; }
.bar-fill  { height:10px; border-radius:20px; transition:width 0.6s ease; }
.bar-blue   { background: linear-gradient(90deg,#3498db,#5dade2); }
.bar-green  { background: linear-gradient(90deg,#27ae60,#58d68d); }
.bar-orange { background: linear-gradient(90deg,#e67e22,#f39c12); }
.bar-purple { background: linear-gradient(90deg,#8e44ad,#bb8fce); }

.pie-row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
.pie-dot { width:14px; height:14px; border-radius:50%; flex-shrink:0; }
.pie-info { font-size:13px; color:#555; flex:1; }
.pie-pct  { font-size:13px; font-weight:700; color:#2c3e50; }

/* ===================== */
/* PRINT                 */
/* ===================== */
@media print {
    .no-print { display:none !important; }
    .lap-wrapper { padding:10px; }
    .summary-grid { grid-template-columns: repeat(4,1fr); }
    .chart-grid { display:none; }
}
@media (max-width:900px) {
    .summary-grid { grid-template-columns: repeat(2,1fr); }
    .filter-grid  { grid-template-columns: 1fr 1fr; }
    .chart-grid   { grid-template-columns: 1fr; }
}
@media (max-width:600px) {
    .summary-grid { grid-template-columns: 1fr; }
    .filter-grid  { grid-template-columns: 1fr; }
    .lap-wrapper  { padding:15px; }
}
</style>

<div class="lap-wrapper" id="printArea">

    <h2>Laporan Transaksi</h2>

    <!-- ===================== -->
    <!-- RINGKASAN             -->
    <!-- ===================== -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon icon-blue">📋</div>
            <div>
                <div class="s-label">Total Transaksi</div>
                <div class="s-value blue"><?= $total_transaksi ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon icon-green">✅</div>
            <div>
                <div class="s-label">Total Pendapatan</div>
                <div class="s-value green">Rp <?= number_format($total_pendapatan) ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon icon-red">⏳</div>
            <div>
                <div class="s-label">Belum Dibayar</div>
                <div class="s-value red">Rp <?= number_format($total_pending) ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon icon-purple">💳</div>
            <div>
                <div class="s-label">Metode Terbanyak</div>
                <div class="s-value purple"><?= htmlspecialchars($metode_terbanyak) ?></div>
            </div>
        </div>
    </div>

    <!-- ===================== -->
    <!-- GRAFIK                -->
    <!-- ===================== -->
    <?php
    // Data untuk grafik status
    $pct_lunas = $total_transaksi > 0 ? round($total_lunas / $total_transaksi * 100) : 0;
    $pct_belum = 100 - $pct_lunas;

    // Data untuk grafik metode
    $total_bayar = array_sum($metode_count);
    $metode_colors = ['Tunai'=>'bar-green','Transfer Bank'=>'bar-blue','QRIS'=>'bar-orange','E-Wallet'=>'bar-purple'];
    $dot_colors    = ['Tunai'=>'#27ae60','Transfer Bank'=>'#3498db','QRIS'=>'#e67e22','E-Wallet'=>'#8e44ad'];

    // Produk terlaris
    $produk_count = [];
    foreach ($_SESSION['transaksi'] as $trx) {
        $key = $trx['nama_barang'];
        if (!isset($produk_count[$key])) $produk_count[$key] = ['qty'=>0,'total'=>0];
        $produk_count[$key]['qty']   += $trx['jumlah'];
        $produk_count[$key]['total'] += $trx['subtotal'];
    }
    arsort($produk_count);
    $max_qty = !empty($produk_count) ? max(array_column($produk_count,'qty')) : 1;
    ?>

    <div class="chart-grid no-print">
        <!-- Status Pembayaran -->
        <div class="chart-card">
            <h4>📊 Status Pembayaran</h4>
            <div class="bar-item">
                <div class="bar-label">
                    <span>✅ Lunas (<?= $total_lunas ?> transaksi)</span>
                    <span><?= $pct_lunas ?>%</span>
                </div>
                <div class="bar-track"><div class="bar-fill bar-green" style="width:<?= $pct_lunas ?>%"></div></div>
            </div>
            <div class="bar-item">
                <div class="bar-label">
                    <span>⏳ Belum Lunas (<?= $total_belum ?> transaksi)</span>
                    <span><?= $pct_belum ?>%</span>
                </div>
                <div class="bar-track"><div class="bar-fill bar-orange" style="width:<?= $pct_belum ?>%"></div></div>
            </div>
            <br>
            <div style="font-size:13px;color:#888;margin-top:4px;">
                Total Nilai Lunas: <strong style="color:#27ae60;">Rp <?= number_format($total_pendapatan) ?></strong><br>
                Total Nilai Pending: <strong style="color:#e74c3c;">Rp <?= number_format($total_pending) ?></strong>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="chart-card">
            <h4>💳 Metode Pembayaran</h4>
            <?php if (!empty($metode_count)): ?>
                <?php foreach ($metode_count as $m => $cnt):
                    $pct  = $total_bayar > 0 ? round($cnt / $total_bayar * 100) : 0;
                    $cls  = $metode_colors[$m] ?? 'bar-blue';
                    $dot  = $dot_colors[$m]    ?? '#3498db';
                ?>
                <div class="pie-row">
                    <div class="pie-dot" style="background:<?= $dot ?>"></div>
                    <div class="pie-info"><?= htmlspecialchars($m) ?> (<?= $cnt ?>x)</div>
                    <div class="pie-pct"><?= $pct ?>%</div>
                </div>
                <div class="bar-track" style="margin-bottom:10px;">
                    <div class="bar-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#aaa;font-size:13px;font-style:italic;">Belum ada pembayaran.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Produk Terlaris -->
    <?php if (!empty($produk_count)): ?>
    <div class="chart-card no-print" style="margin-bottom:25px;">
        <h4>🏆 Produk Terlaris</h4>
        <?php
        $rank = 1;
        $max_val = max(array_column($produk_count,'qty')) ?: 1;
        foreach ($produk_count as $nama => $info):
            $pct = round($info['qty'] / $max_val * 100);
            $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#'.$rank));
        ?>
        <div class="bar-item">
            <div class="bar-label">
                <span><?= $medal ?> <?= htmlspecialchars($nama) ?> — <?= $info['qty'] ?> pcs terjual</span>
                <span style="color:#27ae60;font-weight:700;">Rp <?= number_format($info['total']) ?></span>
            </div>
            <div class="bar-track"><div class="bar-fill bar-blue" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php $rank++; endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ===================== -->
    <!-- FILTER                -->
    <!-- ===================== -->
    <div class="card no-print">
        <h3>🔍 Filter Laporan</h3>
        <form method="GET" action="dashboard.php">
            <input type="hidden" name="page" value="Laporan/laporan">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">-- Semua Status --</option>
                        <option value="Lunas"       <?= $filter_status === 'Lunas'       ? 'selected' : '' ?>>Lunas</option>
                        <option value="Belum Lunas" <?= $filter_status === 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Metode Bayar</label>
                    <select name="metode">
                        <option value="">-- Semua Metode --</option>
                        <option value="Tunai"        <?= $filter_metode === 'Tunai'        ? 'selected' : '' ?>>Tunai</option>
                        <option value="Transfer Bank" <?= $filter_metode === 'Transfer Bank' ? 'selected' : '' ?>>Transfer Bank</option>
                        <option value="QRIS"         <?= $filter_metode === 'QRIS'         ? 'selected' : '' ?>>QRIS</option>
                        <option value="E-Wallet"     <?= $filter_metode === 'E-Wallet'     ? 'selected' : '' ?>>E-Wallet</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="dari" value="<?= htmlspecialchars($filter_dari) ?>">
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="sampai" value="<?= htmlspecialchars($filter_sampai) ?>">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="dashboard.php?page=Laporan/laporan" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- ===================== -->
    <!-- TABEL LAPORAN         -->
    <!-- ===================== -->
    <div class="card">
        <div class="table-header">
            <h3>Detail Laporan Transaksi</h3>
            <div class="table-actions">
                <span class="total-badge">
                    Total: Rp <?= number_format($total_filter) ?>
                    (<?= count($data_laporan) ?> transaksi)
                </span>
                <button class="btn-print-all no-print" onclick="cetakLaporan()">🖨 Cetak Laporan</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="lap-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>ID Pembayaran</th>
                        <th>Customer</th>
                        <th>Produk</th>
                        <th>Kode Barang</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Metode</th>
                        <th>Waktu Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($data_laporan)): ?>
                    <?php foreach ($data_laporan as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                        <td><?= htmlspecialchars($row['id_pembayaran']) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                        <td>Rp <?= number_format($row['harga']) ?></td>
                        <td><?= $row['jumlah'] ?> pcs</td>
                        <td style="font-weight:600;color:#27ae60;">Rp <?= number_format($row['subtotal']) ?></td>
                        <td>
                            <?php if ($row['metode'] !== '-'): ?>
                                <span class="badge badge-metode"><?= htmlspecialchars($row['metode']) ?></span>
                            <?php else: ?>
                                <span style="color:#bbb;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['waktu']) ?></td>
                        <td>
                            <span class="badge <?= $row['status'] === 'Lunas' ? 'badge-lunas' : 'badge-belum' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- TOTAL ROW -->
                    <tr style="background:#f5f7fa;font-weight:700;">
                        <td colspan="8" style="text-align:right;color:#444;padding:12px 14px;">Total</td>
                        <td style="color:#27ae60;padding:12px 14px;">Rp <?= number_format($total_filter) ?></td>
                        <td colspan="3"></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="12" class="no-data">Tidak ada data yang sesuai filter.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function cetakLaporan() {
    window.print();
}
</script>
