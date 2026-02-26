<?php
//session_start();

// =====================
// CEK & BUAT SESSION
// =====================
if (!isset($_SESSION['customer']))      $_SESSION['customer']      = [];
if (!isset($_SESSION['products']))      $_SESSION['products']      = [];
if (!isset($_SESSION['transaksi']))     $_SESSION['transaksi']     = [];
if (!isset($_SESSION['pembayaran']))    $_SESSION['pembayaran']    = [];
if (!isset($_SESSION['no_transaksi']))  $_SESSION['no_transaksi']  = 1;
if (!isset($_SESSION['no_pembayaran'])) $_SESSION['no_pembayaran'] = 1;

// =====================
// HAPUS TRANSAKSI
// =====================
if (isset($_GET['hapus_trx'])) {
    $idx    = (int)$_GET['hapus_trx'];
    $id_trx = $_SESSION['transaksi'][$idx]['id_transaksi'] ?? '';
    // Hapus pembayaran terkait juga
    $_SESSION['pembayaran'] = array_values(array_filter($_SESSION['pembayaran'], function($p) use ($id_trx) {
        return $p['id_transaksi'] !== $id_trx;
    }));
    unset($_SESSION['transaksi'][$idx]);
    $_SESSION['transaksi'] = array_values($_SESSION['transaksi']);
    header("Location: dashboard.php?page=Transaksi/transaksi");
    exit;
}

// =====================
// HAPUS PEMBAYARAN
// =====================
if (isset($_GET['hapus_bayar'])) {
    $idx    = (int)$_GET['hapus_bayar'];
    $id_trx = $_SESSION['pembayaran'][$idx]['id_transaksi'] ?? '';
    foreach ($_SESSION['transaksi'] as &$trx) {
        if ($trx['id_transaksi'] == $id_trx) { $trx['status'] = 'Belum Lunas'; break; }
    }
    unset($trx);
    unset($_SESSION['pembayaran'][$idx]);
    $_SESSION['pembayaran'] = array_values($_SESSION['pembayaran']);
    header("Location: dashboard.php?page=Transaksi/transaksi");
    exit;
}

// =====================
// SIMPAN TRANSAKSI
// =====================
if (isset($_POST['simpan'])) {
    $id_transaksi = "TR" . str_pad($_SESSION['no_transaksi'], 3, "0", STR_PAD_LEFT);
    $_SESSION['no_transaksi']++;

    $id_pelanggan = $_POST['id_pelanggan'] ?? '';
    $kode_barang  = $_POST['kode_barang']  ?? '';
    $jumlah       = (int)($_POST['jumlah'] ?? 0);

    $nama_pelanggan = '';
    $nama_barang    = '';
    $harga          = 0;
    $stok_sekarang  = 0;
    $index_produk   = -1;

    foreach ($_SESSION['products'] as $index => $product) {
        if ($product['kode_barang'] == $kode_barang) {
            $nama_barang   = $product['nama_barang'];
            $harga         = $product['harga'];
            $stok_sekarang = $product['stok'];
            $index_produk  = $index;
            break;
        }
    }

    if ($jumlah > $stok_sekarang) {
        echo "<script>alert('Stok tidak mencukupi!');</script>";
    } else {
        foreach ($_SESSION['customer'] as $customer) {
            if ($customer['id_pelanggan'] == $id_pelanggan) {
                $nama_pelanggan = $customer['nama_pelanggan'];
                break;
            }
        }
        $subtotal = $harga * $jumlah;
        if ($nama_pelanggan != '' && $nama_barang != '' && $jumlah > 0) {
            $_SESSION['products'][$index_produk]['stok'] -= $jumlah;
            $_SESSION['transaksi'][] = [
                'id_transaksi'   => $id_transaksi,
                'nama_pelanggan' => $nama_pelanggan,
                'kode_barang'    => $kode_barang,
                'nama_barang'    => $nama_barang,
                'harga'          => $harga,
                'jumlah'         => $jumlah,
                'subtotal'       => $subtotal,
                'status'         => 'Belum Lunas',
            ];
        }
    }
}

// =====================
// SIMPAN PEMBAYARAN
// =====================
$pesan_sukses = '';
$pesan_error  = '';

if (isset($_POST['bayar'])) {
    $id_transaksi = $_POST['id_transaksi'] ?? '';
    $metode       = $_POST['metode']       ?? '';
    $uang_bayar   = (int)($_POST['uang_bayar'] ?? 0);
    $catatan      = $_POST['catatan']      ?? '';

    $subtotal = 0;
    $trx_data = null;
    foreach ($_SESSION['transaksi'] as $trx) {
        if ($trx['id_transaksi'] == $id_transaksi) { $subtotal = $trx['subtotal']; $trx_data = $trx; break; }
    }

    if (!$trx_data) {
        $pesan_error = 'Transaksi tidak ditemukan.';
    } elseif ($metode == 'Tunai' && $uang_bayar < $subtotal) {
        $pesan_error = 'Uang bayar kurang dari total tagihan!';
    } else {
        $kembalian = ($metode == 'Tunai') ? ($uang_bayar - $subtotal) : 0;
        $id_bayar  = "PB" . str_pad($_SESSION['no_pembayaran'], 3, "0", STR_PAD_LEFT);
        $_SESSION['no_pembayaran']++;

        foreach ($_SESSION['transaksi'] as &$trx) {
            if ($trx['id_transaksi'] == $id_transaksi) { $trx['status'] = 'Lunas'; break; }
        }
        unset($trx);

        $_SESSION['pembayaran'][] = [
            'id_pembayaran'  => $id_bayar,
            'id_transaksi'   => $id_transaksi,
            'nama_pelanggan' => $trx_data['nama_pelanggan'],
            'nama_barang'    => $trx_data['nama_barang'],
            'subtotal'       => $subtotal,
            'metode'         => $metode,
            'uang_bayar'     => ($metode == 'Tunai') ? $uang_bayar : $subtotal,
            'kembalian'      => $kembalian,
            'catatan'        => $catatan,
            'waktu'          => date('d/m/Y H:i'),
        ];
        $pesan_sukses = $id_bayar;
    }
}

// Helper
$total_semua = array_sum(array_column($_SESSION['transaksi'], 'subtotal'));
$bayar_id    = $_GET['bayar'] ?? '';
?>

<style>
.trx-wrapper {
    padding: 20px 30px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}
.trx-wrapper h2 { font-size:24px; font-weight:700; margin-bottom:20px; color:#2c3e50; }

.card {
    background: #fff;
    border-radius: 10px;
    padding: 25px 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}
.card h3 {
    font-size: 16px; font-weight:700; margin-bottom:18px;
    color:#2c3e50; border-bottom:2px solid #f0f0f0; padding-bottom:10px;
}
.form-grid { display:grid; grid-template-columns:1fr 1fr 180px; gap:16px; align-items:end; }
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-group.full { grid-column:1/-1; }
.form-group label { font-size:13px; font-weight:600; color:#555; }
.form-group select,
.form-group input,
.form-group textarea {
    padding:9px 12px; border:1px solid #ddd; border-radius:6px;
    font-size:14px; color:#333; background:#fafafa;
    transition:border-color 0.2s; outline:none;
    width:100%; box-sizing:border-box; font-family:inherit;
}
.form-group select:focus,
.form-group input:focus,
.form-group textarea:focus { border-color:#3498db; background:#fff; }

/* TABLE */
.table-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
.table-header h3 { font-size:16px; font-weight:700; color:#2c3e50; margin:0; border:none; padding:0; }
.total-badge {
    background:#eaf6ff; color:#2980b9; border:1px solid #b3d9f5;
    border-radius:6px; padding:6px 14px; font-size:14px; font-weight:600;
}
.table-responsive { overflow-x:auto; }
table.main-table { width:100%; border-collapse:collapse; font-size:14px; }
table.main-table thead tr { background:#f5f7fa; }
table.main-table th {
    padding:12px 14px; text-align:left; font-weight:700;
    color:#444; border-bottom:2px solid #e8e8e8; white-space:nowrap;
}
table.main-table td { padding:11px 14px; border-bottom:1px solid #f0f0f0; color:#555; vertical-align:middle; }
table.main-table tbody tr:hover { background:#fafcff; }
td.no-data { text-align:center; color:#aaa; padding:30px; font-style:italic; }

/* BUTTONS */
.btn-simpan { padding:10px 22px; background:#27ae60; color:#fff; border:none; border-radius:6px; font-size:14px; font-weight:600; cursor:pointer; transition:background 0.2s; }
.btn-simpan:hover { background:#219150; }
.btn-hapus { padding:6px 14px; background:#e74c3c; color:#fff; border:none; border-radius:5px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; transition:background 0.2s; }
.btn-hapus:hover { background:#c0392b; }
.btn-bayar-row { padding:6px 14px; background:#2980b9; color:#fff; border:none; border-radius:5px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; transition:background 0.2s; }
.btn-bayar-row:hover { background:#1f6390; }
.btn-struk { padding:6px 14px; background:#8e44ad; color:#fff; border:none; border-radius:5px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; transition:background 0.2s; }
.btn-struk:hover { background:#6c3483; }
.btn-bayar-submit { padding:11px 28px; background:#27ae60; color:#fff; border:none; border-radius:6px; font-size:14px; font-weight:700; cursor:pointer; transition:background 0.2s; margin-top:8px; }
.btn-bayar-submit:hover { background:#219150; }
.btn-batal { padding:11px 20px; background:#ecf0f1; color:#555; border:none; border-radius:6px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; transition:background 0.2s; margin-top:8px; margin-left:8px; }
.btn-batal:hover { background:#dfe6e9; }

/* BADGE */
.badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.badge-lunas  { background:#eafaf1; color:#1e8449; border:1px solid #a9dfbf; }
.badge-belum  { background:#fdf0f0; color:#c0392b; border:1px solid #f5b7b1; }
.badge-metode { background:#eaf4fc; color:#2980b9; border:1px solid #aed6f1; }

/* ALERT */
.alert { padding:12px 16px; border-radius:7px; margin-bottom:18px; font-size:14px; font-weight:600; display:flex; align-items:center; gap:10px; }
.alert-success { background:#eafaf1; color:#1e8449; border:1px solid #a9dfbf; }
.alert-error   { background:#fdf0f0; color:#c0392b; border:1px solid #f5b7b1; }

/* FORM BAYAR INLINE */
.form-bayar-card {
    background:#f0f7ff;
    border:2px solid #3498db;
    border-radius:10px;
    padding:22px 26px;
    margin-bottom:25px;
    animation:fadeIn 0.25s ease;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);} }
.form-bayar-card h3 { font-size:15px; font-weight:700; color:#1a5276; margin-bottom:16px; border-bottom:1px solid #aed6f1; padding-bottom:10px; }

.tagihan-info {
    background:#fff; border:1px solid #b3d9f5; border-radius:8px;
    padding:12px 16px; margin-bottom:16px;
    display:flex; gap:30px; flex-wrap:wrap;
}
.tagihan-info .t-item { font-size:13px; color:#555; }
.tagihan-info .t-item strong { display:block; font-size:15px; color:#2c3e50; margin-top:2px; }
.tagihan-info .t-total strong { font-size:17px; color:#27ae60; }

.form-bayar-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

.metode-group { display:flex; gap:10px; flex-wrap:wrap; }
.metode-group input[type="radio"] { display:none; }
.metode-group label {
    padding:7px 16px; border:2px solid #ddd; border-radius:20px;
    font-size:13px; font-weight:600; cursor:pointer; color:#666;
    transition:all 0.2s; background:#fafafa;
}
.metode-group input[type="radio"]:checked + label {
    border-color:#27ae60; background:#eafaf1; color:#1e8449;
}
#kembalian-info {
    border-radius:7px; padding:10px 16px;
    font-size:14px; font-weight:700;
    display:none; margin-top:8px;
}

/* MODAL STRUK */
.modal-overlay { display:none; position:fixed; top:0;left:0;width:100%;height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; }
.modal-overlay.active { display:flex; }
.modal-struk { background:#fff; border-radius:10px; width:360px; max-width:95vw; box-shadow:0 10px 40px rgba(0,0,0,0.2); overflow:hidden; }
.struk-header { background:#2c3e50; color:#fff; text-align:center; padding:20px; }
.struk-header h3 { margin:0 0 4px; font-size:18px; letter-spacing:1px; }
.struk-header p  { margin:0; font-size:12px; opacity:0.8; }
.struk-body { padding:20px; }
.struk-divider { border:none; border-top:1px dashed #ccc; margin:12px 0; }
.struk-row { display:flex; justify-content:space-between; font-size:13px; margin-bottom:7px; color:#555; }
.struk-row span:first-child { color:#888; }
.struk-row.bold { font-weight:700; color:#2c3e50; font-size:14px; }
.struk-row.hijau { font-weight:700; color:#1e8449; font-size:14px; }
.struk-footer { text-align:center; padding:0 20px 16px; font-size:12px; color:#aaa; }
.modal-actions { display:flex; gap:10px; padding:0 20px 20px; justify-content:center; }
.btn-print { padding:9px 22px; background:#2980b9; color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-close-modal { padding:9px 22px; background:#ecf0f1; color:#555; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }

@media print {
    body * { visibility:hidden; }
    .modal-struk, .modal-struk * { visibility:visible; }
    .modal-struk { position:fixed; top:0; left:50%; transform:translateX(-50%); }
    .modal-actions { display:none; }
    .modal-overlay { display:flex !important; background:none; }
}
@media (max-width:768px) {
    .form-grid, .form-bayar-grid { grid-template-columns:1fr; }
    .trx-wrapper { padding:15px; }
}
</style>

<!-- MODAL STRUK -->
<div class="modal-overlay" id="modalStruk">
    <div class="modal-struk">
        <div class="struk-header">
            <h3>🧾 STRUK PEMBAYARAN</h3>
            <p id="struk-waktu"></p>
        </div>
        <div class="struk-body">
            <div class="struk-row"><span>ID Pembayaran</span><span id="struk-id-bayar"></span></div>
            <div class="struk-row"><span>ID Transaksi</span><span id="struk-id-trx"></span></div>
            <div class="struk-row"><span>Customer</span><span id="struk-customer"></span></div>
            <hr class="struk-divider">
            <div class="struk-row"><span>Produk</span><span id="struk-produk"></span></div>
            <div class="struk-row bold"><span>Total Tagihan</span><span id="struk-total"></span></div>
            <hr class="struk-divider">
            <div class="struk-row"><span>Metode</span><span id="struk-metode"></span></div>
            <div class="struk-row"><span>Uang Bayar</span><span id="struk-bayar"></span></div>
            <div class="struk-row hijau"><span>Kembalian</span><span id="struk-kembalian"></span></div>
            <div class="struk-row" id="struk-catatan-row"><span>Catatan</span><span id="struk-catatan"></span></div>
        </div>
        <div class="struk-footer"><p>❤ Terima kasih sudah berbelanja! ❤</p></div>
        <div class="modal-actions">
            <button class="btn-print" onclick="window.print()">🖨 Print</button>
            <button class="btn-close-modal" onclick="tutupStruk()">Tutup</button>
        </div>
    </div>
</div>

<div class="trx-wrapper">

    <h2>Transaksi</h2>

    <?php if ($pesan_error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($pesan_error) ?></div>
    <?php endif; ?>
    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success">✔ Pembayaran <strong><?= $pesan_sukses ?></strong> berhasil disimpan!</div>
    <?php endif; ?>

    <!-- FORM TAMBAH TRANSAKSI -->
    <div class="card">
        <h3>+ Tambah Transaksi</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Customer</label>
                    <select name="id_pelanggan" required>
                        <option value="">-- Pilih Customer --</option>
                        <?php foreach ($_SESSION['customer'] as $c): ?>
                            <option value="<?= $c['id_pelanggan'] ?>"><?= htmlspecialchars($c['nama_pelanggan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Produk</label>
                    <select name="kode_barang" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($_SESSION['products'] as $p): ?>
                            <option value="<?= $p['kode_barang'] ?>">
                                <?= htmlspecialchars($p['nama_barang']) ?>
                                - Rp <?= number_format($p['harga']) ?>
                                (Stok: <?= $p['stok'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" min="1" placeholder="0" required>
                </div>
            </div>
            <br>
            <button type="submit" name="simpan" class="btn-simpan">Simpan Transaksi</button>
        </form>
    </div>

    <!-- FORM PEMBAYARAN INLINE (muncul saat tombol Bayar diklik) -->
    <?php if ($bayar_id):
        $trx_bayar = null;
        foreach ($_SESSION['transaksi'] as $t) {
            if ($t['id_transaksi'] == $bayar_id) { $trx_bayar = $t; break; }
        }
    ?>
    <?php if ($trx_bayar && ($trx_bayar['status'] ?? 'Belum Lunas') === 'Belum Lunas'): ?>
    <div class="form-bayar-card">
        <h3>💳 Form Pembayaran — <?= htmlspecialchars($bayar_id) ?></h3>
        <div class="tagihan-info">
            <div class="t-item"><span>Customer</span><strong><?= htmlspecialchars($trx_bayar['nama_pelanggan']) ?></strong></div>
            <div class="t-item"><span>Produk</span><strong><?= htmlspecialchars($trx_bayar['nama_barang']) ?></strong></div>
            <div class="t-item"><span>Jumlah</span><strong><?= $trx_bayar['jumlah'] ?> pcs</strong></div>
            <div class="t-item"><span>Harga Satuan</span><strong>Rp <?= number_format($trx_bayar['harga']) ?></strong></div>
            <div class="t-item t-total"><span>Total Tagihan</span><strong>Rp <?= number_format($trx_bayar['subtotal']) ?></strong></div>
        </div>
        <form method="POST">
            <input type="hidden" name="id_transaksi" value="<?= htmlspecialchars($bayar_id) ?>">
            <div class="form-bayar-grid">
                <div class="form-group full">
                    <label>Metode Pembayaran</label>
                    <div class="metode-group">
                        <input type="radio" name="metode" id="m-tunai"    value="Tunai"        onchange="toggleUangBayar()" required>
                        <label for="m-tunai">💵 Tunai</label>
                        <input type="radio" name="metode" id="m-transfer" value="Transfer Bank" onchange="toggleUangBayar()">
                        <label for="m-transfer">🏦 Transfer Bank</label>
                        <input type="radio" name="metode" id="m-qris"     value="QRIS"         onchange="toggleUangBayar()">
                        <label for="m-qris">📱 QRIS</label>
                        <input type="radio" name="metode" id="m-ewallet"  value="E-Wallet"     onchange="toggleUangBayar()">
                        <label for="m-ewallet">💳 E-Wallet</label>
                    </div>
                </div>
                <div class="form-group" id="uang-bayar-group" style="display:none;">
                    <label>Uang Bayar (Rp)</label>
                    <input type="number" name="uang_bayar" id="uangBayar" min="0"
                           placeholder="Masukkan nominal"
                           oninput="hitungKembalian(<?= $trx_bayar['subtotal'] ?>)">
                    <div id="kembalian-info"></div>
                </div>
                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Nomor rekening, referensi, dsb..."></textarea>
                </div>
            </div>
            <button type="submit" name="bayar" class="btn-bayar-submit">✔ Proses Pembayaran</button>
            <a href="dashboard.php?page=Transaksi/transaksi" class="btn-batal">✕ Batal</a>
        </form>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- TABEL TRANSAKSI -->
    <div class="card">
        <div class="table-header">
            <h3>Data Transaksi</h3>
            <span class="total-badge">Total: Rp <?= number_format($total_semua) ?></span>
        </div>
        <div class="table-responsive">
            <table class="main-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>Customer</th>
                        <th>Kode Barang</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($_SESSION['transaksi'])): ?>
                    <?php foreach ($_SESSION['transaksi'] as $i => $trx): ?>
                        <?php $status = $trx['status'] ?? 'Belum Lunas'; ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($trx['id_transaksi']) ?></td>
                            <td><?= htmlspecialchars($trx['nama_pelanggan']) ?></td>
                            <td><?= htmlspecialchars($trx['kode_barang']) ?></td>
                            <td><?= htmlspecialchars($trx['nama_barang']) ?></td>
                            <td>Rp <?= number_format($trx['harga']) ?></td>
                            <td><?= $trx['jumlah'] ?></td>
                            <td style="font-weight:600;color:#27ae60;">Rp <?= number_format($trx['subtotal']) ?></td>
                            <td>
                                <span class="badge <?= $status === 'Lunas' ? 'badge-lunas' : 'badge-belum' ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td style="display:flex;gap:6px;flex-wrap:wrap;">
                                <?php if ($status === 'Belum Lunas'): ?>
                                    <a class="btn-bayar-row"
                                       href="dashboard.php?page=Transaksi/transaksi&bayar=<?= $trx['id_transaksi'] ?>">
                                        💳 Bayar
                                    </a>
                                <?php else: ?>
                                    <?php
                                    $idx_bayar = null;
                                    foreach ($_SESSION['pembayaran'] as $ib => $pb) {
                                        if ($pb['id_transaksi'] == $trx['id_transaksi']) { $idx_bayar = $ib; break; }
                                    }
                                    ?>
                                    <?php if ($idx_bayar !== null): ?>
                                        <button class="btn-struk" onclick="lihatStruk(<?= $idx_bayar ?>)">🧾 Struk</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a class="btn-hapus"
                                   href="dashboard.php?page=Transaksi/transaksi&hapus_trx=<?= $i ?>"
                                   onclick="return confirm('Yakin hapus transaksi ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="no-data">Belum Ada Transaksi</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const dataPembayaran = <?= json_encode(array_values($_SESSION['pembayaran'])) ?>;

function toggleUangBayar() {
    const isTunai = document.getElementById('m-tunai').checked;
    const grup    = document.getElementById('uang-bayar-group');
    grup.style.display = isTunai ? 'flex' : 'none';
    if (!isTunai) document.getElementById('kembalian-info').style.display = 'none';
}

function hitungKembalian(subtotal) {
    const bayar     = parseInt(document.getElementById('uangBayar').value || 0);
    const kembalian = bayar - subtotal;
    const box       = document.getElementById('kembalian-info');
    if (bayar > 0) {
        box.style.display = 'block';
        if (kembalian >= 0) {
            box.style.background   = '#eafaf1';
            box.style.border       = '1px solid #a9dfbf';
            box.style.color        = '#1e8449';
            box.textContent        = '✔ Kembalian: Rp ' + kembalian.toLocaleString('id-ID');
        } else {
            box.style.background   = '#fdf0f0';
            box.style.border       = '1px solid #f5b7b1';
            box.style.color        = '#c0392b';
            box.textContent        = '✖ Kurang: Rp ' + Math.abs(kembalian).toLocaleString('id-ID');
        }
    } else {
        box.style.display = 'none';
    }
}

function lihatStruk(index) {
    const d = dataPembayaran[index];
    if (!d) return;
    document.getElementById('struk-id-bayar').textContent  = d.id_pembayaran;
    document.getElementById('struk-id-trx').textContent    = d.id_transaksi;
    document.getElementById('struk-customer').textContent  = d.nama_pelanggan;
    document.getElementById('struk-produk').textContent    = d.nama_barang;
    document.getElementById('struk-waktu').textContent     = d.waktu;
    document.getElementById('struk-total').textContent     = 'Rp ' + parseInt(d.subtotal).toLocaleString('id-ID');
    document.getElementById('struk-metode').textContent    = d.metode;
    document.getElementById('struk-bayar').textContent     = 'Rp ' + parseInt(d.uang_bayar).toLocaleString('id-ID');
    document.getElementById('struk-kembalian').textContent = 'Rp ' + parseInt(d.kembalian).toLocaleString('id-ID');
    const catatanRow = document.getElementById('struk-catatan-row');
    if (d.catatan) {
        document.getElementById('struk-catatan').textContent = d.catatan;
        catatanRow.style.display = 'flex';
    } else {
        catatanRow.style.display = 'none';
    }
    document.getElementById('modalStruk').classList.add('active');
}

function tutupStruk() {
    document.getElementById('modalStruk').classList.remove('active');
}

document.getElementById('modalStruk').addEventListener('click', function(e) {
    if (e.target === this) tutupStruk();
});
</script>
