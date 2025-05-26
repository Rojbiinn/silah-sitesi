<?php
// session_start(); // Already started by dizi_i.php
// require_once 'db_config.php'; // May be needed if interacting with DB

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION["uye"])) {
    $_SESSION['message'] = "Ödeme yapmak için lütfen giriş yapınız.";
    $_SESSION['message_type'] = "error";
    header("Location: dizi_i.php?sayfa=uye_giris"); // Giriş sayfasına yönlendir
    exit;
}

// Sepet boş mu kontrol et
if (empty($_SESSION['cart'])) {
    $_SESSION['message'] = "Sepetiniz boş. Ödeme yapamazsınız.";
    $_SESSION['message_type'] = "error";
    header("Location: dizi_i.php?sayfa=sepet"); // Sepet sayfasına yönlendir
    exit;
}

$order_message = '';
$order_message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // === SİPARİŞİ VERİTABANINA KAYDETME MANTIĞI (GERÇEK UYGULAMA) ===
    // 1. `db_config.php` dosyasını dahil et (henüz edilmediyse).
    //    require_once 'db_config.php';

    // 2. Gerekli verileri topla:
    //    $user_id = $_SESSION['uye_id'];
    //    $shipping_fullname = trim($_POST['fullname']);
    //    $shipping_address = trim($_POST['address']);
    //    $shipping_phone = trim($_POST['phone']);
    //    $grand_total = 0; // Sepet üzerinden tekrar hesapla veya session'dan al (güvenlik için tekrar hesapla)
    //    // foreach ($_SESSION['cart'] as $item) { $grand_total += $item['price'] * $item['quantity']; }

    // 3. Veritabanı işlemi (transaction kullanmak iyi bir pratik olacaktır):
    //    $mysqli->begin_transaction();
    //    try {
    //        // a. `orders` tablosuna yeni bir kayıt ekle
    //        $sql_order = "INSERT INTO orders (user_id, total_amount, shipping_fullname, shipping_address, shipping_phone) VALUES (?, ?, ?, ?, ?)";
    //        // $stmt_order = $mysqli->prepare($sql_order);
    //        // $stmt_order->bind_param("idsss", $user_id, $grand_total, $shipping_fullname, $shipping_address, $shipping_phone);
    //        // $stmt_order->execute();
    //        // $order_id = $mysqli->insert_id; // Eklenen siparişin ID'sini al
    //        // $stmt_order->close();
    //
    //        // b. `order_items` tablosuna sepetteki her ürün için kayıt ekle
    //        $sql_order_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
    //        // $stmt_item = $mysqli->prepare($sql_order_item);
    //        // foreach ($_SESSION['cart'] as $product_id_in_cart => $item) {
    //        //     $stmt_item->bind_param("iiid", $order_id, $product_id_in_cart, $item['quantity'], $item['price']);
    //        //     $stmt_item->execute();
    //        // }
    //        // $stmt_item->close();
    //
    //        // c. (İsteğe bağlı) Stok güncellemesi yapılabilir `products` tablosunda.
    //
    //        $mysqli->commit(); // Her şey yolundaysa işlemi onayla
    //
    //        // d. Sepeti temizle
    //        unset($_SESSION['cart']);
    //        $_SESSION['message'] = "Siparişiniz başarıyla alındı!";
    //        $_SESSION['message_type'] = "success";
    //        header("Location: dizi_i.php?sayfa=siparis_detay&id=" . $order_id); // Veya siparişlerim sayfasına
    //        exit;
    //    } catch (mysqli_sql_exception $exception) {
    //        $mysqli->rollback(); // Bir hata olursa işlemi geri al
    //        $_SESSION['message'] = "Siparişiniz işlenirken bir hata oluştu: " . $exception->getMessage();
    //        $_SESSION['message_type'] = "error";
    //        // header("Location: dizi_i.php?sayfa=checkout"); // Checkout sayfasına geri yönlendir
    //        // exit;
    //        // Şimdilik simulasyon devam ediyor:
    //        $order_message = "Siparişiniz işlenirken bir hata oluştu (simülasyon): " . $exception->getMessage();
    //        $order_message_type = "error";
    //    }
    // === SİPARİŞ KAYDETME MANTIĞI SONU ===

    // Şimdilik sadece bir başarı mesajı gösterelim ve sepeti temizleyelim (SİMÜLASYON)
    unset($_SESSION['cart']);
    $_SESSION['message'] = "Siparişiniz başarıyla alındı! (Bu bir simülasyondur)";
    $_SESSION['message_type'] = "success";
    header("Location: dizi_i.php"); // Ana sayfaya veya siparişlerim sayfasına yönlendir
    exit;
}

?>
<div class="checkout-page form-container">
    <h2>Ödeme Sayfası</h2>

    <p style="text-align:center;">Hoşgeldiniz, <strong><?php echo htmlspecialchars($_SESSION["uye"]); ?></strong>. Siparişinizi tamamlamak üzeresiniz.</p>

    <h3>Sipariş Özeti</h3>
    <?php if (!empty($_SESSION['cart'])):
        $grand_total = 0;
    ?>
    <table class="cart-table summary-table">
        <thead>
            <tr>
                <th>Ürün Adı</th>
                <th>Fiyat</th>
                <th>Miktar</th>
                <th>Toplam</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($_SESSION['cart'] as $item): 
            $line_total = $item['price'] * $item['quantity'];
            $grand_total += $line_total;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo number_format($item['price'], 2); ?> TL</td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($line_total, 2); ?> TL</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;"><strong>Genel Toplam:</strong></td>
                <td><strong><?php echo number_format($grand_total, 2); ?> TL</strong></td>
            </tr>
        </tfoot>
    </table>

    <form action="dizi_i.php?sayfa=checkout" method="POST" class="checkout-form-fields">
        <h4>Teslimat Bilgileri</h4>
        <div class="form-group">
            <label for="fullname">Ad Soyad:</label>
            <input type="text" name="fullname" id="fullname" required>
        </div>
        <div class="form-group">
            <label for="address">Adres:</label>
            <textarea name="address" id="address" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="phone">Telefon:</label>
            <input type="tel" name="phone" id="phone" required>
        </div>
        
        <button type="submit" name="place_order" class="btn btn-success" style="width:100%; padding:12px; font-size:1.1em;">Siparişi Onayla (Simülasyon)</button>
    </form>

    <?php else: ?>
        <p>Özetlenecek bir sepet bulunamadı.</p>
    <?php endif; ?>
    <br>
    <div style="text-align:center;">
        <a href="dizi_i.php?sayfa=sepet" class="btn btn-secondary">Sepete Geri Dön</a>
    </div>
</div>

<?php
// Stil tanımlamaları css/main_styles.css dosyasına taşındı.
/*
<style>
.checkout-page h2, .checkout-page h3, .checkout-page h4 { ... }
.summary-table { ... }
.checkout-form { ... }
.checkout-form div { ... }
.checkout-form label { ... }
.checkout-form input[type="text"], ... { ... }
.checkout-form .btn-primary { ... }
.checkout-form .btn-primary:hover { ... }
.checkout-page .btn-secondary { ... }
</style>
*/
?> 