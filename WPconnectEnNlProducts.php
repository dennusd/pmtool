<?php 

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( 'classes/class.Global.php' );

$products = new Products();

?>
<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <title></title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <meta property="og:title" content="">
  <meta property="og:type" content="">
  <meta property="og:url" content="">
  <meta property="og:image" content="">

  <link rel="manifest" href="site.webmanifest">
  <link rel="apple-touch-icon" href="icon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
  <!-- <link rel="stylesheet" href="https://use.typekit.net/osg5acv.css"> -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/base.css?<?php echo uniqid( '', true); ?>">

</head>

<body>
    <img class="ROMYNOX-R" src="images/ROMYNOX-R.png">

    <div class="topbar">Categorie: <?php echo $products->categoryMenu(); ?><!--<span class="admin--cat-settings">> Edit category</span><span id="open-configurator" class="admin--configurator">> Configurator</span> --></div>
    
    <?php $products->WPconnectEnNlProducts(); ?>

    <script src="js/vendor/modernizr-3.11.2.min.js"></script>
    <script src="https://kit.fontawesome.com/4c539e74af.js" crossorigin="anonymous"></script>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/ymcohp1ydelemenavx5h4t2755ctdn2s9gyg8r96qmo7kug1/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.tiny.cloud/1/ymcohp1ydelemenavx5h4t2755ctdn2s9gyg8r96qmo7kug1/tinymce/5/jquery.tinymce.min.js" referrerpolicy="origin"></script>
    <script src="js/plugins.js?<?php echo uniqid( '', true ); ?>">"></script>
    <script src="js/main.js?<?php echo uniqid( '', true ); ?>">"></script>
</body>
</html>