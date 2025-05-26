<?php
// session_start(); // Already started by dizi_i.php if included, or start if accessed directly.
// If not already started (e.g. direct access), uncomment the line above.
// require_once 'db_config.php'; // Included further down if needed.

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    require_once 'db_config.php'; // Need DB for login

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $login_error = "Kullanıcı adı ve şifre boş bırakılamaz.";
    } else {
        // Kullanıcıyı veritabanında ara (KULLANICI ADI BENZERSİZ OLMALI)
        // Güvenlik: SQL Injection'ı önlemek için prepared statement kullanın.
        // Şifreler veritabanında HASH'LENMİŞ olarak saklanmalıdır.
        $sql = "SELECT id, username, password_hash, role FROM users WHERE username = ?"; // role sütununu da çek

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $db_username, $hashed_password_from_db, $user_role); // $user_role'ü bind et
                    if ($stmt->fetch()) {
                        // Şifreyi doğrula
                        if (password_verify($password, $hashed_password_from_db)) {
                            // Şifre doğru, session başlat ve kullanıcıyı kaydet
                            if (session_status() == PHP_SESSION_NONE) {
                                session_start(); // Ensure session is started before using $_SESSION
                            }
                            $_SESSION["uye"] = $db_username; 
                            $_SESSION["uye_id"] = $id;
                            $_SESSION["uye_role"] = $user_role; // Kullanıcı rolünü session'a kaydet
                            
                            $_SESSION['message'] = "Başarıyla giriş yaptınız, Hoşgeldiniz " . htmlspecialchars($db_username) . "!";
                            $_SESSION['message_type'] = "success";
                            
                            // Ana sayfaya yönlendir
                            header("location: dizi_i.php");
                            exit;
                        } else {
                            $login_error = "Geçersiz kullanıcı adı veya şifre.";
                        }
                    }
                } else {
                    $login_error = "Geçersiz kullanıcı adı veya şifre.";
                }
            } else {
                $login_error = "Oops! Bir şeyler ters gitti. Lütfen daha sonra tekrar deneyin.";
            }
            $stmt->close();
        }
        $mysqli->close();
    }
}
?>

<div class="form-container">
    <h3>Üye Girişi</h3>
    <?php if (!empty($login_error)): ?>
        <div class="message error"><?php echo $login_error; ?></div>
    <?php endif; ?>
    <form action="index.php?sayfa=uye_giris" method="post">
        <div class="form-group">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" id="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="password">Şifre:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <button type="submit" name="login" class="btn btn-primary">Giriş Yap</button>
        </div>
    </form>
    <p style="text-align: center; margin-top:15px;">Hesabınız yok mu? <a href="index.php?sayfa=yeni_uyelik">Yeni üyelik oluşturun</a>.</p>
</div>

<?php
// Stil tanımlamaları css/main_styles.css dosyasına taşındı.
// Formlar için .form-container, .form-group, .btn sınıfları kullanılacak.
/*
<style>
.login-container { ... }
.login-form div { ... }
.login-form label { ... }
.login-form input[type="text"], ... { ... }
.login-form .btn { ... }
.login-form .btn:hover { ... }
.login-container p { ... }
.message.error { ... }
</style>
*/
?> 