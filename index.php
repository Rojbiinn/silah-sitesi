<?php
// index.php - Ana Giriş Noktası

// Temel yapılandırma veya başlangıç işlemleri burada yapılabilir (eğer gerekirse).
// Örneğin, tüm site için geçerli olacak bazı ayarlar veya otomatik yükleyiciler.

// Ana sayfa düzenini ve yönlendiriciyi içeren dizi_i.php'yi yükle.
// dizi_i.php zaten session_start() ile başlıyor.
if (file_exists('dizi_i.php')) {
    require_once 'dizi_i.php';
} else {
    // Kritik bir dosya eksikse hata göster.
    echo "<h1>Kritik Hata</h1>";
    echo "<p>Ana site dosyası (dizi_i.php) bulunamadı. Lütfen site yöneticisi ile iletişime geçin.</p>";
    // Geliştirme aşamasında daha detaylı hata loglama yapılabilir.
    // error_log("CRITICAL ERROR: dizi_i.php not found.");
}

?> 