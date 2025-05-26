<?php
@session_start(); // Session'ı en başta başlat
require_once 'db_config.php'; // Veritabanı bağlantısını dahil et
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ürün Listesi</title>
    <link rel="stylesheet" href="css/styles.css"> 
</head>
<body>

<div class="product-container"> 
<?php
// Ürünleri veritabanından çek
// ÖNEMLİ: Bu sorgu, ürün tablosunun 'id', 'name', 'price', 'member_price' ve 'image_filename' sütunlarına sahip olduğunu varsayar.
$sql = "SELECT id, name, price, member_price, image_filename FROM products"; // Tablo ve sütun adları güncellendi

if ($stmt = $mysqli->prepare($sql)) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $product_id = htmlspecialchars($row['id']);
                $product_name = htmlspecialchars($row['name']);
                $regular_price = htmlspecialchars($row['price']);
                $member_price = isset($row['member_price']) ? htmlspecialchars($row['member_price']) : null;
                $image_filename = htmlspecialchars(basename($row['image_filename']));

                $image_path = "silahlar/" . $image_filename;

                echo "<div class='product-card'>";
                if (file_exists($image_path)) {
                    echo "<img src='" . $image_path . "' alt='" . $product_name . "'>";
                } else {
                    echo "<img src='silahlar/placeholder.jpg' alt='Resim Yok'>";
                }
                echo "<div class='product-name'>" . $product_name . "</div>";

                $display_price = $regular_price;
                $price_to_use_for_cart = $regular_price;

                if (isset($_SESSION["uye"]) && $member_price !== null) {
                    echo "<div class='product-price original-price'><del>" . number_format($regular_price, 2) . " TL</del></div>";
                    echo "<div class='product-price member-price'>" . number_format($member_price, 2) . " TL (Üye Fiyatı)</div>";
                    $price_to_use_for_cart = $member_price;
                } else {
                    echo "<div class='product-price'>" . number_format($regular_price, 2) . " TL</div>";
                }
                echo "<a href='sepete_ekle.php?urun_no=" . $product_id . "&urun_adi=" . urlencode($product_name) . "&urun_fiyati=" . $price_to_use_for_cart . "' class='add-to-cart-btn'>Sepete Ekle</a>";
                echo "</div>"; // product-card sonu
            }
        } else {
            echo "<p>Gösterilecek ürün bulunamadı.</p>";
        }
    } else {
        echo "<p>Hata: Sorgu çalıştırılamadı. (" . $stmt->errno . ") " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p>Hata: Sorgu hazırlanamadı. (" . $mysqli->errno . ") " . $mysqli->error . "</p>";
}

$mysqli->close(); // Veritabanı bağlantısını kapat
?>
</div> 

</body>
</html>