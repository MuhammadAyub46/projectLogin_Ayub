<h2>List Customer</h2>
 <style>
    .card {
        background: white;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .btn {
        padding: 8px 12px;
        text-decoration: none;
        border-radius: 4px;
        color: white;
        font-size: 14px;
    }

    .btn-tambah {
        background: #27ae60;
    }

    .btn-edit {
        background: #2980b9;
    }

    .btn-hapus {
        background: #c0392b;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: center;
    }

    th {
        background: #f8f8f8;
    }
</style>

<div class="card">
    <div class= "card-header">
        <h3> List Customer </h3>
        <a href= "dashboard.php?page=Customer/tambah-customer" class="btn btn-tambah">+ Tambah Customer</a>
</div>
<table>
    <tr>
        <th>No</th>
        <th>ID Customer</th> <th>Kode Customer</th>
        <th>Nama Customer</th>
        <th>Alamat</th>
        <th>No HP</th>
        <th>Email</th>
        <th>Aksi</th>
</tr>

<?php
if (!empty($_SESSION['customer'])) {
    $i = 1;
    foreach ($_SESSION['customer'] as $customer) {
        echo "<tr>";
        echo "<td>" . $i++ . "</td>";
        echo "<td>" . $customer['id_pelanggan'] . "</td>"; // Penambahan kolom ID
        echo "<td>" . $customer['kode_pelanggan'] . "</td>";
        echo "<td>" . $customer['nama_pelanggan'] . "</td>";
        echo "<td>" . $customer['alamat'] . "</td>";
        echo "<td>" . $customer['no_hp'] . "</td>";
        echo "<td>" . $customer['email'] . "</td>";
        echo "<td>
                <a href='dashboard.php?page=Customer/edit-customer&id_pelanggan=" . $customer['id_pelanggan'] . "' class='btn btn-edit'>Edit</a>
                <a href='dashboard.php?page=Customer/delete-customer&id_pelanggan=" . $customer['id_pelanggan'] . "' class='btn btn-hapus'
                onclick=\"return confirm('Yakin hapus data?')\">
                    Hapus
                </a>
              </td>";
        echo "</tr>";
        $i + 1;
    }
} else {
    echo "<tr><td colspan='8'>Belum Ada Customer</td></tr>"; // Colspan jadi 8 karena tambah 1 kolom
}
?>
</table>
</div>
