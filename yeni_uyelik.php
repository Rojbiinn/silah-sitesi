<?php
// session_start(); // Already started by dizi_i.php, or start if accessed directly.
// require_once 'db_config.php'; // Included further down if needed.

$registration_message = '';
$registration_message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    require_once 'db_config.php';

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);
    // E-posta gibi ek alanlar eklenebilir.
    // $email = trim($_POST['email']); 

    // Doğrulamalar
    if (empty($username) || empty($password) || empty($password_confirm)) {
        $registration_message = "Tüm alanlar zorunludur.";
        $registration_message_type = "error";
    } elseif (strlen($username) > 100) { // Max username length based on DB
        $registration_message = "Kullanıcı adı çok uzun (maksimum 100 karakter).";
        $registration_message_type = "error";
    } elseif (strlen($password) < 6) { // Örnek: minimum şifre uzunluğu
        $registration_message = "Şifre en az 6 karakter olmalıdır.";
        $registration_message_type = "error";
    } elseif ($password !== $password_confirm) {
        $registration_message = "Şifreler eşleşmiyor.";
        $registration_message_type = "error";
    } else {
        // Kullanıcı adı veritabanında var mı kontrol et
        $sql_check_user = "SELECT id FROM users WHERE username = ?"; // Tablo: users, Sütun: username
        if ($stmt_check = $mysqli->prepare($sql_check_user)) {
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $registration_message = "Bu kullanıcı adı zaten alınmış.";
                $registration_message_type = "error";
            }
            $stmt_check->close();
        }

        // Hata yoksa kullanıcıyı kaydet
        if (empty($registration_message)) {
            // Şifreyi hash'le (PHP 5.5+ için password_hash)
            $hashed_password_to_store = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert_user = "INSERT INTO users (username, password_hash) VALUES (?, ?)"; // Tablo: users, Sütunlar: username, password_hash
            if ($stmt_insert = $mysqli->prepare($sql_insert_user)) {
                $stmt_insert->bind_param("ss", $username, $hashed_password_to_store);
                if ($stmt_insert->execute()) {
                    $_SESSION['message'] = "Yeni üyelik başarıyla oluşturuldu. Şimdi giriş yapabilirsiniz.";
                    $_SESSION['message_type'] = "success";
                    // Giriş sayfasına veya ana sayfaya yönlendir
                    header("location: dizi_i.php?sayfa=uye_giris");
                    exit;
                } else {
                    $registration_message = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
                    $registration_message_type = "error";
                }
                $stmt_insert->close();
            } else {
                $registration_message = "Veritabanı hatası. Lütfen tekrar deneyin.";
                $registration_message_type = "error";
            }
        }
    }
    $mysqli->close();
}
?>

<div class="form-container">
    <h3>Yeni Üyelik Oluştur</h3>
    <?php if (!empty($registration_message)): ?>
        <div class="message <?php echo $registration_message_type; ?>"><?php echo $registration_message; ?></div>
    <?php endif; ?>
    <form action="index.php?sayfa=yeni_uyelik" method="post">
        <div class="form-group">
            <label for="reg_username">Kullanıcı Adı:</label>
            <input type="text" name="username" id="reg_username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="reg_password">Şifre (en az 6 karakter):</label>
            <input type="password" name="password" id="reg_password" required>
        </div>
        <div class="form-group">
            <label for="reg_password_confirm">Şifre Tekrar:</label>
            <input type="password" name="password_confirm" id="reg_password_confirm" required>
        </div>
        <div class="form-group">
            <button type="submit" name="register" class="btn btn-success">Kayıt Ol</button>
        </div>
    </form>
    <p style="text-align: center; margin-top:15px;">Zaten hesabınız var mı? <a href="index.php?sayfa=uye_giris">Giriş yapın</a>.</p>
</div>

<?php
// Stil tanımlamaları css/main_styles.css dosyasına taşındı.
// Formlar için .form-container, .form-group, .btn sınıfları kullanılacak.
/*
<style>
.registration-container { ... }
.registration-form div { ... }
.registration-form label { ... }
.registration-form input[type="text"], ... { ... }
.registration-form .btn { ... }
.registration-form .btn:hover { ... }
.registration-container p { ... }
</style>
*/
?> 