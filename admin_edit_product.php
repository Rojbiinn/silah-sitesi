<?php
// admin_edit_product.php
if (!isset($_SESSION['uye_role']) || $_SESSION['uye_role'] !== 'admin') {
    echo "<p>Bu sayfaya erişim yetkiniz bulunmamaktadır.</p>";
    return;
}

require_once 'db_config.php';

$product_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$product = null;
$message = '';
$message_type = '';

if (!$product_id) {
    $_SESSION['message'] = "Geçersiz ürün IDsi.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php?sayfa=admin_list_products");
    exit;
}

// Ürün bilgilerini çek
$sql_get_product = "SELECT id, name, description, price, member_price, image_filename FROM products WHERE id = ?";
if ($stmt_get = $mysqli->prepare($sql_get_product)) {
    $stmt_get->bind_param("i", $product_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "Düzenlenecek ürün bulunamadı.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php?sayfa=admin_list_products");
        exit;
    }
    $stmt_get->close();
} else {
    // Hata durumunda, mesajı session'a kaydedip listeleme sayfasına yönlendirelim.
    $_SESSION['message'] = "Veritabanı sorgusu hazırlanamadı: " . $mysqli->error;
    $_SESSION['message_type'] = "error";
    header("Location: index.php?sayfa=admin_list_products");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = filter_var(trim($_POST['price']), FILTER_VALIDATE_FLOAT);
    $member_price = isset($_POST['member_price']) && !empty($_POST['member_price']) ? filter_var(trim($_POST['member_price']), FILTER_VALIDATE_FLOAT) : null;
    
    $current_image_filename = $product['image_filename']; // Mevcut resim
    $new_image_filename = $current_image_filename; // Varsayılan olarak mevcut resmi tut
    $upload_ok = 1;
    $target_dir = "silahlar/";

    if (empty($product_name) || $price === false || $price <= 0) {
        $message = "Ürün adı ve geçerli bir fiyat zorunludur.";
        $message_type = "error";
        $upload_ok = 0; // Temel bilgiler hatalıysa resim yükleme ve db işlemini atla
    }

    // Yeni resim yükleme işlemi (eğer dosya seçildiyse)
    if ($upload_ok && isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if ($check === false) {
            $message = "Yüklenen dosya bir resim değil."; $message_type = "error"; $upload_ok = 0;
        }
        // if (file_exists($target_file)) { /* İsteğe bağlı: Aynı isimde dosya varsa farklı işlem */ }
        if ($_FILES["product_image"]["size"] > 5000000) { // 5MB limit
            $message = "Dosya boyutu çok büyük."; $message_type = "error"; $upload_ok = 0;
        }
        if (!in_array($image_file_type, ["jpg", "png", "jpeg", "gif"])) {
            $message = "Sadece JPG, JPEG, PNG & GIF formatları geçerlidir."; $message_type = "error"; $upload_ok = 0;
        }

        if ($upload_ok) {
            // Dosya adını güvenli hale getir ve benzersizleştir (örn: zaman damgası + orijinal ad)
            $original_filename = basename($_FILES["product_image"]["name"]);
            $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            // Sadece izin verilen karakterleri tut, boşlukları _ ile değiştir
            $safe_basename = preg_replace("/[^a-zA-Z0-9._-]+", "", pathinfo($original_filename, PATHINFO_FILENAME));
            $safe_filename = time() . "_" . $safe_basename . "." . $extension; // Benzersiz bir ad oluştur
            
            $new_target_file = $target_dir . $safe_filename;

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $new_target_file)) {
                // Yeni resim başarıyla yüklendi, eski resmi sil (eğer farklıysa ve boş değilse)
                if (!empty($current_image_filename) && $current_image_filename !== $safe_filename && file_exists($target_dir . $current_image_filename)) {
                    unlink($target_dir . $current_image_filename);
                }
                $new_image_filename = $safe_filename;
            } else {
                $message = "Yeni resim yüklenirken hata."; $message_type = "error"; $upload_ok = 0;
            }
        }
    } elseif (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] != UPLOAD_ERR_NO_FILE) {
        $message = "Resim yüklemede bir hata oluştu: " . $_FILES["product_image"]["error"];
        $message_type = "error";
        $upload_ok = 0;
    }
    
    // Veritabanını güncelle (eğer temel bilgiler geçerliyse ve resim yüklemede kritik hata yoksa)
    if ($upload_ok && !empty($product_name) && $price !== false && $price > 0) {
        $sql_update = "UPDATE products SET name = ?, description = ?, price = ?, member_price = ?, image_filename = ? WHERE id = ?";
        if ($stmt_update = $mysqli->prepare($sql_update)) {
            $stmt_update->bind_param("ssddsi", $product_name, $description, $price, $member_price, $new_image_filename, $product_id);
            if ($stmt_update->execute()) {
                $_SESSION['message'] = "Ürün başarıyla güncellendi.";
                $_SESSION['message_type'] = "success";
                header("Location: index.php?sayfa=admin_list_products");
                exit;
            } else {
                $message = "Veritabanı güncelleme hatası: " . $stmt_update->error;
                $message_type = "error";
            }
            $stmt_update->close();
        } else {
            $message = "Güncelleme sorgusu hazırlanamadı: " . $mysqli->error;
            $message_type = "error";
        }
    } elseif ($upload_ok && (empty($product_name) || $price === false || $price <= 0)){
        $message = "Ürün adı ve fiyat gibi zorunlu alanlar doldurulmadı veya geçersiz.";
        $message_type = "error";
    }
    // Eğer buraya gelinmişse ve bir mesaj varsa, formu tekrar gösterirken bu mesajı kullan.
    // Başarılı güncelleme durumunda zaten exit ile çıkılmış olacak.
    // Formun tekrar yüklenmesinde POST verilerinin kaybolmaması için ürün bilgilerini güncelle
    if(!empty($message)) { // Hata varsa formun güncel değerlerle dolması için
        $product['name'] = $product_name;
        $product['description'] = $description;
        $product['price'] = $price;
        $product['member_price'] = $member_price;
        // image_filename zaten $new_image_filename ile güncelleniyor, db'ye yazılmasa bile formda gösterilir
    }

}

?>
<div class="form-container">
    <h3>Ürünü Düzenle: <?php echo htmlspecialchars($product['name']); ?></h3>
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form action="index.php?sayfa=admin_edit_product&id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">Ürün Adı:</label>
            <input type="text" name="product_name" id="product_name" required value="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="form-group">
            <label for="description">Açıklama:</label>
            <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Fiyat (TL):</label>
            <input type="number" name="price" id="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($product['price']); ?>">
        </div>
        <div class="form-group">
            <label for="member_price">Üye Fiyatı (TL) (Opsiyonel):</label>
            <input type="number" name="member_price" id="member_price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['member_price'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label>Mevcut Resim:</label>
            <?php if (!empty($product['image_filename']) && file_exists('silahlar/' . $product['image_filename'])): ?>
                <img src="silahlar/<?php echo htmlspecialchars($product['image_filename']); ?>" alt="Mevcut Resim" style="max-width: 200px; display:block; margin-bottom:10px;">
            <?php else: ?>
                <p>Mevcut resim yok.</p>
            <?php endif; ?>
            <label for="product_image">Resmi Değiştir (Opsiyonel):</label>
            <input type="file" name="product_image" id="product_image" accept="image/jpeg,image/png,image/gif">
        </div>

        <div class="form-group">
            <button type="submit" name="edit_product" class="btn btn-primary">Değişiklikleri Kaydet</button>
            <a href="index.php?sayfa=admin_list_products" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div> 