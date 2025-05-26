<?php
// admin_list_products.php
if (!isset($_SESSION['uye_role']) || $_SESSION['uye_role'] !== 'admin') {
    echo "<p>Bu sayfaya erişim yetkiniz bulunmamaktadır.</p>";
    return;
}

require_once 'db_config.php';

$message = '';
$message_type = '';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id_to_delete = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($product_id_to_delete) {
        // Önce ürünün resim dosyasını al (silmek için)
        $sql_get_image = "SELECT image_filename FROM products WHERE id = ?";
        if ($stmt_get_image = $mysqli->prepare($sql_get_image)) {
            $stmt_get_image->bind_param("i", $product_id_to_delete);
            $stmt_get_image->execute();
            $stmt_get_image->bind_result($image_to_delete);
            $stmt_get_image->fetch();
            $stmt_get_image->close();

            // Ürünü veritabanından sil
            $sql_delete = "DELETE FROM products WHERE id = ?";
            if ($stmt_delete = $mysqli->prepare($sql_delete)) {
                $stmt_delete->bind_param("i", $product_id_to_delete);
                if ($stmt_delete->execute()) {
                    // Resim dosyasını sil (varsa)
                    if (!empty($image_to_delete) && file_exists("silahlar/" . $image_to_delete)) {
                        unlink("silahlar/" . $image_to_delete);
                    }
                    $_SESSION['message'] = "Ürün başarıyla silindi.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Ürün silinirken bir hata oluştu: " . $stmt_delete->error;
                    $_SESSION['message_type'] = "error";
                }
                $stmt_delete->close();
            } else {
                $_SESSION['message'] = "Silme sorgusu hazırlanamadı: " . $mysqli->error;
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Resim bilgisi alınamadı: " . $mysqli->error;
            $_SESSION['message_type'] = "error";
        }
        // Mesajı gösterdikten sonra GET parametrelerini temizlemek için yönlendirme
        header("Location: index.php?sayfa=admin_list_products");
        exit;
    }
}

// Ürünleri çek
$products = [];
$sql_get_products = "SELECT id, name, price, member_price, image_filename FROM products ORDER BY id DESC";
if ($result = $mysqli->query($sql_get_products)) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $result->free();
} else {
    $message = "Ürünler çekilirken bir hata oluştu: " . $mysqli->error;
    $message_type = "error";
}

?>
<div class="admin-content-container form-container">
    <h3>Ürünleri Listele / Yönet</h3>

    <?php
    // Session mesajlarını göster (dizi_i.php zaten yapıyor ama silme sonrası hemen görmek için eklenebilir)
    if (isset($_SESSION['message'])) {
        echo "<div class=\"message " . htmlspecialchars($_SESSION['message_type']) . "\">" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    } elseif (!empty($message)) { // Sadece bu sayfada oluşan hatalar için
         echo "<div class=\"message " . htmlspecialchars($message_type) . "\">" . htmlspecialchars($message) . "</div>";
    }
    ?>

    <a href="index.php?sayfa=admin_add_product" class="btn btn-success" style="margin-bottom: 20px;">Yeni Ürün Ekle</a>

    <?php if (!empty($products)): ?>
    <table class="cart-table"> <!-- Varolan .cart-table stilini kullanabiliriz -->
        <thead>
            <tr>
                <th>ID</th>
                <th>Resim</th>
                <th>Ürün Adı</th>
                <th>Fiyat</th>
                <th>Üye Fiyatı</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['id']); ?></td>
                <td>
                    <?php if (!empty($product['image_filename']) && file_exists('silahlar/' . $product['image_filename'])): ?>
                        <img src="silahlar/<?php echo htmlspecialchars($product['image_filename']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: auto;">
                    <?php else: ?>
                        <small>Resim Yok</small>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo number_format($product['price'], 2); ?> TL</td>
                <td><?php echo $product['member_price'] ? number_format($product['member_price'], 2) . ' TL' : '-'; ?></td>
                <td>
                    <a href="index.php?sayfa=admin_edit_product&id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">Düzenle</a>
                    <a href="index.php?sayfa=admin_list_products&action=delete&id=<?php echo $product['id']; ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">Sil</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>Gösterilecek ürün bulunamadı.</p>
    <?php endif; ?>
</div>
<style>
/* Butonların tabloda daha iyi görünmesi için küçük bir ayar */
.cart-table .btn-sm {
    padding: 5px 10px;
    font-size: 0.85em;
    margin-right: 5px;
}
.admin-content-container { /* form-container stilini alması için */
    width: 95%;
    max-width: 1000px; /* Daha geniş olabilir listeleme için */
}
</style> 