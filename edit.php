<?php 

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( 'classes/class.Global.php' );

$products = new Products();

$dataset = $products->getProdGroup( $_POST['id'] );

?>
    <div id="close-notice" class="edit--popup-notice" style="display: none">
        <h2>Notice!</h2>
        <p>When copy/pasting text from a supplier, double-check any used quotes ( " ).<br>
        Be sure you replace any <i>'italic'</i> quotes with normal quotes</p>
        <p>This for technical reasons. You can click on this popup to close it!</p>
        <p>Thank you! Kind regards,<br>
        Dennis</p>
    </div>
    <form id="product" action="proces.php" method="post" style="margin-bottom: 100px">
        <input type="hidden" name="product_id" value="<?php echo $_POST['id']; ?>">
        <?php echo $products->getDetails( 'topbar' ); ?>
        <div class="item--button edit--toggle-content" style="clear: both"><i class="fa-light fa-eye"></i> Toggle content</div>
        <div class="details edit--content-container">
            <div style="min-width: 500px; width: 500px; padding-right: 20px;">
                <?php echo $products->getDetails( 'details' ); ?>
            </div>
            <div style="min-width: 420px; width: 420px; padding-right: 20px; border-right: 1px solid #ee2e24;">
                <h2>FEATURES ENGLISH</h2>
                <div class="tiny-features">
                    <textarea id="tinymce-features"><?php echo $products->getFeatures(); ?></textarea>
                </div>
                <h2>TECHNICAL INFORMATION</h2>
                <div class="tiny-techinfo">
                    <textarea id="tinymce-techinfo"><?php echo $products->getTechinfo(); ?></textarea>
                </div>
            </div>

            <div style="min-width: 500px; width: 500px; padding-left: 20px; border-left: 1px solid #ee2e24;">
                <?php echo $products->getDetailsNL(); ?>
            </div>
            <div style="min-width: 420px; width: 420px; padding-left: 20px;">
                <h2>FEATURES NEDERLANDS</h2>
                <div class="tiny-features">
                    <textarea id="tinymce-features_nl"><?php echo $products->getFeaturesNL(); ?></textarea>
                </div>
                <h2>TECHNISCHE INFORMATIE</h2>
                <div class="tiny-techinfo">
                    <textarea id="tinymce-techinfo_nl"><?php echo $products->getTechinfoNL(); ?></textarea>
                </div>
            </div>
            <div style="min-width: 295px; width: 295px; padding-left: 20px;">
                <h2>SPECIFICATIONS</h2>
                <?php echo $products->getSpecs( $dataset, 'select' ); ?>
                <?php echo $products->getSpecs( $dataset, 'field' ); ?>
            </div>
            <div style="min-width: 375px; padding-left: 20px;">
                <h2 id="spec-checkbox-toggle">SPECIFICATIONS</h2>
                <div id="spec-checkbox-container">
                    <?php echo $products->getSpecs( $dataset, 'checkbox' ); ?>
                </div>
            </div>


        </div>
<!--
        <div class="item--button edit--toggle-pdf" style="margin-bottom: 10px; margin-top: 20px; clear: both"><i class="fa-light fa-eye"></i> Toggle PDF files</div>
        <div class="details--pdf">
            <?php 
                $pdfs = $products->getAttachements( $_POST['id'], 'items' );
                echo $pdfs['html'];
            ?>
        </div>

    
        <div class="details" style="width: 300px">
            <h2>SPECIFICATIONS</h2>
            <?php echo $products->getSpecs( $dataset, 'select' ); ?>
            <?php echo $products->getSpecs( $dataset, 'field' ); ?>
        </div>
        <div class="details" style="width: 22%; min-width: 390px">
            <h2>SPECIFICATIONS</h2>
            <?php echo $products->getSpecs( $dataset, 'checkbox' ); ?>
        </div>
        -->
        <div class="details--cta-btns">
            <div class="item--button" id="close" style="margin-right: 20px"><i class="fa-light fa-circle-xmark" style="margin-right: 10px; font-size: 13px;"></i>Close</div>
            <div class="item--button button-green" id="submitform"><i class="fa-light fa-floppy-disk" style="margin-right: 10px; font-size: 13px;"></i> Save</div>
        </div>

    </form>