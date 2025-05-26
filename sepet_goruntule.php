<?php
// session_start(); // Zaten dizi_i.php tarafından başlatılıyor, eğer bu dosya doğrudan çağrılırsa diye eklenebilir.
// require_once 'db_config.php'; // Veritabanı bağlantısı genellikle sepet görüntülemede gerekmez, ama gerekirse eklenebilir.

// Sepet işlemlerini (miktar güncelleme, ürün silme) en başta işle
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity']) && isset($_POST['product_id'])) {
        $product_id_to_update = $_POST['product_id'];
        $new_quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        if ($new_quantity !== false && $new_quantity > 0) {
            if (isset($_SESSION['cart'][$product_id_to_update])) {
                $_SESSION['cart'][$product_id_to_update]['quantity'] = $new_quantity;
                $_SESSION['message'] = "Sepet güncellendi.";
                $_SESSION['message_type'] = "success";
            }
        } elseif ($new_quantity !== false && $new_quantity <= 0) {
            // Miktar 0 veya daha az ise ürünü sepetten çıkar
            unset($_SESSION['cart'][$product_id_to_update]);
            $_SESSION['message'] = "Ürün sepetten çıkarıldı.";
            $_SESSION['message_type'] = "success";
        }
        // Sayfayı yeniden yükleyerek POST verisinin tekrar gönderilmesini engelle
        header("Location: dizi_i.php?sayfa=sepet");
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $product_id_to_remove = $_GET['id'];
    if (isset($_SESSION['cart'][$product_id_to_remove])) {
        $removed_product_name = $_SESSION['cart'][$product_id_to_remove]['name'];
        unset($_SESSION['cart'][$product_id_to_remove]);
        $_SESSION['message'] = htmlspecialchars($removed_product_name) . " sepetten çıkarıldı.";
        $_SESSION['message_type'] = "success";
    }
    // Sayfayı yeniden yükleyerek GET parametrelerinin kalmasını engelle
    header("Location: dizi_i.php?sayfa=sepet");
    exit;
}

?>
<div class="cart-page">
    <h2>Alışveriş Sepetiniz</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p>Sepetinizde henüz ürün bulunmamaktadır. <a href="dizi_i.php">Alışverişe devam et</a>.</p>
    <?php else: ?>
        <form action="dizi_i.php?sayfa=sepet" method="post">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Fiyat</th>
                        <th>Miktar</th>
                        <th>Toplam</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $item_id => $item):
                        $line_total = $item['price'] * $item['quantity'];
                        $grand_total += $line_total;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> TL</td>
                            <td>
                                <form action="dizi_i.php?sayfa=sepet" method="post" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item_id); ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="quantity-input">
                                    <button type="submit" name="update_quantity" class="update-btn">Güncelle</button>
                                </form>
                            </td>
                            <td><?php echo number_format($line_total, 2); ?> TL</td>
                            <td>
                                <a href="dizi_i.php?sayfa=sepet&action=remove&id=<?php echo htmlspecialchars($item_id); ?>" class="remove-btn">Kaldır</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;"><strong>Genel Toplam:</strong></td>
                        <td colspan="2"><strong><?php echo number_format($grand_total, 2); ?> TL</strong></td>
                    </tr>
                </tfoot>
            </table>
        </form>
        <div class="cart-actions">
            <a href="dizi_i.php" class="btn btn-secondary">Alışverişe Devam Et</a>
            <?php if (!empty($_SESSION['cart'])): ?>
            <a href="dizi_i.php?sayfa=checkout" class="btn btn-primary">Ödemeye Geç</a> 
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Stil tanımlamaları css/main_styles.css dosyasına taşındı.
// Aşağıdaki <style> bloğu kaldırıldı.
/*
<style>
.cart-page h2 { ... }
.cart-table { ... }
.cart-table th, .cart-table td { ... }
.cart-table th { ... }
.cart-table .quantity-input { ... }
.cart-table .update-btn, .cart-table .remove-btn { ... }
.cart-table .update-btn { ... }
.cart-table .remove-btn { ... }
.cart-actions { ... }
.cart-actions .btn { ... }
.cart-actions .btn-primary { ... }
.cart-actions .btn-secondary { ... }
</style>
*/
?> 