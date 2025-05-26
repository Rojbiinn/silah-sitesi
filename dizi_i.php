<?php 
session_start(); // Oturumu en üste taşı
// error_reporting(0); // Hata raporlamayı geliştirme aşamasında açık tutmak daha iyidir
$current_page = isset($_GET["sayfa"]) ? $_GET["sayfa"] : 'anasayfa'; // Varsayılan sayfa anasayfa
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="tr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Silah Satış Platformu - <?php echo ucfirst(str_replace("_", " ", $current_page)); ?></title>
<link rel="stylesheet" href="css/main_styles.css">

</head>

<body>
<div id="kapsayici">
    <header id="banner">
        <h1>Silah Satış Platformu</h1>
    </header>

    <nav id="main-nav">
        <ul>
            <li><a href="index.php?sayfa=anasayfa" class="<?php echo ($current_page === 'anasayfa' || $current_page === '') ? 'active' : ''; ?>">Ana Sayfa</a></li>
            <li><a href="index.php?sayfa=urunler" class="<?php echo $current_page === 'urunler' ? 'active' : ''; ?>">Ürünler</a></li>
            <li><a href="index.php?sayfa=sepet" class="<?php echo $current_page === 'sepet' ? 'active' : ''; ?>">Sepet</a></li>
            <?php if (isset($_SESSION['uye_role']) && $_SESSION['uye_role'] === 'admin'): ?>
                <li><a href="index.php?sayfa=admin_panel" class="<?php echo strpos($current_page, 'admin') === 0 ? 'active' : ''; ?>">Admin Paneli</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION['uye'])): ?>
                <li><a href="guvenli_cikis.php">Çıkış Yap (<?php echo htmlspecialchars($_SESSION['uye']); ?>)</a></li>
            <?php else: ?>
                <li><a href="index.php?sayfa=uye_giris" class="<?php echo $current_page === 'uye_giris' ? 'active' : ''; ?>">Giriş Yap</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <main id="icerik">
    <?php 
    // Session mesajlarını göster
    if (isset($_SESSION['message'])) {
        echo "<div class=\"message " . htmlspecialchars($_SESSION['message_type']) . "\">" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']); // Mesajı gösterdikten sonra temizle
        unset($_SESSION['message_type']);
    }
    // Ana içerik alanı
    switch($current_page)
    {
        case "yeni_uyelik":
            include_once("yeni_uyelik.php");
            break;
        case "uye_giris":
            include_once("uye_giris.php");
            break;
        case "sepet":
            include_once("sepet_goruntule.php");
            break;
        case "checkout":
            include_once("checkout.php");
            break;
        case "admin_panel": 
        case "admin_add_product": 
        case "admin_list_products":
        case "admin_edit_product":
            if (isset($_SESSION['uye_role']) && $_SESSION['uye_role'] === 'admin') {
                if ($current_page === 'admin_panel' && file_exists("admin_panel.php")) {
                    include_once("admin_panel.php");
                } elseif ($current_page === 'admin_add_product' && file_exists("admin_add_product.php")) {
                    include_once("admin_add_product.php");
                } elseif ($current_page === 'admin_list_products' && file_exists("admin_list_products.php")) {
                    include_once("admin_list_products.php");
                } elseif ($current_page === 'admin_edit_product' && file_exists("admin_edit_product.php")) {
                    include_once("admin_edit_product.php"); // Henüz oluşturulmadı, ama case'i ekleyelim
                } else {
                    echo "<p>Admin sayfası bulunamadı.</p>";
                }
            } else {
                echo "<p>Bu alana erişim yetkiniz yok.</p>";
            }
            break;
        case "urunler": // Tüm ürünleri göster
            include_once("urun_listesi.php");
            break;
        case "anasayfa":
        default:
            // Ana sayfada da ürün listesini veya özel bir içeriği gösterebiliriz.
            // Şimdilik ürün listesini gösterelim.
            echo "<h2>Mağazamıza Hoşgeldiniz!</h2><p>En yeni ürünlerimizi aşağıda bulabilirsiniz.</p>";
            if (file_exists("urun_listesi.php")) {
                include_once("urun_listesi.php");
            } else {
                echo "<p>Ürün listesi bulunamadı.</p>";
            }
            break;
    }
    ?>
    </main>

    <aside id="sag">
        <h4>Hesabım</h4>
        <?php 
        if(isset($_SESSION["uye"])) {
            echo "<p>Hoşgeldiniz, <strong>" . htmlspecialchars($_SESSION["uye"]) . "</strong>!</p>";
            if (isset($_SESSION['uye_role']) && $_SESSION['uye_role'] === 'admin') {
                echo "<a href='index.php?sayfa=admin_panel' class='admin-link btn'>Admin Paneli</a>";
            }
            echo "<a href='guvenli_cikis.php' class='auth-link btn btn-danger'>Güvenli Çıkış</a>";
        } else { 
            echo "<a href='index.php?sayfa=uye_giris' class='auth-link btn btn-primary'>Üye Girişi</a>";
            echo "<a href='index.php?sayfa=yeni_uyelik' class='auth-link btn btn-success' style='margin-top:10px;'>Yeni Üyelik</a>";
        }
        ?>
        <hr style="margin: 20px 0;">
        <h4>Sepetim</h4>
        <a href="index.php?sayfa=sepet" class="cart-link btn">Sepeti Görüntüle</a>
        <?php 
          // Mini sepet özeti (isteğe bağlı)
          $total_items_in_cart = 0;
          if (!empty($_SESSION['cart'])) {
              foreach ($_SESSION['cart'] as $item) {
                  $total_items_in_cart += $item['quantity'];
              }
          }
          echo "<p><small>Sepetinizde {$total_items_in_cart} ürün var.</small></p>";
        ?>
    </aside>
 
    <footer id="haber">
        <p>&copy; <?php echo date("Y"); ?> Silah Satış Platformu. Tüm hakları saklıdır.</p>
        <p><small>Uyarı: Bu site demo amaçlıdır. Gerçek silah satışı yapılmamaktadır.</small></p>
    </footer>

</div>
</body>
</html>

