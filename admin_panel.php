<?php
// admin_panel.php
// Bu dosya dizi_i.php içerisinden include edildiği için session zaten başlatılmış ve rol kontrolü yapılmış olmalı.
// Ancak doğrudan erişime karşı ek bir kontrol eklenebilir.
if (!isset($_SESSION['uye_role']) || $_SESSION['uye_role'] !== 'admin') {
    echo "<p>Bu sayfaya erişim yetkiniz bulunmamaktadır.</p>";
    // Gerekirse ana sayfaya yönlendir:
    // header('Location: index.php');
    // exit;
    return; // dizi_i.php içinde include edildiği için exit yerine return daha uygun olabilir.
}
?>
<div class="admin-panel-container">
    <h2>Admin Paneli</h2>
    <p>Hoşgeldiniz, <?php echo htmlspecialchars($_SESSION['uye']); ?>!</p>
    
    <ul class="admin-menu">
        <li><a href="index.php?sayfa=admin_add_product">Yeni Ürün Ekle</a></li>
        <li><a href="index.php?sayfa=admin_list_products">Ürünleri Listele/Düzenle</a></li>
        <!-- Gelecekte eklenebilecek linkler -->
        <!-- <li><a href="index.php?sayfa=admin_view_orders">Siparişleri Görüntüle</a></li> -->
    </ul>
</div>

<?php
// Stil tanımlamaları css/main_styles.css dosyasına taşındı.
/*
<style>
.admin-panel-container { ... }
.admin-panel-container h2 { ... }
.admin-menu { ... }
.admin-menu li { ... }
.admin-menu li a { ... }
.admin-menu li a:hover { ... }
</style>
*/
?> 