<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Ürünler</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php
require_once 'db_config.php'; // Veritabanı bağlantısını dahil et

echo '<div class="product-container">';

// Veritabanından ürünleri çek
// ÖNEMLİ: Bu SQL sorgusu ürün tablosunun sütunlarının `id`, `name`, `price` ve `image_filename` olduğunu varsayar.
// Gerçek tablo yapınıza göre bu adları güncellemeniz gerekebilir.
$sql = "SELECT id, name, price, image_filename FROM products"; // Tablo: products, Sütunlar: name, price, image_filename

if ($stmt = $mysqli->prepare($sql)) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // image_filename zaten dosya adını içeriyor (.jpg dahil)
                $image_path = "silahlar/" . htmlspecialchars(basename($row['image_filename']));

                echo "<div class='product-card'>";
                if (file_exists($image_path)) {
                    echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($row['name']) . "'>";
                } else {
                    echo "<img src='silahlar/placeholder.jpg' alt='Resim Yok'>";
                }
                echo "<div class='product-name'>" . htmlspecialchars($row['name']) . "</div>";
                echo "<div class='product-price'>" . htmlspecialchars(number_format($row['price'], 2)) . " TL</div>";
                echo "</div>";
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

echo '</div>'; // product-container sonu

$mysqli->close(); // Veritabanı bağlantısını kapat
?>

</body>
</html>