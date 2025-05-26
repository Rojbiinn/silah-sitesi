<?php
session_start();
require_once 'db_config.php'; // Veritabanı bağlantısı için.

// Gelen ürün bilgilerini al ve temizle
$product_id = isset($_GET['urun_no']) ? trim($_GET['urun_no']) : null;
$product_name = isset($_GET['urun_adi']) ? trim(urldecode($_GET['urun_adi'])) : null; // urldecode ile çöz
$product_price = isset($_GET['urun_fiyati']) ? filter_var(trim($_GET['urun_fiyati']), FILTER_VALIDATE_FLOAT) : null;

// Temel doğrulama
if ($product_id === null || $product_name === null || $product_price === false || $product_price <= 0) {
    // Gerekli parametreler eksik veya geçersizse bir hata mesajı gösterip yönlendirme yapılabilir.
    // Veya sadece ana sayfaya yönlendir.
    $_SESSION['message'] = "Sepete ekleme sırasında bir sorun oluştu: Geçersiz ürün bilgileri.";
    $_SESSION['message_type'] = "error";
    header('Location: dizi_i.php'); // Ana sayfaya veya ürünler sayfasına yönlendir
    exit;
}

// Sepet session'da yoksa oluştur
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Ürün sepette zaten var mı kontrol et
if (isset($_SESSION['cart'][$product_id])) {
    // Varsa, miktarını artır
    $_SESSION['cart'][$product_id]['quantity']++;
    $_SESSION['message'] = htmlspecialchars($product_name) . " sepetinize tekrar eklendi (Miktar: " . $_SESSION['cart'][$product_id]['quantity'] . ").";
} else {
    // Yoksa, yeni ürün olarak ekle
    $_SESSION['cart'][$product_id] = array(
        'id' => $product_id,
        'name' => $product_name,
        'price' => $product_price,
        'quantity' => 1
    );
    $_SESSION['message'] = htmlspecialchars($product_name) . " sepetinize eklendi.";
}
$_SESSION['message_type'] = "success";

// Kullanıcıyı sepet sayfasına veya ürünlerin olduğu sayfaya yönlendir
// Örnek: header('Location: dizi_i.php?sayfa=sepet'); // Sepet sayfasına yönlendir
header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dizi_i.php')); // Önceki sayfaya veya ana sayfaya yönlendir
exit;
?> 