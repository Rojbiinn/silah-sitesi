<?php
session_start(); // Oturumu başlat

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session cookie'sini sil (isteğe bağlı, daha kapsamlı temizlik için)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session'ı yok et
session_destroy();

// Kullanıcıya mesaj ver (isteğe bağlı, genellikle doğrudan yönlendirme yapılır)
// Session yok edildiği için bu mesaj $_SESSION ile set edilemez, doğrudan echo edilebilir veya query param ile gönderilebilir.
// Ama en iyisi doğrudan ana sayfaya yönlendirmek ve orada genel bir "Başarıyla çıkış yapıldı" mesajı göstermek olabilir.

// Ana sayfaya yönlendir
header("location: dizi_i.php");
exit;
?> 