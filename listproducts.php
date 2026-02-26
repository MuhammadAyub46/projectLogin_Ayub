<h2>List Product</h2>
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
        <h3> List Produk </h3>
        <a href= "dashboard.php?page=Product/tambah-product" class="btn btn-tambah">+ Tambah Produk</a>
</div>
<table>
    <tr>
        <th>No</th>
        <th>Kode</th>
        <th>Nama Produk</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Stok</th>
        <th>Satuan</th>
        <th>Aksi</th>
</tr>

<!-- <tr>
    <td>1</td>
     <td>K01</td>
 <td>Crystalin</td>
  <td>Minuman</td>
 <td>Rp. 3.000</td>
  <td>10</td>
   <td>pcs</td>

   <td>
    <a href="" class="btn btn-edit">Edit</a>
    <a href="" class="btn btn-hapus">Hapus</a>
    <onclick="return confirm>

</a>
</td>
</tr>  -->


<?php
if (!empty($_SESSION['products'])) {
    $i = 1;
    foreach ($_SESSION['products'] as $product) {
        echo "<tr>";
        echo "<td>" . $i++ . "</td>";
        echo "<td>" . $product['kode_barang'] . "</td>";
        echo "<td>" . $product['nama_barang'] . "</td>";
        echo "<td>" . $product['kategori'] . "</td>";
        echo "<td>Rp " . number_format($product['harga'], 0, ',', '.') . "</td>";
        echo "<td>" . $product['stok'] . "</td>";
        echo "<td>" . $product['satuan'] . "</td>";
        echo "<td>
                <a href='dashboard.php?page=Product/edit-product&kode_barang=" . $product['kode_barang'] . "' class='btn btn-edit'>Edit</a>
                <a href='dashboard.php?page=Product/delete-product&kode_barang=" . $product['kode_barang'] . "' class='btn btn-hapus'
                onclick=\"return confirm('Yakin hapus data?')\">
                    Hapus
                </a>
              </td>";
        echo "</tr>";
        $i + 1; // Baris ini ada di gambar, namun sebenarnya tidak diperlukan karena $i sudah ditambah di atas ($i++)
    }
} else {
    echo "<tr><td colspan='8'>Belum Ada Produk</td></tr>";
}
?>
</table>
</div>
