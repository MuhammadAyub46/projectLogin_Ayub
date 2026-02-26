<?php
session_start();
$kode_barang = $_GET['kode_barang']??'';
foreach ($_SESSION['products'] as $index => $product) {
    if ($product['kode_barang'] == $kode_barang){
        unset($_SESSION['products'][$index]);
        header("Location: dashboard.php?page=Product/listproducts");
        break;
    }
}
