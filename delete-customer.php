<?php
// session_start();
$id_customer = $_GET['id_pelanggan'] ?? '';

foreach ($_SESSION['customer'] as $index => $customer) {
    if ($customer['id_pelanggan'] == $id_customer) {
        unset($_SESSION['customer'][$index]);
        // Re-index array agar urutan key session tetap rapi
        $_SESSION['customer'] = array_values($_SESSION['customer']);

        header("Location: dashboard.php?page=Customer/customer");
        break;
    }
}
?>
