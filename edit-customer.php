<h2>Edit Customer</h2>

<?php
// Ambil ID dari URL
$id_customer_url = $_GET['id_pelanggan'] ?? '';
$customer_edit = null;

// Cari data customer berdasarkan id_pelanggan
foreach ($_SESSION['customer'] as $index => $customer) {
    if ($customer['id_pelanggan'] == $id_customer_url) {
        $customer_edit = $_SESSION['customer'][$index];
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_customer = $_POST['kode_customer'] ?? '';
    $nama_customer = $_POST['nama_customer'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    $email = $_POST['email'] ?? '';

    // Update data di dalam session
    foreach ($_SESSION['customer'] as $index => $customer) {
        if ($customer['id_pelanggan'] == $id_customer_url) {
            $_SESSION['customer'][$index] = [
                'id_pelanggan'   => $id_customer_url,// pakai ID lama!
                'kode_pelanggan' => $kode_customer,
                'nama_pelanggan' => $nama_customer,
                'alamat'         => $alamat,
                'no_hp'          => $no_hp,
                'email'          => $email
            ];
            break;
        }
    }
    header("Location: dashboard.php?page=Customer/customer");
}
?>

<style>
/* Card */
.card {
    background: #ffffff;
    padding: 30px;
    border-radius: 10px;
    max-width: 720px;
    margin-right: auto;
    margin-left: 0;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

/* Judul */
.card h3 {
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

/* Form */
.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
}

input, textarea {
    width: 100%;
    background-color: white;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

/* Style khusus untuk input yang tidak boleh diubah */
input[readonly] {
    background-color: #f4f4f4;
    cursor: not-allowed;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #3498db;
}

/* Tombol */
.btn {
    padding: 10px 16px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
    display: inline-block;
}

.btn-tambah {
    background: #27ae60;
}

.btn-tambah:hover {
    background: #219150;
}

.btn-hapus {
    background: #c0392b;
}

.btn-hapus:hover {
    background: #a93226;
}
</style>

<div class="card">
    <h3>Edit Customer</h3>

    <form method="post">
        <div class="form-group">
            <label>ID Customer</label>
            <input type="number" name="id_customer" value="<?= $customer_edit['id_pelanggan'] ?? '' ?>" readonly>
        </div>

        <div class="form-group">
            <label>Kode Customer</label>
            <input type="text" name="kode_customer" value="<?= $customer_edit['kode_pelanggan'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <label>Nama Customer</label>
            <input type="text" name="nama_customer" value="<?= $customer_edit['nama_pelanggan'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="3" required><?= $customer_edit['alamat'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label>No HP</label>
            <input type="text" name="no_hp" value="<?= $customer_edit['no_hp'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= $customer_edit['email'] ?? '' ?>" required>
        </div>

        <button type="submit" name="simpan" class="btn btn-tambah">Simpan Perubahan</button>
        <a href="dashboard.php?page=Customer/customer" class="btn btn-hapus">Batal</a>
    </form>
</div>
