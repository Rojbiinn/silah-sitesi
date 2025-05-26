<?php
// admin_add_product.php
if (!isset($_SESSION['uye_role']) || $_SESSION['uye_role'] !== 'admin') {
    echo "<p>Bu sayfaya erişim yetkiniz bulunmamaktadır.</p>";
    return;
}

require_once 'db_config.php'; // Veritabanı bağlantısı için

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = filter_var(trim($_POST['price']), FILTER_VALIDATE_FLOAT);
    $member_price = isset($_POST['member_price']) && !empty($_POST['member_price']) ? filter_var(trim($_POST['member_price']), FILTER_VALIDATE_FLOAT) : null;
    
    $image_filename = null;
    $upload_ok = 1;
    $target_dir = "silahlar/"; // Resimlerin yükleneceği klasör

    // Temel doğrulamalar
    if (empty($product_name) || $price === false || $price <= 0) {
        $message = "Ürün adı ve geçerli bir fiyat zorunludur.";
        $message_type = "error";
        $upload_ok = 0;
    }

    // Resim yükleme işlemi
    if ($upload_ok && isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Gerçek bir resim olup olmadığını kontrol et
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if ($check === false) {
            $message = "Yüklenen dosya bir resim değil.";
            $message_type = "error";
            $upload_ok = 0;
        }

        // Dosya zaten var mı kontrol et (isteğe bağlı, üzerine yazabilir veya adını değiştirebilirsiniz)
        // if (file_exists($target_file)) {
        //     $message = "Aynı ada sahip bir dosya zaten mevcut.";
        //     $message_type = "error";
        //     $upload_ok = 0;
        // }

        // Dosya boyutu limiti (örneğin 5MB)
        if ($_FILES["product_image"]["size"] > 5000000) {
            $message = "Dosya boyutu çok büyük (maksimum 5MB).";
            $message_type = "error";
            $upload_ok = 0;
        }

        // İzin verilen dosya formatları
        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif") {
            $message = "Sadece JPG, JPEG, PNG & GIF formatlarındaki dosyalara izin verilmektedir.";
            $message_type = "error";
            $upload_ok = 0;
        }

        if ($upload_ok) {
            // Dosya adını güvenli hale getir (isteğe bağlı, benzersiz bir ad oluşturmak daha iyi olabilir)
            $safe_filename = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES["product_image"]["name"]));
            $target_file = $target_dir . $safe_filename;
            
            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_filename = $safe_filename;
            } else {
                $message = "Resim yüklenirken bir hata oluştu.";
                $message_type = "error";
                $upload_ok = 0; // Veritabanına kaydetmeyi engelle
            }
        }
    } elseif (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] != UPLOAD_ERR_NO_FILE && $_FILES["product_image"]["error"] != 0) {
        // Dosya seçilmiş ama yükleme hatası var (NO_FILE dışında bir hata)
        $message = "Resim yüklenirken bir hata oluştu. Hata kodu: " . $_FILES["product_image"]["error"];
        $message_type = "error";
        $upload_ok = 0;
    }

    // Veritabanına kaydet
    if ($upload_ok && !empty($product_name) && $price !== false && $price > 0) {
        $sql = "INSERT INTO products (name, description, price, member_price, image_filename) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssdds", $product_name, $description, $price, $member_price, $image_filename);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Ürün başarıyla eklendi.";
                $_SESSION['message_type'] = "success";
                header("Location: index.php?sayfa=admin_panel"); // Veya ürün listeleme sayfasına
                exit;
            } else {
                $message = "Veritabanına kayıt sırasında hata: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "Veritabanı sorgusu hazırlanamadı: " . $mysqli->error;
            $message_type = "error";
        }
    } elseif ($upload_ok && (empty($product_name) || $price === false || $price <= 0)){
        // Bu durum zaten yukarıda $upload_ok = 0 ile yakalanmalı ama ek kontrol
        $message = "Ürün adı ve fiyat gibi zorunlu alanlar doldurulmadı veya geçersiz.";
        $message_type = "error";
    }
    // $mysqli->close(); // $mysqli dizi_i.php sonunda kapatılıyor.
}

// Admin mesajlarını göster (eğer admin paneline yönlendirme sonrası geliyorsa)
if (isset($_SESSION['admin_message'])) {
    /*
    $message = $_SESSION['admin_message'];
    $message_type = $_SESSION['admin_message_type'];
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
    */
}

?>
<div class="form-container">
    <h3>Yeni Ürün Ekle</h3>
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form action="index.php?sayfa=admin_add_product" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">Ürün Adı:</label>
            <input type="text" name="product_name" id="product_name" required value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="description">Açıklama:</label>
            <textarea name="description" id="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Fiyat (TL):</label>
            <input type="number" name="price" id="price" step="0.01" min="0" required value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="member_price">Üye Fiyatı (TL) (Opsiyonel):</label>
            <input type="number" name="member_price" id="member_price" step="0.01" min="0" value="<?php echo isset($_POST['member_price']) ? htmlspecialchars($_POST['member_price']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="product_image">Ürün Resmi:</label>
            <input type="file" name="product_image" id="product_image" accept="image/jpeg,image/png,image/gif">
        </div>
        <div class="form-group">
            <button type="submit" name="add_product" class="btn btn-success">Ürünü Ekle</button>
        </div>
    </form>
</div>

<?php
// Stil tanımlamaları css/main_styles.css dosyasına taşındı.
/*
<style>
.add-product-container { ... }
.add-product-container h3 { ... }
.product-form div { ... }
.product-form label { ... }
.product-form input[type="text"], ... { ... }
.product-form textarea { ... }
.product-form .btn { ... }
.product-form .btn:hover { ... }
.message.success { ... }
.message.error { ... }
</style>
*/
?> 