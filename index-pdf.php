<?php 

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( __DIR__.'/classes/class.Global.php' );

$products = new Products();

?>
<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <title>Product Manager 2.2 - ROMYNOX</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow" />

  <meta property="og:title" content="">
  <meta property="og:type" content="">
  <meta property="og:url" content="">
  <meta property="og:image" content="">

  <link rel="apple-touch-icon" href="icon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
  <!-- <link rel="stylesheet" href="https://use.typekit.net/osg5acv.css"> -->
  <link href="assets/font-awesome-pro/css/fontawesome.css" rel="stylesheet">
  <link href="assets/font-awesome-pro/css/all.css" rel="stylesheet">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/base.css?<?php echo uniqid( '', true ); ?>">

</head>

<body>
    <img class="ROMYNOX-R" src="img/ROMYNOX-R.png">

    <div class="topbar">
        <span class="topbar--total" style="margin-right: 20px">Total products: <span class="topbar--total-number" style="font-weight: 700"><?php echo $products->totalProducts(); ?></span></span> 
        <span class="topbar--search" style="margin-right: 20px"><form id="searchrx" name="searchrx" action=""><input type="text" name="rxc" class="topbar--RX" placeholder="RX-code"></form><span class="btn-searchrx"><i class="fa-light fa-search"></i></span></span>
        <span>Category: <?php echo $products->categoryMenu_NEW(); ?></span>

        <!-- span class="admin--cat-settings">> Edit category</span><span id="open-configurator" class="admin--configurator">> Configurator</span> -->
        
    </div>
    
    <div class="main">
        <div class="list">
            <?php echo $products->parseOverview( 'pdf' ); ?>
        </div>
        <div class="details"></div>
    </div>
    <div class="popup popup--hidden">
        <!-- <div class="popup--close"></div> -->
        <!-- <iframe id="popup--iframe" name="popup" src="edit.php"></iframe> -->
        <div id="popup--content"></div>
    </div>

    <script src="js/vendor/modernizr-3.11.2.min.js"></script>
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/ymcohp1ydelemenavx5h4t2755ctdn2s9gyg8r96qmo7kug1/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.tiny.cloud/1/ymcohp1ydelemenavx5h4t2755ctdn2s9gyg8r96qmo7kug1/tinymce/5/jquery.tinymce.min.js" referrerpolicy="origin"></script>
    <script src="js/plugins.js?<?php echo uniqid( '', true); ?>">"></script>
    <script src="js/main.js?<?php echo uniqid( '', true); ?>">"></script>
</body>
</html>