<?php 

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( 'classes/class.Global.php' );

$products = new Products();

$dataset = $products->getProdGroup( $_POST['id'] );

?>
    <form id="product" action="proces.php" method="post">
        <input type="hidden" name="product_id" value="<?php echo $_POST['id']; ?>">
        <div class="details">
            <div style="width: 764px; min-width: 764px">
                <?php echo $products->getDetails(); ?>
            </div>
            <div style="width: 764px; min-width: 764px">
                <?php echo $products->getDetails(); ?>
            </div>
            <div class="details--numerique" style="min-width: 625px; width: 625px;">
                <h2>FEATURES</h2>
                <div class="tiny-features">
                    <textarea id="tinymce-features"><?php echo $products->getFeatures(); ?></textarea>
                </div>
                <h2>SPECIFICATIONS</h2>
                <?php echo $products->getSpecs( $dataset, 'select' ); ?>
                <?php echo $products->getSpecs( $dataset, 'field' ); ?>
            </div>
            <div style="width: 22%; min-width: 390px;">
                <h2>SPECIFICATIONS</h2>
                <?php echo $products->getSpecs( $dataset, 'checkbox' ); ?>
            </div>
        </div>
        <div class="details--cta-btns">
            <div class="details--close" id="close">Sluiten</div>
            <div class="details--save" id="submitform">Opslaan</div>
        </div>

    </form>