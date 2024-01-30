<?php

class products {

    var $mysqli;

/*

    Open database connection

*/
    public function __construct() {
        //$this->mysqli = new mysqli( 'localhost','root','root','producten' );
        $this->mysqli = new mysqli( 'localhost','u215708619_RomyDen','~0Mi2Kg9E!w','u215708619_producten' );

        if( $this->mysqli -> connect_errno ) :
            echo 'Failed to connect to MySQL: ' . $this->mysqli->connect_error;
            exit();
        endif;
    }

/*

    Strip input for safety

*/
    function safeinput( $input ) {
        $input = stripslashes( $input );
        $input = strip_tags( $input );
        $input = htmlspecialchars( $input );
        $input = htmlentities( $input );
        return $input; 
    }

/*

    Generate Redirects for .htaccess

*/
    function generateRedirects() {
        $q      = ' SELECT 
                    p.virtuemart_product_id, 
                    p.vm_cat_slug1, 
                    p.vm_cat_slug2, 
                    p.vm_cat_slug3, 
                    p.vm_slug_en, 
                    p.wc_slug_en,
                    p.vm_cat_nl_slug1, 
                    p.vm_cat_nl_slug2, 
                    p.vm_cat_nl_slug3, 
                    p.vm_slug_nl, 
                    p.wc_slug_nl
                    FROM producten AS p
                    ORDER BY p.virtuemart_product_id';

        if( $result = $this->mysqli -> query($q) ) :

            while( $row = $result->fetch_assoc() ) :

                $url_part_1 = NULL;
                $url_part_2 = NULL;
                $url_part_3 = NULL;

                // EN redirects
                //if (!empty($row['vm_cat_slug1'])) $url_part_1 = $row['vm_cat_slug1'] .'/';
                //if (!empty($row['vm_cat_slug2'])) $url_part_2 = $row['vm_cat_slug2'] .'/';
                //if (!empty($row['vm_cat_slug3'])) $url_part_3 = $row['vm_cat_slug3'] .'/';
                //$url = '/'. $url_part_1 . $url_part_2 . $url_part_3 . $row['vm_slug_en'] .'-detail';
                //echo 'RedirectMatch 301 '. $url .' https://romynox.nl/'. $row['wc_slug_en'] .'<br>';

                // NL redirects 
                if (!empty($row['vm_cat_nl_slug1'])) $url_part_1 = $row['vm_cat_nl_slug1'] .'/';
                if (!empty($row['vm_cat_nl_slug2'])) $url_part_2 = $row['vm_cat_nl_slug2'] .'/';
                if (!empty($row['vm_cat_nl_slug3'])) $url_part_3 = $row['vm_cat_nl_slug3'] .'/';
                $url = '/'. $url_part_1 . $url_part_2 . $url_part_3 . $row['vm_slug_nl'] .'-detail';
                echo 'RedirectMatch 301 '. $url .' https://romynox.nl/'. $row['wc_slug_nl'] .'<br>';


            endwhile;
        endif;
    }

    public function updatePRODTYPE() {
        $q = 'SELECT p.* FROM producten AS p JOIN prod_cat_new_link pcnl ON pcnl.virtuemart_product_id = p.virtuemart_product_id WHERE pcnl.category_id = 70';

        echo $q;
        if( $result = $this->mysqli -> query($q) ) :
            while( $row = $result->fetch_assoc() ) :
                $vmid = $row['virtuemart_product_id'];
                $q_sub = 'UPDATE producten SET prodtype = "SU" WHERE virtuemart_product_id = '. $vmid;

                if( $this->mysqli->query($q_sub) ):
                    echo 'Done';
                endif;
            endwhile;
        endif;
    }

/*

    Topbar Category menu

*/
    public function categoryMenu() {

        $output = NULL;
        $q      = 'SELECT c.category_id,
                  c.category_name,
                  COUNT(sd.prod_group) AS SpecCount
                  FROM spec_datasets AS sd
                  RIGHT JOIN categories c
                  ON sd.prod_group = c.dataset
                  GROUP BY c.category_id
                  ORDER BY c.category_name';

        if( $result = $this->mysqli -> query($q) ) :
            $output .= '213321321<form id="topbar-form" method="post">
            <select id="cat-select" name="cat-select">
                <option value="">Alles</option>';
            while( $row = $result->fetch_assoc() ) :
                $output .= '<option value="'. $row['category_id'] .'">'. $row['category_name'] .'</option>';
            endwhile;
            $output .= '</select></form><br>';
        endif;

        return $output;

    }

/*

    Topbar New Category menu

*/
    public function categoryMenu_NEW() {

        $output = NULL;
        $q      = ' SELECT cn.category_id, cn.Woocommerce_en 
                    FROM categories_new AS cn
                    INNER JOIN prod_cat_new_link pcnl
                    ON pcnl.category_id = cn.category_id
                    GROUP BY cn.category_id
                    ORDER BY cn.Woocommerce_en';

        if( $result = $this->mysqli -> query($q) ) :
            $output .= '<form id="topbar-form" method="post" action="?catselect">
            <select id="cat-select" name="cat-select">
                <option value="">All</option>';
            while( $row = $result->fetch_assoc() ) :
                $output .= '<option value="'. $row['category_id'] .'">'. $row['Woocommerce_en'] .'</option>';
            endwhile;
            $output .= '</select></form><br>';
        endif;

        return $output;

    }

    public function totalProducts() {

        $output = NULL;
        $q      = ' SELECT COUNT(id) AS totalProducts FROM producten AS p
                    JOIN prod_cat_new_link pcnl
                    ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                    JOIN categories_new cn
                    ON cn.category_id = pcnl.category_id
                    WHERE cn.category_id <> 118';

        if( $result = $this->mysqli->query($q) ) :
            $row = mysqli_fetch_array($result);

            return $row[0];
        endif;

    }

/*

    Generate list based on category

*/
    public function parseOverview( $design ) {
        
        if( isset($_GET['rxc'])):
            $rxc = safeinput( $_GET['rxc']);
        endif;

        if( isset($_POST['cat-select'])):
            $catid = safeinput( $_POST['cat-select']);
        endif;

        $q = NULL;
        $resultArray = array();
        $virtuemart_product_id = NULL;

        if( !empty($rxc) ): 
            $q = '
                SELECT
                    p.*,
                    cn.category_id,
                    cn.Woocommerce_en,
                    cn.Woocommerce_nl
                FROM producten AS p 
                LEFT JOIN prod_cat_link pcl 
                ON p.virtuemart_product_id = pcl.virtuemart_product_id
                LEFT JOIN prod_cat_new_link pcnl
                ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                LEFT JOIN categories_new cn
                ON cn.category_id = pcnl.category_id  
                WHERE p.rxcode = "'. $rxc .'"
                ORDER BY p.product_name
                LIMIT 999';
        elseif( !empty($catid) ): 
            $q = '
                SELECT
                    p.*,
                    cn.category_id,
                    cn.Woocommerce_en,
                    cn.Woocommerce_nl
                FROM producten AS p 
                LEFT JOIN prod_cat_link pcl 
                ON p.virtuemart_product_id = pcl.virtuemart_product_id
                LEFT JOIN prod_cat_new_link pcnl
                ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                LEFT JOIN categories_new cn
                ON cn.category_id = pcnl.category_id  
                WHERE cn.category_id = '. $catid .'
                /*AND NOT p.archive = 1*/
                ORDER BY p.product_name
                LIMIT 999';
        else :
            $q = ' SELECT 
                        p.*,
                        c.category_name,
                        c.category_id,
                        cn.category_id,
                        cn.Woocommerce_en,
                        cn.Woocommerce_nl
                    FROM producten AS p
                    LEFT JOIN prod_cat_link pcl
                    ON pcl.virtuemart_product_id = p.virtuemart_product_id
                    LEFT JOIN categories c 
                    ON c.category_id = pcl.category_id
                    LEFT JOIN prod_cat_new_link pcnl
                    ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                    LEFT JOIN categories_new cn
                    ON cn.category_id = pcnl.category_id  
                    /*WHERE NOT p.archive = 1*/
                    WHERE pcnl.category_id <> 118
                    ORDER BY p.product_name
                    LIMIT 999';
        endif;
        
        echo '<div class="debug">'. $q .'</div>';
        
        if( $result = $this->mysqli->query($q) ) :
            while( $row = $result->fetch_assoc() ):
                if( !empty($row['product_s_desc']) ):
                    $product_s_desc = '';
                else:
                    $product_s_desc = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>English short description</div>';
                endif;

                if( !empty($row['product_paragraph']) ):
                    $product_paragraph = '';
                else:
                    $product_paragraph = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>English paragraph</div>';
                endif;

                if( !empty($row['product_desc']) ):
                    $product_desc = '';
                else:
                    $product_desc = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>English description</div>';
                endif;

                if( !empty($row['product_features']) ):
                    $product_features = '';
                else:
                    $product_features = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>English features</div>';
                endif;

                if( !empty($row['product_s_desc_nl']) ):
                    $product_s_desc_nl = '';
                else:
                    $product_s_desc_nl = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>Dutch short description</div>';
                endif;

                if( !empty($row['product_paragraph_nl']) ):
                    $product_paragraph_nl = '';
                else:
                    $product_paragraph_nl = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>Dutch paragraph</div>';
                endif;

                if( !empty($row['product_desc_nl']) ):
                    $product_desc_nl = '';
                else:
                    $product_desc_nl = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>Dutch description</div>';
                endif;

                if( !empty($row['product_features_nl']) ):
                    $product_features_nl = '';
                else:
                    $product_features_nl = '<div class="prod-spec-missing"><i class="fa-solid fa-triangle-exclamation"></i>Dutch features</div>';
                endif;

                // Genereer item uit array
                $resultArray = array(   'id'                    => $row['virtuemart_product_id'],
                                        'real_id'               => $row['id'],
                                        'prodtype'              => $row['prodtype'],
                                        'wc_id_en'              => $row['wc_id_en'],
                                        'wc_id_nl'              => $row['wc_id_nl'],
                                        'rxcode'                => $row['rxcode'],
                                        'articlenr'             => $row['articlenr'],
                                        'name'                  => $row['product_name'], 
                                        'short_desc'            => $row['product_s_desc'],
                                        'wc_cat'                => $row['Woocommerce_en'],
                                        'cat'                   => $row['Woocommerce_en'],
                                        'catNL'                 => $row['Woocommerce_nl'],
                                        'new_catid'             => $row['category_id'],
                                        'product_s_desc'        => $product_s_desc,
                                        'product_paragraph'     => $product_paragraph,
                                        'product_desc'          => $product_desc,
                                        'product_features'      => $product_features,
                                        
                                        'product_s_desc_nl'     => $product_s_desc_nl,
                                        'product_paragraph_nl'  => $product_paragraph_nl,
                                        'product_desc_nl'       => $product_desc_nl,
                                        'product_features_nl'   => $product_features_nl);
                echo self::createItem($resultArray, $design);
                unset($product_paragraph);
            endwhile;
        endif;
    }

/*
    
    Create item for overview

*/
    public function createItem( $array, $design ) {
        $output                 = NULL;
        $cat                    = $array['cat'];
        $images                 = NULL;
        $images                 .= self::getImages($array['id']);
        $product_name           = htmlspecialchars_decode($array['name']);
        if( empty($product_name)) $product_name = 'NEW PRODUCT :)';
        $item_short_desc        = htmlspecialchars_decode($array['short_desc']);
        $rxcode                 = NULL;
        $newCategory            = self::newCategoryList( $array['id'], $array['new_catid'], $array['cat'] );

        $product_s_desc         = $array['product_s_desc'];
        $product_paragraph      = $array['product_paragraph'];
        $product_desc           = $array['product_desc'];
        $product_features       = $array['product_features'];
        $product_s_desc_nl      = $array['product_s_desc_nl'];
        $product_paragraph_nl   = $array['product_paragraph_nl'];
        $product_desc_nl        = $array['product_desc_nl'];
        $product_features_nl    = $array['product_features_nl'];

        if( $array['rxcode'] ):
            $rxcode = '<div class="item--rxcode"><strong>RX </strong> '. $array['rxcode'] .'</div> ';
        endif;

        //$specCounter = '<div class="item--specs">'. self::countSpecs( $array['id'] ) .'</div>';
        $itemstyle = '<div class="item">';
        if( $cat == NULL ) $itemstyle = '<div class="item" style="padding-left: 10px; border-left: 10px solid red;">';

        //$pdfs = self::getPDFs( $array['id'] );
        $pdfs = self::getAttachements( $array['id'], 'compact' );
        $pdf_buttons = self::getAttachements( $array['id'], 'buttons' );

        if( $design == 'general' ):

            $output = <<<HTML
                {$itemstyle}
                    <div class="item--data">
                        <div class="item--text" id="item-{$array['id']}" data-realid="{$array['real_id']}">
                            <div><h2>{$product_name}</h2></div>
                            <div class="item--cat">{$newCategory}</div>
                            <div>
                            <div class="compact-pdf"><span style="font-weight: bold; margin-right: 10px;">{$pdfs['count']} PDFs:</span>{$pdfs['html']}</div>
                            <!-- <div class="item--errors">{$product_paragraph}{$product_s_desc}{$product_desc}{$product_features}</div>
                            <div class="item--errors">{$product_paragraph_nl}{$product_s_desc_nl}{$product_desc_nl}{$product_features_nl}</div> -->
                            </div>
                        </div>
                    </div>
                    <div class="item--pdfs">
                    {$pdf_buttons['html']}
                    </div>
                    <div class="item--info">
                        <table>
                            <tr>
                                <td>RX:</td>
                                <td class="item--rxcode"><strong>{$array['rxcode']}</strong></td>
                            </tr>
                            <tr>
                                <td>VMID:</td>
                                <td><strong>{$array['id']}</strong> &nbsp; Prodtype: <strong>{$array['prodtype']}</strong></td>
                            </tr>
                            <tr>
                                <td>WC ID's:</td> 
                                <td><div class="flag-icon-en" src="img/flag-en.png"></div><div class="wcid">{$array['wc_id_en']}</div><div class="flag-icon-nl"></div><div class="wcid">{$array['wc_id_nl']}</div></td>
                            </tr>
                        </table>
                    </div>
                    <div class="item--actions">
                        <div style="margin-bottom: 10px;" data-val="{$array['id']}" data-cat="{$array['new_catid']}" class="item--button item--edit"><i class="fa-light fa-pen" style="margin-right: 10px"></i> Edit</div>
                        <a style="margin-bottom: 10px;" data-val="{$array['id']}" data-cat="{$array['new_catid']}" href="export.php?id={$array['id']}" class="item--button"><i class="fa-light fa-download" style="margin-right: 10px"></i> Export</a>
                        <a data-val="{$array['id']}" class="item--button item--pdf" href="mpdf.php?id={$array['id']}" target="_blank"><i class="fa-light fa-file-pdf"></i></a>
                    </div>
                    <div class="item--images images-wrapper-{$array['id']}">
                        {$images}
                    </div>
                    <div class="item--images-upload" data-val="{$array['id']}">
                        <!-- <i class="fa-solid fa-cloud-arrow-up"></i> -->
                        <form enctype="multipart/form-data" id="formupload{$array['id']}" class="formupload formupload{$array['id']}" data-id="{$array['id']}" method="post"  style="height: 90px">
                            <input type="hidden" value="{$array['id']}" name="id">
                            <input type="hidden" value="{$array['id']}" name="test">
                            <input type="file" name="fileToUpload" id="fileToUpload" class="fileToUpload" placeholder="">
                            <!-- <span class="btn-uploadimage" data-saveitemid="{$array['id']}" style="padding:2px 4px;margin-left:20px;background:#105CA1;color:white;">Save</span> -->
                        </form>
                    </div>
                </div>
            HTML;

        endif;

        if( $design == 'pdf' ):

            $output = <<<HTML
                <div class="item" style="height: auto !important;">
                    <div class="item--data">
                        <div class="item--text" id="item-{$array['id']}" data-realid="{$array['real_id']}">
                            <div><h2>{$product_name}</h2></div>
                            <div>{$item_short_desc}</div>
                        </div>
                    </div>
                    <div class="item--pdfs">
                        <div class="pdfs-header">Brochures</div>
                        {$pdf_buttons['brochure']}

                        <div class="add-pdf add-brochure" target="_blank"><i class="fa-solid fa-plus"></i> Add</div>
                    </div>
                    <div class="item--pdfs">
                        <div class="pdfs-header">Certificates</div>
                        {$pdf_buttons['certificate']}
                        <div class="add-pdf add-certificate" target="_blank"><i class="fa-solid fa-plus"></i> Add</div>
                    </div>
                    <div class="item--pdfs">
                        <div class="pdfs-header">Datasheets</div>
                        {$pdf_buttons['datasheet']}
                        <div class="add-pdf add-datasheet" target="_blank"><i class="fa-solid fa-plus"></i> Add</div>
                    </div>
                    <div class="item--pdfs">
                        <div class="pdfs-header">Manuals</div>
                        {$pdf_buttons['manual']}

                        <form enctype="multipart/form-data" id="formupload{$array['id']}" class="formupload formupload{$array['id']}" data-id="{$array['id']}" method="post"  style="height: 90px">
                            <input type="hidden" value="{$array['id']}" name="id">
                            <input type="hidden" value="{$array['id']}" name="test">
                            <input type="file" name="PDFToUpload" id="PDFToUpload" class="PDFToUpload" placeholder="">
                        </form>

                        <!-- <div class="add-pdf add-manual" target="_blank"><i class="fa-solid fa-plus"></i> Add</div> -->
                    </div>
                    <div class="item--pdfs">
                        <div class="pdfs-header">Movies</div>
                        {$pdf_buttons['movie']}
                        <div class="add-pdf add-movie" target="_blank"><i class="fa-solid fa-plus"></i> Add</div>
                    </div>
                    <div class="item--pdfs">
                        <div class="pdfs-header">Inquiry</div>
                    </div>
                    <div class="item--info">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td>RX:</td>
                                <td class="item--rxcode"><strong>{$array['rxcode']}</strong></td>
                            </tr>
                            <tr>
                                <td>VMID:</td>
                                <td><strong>{$array['id']}</strong> &nbsp; Prodtype: <strong>{$array['prodtype']}</strong></td>
                            </tr>
                            <tr>
                                <td>WC ID's:</td> 
                                <td><div class="flag-icon-en" src="img/flag-en.png"></div><div class="wcid">{$array['wc_id_en']}</div><div class="flag-icon-nl"></div><div class="wcid">{$array['wc_id_nl']}</div></td>
                            </tr>
                        </table>
                    </div>
                </div>
            HTML;

        endif;

        unset($product_paragraph);
        return $output;
    }

    public function newCategoryList( $uniqueID, $currentID, $currentNAME ) {

        $output = NULL;
        $q      = ' SELECT cn.category_id, cn.Woocommerce_en 
                    FROM categories_new AS cn
                    ORDER BY cn.Woocommerce_en';
        
        if( $result = $this->mysqli -> query($q) ) :
            $output .= '<form id="form'. $uniqueID .'" method="post">
            <input type="hidden" name="id" value="'. $uniqueID .'">
            <select id="quick-cat-'. $uniqueID .'" name="quick-cat" class="quick-cat-select">
                <option value="'. $currentID .'">'. $currentNAME .'</option>
                <option value="">&nbsp;</option>';
            while( $row = $result->fetch_assoc() ) :
                if( $row['category_id'] == '118' ): 
                    continue;
                endif;
                $output .= '<option value="'. $row['category_id'] .'">'. $row['Woocommerce_en'] .'</option>';
            endwhile;

            $output .= '<option value="">&nbsp;</option>';
            $output .= '<option value="">======</option>';
            $output .= '<option value="">&nbsp;</option>';

            $output .= '<option value="118">Put in ze archive!</option>';
;           $output .= '</select><span class="btn-saveitem" data-saveitemid="'. $uniqueID .'"><i class="fa-light fa-floppy-disk"></i></span></form><div class="itemstatus" id="itemstatus'. $uniqueID .'"></div>';
        endif;

        return $output;
    
}

/*
    
    Create item for overview

*/
    public function updateItem( $id, $part ) {

        $q = 'SELECT
            p.virtuemart_product_id,
            p.rxcode,
            p.product_name,
            p.product_s_desc
        FROM producten AS p 
        WHERE p.virtuemart_product_id ='. $id;

        //echo $q;

        if( $result = $this->mysqli->query($q) ):
            while( $row = $result->fetch_assoc() ):
                $product_name          = htmlspecialchars_decode( $row['product_name'] );
                //$item_short_desc    = htmlspecialchars_decode( $row['product_s_desc'] );
                //$cat                = htmlspecialchars_decode( $row['Woocommerce_en'] );
                //$newCategory        = self::newCategoryList( $id, $row['category_id'], $row['Woocommerce_en'] );
                $rxcode             =  $row['rxcode'];
            endwhile;
        endif;

        /*$specCounter = '<div class="item--specs">'. self::countSpecs( $array['id'] ) .'</div>';*/

        if( $part = 'productname' ):
            echo $product_name;
        endif;

        if( $part = 'rxcode' ):
            //echo $rxcode;
        endif;

/*
        $output = <<<HTML
                        <div style="margin-bottom: 5px">{$rxcode}<h2>{$product_name}</h2></div>
                        <div style="display:none;" class="item--short-desc">{$item_short_desc}</div>
                        <div style="display:none;" class="item--cat">Current category: {$cat}</div>
                        <div class="item--cat">{$newCategory}</div>
        HTML;       

        return $output; */
    }

/*

    Get product details

*/
    public function getDetails( $part ) {
        $output = NULL;
        $disabled = NULL;
        $id     = self::safeinput( $_POST['id'] );
        $q      = 'SELECT * FROM producten WHERE producten.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $hiddenselect       = NULL;
                $info_url           = NULL;
                $rxcode             = htmlspecialchars_decode( $row['rxcode'] );
                $articlenr          = htmlspecialchars_decode( $row['articlenr'] );
                $product_name       = htmlspecialchars_decode( utf8_encode($row['product_name']) );
                $product_s_desc     = htmlspecialchars_decode( utf8_encode($row['product_s_desc']) );
                $product_paragraph  = htmlspecialchars_decode( utf8_encode($row['product_paragraph']) );
                $product_features   = htmlspecialchars_decode( utf8_encode($row['product_features']) );
                $info_url           = $row['info_url'];
                $wc_id_en           = $row['wc_id_en'];
                $product_desc       = utf8_encode( $row['product_desc'] );

                $prodtype_ss = '<option value="SS">Stainless steel</option>';
                $prodtype_su = '<option value="SU">Single-use</option>';
                //echo $row['prodtype'];

                if( !empty($row['prodtype'])):
                    if( $row['prodtype'] == 'SS' ):
                        $prodtype_ss = '<option value="SS" selected>Stainless steel</option>';
                    endif;
                    if( $row['prodtype'] == 'SU' ):
                        $prodtype_su = '<option value="SU" selected>Singe-use</option>';
                    endif;
                    $disabled = 'disabled';
                    $hiddenselect = '<input type="hidden" name="prodtype" value="'. $row['prodtype'] .'">';
                endif;

                if( !is_null($info_url) ):
                    $visitbutton = '<a class="btn-saveitem" style="width: 80px; text-decoration: none; display: inline-block! important;" href="'. $info_url. '" target="_blank">Visit</a>';
                endif;

                if( $part == 'topbar' ):
                    $output            .= <<<HTML
                        <div class="edit--prodtype">
                            <label style="width: 102px">Product type</label>    
                            <select name="prodtype" {$disabled}>
                                <option value="">-</option>
                                {$prodtype_ss}
                                {$prodtype_su}                            
                            </select>
                            {$hiddenselect}
                            <label style="margin-left: 40px; width: 74px">RX code</label><input type="text" id="rxcode" name="rxcode" value="{$rxcode}">
                            <label style="margin-left: 40px; width: 83px">Article nr</label><input type="text" id="articlenr" name="articlenr" value="{$articlenr}">
                            <label style="margin-left: 40px; width: 103px">Supplier URL</label><input type="text" id="info_url" name="info_url" value="{$info_url}">{$visitbutton}
                        </div>                    
                    HTML;
                endif; // if( $part == 'topbar' ):

                if( $part == 'details' ):
                    $output            .= <<<HTML
                        <h2>ENGLISH (WC ID: {$wc_id_en})</h2>
                        <div class="spec spec--field"><label>Product name</label><input type="text" id="product_name" name="product_name" value="{$product_name}"></div>
                        <div class="spec spec--field"><label>Short description</label><input type="text" id="product_s_desc" name="product_s_desc" value="{$product_s_desc}"></div>
                        <div class="spec spec--field" style="height: 80px !important;"><label style="height: 80px">1st paragraph</label><textarea type="text" id="product_paragraph" name="product_paragraph">{$product_paragraph}</textarea><div class="paragraph_counter" id="product_paragraph_counter"></div></div>
                        <div class="tiny-description">
                            <textarea id="tinymceeditor">{$product_desc}</textarea>
                        </div>
                    HTML;
                endif; // if( $part == 'details' ):

            endwhile;
        endif;

        return $output;
    }

    public function getDetailsNL() {
        $output = NULL;
        $id     = self::safeinput( $_POST['id'] );
        $q      = 'SELECT * FROM producten WHERE producten.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $rxcode                 = htmlspecialchars_decode( $row['rxcode'] );
                $product_name_nl        = htmlspecialchars_decode( utf8_encode($row['product_name_nl']) );
                $product_s_desc_nl      = htmlspecialchars_decode( utf8_encode($row['product_s_desc_nl']) );
                $product_paragraph_nl   = htmlspecialchars_decode( utf8_encode($row['product_paragraph_nl']) );
                $product_features_nl    = htmlspecialchars_decode( utf8_encode($row['product_features_nl']) );
                $wc_id_nl               = $row['wc_id_nl'];
                $product_desc_nl        = utf8_encode( $row['product_desc_nl'] );
                $output                .= <<<HTML
                    <h2>NEDERLANDS (WC ID: {$wc_id_nl})</h2>
                    <div class="spec spec--field"><label>Product name</label><input type="text" id="product_name_nl" name="product_name_nl" value="{$product_name_nl}"></div>
                    <div class="spec spec--field"><label>Short description</label><input type="text" id="product_s_desc_nl" name="product_s_desc_nl" value="{$product_s_desc_nl}"></div>
                    <div class="spec spec--field" style="height: 80px !important;"><label style="height: 80px">1st paragraph</label><textarea type="text" id="product_paragraph_nl" name="product_paragraph_nl">{$product_paragraph_nl}</textarea></div>
                    <div class="tiny-description">
                        <textarea id="tinymceeditor_nl">{$product_desc_nl}</textarea>
                    </div>
                HTML;
            endwhile;
        endif;

        return $output;
    }

    public function getProductType() {
        $id     = self::safeinput( $_POST['id'] );

        $q      = 'SELECT * FROM producten WHERE producten.virtuemart_product_id = '. $id;
    }

    public function getFeatures() {
        $output = NULL;
        $id     = self::safeinput( $_POST['id'] );
        $q      = 'SELECT product_features FROM producten WHERE producten.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $output            .= utf8_encode( $row['product_features'] );
            endwhile;
        endif;

        return $output;
    }

    public function getFeaturesNL() {
        $output = NULL;
        $id     = self::safeinput( $_POST['id'] );
        $q      = 'SELECT product_features_nl FROM producten WHERE producten.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $output            .= utf8_encode( $row['product_features_nl'] );
            endwhile;
        endif;

        return $output;
    }

    public function getTechinfo() {
        $output = NULL;
        $id     = self::safeinput( $_POST['id'] );
        $q      = 'SELECT product_techinfo FROM producten WHERE producten.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $output            .= utf8_encode( $row['product_techinfo'] );
            endwhile;
        endif;

        return $output;
    }

    public function getTechinfoNL() {
        $output = NULL;
        $id     = self::safeinput( $_POST['id'] );
        $q      = 'SELECT product_techinfo_nl FROM producten WHERE producten.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $output            .= utf8_encode( $row['product_techinfo_nl'] );
            endwhile;
        endif;

        return $output;
    }




    public function cleanPDFfiles() {

        $q = 'SELECT * FROM pdf_files WHERE 1=1';

        if( $result = $this->mysqli -> query($q) ):
            while( $row = $result->fetch_assoc() ) :
                $last2 = substr($row['vmid'], -2);
                // Clean all entries with 'nl' in end of VMID
                if( $last2 == 'nl' ):
                    $q_del = 'DELETE FROM pdf_files WHERE pdf_files.id = '. $row['id'];
                    $this->mysqli->query($q_del);
                endif;

                // Remove all 'en' in the end of VMID
                $exploded = explode( 'en', $row['vmid'] );
                $q_update = 'UPDATE pdf_files SET vmid = '. $exploded[0] .' WHERE pdf_files.id = '. $row['id'];

                echo $q_update .'<br>';
                $this->mysqli->query($q_update);
                
                //echo $exploded[0] .'<br>';
            endwhile;
        endif;
    }


    public function getAttachements( $id, $style ) {

        $output = array();
        $output['html'] = NULL;
        $output['brochure'] = NULL;
        $output['datasheet'] = NULL;
        $output['certificate'] = NULL;
        $output['manual'] = NULL;
        $output['movie'] = NULL;
        $css = NULL;

        $q = 'SELECT * FROM attachements WHERE vmid = '. $id .' ORDER BY type, filename ';

        if( $result = $this->mysqli -> query( $q ) ):
            $output['count'] = $result->num_rows;
            $counter = 1;
            while( $row = $result->fetch_assoc() ):

                if( $style == 'items' ):
                    $output['html'] .= '<a class="item--button" href="https://pm.romynox.net/downloads/'. $row['filename'] .'" target="_blank"><i class="fa-light fa-file-pdf"></i> '. $row['filename'] .'</a>';
                endif;

                if( $style == 'compact' ):
                    $output['html'] .= '<a href="https://pm.romynox.net/downloads/'. $row['filename'] .'" target="_blank"><i class="fa-light fa-file-pdf"></i></a>';
                endif;

                if( $style == 'buttons' ):

                    if( $row['type'] == 'brochure' ): 
                        $css = 'pdf-brochure';

                        $output['brochure'] .= '<a href="https://pm.romynox.net/downloads/'. $row['filename'] .'" class="'. $css .'"target="_blank">'. $row['type'].'</a>';
                    endif;
                    if( $row['type'] == 'certificate' ): 
                        $css = 'pdf-certificate';

                        $output['certificate'] .= '<a href="https://pm.romynox.net/downloads/'. $row['filename'] .'" class="'. $css .'"target="_blank">'. $row['type'].'</a>';
                    endif;
                    if( $row['type'] == 'datasheet' ): 
                        $css = 'pdf-datasheet';

                        $output['datasheet'] .= '<a href="https://pm.romynox.net/downloads/'. $row['filename'] .'" class="'. $css .'"target="_blank">'. $row['type'].'</a>';
                    endif;
                    if( $row['type'] == 'manual' ): 
                        $css = 'pdf-manual';

                        $output['manual'] .= '<a href="https://pm.romynox.net/downloads/'. $row['filename'] .'" class="'. $css .'"target="_blank">'. $row['type'].'</a>';
                    endif;
                    if( $row['type'] == 'movie' ): 
                        $css = 'attachement-movie';

                        $output['movie'] .= '<a href="'. $row['filename'] .'" class="'. $css .'"target="_blank">'. $row['type'].'</a>';
                    endif;


                endif;

                $counter++;
            endwhile;
        endif;
        return $output;

    }




    public function getPDFs( $id ) {

        $output = NULL;
        $q = 'SELECT * FROM attachements WHERE virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                if( !empty($row['pdf1']) ):
                    $output  = '<a href="https://www.romynox.nl/downloads/'. $row['pdf1'] .'" target="_blank" style="margin-right: 20px">pdf1</a>';
                endif;
                if( !empty($row['pdf2']) ):
                    $output .= '<a href="https://www.romynox.nl/downloads/'. $row['pdf2'] .'" target="_blank" style="margin-right: 20px">pdf2</a>';
                endif;
                if( !empty($row['pdf3']) ):
                    $output .= '<a href="https://www.romynox.nl/downloads/'. $row['pdf3'] .'" target="_blank" style="margin-right: 20px">pdf3</a>';
                endif;
                if( !empty($row['pdf4']) ):
                    $output .= '<a href="https://www.romynox.nl/downloads/'. $row['pdf4'] .'" target="_blank" style="margin-right: 20px">pdf4</a>';
                endif;
                if( !empty($row['pdf5']) ):
                    $output .= '<a href="https://www.romynox.nl/downloads/'. $row['pdf5'] .'" target="_blank" style="margin-right: 20px">pdf5</a>';
                endif;
                if( !empty($row['pdf6']) ):
                    $output .= '<a href="https://www.romynox.nl/downloads/'. $row['pdf6'] .'" target="_blank" style="margin-right: 20px">pdf6</a>';
                endif;
            endwhile;
        endif;

        return $output;

    }

/*

    Count specs

*/

    public function countSpecs( $id ) {

        $q = 'SELECT value FROM prods_specs AS ps WHERE product_id = '. $id .' AND value != \'\'';
        $result = $this->mysqli->query($q);
        $count = 'Specs: '. mysqli_num_rows( $result );

        /*$q = 'SELECT id FROM spec_cat_link AS scl WHERE category_id = '. $category_id;
        $result = $this->mysqli->query($q);
        $count .= ' / '. mysqli_num_rows( $result );*/
        
        return $count;

    }


    public function getProdGroup( $id ) {
        $dataset    = 'ALL';
        /* Simply get product type and show specs. */
        $q          = 'SELECT p.prodtype FROM producten AS p WHERE p.virtuemart_product_id = '. $id;
        /* Old query to check dataset of specs by category */
        $q_old      = 'SELECT
                    p.virtuemart_product_id,
                    p.product_name,
                    p.prodtype,
                    c.dataset
                    FROM producten AS p 
                    JOIN prod_cat_link pcl ON p.virtuemart_product_id = pcl.virtuemart_product_id
                    JOIN categories c ON pcl.category_id = c.category_id
                    WHERE p.virtuemart_product_id = '. $id .' AND c.dataset IS NOT NULL';
        if( $result = $this->mysqli->query($q) ):
            while( $row = $result->fetch_assoc() ):
                /* Stuff used for dataset by categories... Now switched to Product type
                $dataset = $row['dataset'];

                if( !isset($row['dataset']) ):
                    if( $row['prodtype'] == 'SU') $dataset = 'SINGLEUSE';
                    if( $row['prodtype'] == 'SS') $dataset = 'ALLSPECS';
                endif;
                */
                if( $row['prodtype'] == 'SU') $dataset = 'SINGLEUSE';
                if( $row['prodtype'] == 'SS') $dataset = 'ALLSPECS';

            endwhile;
        endif;

        return $dataset;
    }
/*

    Get specifications

*/
    public function getSpecs( $dataset, $type ) {
        $output     = NULL;
        $id         = self::safeinput( $_POST['id'] );
        $catid      = self::safeinput( $_POST['cat'] );
        $q          = 'SELECT 
                            s.id AS specificationID,
                            s.spec_en,
                            s.type,
                            s.json,
                            s.data,
                            sd.position,
                            ps.value
                        FROM specifications AS s
                        LEFT JOIN spec_datasets AS sd
                            ON s.id = sd.spec_id
                        LEFT JOIN prods_specs as ps
                            ON ps.spec_id = s.id AND ps.product_id = '. $id .'
                        WHERE sd.prod_group = "'. $dataset .'"
                        /* WHERE sc.category_id = "'. $catid .'" */
                        AND s.type = "'. $type .'"';
        if( $type == 'checkbox' ) $q .= 'ORDER BY sd.position ASC';
        if( $type == 'field' ) $q .= 'ORDER BY sd.position ASC';
        if( $type == 'select' ) $q .= 'ORDER BY sd.position ASC';

        // echo '<div class="debug">'. $q .'</div>';

        if( $result = $this->mysqli->query($q) ):
            while( $row = $result->fetch_assoc() ):
                $spec_en    = utf8_encode( $row['spec_en'] );
                $spec_id    = utf8_encode( $row['specificationID'] );
                $spec_value = utf8_encode( $row['value'] );

                // Normal field type specs
                if( $row['type'] == 'field' ):
                    $unit = NULL;
                    if( $row['data'] ) $unit = '<span class="spec--unit">'. $row['data'] .'</span>';

                    $output .= <<<HTML
                        <div class="spec spec--field"><label for="spec-cb-{$spec_id}">{$spec_en}</label><input type="text" id="spec-af-{$spec_id}" name="spec-af-{$spec_id}" value="{$spec_value}">{$unit}</div>
                    HTML;
                endif;

                // Normal field type specs
                if( $row['type'] == 'select' ):
                    $data = $row['data'];
                    //echo $row['specificationID'];
                    $output .= '<div class="spec spec--field"><label for="spec-af-'. $spec_id .'">'. $spec_en .'</label><select id="spec-af-'. $spec_id .'" name="spec-af-'. $spec_id .'">';
                    $output .= '<option value="">&nbsp;</option>';
                    switch( $data ) {
                        case 'maatvoering':
                            $q_data = 'SELECT * FROM maatvoering ORDER BY decimaal';
                            
                            if( $result_data = $this->mysqli->query($q_data) ):
                                while( $row_data = $result_data->fetch_assoc() ):
                                    if( $row_data['decimaal'] == $spec_value ):
                                        $output .= '<option value="'. $row_data['decimaal'] .'" selected>'. $row_data['IMP'] .'" &nbsp; (DN '. $row_data['DIN'] .')</option>';
                                    else:
                                        $output .= '<option value="'. $row_data['decimaal'] .'">'. $row_data['IMP'] .' " &nbsp; (DN '. $row_data['DIN'] .')</option>';
                                    endif;
                                endwhile;
                            endif;
                        break;
                    };

                    $output .= <<<HTML
                        </select></div>
                    HTML;
                endif;

                // Multiple checkbox specs
                if( $row['type'] == 'checkbox' ):
                    
                    $checkboxes         = json_decode( $row['json'], true);
                    $checkbox_values    = json_decode( $row['value'], true);
                    //var_dump($checkbox_values);
                    $output .= '<div class="spec spec--checkbox"><h3>'. $spec_en .'</h3>';

                    //var_dump($checkboxes);
                    //var_dump($checkbox_values);
                     
                    $cb_count = count($checkboxes);
                    foreach( $checkboxes as $key => $value ):
                        if( isset($checkbox_values) ):
                            if( array_key_exists( $key, $checkbox_values )):
                                if( $checkbox_values[$key] == 'on' ):
                                    $output .= '<input type="hidden" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" value="on">';
                                    $output .= '<div><input type="checkbox" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" checked><label for="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'">'. $value .'</label></div>';
                                else:
                                    $output .= '<input type="hidden" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" value="off">';
                                    $output .= '<div><input type="checkbox" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'"><label for="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'">'. $value .'</label></div>';
                                endif;
                            else:
                                $output .= '<input type="hidden" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" value="off">';
                                $output .= '<div><input type="checkbox" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'"><label for="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'">'. $value .'</label></div>';
                            endif;
                        else:
                            $output .= '<input type="hidden" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" value="off">';
                            $output .= '<div><input type="checkbox" name="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'" id="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'"><label for="spec-cb-'. $spec_id .'-'. $key .'-'. $cb_count .'">'. $value .'</label></div>';
                        endif;
                    endforeach;
                    $output .= '</div>';
                endif;

            endwhile;
        endif;

        return $output;
    }

/*
    
    Get images of product

*/
    public function getImages( $id ) {
        $output = NULL;
        $output_images = NULL;

        $q = 'SELECT * FROM prod_image_link WHERE /*prod_image_link.main_image = 1 AND*/ prod_image_link.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query($q) ) :
            while( $row = $result->fetch_assoc() ) :
                //$imagesize = getimagesize( 'http://www.romynox.nl/'. $row['image']);

                /*
                $filename = $row['image']; 
                $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
                $filename_extension = pathinfo($filename, PATHINFO_EXTENSION);

                $imagefilename = $filename_without_ext .' (Klein).'. $filename_extension;
                */
                //echo $filename_extension;

                //$imagefilename = 
                
                if( $row['main_image'] == 1 ):
                    $output_images .= '<img class="product-image main-image product-image-'. $row['pilink_id'] .'" data-product_id="'. $row['virtuemart_product_id'] .'"  data-pilink_id="'. $row['pilink_id'] .'" src="../product-images/thumbs/'. $row['image'] .'">';
                else: 
                    $output_images .= '<img class="product-image product-image-'. $row['pilink_id'] .'" data-product_id="'. $row['virtuemart_product_id'] .'"  data-pilink_id="'. $row['pilink_id'] .'" src="../product-images/thumbs/'. $row['image'] .'">';
                endif;
            endwhile;
        endif;

        $output  = '<form id="form_images'. $id .'" method="post">';
        $output .= $output_images;
        $output .= '</form>';

        return $output;
    }

    public function resetMainImage( $product_id ) {
        $q = '  UPDATE prod_image_link
                SET main_image = 0
                WHERE virtuemart_product_id = '. $product_id ;

        if( $this->mysqli->query($q) ) {
            //echo 'Category saved!';
        } else {
            //echo 'Error saving category';
        }
    }

    public function setMainImage( $product_id, $image_id ) {

        $this->resetMainImage( $product_id );

        $q = '  UPDATE prod_image_link
                SET main_image = 1
                WHERE pilink_id = '. $image_id;
        //echo $q;

        if( $this->mysqli->query($q) ) {
            echo 'Main image set';
        } else {
            echo 'Error setting main image';
        }

    }



    public function saveCatOverview( $product_id, $category_id ) {
        // var_dump( $_POST );

        $q = '  INSERT INTO prod_cat_new_link (virtuemart_product_id, category_id) 
                VALUES ( '. $product_id .', '. $category_id .' )
                ON DUPLICATE KEY UPDATE category_id = '. $category_id;
        //echo $q;

        if( $this->mysqli->query($q) ) {
            echo 'Category saved!';
        } else {
            echo 'Error saving category';
        }
    }

    public function saveUploadImage( $product_id, $image ) {

        $q = '  INSERT INTO prod_image_link (virtuemart_product_id, image) 
                VALUES ( '. $product_id .', "'. $image .'" )';

        if( $this->mysqli->query($q) ) {
            //echo 'Image saved!';
        } else {
            //echo 'Error saving image!';
        }
    }

    public function uploadImage( $product_id ) {

        $target_dir     = "../product-images/";
        $target_file    = $target_dir . basename( $_FILES["fileToUpload"]["name"] );
        $uploadOk       = 1;
        $imageFileType  = strtolower( pathinfo($target_file,PATHINFO_EXTENSION) );
        $filename       = basename( $_FILES["fileToUpload"]["name"] );
        $org_filename   = $filename;
        $counter        = 1;
        // Check if image file is a actual image or fake image
        if( isset($_POST["submit"]) ) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Check if file already exists and add number behind original filename
        while (file_exists($target_file)) {
            $filename_parts = pathinfo($org_filename);
            $new_filename = $filename_parts['filename'] . '_' . $counter . '.' . $filename_parts['extension'];
            $target_file = $target_dir . $new_filename;
            $filename = $new_filename;
            $counter++;
            echo $filename;
        }

        // Check file size
        if($_FILES["fileToUpload"]["size"] > 15000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if($imageFileType == "png" ) {
            $image = imagecreatefrompng( $_FILES["fileToUpload"]["tmp_name"] );
            //imagejpeg($image, 'test.jpg', 6);
        }
        if($imageFileType == "jpg" ) {
            $image = imagecreatefromjpeg( $_FILES["fileToUpload"]["tmp_name"] );
            //$image = imagescale( $image, 300, 300);
            //imagejpeg($image, 'test.jpg', 6);
        }
        
        //$thumb = imagecrop( $image, [ 'x' => 0, 'y' => 0, 'width' => 250, 'height' => 150]);
        $thumb = imagescale( $image, 90, 90 );
        // Check if $uploadOk is set to 0 by an error
        if($uploadOk == 0) {
          echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
          //if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
          if (imagejpeg( $image, $target_dir.$filename, 66 )) {
            imagejpeg( $thumb, $target_dir.'/thumbs/'.$filename, 66 );
            //echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            self::saveUploadImage( $product_id, $filename );
          } else {
            echo "Sorry, there was an error uploading your file.";
          }
        echo self::getImages( $product_id );

        }



    }

/*

    Save data to database 

*/
    public function saveProduct() {
        ksort($_POST);
        $si                         = NULL;
        $insertupdate               = NULL;
        $laatste                    = array_key_last($_POST);
        $product_id                 = safeinput($_POST['product_id']);
        $prodtype                   = safeinput($_POST['prodtype']);
        $product_name               = safeinput($_POST['product_name']);
        $product_name_nl            = safeinput($_POST['product_name_nl']);
        $rxcode                     = NULL;
        $rxcode                     = safeinput($_POST['rxcode']);
        $articlenr                  = safeinput($_POST['articlenr']);
        $product_s_desc             = safeinput($_POST['product_s_desc']);
        $product_paragraph          = safeinput($_POST['product_paragraph']);
        $product_desc               = htmlspecialchars($_POST['product_desc']);
        $product_features           = htmlspecialchars($_POST['product_features']);
        $product_techinfo           = htmlspecialchars($_POST['product_techinfo']);
        $product_s_desc_nl          = safeinput($_POST['product_s_desc_nl']);
        $product_paragraph_nl       = safeinput($_POST['product_paragraph_nl']);
        $product_desc_nl            = htmlspecialchars($_POST['product_desc_nl']);
        $product_features_nl        = htmlspecialchars($_POST['product_features_nl']);
        $product_techinfo_nl        = htmlspecialchars($_POST['product_techinfo_nl']);
        $info_url                   = htmlspecialchars($_POST['info_url']);
        $spec_array[]               = array();
        $q = '  UPDATE producten 
                SET rxcode = "'. $rxcode .'",
                articlenr = "'. $articlenr .'",
                prodtype = "'. $prodtype .'",
                product_name = "'. $product_name .'",
                product_s_desc = "'. $product_s_desc .'",
                product_paragraph = "'. $product_paragraph .'",
                product_desc = "'. $product_desc .'",
                product_features = "'. $product_features .'",
                product_techinfo = "'. $product_techinfo .'",
                product_name_nl = "'. $product_name_nl .'",
                product_s_desc_nl = "'. $product_s_desc_nl .'",
                product_paragraph_nl = "'. $product_paragraph_nl .'",
                product_desc_nl = "'. $product_desc_nl .'",
                product_features_nl = "'. $product_features_nl .'",
                product_techinfo_nl = "'. $product_techinfo_nl .'",
                info_url = "'. $info_url .'"
                WHERE virtuemart_product_id = '. $product_id;
        
        echo '<h2>Debug data</h2>';
        echo '<p>Wanneer je denkt dat er iets fout is gegaan met het opslaan van de data, onderstaande mini-tekst naar Dennis sturen :-)</p>';
        echo '<div class="debug-proces">';
        echo $q;
        echo '</div>';
        echo '<div class="debug-proces">';
        if( $this->mysqli->query($q) ) echo 'SUCCESS';

        foreach( $_POST as $key => $value ):
            if( strpos($key, 'spec-') !== false ):
                $spec_POST  = explode('-', $key);
                $spec_type  = $spec_POST['1'];
                $spec_id    = $spec_POST['2'];
                if( !empty($spec_POST['3']) ) { $spec_pos = $spec_POST['3']; } // e.g. cb1

                $q_exists = 'SELECT id FROM prods_specs WHERE product_id = '. $product_id .' AND spec_id = '. $spec_id .' LIMIT 1';
                echo $q_exists .'<br>';
                $exists = $this->mysqli->query($q_exists);
                $row = $exists->fetch_assoc();

                switch( $spec_type ) {
                    case 'af':
                        if( !$exists->num_rows ): 
                            $q = 'INSERT INTO prods_specs(product_id, spec_id, value) VALUES ( '. $product_id .', '. $spec_id .', "'. $value .'" )';
                        else:
                            $q = 'UPDATE prods_specs SET product_id = "'. $product_id .'", spec_id = "'. $spec_id .'", value = "'. $value .'" WHERE id = '.  $row['id'];
                        endif;
                        
                        if( $this->mysqli->query($q) ) echo '> Field ['. $key .']save done<br>';
                    break;

                    case 'cb':
                        if( is_null($si) || $si == $spec_id ):
                            $spec_checkbox_data[$spec_pos] = $value;
                        endif;

                        if( !is_null($si) && $si != $spec_id ):
                            echo "VOLGENDE";
                            echo json_encode( $spec_checkbox_data ) .'<br>';
                            $save_value = json_encode( $spec_checkbox_data );

                            if( $insertupdate == 'insert' ):
                                $q_save = 'INSERT INTO prods_specs(product_id, spec_id, value) VALUES ( '. $product_id .', '. $si .',\''. $save_value .'\' )';
                                echo $q_save .'(1)<br>';
                                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';
                            endif;
                            if( $insertupdate == 'update' ):
                                $q_save = 'UPDATE prods_specs SET product_id = "'. $product_id .'", spec_id = "'. $si .'", value = \''. $save_value .'\' WHERE id = '.  $unique_id;
                                echo $si .' --- '. $q_save .'(2)<br>';
                                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';
                            endif;

                            unset( $spec_checkbox_data );

                            $spec_checkbox_data[$spec_pos] = $value;
                        endif;

                        if( $key == $laatste ):
                            echo json_encode( $spec_checkbox_data ) .'<br>';
                            $save_value = json_encode( $spec_checkbox_data );
                            
                            if( !$exists->num_rows ):
                                $q_save = 'INSERT INTO prods_specs(product_id, spec_id, value) VALUES ( '. $product_id .', '. $spec_id .',\''. $save_value .'\' )';
                                echo $q_save .'(3)<br>';
                                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';
                            endif;
                            if( $exists->num_rows ):
                                $q_save = 'UPDATE prods_specs SET product_id = "'. $product_id .'", spec_id = "'. $spec_id .'", value = \''. $save_value .'\' WHERE id = '.  $row['id'];
                                echo $q_save .'(4)<br>';
                                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';
                            endif;

                        endif;
                        echo $key .': '. $value .' ('. $spec_id .' - '. $spec_type .' - '. $spec_pos .' - '. $value .')<br>';

                        if( $exists->num_rows ) $insertupdate = 'update';
                        if( !$exists->num_rows ) $insertupdate = 'insert';

                        $si = $spec_id;
                        $unique_id = $row['id']; // set unique id for prods_specs table
                    break;

                }
            endif;
        endforeach;
        echo '</div>';

        //header( 'location: http://10.25.115.55/development/producten/' );
    }

/*

    Proces JSON data

*/
    public function parseJSON( $json, $type ) {
        $output = NULL;

        // Convert JSON data to array (true)
        $json = json_decode($json, true);

        echo '<div>';

        foreach( $Checkboxes1 as $key => $value ):
            if( array_key_exists( $key, $Database )):
                if( $Database[$key] == 1 ){
                    echo '<label for="'. $key .'">'. $value .'</label><input type="checkbox" name="'. $key .'" id="'. $key .'" checked><br>';
                } elseif( $Database[$key] == 0 ){
                    echo '<label for="'. $key .'">'. $value .'</label><input type="checkbox" name="'. $key .'" id="'. $key .'"><br>';
                }
            endif;
        endforeach;

        echo '</div>';
    }






/*

    Get product details

*/

    public $pdf_rxcode;
    public $pdf_product_name;
    public $pdf_product_s_desc;
    public $pdf_product_paragraph;
    public $pdf_product_features;
    public $pdf_product_desc;
    public $pdf_product_techinfo;

    public function getDetailsPDF() {
        $output = NULL;
        $id     = self::safeinput( $_REQUEST['id'] );
        $q      = ' SELECT * FROM producten AS p
                    JOIN prod_image_link pil
                    ON pil.virtuemart_product_id = '. $id .'
                    WHERE p.virtuemart_product_id = '. $id;

        if( $result = $this->mysqli -> query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :
                $this->pdf_rxcode             = htmlspecialchars_decode( $row['rxcode'] );
                $this->pdf_product_name       = htmlspecialchars_decode( utf8_encode($row['product_name']) );
                $this->pdf_product_s_desc     = htmlspecialchars_decode( utf8_encode($row['product_s_desc']) );
                $this->pdf_product_paragraph  = htmlspecialchars_decode( utf8_encode($row['product_paragraph']) );
                $this->pdf_product_features   = htmlspecialchars_decode( utf8_encode($row['product_features']) );
                $this->pdf_product_desc       = utf8_encode( $row['product_desc'] );
                $this->pdf_product_techinfo   = utf8_encode( $row['product_techinfo'] );
                $this->pdf_image              = 'product-images/'. $row['image'];
            endwhile;
        endif;
    }

    public function CategoriesUpdates() {

        $output = NULL;

        $q      = ' SELECT
                        p.virtuemart_product_id,
                        p.product_name,
                        c.category_name,
                        cn.category_id,
                        cn.Woocommerce_nl
                    FROM producten AS p 
                    JOIN prod_cat_link pcl 
                        ON p.virtuemart_product_id = pcl.virtuemart_product_id
                    JOIN categories c 
                        ON pcl.category_id = c.category_id
                    LEFT JOIN categories_new cn
                        ON cn.category_id = c.cat_new_id
                    ORDER BY p.product_name';
        echo $q .'<br>';

        if( $result = $this->mysqli->query( $q ) ) :
            while( $row = $result->fetch_assoc() ) :

                $vmid = $row['virtuemart_product_id'];
                $cid = $row['category_id'];

                $q_save = 'INSERT INTO prod_cat_new_link(virtuemart_product_id, category_id) VALUES ( '. $vmid .', '. $cid .' )';
                echo $q_save .'<br><br>';

                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';

                $cats = NULL;
            endwhile;
        endif;

    }

    public function dloadDescription() {
        $q = NULL;
        $virtuemart_product_id = NULL;
        $catid = NULL;

        if( isset($_POST['cat-select'])):
            $catid = safeinput( $_POST['cat-select']);
        endif;

        $q = NULL;
        $resultArray = array();
        $virtuemart_product_id = NULL;

        if( !empty($catid) ) : 
            $q = ' SELECT 
                        p.virtuemart_product_id,
                        p.rxcode,
                        p.product_name,
                        p.product_desc
                    FROM producten AS p
                    LEFT JOIN prod_cat_new_link pcnl
                    ON pcnl.virtuemart_product_id = p.virtuemart_product_id
                    LEFT JOIN categories_new cn
                    ON cn.category_id = pcnl.category_id
                    WHERE cn.category_id = '. $catid .'
                    ORDER BY p.virtuemart_product_id ASC';

        else :
            $q = ' SELECT 
                        p.virtuemart_product_id,
                        p.rxcode,
                        p.product_name,
                        p.product_desc
                    FROM producten AS p
                    ORDER BY p.virtuemart_product_id ASC';
        endif;

        if( $result = $this->mysqli->query($q) ) :
            while( $row = $result->fetch_assoc() ):
                $product_desc = html_entity_decode($row['product_desc']);

                echo $row['product_name'] .' (VMID: '. $row['virtuemart_product_id'] .')<br>';
                echo '<span data-val="'. $row['virtuemart_product_id'] .'" data-cat="'. $catid .'" class="item--edit">Edit</span>';
                echo '<div class="product-description">'. $product_desc .'</div>';
                echo '<hr>';
            endwhile;
        endif;

    }

    public function stripImageURLS( $imageURL ) {

        $stripped_image = str_replace( 'https://www.romynox.nl/images/stories/virtuemart/product/', '',$imageURL );        
        $stripped_image = str_replace( 'images/stories/virtuemart/product/', '', $stripped_image );

        return( $stripped_image );
    }

    public function categoryProductsOverview() {

        $prev_category = NULL;
        $counter = 0;
        $q = '  SELECT p.virtuemart_product_id, cn.category_id, cn.Woocommerce_en, cn.Woocommerce_nl, p.product_name, p.product_name_nl
                    FROM categories_new AS cn
                LEFT JOIN prod_cat_new_link pcnl
                    ON pcnl.category_id = cn.category_id
                JOIN producten p
                    ON p.virtuemart_product_id = pcnl.virtuemart_product_id
                ORDER BY cn.Woocommerce_nl, p.product_name';
        
        if( $result = $this->mysqli -> query( $q ) ) :
            echo '<ul class="productsListCats">';
            while( $row = $result->fetch_assoc() ) :
                $product_name       = htmlspecialchars_decode($row['product_name']);
                $product_name_nl    = htmlspecialchars_decode($row['product_name_nl']);
                $images             = self::getImages($row['virtuemart_product_id']);
                $catList            = self::newCategoryList( $row['virtuemart_product_id'], $row['category_id'], $row['Woocommerce_nl'] );

                if( $prev_category != $row['Woocommerce_nl'] ) {
                    echo '<h3 style="width:500px; display:inline-block; background-color:gray;padding:2px 8px;margin-right:20px;color:white">NL: '. $row['Woocommerce_nl'] .'</h3><h3 style="width:500px;display:inline-block; background-color:gray;padding:2px 8px;margin-right:20px;color:white">EN: '. $row['Woocommerce_en'] .'</h3>';
                }
                echo '<li><span style="display:inline-block;width:50px;font-weight:bold">'. $row['virtuemart_product_id'] .'</span><span style="display:inline-block;width:500px">'. $product_name_nl .'</span><span style="display:inline-block;width:500px">'. $product_name .'</span>'. $catList .'<span class="product-images">'. $images .'</span></li>';
                $prev_category = $row['Woocommerce_nl'];
                $counter++;
            endwhile;
            echo '</ul>';
            echo '<strong>&nbsp; '. $counter .' producten</strong>';
        endif;

    }



































/*      ============================================================================


        Functions below inject data directly into the Wordpress database, hence it's 
        important the Wordpress database is reachable from the location where this
        tool is located!

        Also backing up the database is mandatory while testing... No way to revert stuff :-)

      =========================================================================== */


/*

        Category injection

*/


    public function categoryInjection() {

        $last_term_id = NULL;
        $last_translation_id = NULL;

        $WP_mysqli = new mysqli( 'localhost','root','root','wordpress_clean' );
        
        $q = 'SELECT * FROM categories_new ORDER BY Woocommerce_en';
        
        if( $result = $this->mysqli->query($q) ):
            while( $row = $result->fetch_assoc() ):

                $WP_q = 'SELECT term_id FROM wp_terms ORDER BY term_id DESC LIMIT 1';

                if( $WP_result = $WP_mysqli->query($WP_q) ):
                    while( $WP_row = $WP_result->fetch_assoc() ):
                        $last_term_id = $WP_row['term_id'];
                    endwhile;
                endif;

                $WP_q = 'SELECT trid FROM wp_icl_translations ORDER BY translation_id DESC LIMIT 1';
                
                if( $WP_result = $WP_mysqli->query($WP_q) ):
                    while( $WP_row = $WP_result->fetch_assoc() ):
                        $last_trid = $WP_row['trid'];
                    endwhile;
                endif;

                //echo $row['Woocommerce_en'] .': ';
                if( strpos( $row['Woocommerce_en'], '>' ) !== false):
                    $parent = false;
                    $cat_en = explode( ' > ', $row['Woocommerce_en'] );
                    $cat_en = $cat_en[1];
                    $cat_en = html_entity_decode( $cat_en );
                    $cat_nl = explode( ' > ', $row['Woocommerce_nl'] );
                    $cat_nl = $cat_nl[1];
                    $cat_nl = html_entity_decode( $cat_nl );
                else:
                    $parent = true;
                    $parent_id_en = $last_term_id+1;
                    $parent_id_nl = $last_term_id+2;
                    $cat_en = html_entity_decode( $row['Woocommerce_en'] );
                    $cat_nl = html_entity_decode( $row['Woocommerce_nl'] );
                endif;

                echo '<strong>'. $row['Woocommerce_en'] .':</strong><br>';
                echo $cat_en .': ';
                $cat_en_slug = $row['slug_en'];
                /*
                $cat_en_slug = str_replace(' & ', '-', $cat_en);
                $cat_en_slug = str_replace('(', '', $cat_en_slug);
                $cat_en_slug = str_replace(')', '', $cat_en_slug);
                $cat_en_slug = str_replace(' ', '-', $cat_en_slug);
                $cat_en_slug = str_replace('/', '-', $cat_en_slug);
                $cat_en_slug = str_replace('--', '-', $cat_en_slug);
                $cat_en_slug = strtolower($cat_en_slug);
                */
                echo $cat_en_slug .'<br>';

                $term_id_en = $last_term_id+1;
                $WP_q_save = 'INSERT INTO wp_terms( term_id, name, slug) VALUES ( '. $term_id_en .', "'. $cat_en .'", "'. $cat_en_slug .'")';
                if( $WP_mysqli->query( $WP_q_save ) ) echo $WP_q_save .': done<br>';
                if( $parent == true ):
                    $WP_q_save = 'INSERT INTO wp_term_taxonomy( term_id, taxonomy, description, parent ) VALUES ( '. $term_id_en .', "product_cat", "", 0)';
                    $WP_q_save2 = 'INSERT INTO wp_wc_category_lookup( category_tree_id, category_id ) VALUES ( '. $term_id_en .', '. $term_id_en .' )';
                endif;

                if( $parent == false ):
                    $WP_q_save = 'INSERT INTO wp_term_taxonomy( term_id, taxonomy, description, parent) VALUES ( '. $term_id_en .', "product_cat", "", '. $parent_id_en .')';
                    $WP_q_save2 = 'INSERT INTO wp_wc_category_lookup( category_tree_id, category_id ) VALUES ( '. $parent_id_en .', '. $term_id_en .' )';
                endif;

                echo $WP_q_save .'<br>';
                if( $WP_mysqli->query( $WP_q_save ) ) echo $WP_q_save .': done<br>';
                if( $WP_mysqli->query( $WP_q_save2 ) ) echo $WP_q_save2 .': done<br>';

                echo '<strong>'. $row['Woocommerce_nl'] .':</strong><br>';
                echo $cat_nl .': ';
                $cat_nl_slug = $row['slug_nl'];
                /*
                $cat_nl_slug = str_replace(' & ', '-', $cat_nl);
                $cat_nl_slug = str_replace('(', '', $cat_nl_slug);
                $cat_nl_slug = str_replace(')', '', $cat_nl_slug);
                $cat_nl_slug = str_replace(' ', '-', $cat_nl_slug);
                $cat_nl_slug = str_replace('/', '-', $cat_nl_slug);
                $cat_nl_slug = str_replace('--', '-', $cat_nl_slug);
                $cat_nl_slug = strtolower($cat_nl_slug);     
                */        
                echo $cat_nl_slug .'<br>';
                if( $parent == true ) echo 'PARENT = TRUE<br>';

                $term_id_nl = $last_term_id+2;
                $WP_q_save = 'INSERT INTO wp_terms( term_id, name, slug) VALUES ( '. $term_id_nl .', "'. $cat_nl .'", "'. $cat_nl_slug .'")';
                if( $WP_mysqli->query( $WP_q_save ) ) echo $WP_q_save .': done<br>';
                if( $parent == true ):
                    $WP_q_save = 'INSERT INTO wp_term_taxonomy( term_id, taxonomy, description, parent) VALUES ( '. $term_id_nl .', "product_cat", "", 0)';
                    $WP_q_save2 = 'INSERT INTO wp_wc_category_lookup( category_tree_id, category_id ) VALUES ( '. $term_id_nl .', '. $term_id_nl .' )';
                endif;
                
                if( $parent == false ):
                    $WP_q_save = 'INSERT INTO wp_term_taxonomy( term_id, taxonomy, description, parent) VALUES ( '. $term_id_nl .', "product_cat", "", '. $parent_id_nl .')';
                    $WP_q_save2 = 'INSERT INTO wp_wc_category_lookup( category_tree_id, category_id ) VALUES ( '. $parent_id_nl .', '. $term_id_nl .' )';
                endif;

                if( $WP_mysqli->query( $WP_q_save ) ) echo $WP_q_save .': done<br>';
                if( $WP_mysqli->query( $WP_q_save2 ) ) echo $WP_q_save2 .': done<br>';
                echo $WP_q_save .'<br>';

                /* 
                    icl_translations 
                */

                $trid_id = $last_trid+1;
                $WP_q_save = 'INSERT INTO wp_icl_translations( element_type, element_id, trid, language_code ) VALUES ( "tax_product_cat", '. $term_id_en .', '. $trid_id .', "en" )';
                $WP_q_save2 = 'INSERT INTO wp_icl_translations( element_type, element_id, trid, language_code ) VALUES ( "tax_product_cat", '. $term_id_nl .', '. $trid_id .', "nl" )';
                if( $WP_mysqli->query( $WP_q_save ) ) echo $WP_q_save .': done<br>';
                if( $WP_mysqli->query( $WP_q_save2 ) ) echo $WP_q_save2 .': done<br>';

            endwhile;
        endif;

    }

    public function WPconnectEnNlProducts() {

        $last_term_id = NULL;
        $last_translation_id = NULL;

        $WP_mysqli = new mysqli( 'localhost', 'root', 'root', 'wordpress_clean' );
        
        $WP_q = '   SELECT m.post_id, m.meta_key, m.meta_value, icl.trid
                    FROM wp_postmeta AS m 
                    JOIN wp_icl_translations icl
                    ON icl.element_id = m.post_id
                    AND icl.element_type = "post_product"
                    WHERE m.meta_key = "_sku"
                    AND m.meta_value LIKE "%en"
                    ORDER BY icl.trid ASC';
        
        if( $WP_result = $WP_mysqli->query( $WP_q ) ):
            while( $WP_row = $WP_result->fetch_assoc() ):
                
                $vmid = substr( $WP_row['meta_value'], 4, -2 );
                $post_id_en = $WP_row['post_id'];

                $WP_sub_q = '   SELECT m.post_id
                                FROM wp_postmeta AS m 
                                WHERE m.meta_value = "vmid'. $vmid .'nl" 
                                ORDER BY m.meta_value ASC';

                if( $WP_sub_result = $WP_mysqli->query( $WP_sub_q ) ):
                    while( $WP_sub_row = $WP_sub_result->fetch_assoc() ):
                        $post_id_nl = $WP_sub_row['post_id'];
                    endwhile;
                endif;                

                echo 'VMID: '. $vmid .' - trid: '. $WP_row['trid'] .' - post_id_en: '. $post_id_en .' - post_id_nl: '. $post_id_nl .'<br>';

                $WP_q_save = '  UPDATE wp_icl_translations
                                SET trid = '. $WP_row['trid'] .'
                                WHERE element_id = '. $post_id_nl;
                echo $WP_q_save .'<br>';
                
                if( $WP_mysqli->query( $WP_q_save ) ) echo $WP_q_save .': done<br>';
                

            endwhile;
        endif;

    }

/*
    
    Elke import staat standaard op engels... Onderstaande functie past de Nederlandse producten aan naar NL in de wp_icl_translations table

*/
    public function update_icl_translations() {

        $last_term_id = NULL;
        $last_translation_id = NULL;

        $WP_mysqli = new mysqli( 'localhost', 'root', 'root', 'wordpress_clean' );
        
        $WP_q = '   SELECT m.post_id, m.meta_key, m.meta_value, p.post_title 
                    FROM wp_postmeta AS m 
                    JOIN wp_posts p ON p.ID = m.post_id 
                    WHERE m.meta_key = "_sku"
                    AND m.meta_value LIKE "%nl"
                    ORDER BY m.meta_value ASC';

        if( $WP_result = $WP_mysqli->query( $WP_q ) ):
            while( $WP_row = $WP_result->fetch_assoc() ):
               
                $WP_sub_q = '   UPDATE wp_icl_translations
                                SET language_code = "nl"
                                WHERE element_id = '. $WP_row['post_id'];
                
                if( $WP_mysqli->query( $WP_sub_q ) ) echo $WP_sub_q .': done<br>';
            endwhile;
        endif;

    }

    public function categorySlugs() {

        $last_term_id = NULL;
        $last_translation_id = NULL;

        $WP_mysqli = new mysqli( 'localhost','root','root','wordpress_clean' );
        
        $q = 'SELECT * FROM categories_new ORDER BY Woocommerce_en';
        
        if( $result = $this->mysqli->query($q) ):
            while( $row = $result->fetch_assoc() ):

                //echo $row['Woocommerce_en'] .': ';
                if( strpos( $row['Woocommerce_en'], '>' ) !== false):
                    $parent = false;
                    $cat_en = explode( ' > ', $row['Woocommerce_en'] );
                    $cat_en = $cat_en[1];
                    $cat_nl = explode( ' > ', $row['Woocommerce_nl'] );
                    $cat_nl = $cat_nl[1];
                else:
                    $parent = true;
                    $parent_id_en = $last_term_id+1;
                    $parent_id_nl = $last_term_id+2;
                    $cat_en = $row['Woocommerce_en'];
                    $cat_nl = $row['Woocommerce_nl'];
                endif;

                echo '<strong>'. $row['Woocommerce_en'] .':</strong><br>';
                echo $cat_en .': ';
                $cat_en_slug = str_replace(' & ', '-', $cat_en);
                $cat_en_slug = str_replace('(', '', $cat_en_slug);
                $cat_en_slug = str_replace(')', '', $cat_en_slug);
                $cat_en_slug = str_replace(' ', '-', $cat_en_slug);
                $cat_en_slug = str_replace('/', '-', $cat_en_slug);
                $cat_en_slug = str_replace('--', '-', $cat_en_slug);
                $cat_en_slug = strtolower($cat_en_slug);
                echo $cat_en_slug .'<br>';

                $q_save = 'UPDATE categories_new SET slug_en = "'. $cat_en_slug .'" WHERE category_id = '. $row['category_id'];
                echo $q_save;
                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';

                echo '<strong>'. $row['Woocommerce_nl'] .':</strong><br>';
                echo $cat_nl .': ';
                $cat_nl_slug = str_replace(' & ', '-', $cat_nl);
                $cat_nl_slug = str_replace('(', '', $cat_nl_slug);
                $cat_nl_slug = str_replace(')', '', $cat_nl_slug);
                $cat_nl_slug = str_replace(' ', '-', $cat_nl_slug);
                $cat_nl_slug = str_replace('/', '-', $cat_nl_slug);
                $cat_nl_slug = str_replace('--', '-', $cat_nl_slug);
                $cat_nl_slug = strtolower($cat_nl_slug);
                echo $cat_nl_slug .'<br>';

                $q_save = 'UPDATE categories_new SET slug_nl = "'. $cat_nl_slug .'" WHERE category_id = '. $row['category_id'];
                if( $this->mysqli->query( $q_save ) ) echo $q_save .': done<br>';

                echo '<br>';
            endwhile;
        endif;

    }


    function CSV_map_id() {
        // Open the File
        $file = fopen( "id-mapping-csv.csv", "r" );
        // as long as is not end of file continue loop through
        $f = fopen( 'mapped-ids.csv', 'w' );
        if ($f === false) {
            die( 'Error opening the file ' . $csv_filename);
        }

         
        while(!feof($file)){
            // get the file string by line
            $thisLine = fgets($file);

            $q = '  SELECT 
                        p.wc_id_en, p.wc_id_nl 
                    FROM 
                        producten AS p
                    WHERE
                        p.wc_id_en = '. $thisLine;

            echo $thisLine .' - ';

            $nl = NULL;

            if( $result = $this->mysqli->query($q) ):
                while( $row = $result->fetch_assoc() ):
                    $nl = $row['wc_id_nl'];
                endwhile;

                if( is_null($nl) ):
                    $nl = 'LEEG';
                endif;

                echo $nl .'<br>';
            endif;

            $csv_array = [
                $thisLine,
                $nl
            ];

            fputcsv( $f, $csv_array, ',' );
        }
        fclose( $file );
        fclose( $f );
    }


































    public function pdfsplit() {
        $output = NULL;
        $q      = 'SELECT * FROM pdf_files ORDER BY virtuemart_product_id';

        if( $result = $this->mysqli -> query( $q ) ) :
            $output .= '<table>';
            $output .= '<tr>';
            $output .= '<th>id</th>';
            $output .= '<th>pdf filename</th>';
            $output .= '</tr>';

            while( $row = $result->fetch_assoc() ) :
                if( !empty($row['pdf1']) ):
                    $filename = str_replace( 'https://www.romynox.nl/downloads/', '', $row['pdf1']);
                    $filename = str_replace( 'https://romynox.nl/downloads/', '', $filename);

                    $output            .= '<tr>';
                    $output            .= '<td>'. $row['virtuemart_product_id'] .'</td><td>'. $filename .'</td>';
                    $output            .= '</tr>';

                    $q_insert = "INSERT INTO pdf ( id, filename, displayname, type, pid ) VALUES ( '', '". $filename ."', NULL, NULL, '". $row['virtuemart_product_id'] ."')";

                    echo $q_insert .'<br>';
                    $this->mysqli -> query( $q_insert );
                endif;
                if( !empty($row['pdf2']) ): 
                    $filename = str_replace( 'https://www.romynox.nl/downloads/', '', $row['pdf2']);
                    $filename = str_replace( 'https://romynox.nl/downloads/', '', $filename);

                    $output            .= '<tr>';
                    $output            .= '<td>'. $row['virtuemart_product_id'] .'</td><td>'. $filename .'</td>';
                    $output            .= '</tr>';

                    $q_insert = "INSERT INTO pdf ( id, filename, displayname, type, pid ) VALUES ( '', '". $filename ."', NULL, NULL, '". $row['virtuemart_product_id'] ."')";

                    echo $q_insert .'<br>';
                    $this->mysqli -> query( $q_insert );
                endif;
                if( !empty($row['pdf3']) ): 
                    $filename = str_replace( 'https://www.romynox.nl/downloads/', '', $row['pdf3']);
                    $filename = str_replace( 'https://romynox.nl/downloads/', '', $filename);

                    $output            .= '<tr>';
                    $output            .= '<td>'. $row['virtuemart_product_id'] .'</td><td>'. $filename .'</td>';
                    $output            .= '</tr>';

                    $q_insert = "INSERT INTO pdf ( id, filename, displayname, type, pid ) VALUES ( '', '". $filename ."', NULL, NULL, '". $row['virtuemart_product_id'] ."')";

                    echo $q_insert .'<br>';
                    $this->mysqli -> query( $q_insert );
                endif;
                if( !empty($row['pdf4']) ): 
                    $filename = str_replace( 'https://www.romynox.nl/downloads/', '', $row['pdf4']);
                    $filename = str_replace( 'https://romynox.nl/downloads/', '', $filename);

                    $output            .= '<tr>';
                    $output            .= '<td>'. $row['virtuemart_product_id'] .'</td><td>'. $filename .'</td>';
                    $output            .= '</tr>';

                    $q_insert = "INSERT INTO pdf ( id, filename, displayname, type, pid ) VALUES ( '', '". $filename ."', NULL, NULL, '". $row['virtuemart_product_id'] ."')";

                    echo $q_insert .'<br>';
                    $this->mysqli -> query( $q_insert );
                endif;
                if( !empty($row['pdf5']) ): 
                    $filename = str_replace( 'https://www.romynox.nl/downloads/', '', $row['pdf5']);
                    $filename = str_replace( 'https://romynox.nl/downloads/', '', $filename);

                    $output            .= '<tr>';
                    $output            .= '<td>'. $row['virtuemart_product_id'] .'</td><td>'. $filename .'</td>';
                    $output            .= '</tr>';

                    $q_insert = "INSERT INTO pdf ( id, filename, displayname, type, pid ) VALUES ( '', '". $filename ."', NULL, NULL, '". $row['virtuemart_product_id'] ."')";

                    echo $q_insert .'<br>';
                    $this->mysqli -> query( $q_insert );
                endif;
                if( !empty($row['pdf6']) ): 
                    $filename = str_replace( 'https://www.romynox.nl/downloads/', '', $row['pdf6']);
                    $filename = str_replace( 'https://romynox.nl/downloads/', '', $filename);

                    $output            .= '<tr>';
                    $output            .= '<td>'. $row['virtuemart_product_id'] .'</td><td>'. $filename .'</td>';
                    $output            .= '</tr>';

                    $q_insert = "INSERT INTO pdf ( id, filename, displayname, type, pid ) VALUES ( '', '". $filename ."', NULL, NULL, '". $row['virtuemart_product_id'] ."')";

                    echo $q_insert .'<br>';
                    $this->mysqli -> query( $q_insert );
                endif;
            endwhile;

            //$output .= '</table>';
        endif;

        return $output;
    }

















    public function importPDFtypes() {

        // Define the file name and path
        $file = 'pdffiles.csv';

        // Open the CSV file for reading
        $csv_file = fopen($file, 'r');

        if ($csv_file !== false) {
            // Initialize an array to store the CSV data
            $csv_data = array();

            // Read and process the CSV data line by line
            while (($row = fgetcsv($csv_file, null, ';')) !== false) {
                // Assuming the CSV file has three columns (adjust as needed)
                $filename = $row[0];
                $filename = str_replace( ' ', '%20', $filename );
                $column2 = $row[1];
                $pdftype = $row[3];

                $q = 'SELECT * FROM pdf WHERE filename LIKE "%'. $filename .'%"';

                echo 'CSV Filename: '. $filename .'<br>';

                if( $result = $this->mysqli -> query( $q ) ) :

                    while( $row = $result->fetch_assoc() ) :
                        if( !empty($row)):
                            echo 'Database Filename: '. $row['filename'] .'<br>';

                            $q_update = 'UPDATE pdf SET type = "'. $pdftype .'" WHERE filename LIKE "%'. $filename .'%"';
                            echo $q_update;
                            $this->mysqli -> query( $q_update );
                        endif;
                    endwhile;
                endif;

                echo '<hr>';
            }

            // Close the CSV file
            fclose($csv_file);

            // At this point, you can do further processing with the $csv_data array
            // For example, you can display the imported data to the user
        } else {
            echo "Failed to open the CSV file.";
        }

    }

    public function importPDFnewfilenames() {

        // Define the file name and path
        $file = 'pdf-bestanden-FILTERED.csv';

        // Open the CSV file for reading
        $csv_file = fopen($file, 'r');

        if ($csv_file !== false) {
            // Initialize an array to store the CSV data
            $csv_data = array();

            // Read and process the CSV data line by line
            while (($row = fgetcsv($csv_file, null, ';')) !== false) {
                // Assuming the CSV file has three columns (adjust as needed)
                $filename       = $row[0];
                $filename       = str_replace(' ', '%20', $filename);
                $filename_new   = str_replace(' ', '_', $row[1]) .'.pdf';

                echo $filename .' -----> '. $filename_new;

                $q = 'SELECT * FROM pdf WHERE filename LIKE "%'. $filename .'%"';

                echo 'CSV Filename: '. $filename .'<br>';

                if( $result = $this->mysqli -> query( $q ) ) :

                    while( $row = $result->fetch_assoc() ) :
                        if( !empty($row)):

                            $q_update = 'UPDATE pdf SET filename_new = "'. $filename_new .'" WHERE filename LIKE "%'. $filename .'%"';
                            echo $q_update;
                            $this->mysqli -> query( $q_update );
                        endif;
                    endwhile;
                endif;

                echo '<hr>';
            }


            // Close the CSV file
            fclose($csv_file);

            // At this point, you can do further processing with the $csv_data array
            // For example, you can display the imported data to the user
        } else {
            echo "Failed to open the CSV file.";
        }

    }


    public function copyFile($sourceFile, $destinationFile) {

        echo $sourceFile .'<br>';

        if (copy( $sourceFile, $destinationFile)) {
            return true;
        } else {
            return false;
        }
    }

    public function renamePDFFiles() {
        $output = NULL;
        $q      = 'SELECT * FROM attachements WHERE type <> "movie" ORDER BY type';

        if( $result = $this->mysqli -> query( $q ) ) :
            echo '<table>';
            while( $row = $result->fetch_assoc() ) :
                //echo '<td>'. $row['type'] .'</td><td>'. $row['filename'] .'</td><td>'. $row['filename_new'] .'</td>';

                if (file_exists( 'downloads/'. $row['filename'])) {

                    self::copyFile( 'downloads/'. $row['filename'], 'downloads_renamed/'. $row['filename_new']);

                } else {
                    echo '<tr>';        
                    echo '<td>'. $row['type'] .'</td><td>'. $row['filename'] .'</td><td>Does not exist!</td>';
                    echo '</tr>';
                }

            endwhile;
            echo '</table>';
        endif;
    }

    public function checkPDFFiles() {

        $q      = 'SELECT * FROM attachements WHERE type <> "movie" ORDER BY type';
        $counter = 0;

        if( $result = $this->mysqli -> query( $q ) ) :
            echo '<table>';
            while( $row = $result->fetch_assoc() ) :
                $counter++;
                $exist = NULL;
                if (!file_exists( 'downloads_renamed/'. $row['filename_new'])):
                    $exist = 'Does not exist!';
                endif;

                echo '<tr>';        
                echo '<td>'. $row['id'] .'</td><td>'. $row['filename'] .'</td><td>'. $row['filename_new'] .'</td><td>'. $exist .'</td>';
                echo '</tr>';

            endwhile;
            echo '</table>';

            echo $counter;
        endif;

    }



    public function generatePDFImport() {

        $q          = ' SELECT p.product_name, p.virtuemart_product_id, p.wc_id_nl, p.wc_id_en
                        FROM producten AS p';

        if( $result = $this->mysqli -> query( $q ) ) :

            $csv_filename = 'import-pdf-brochure.csv';
            $f = fopen($csv_filename, 'w');
            if ($f === false) { die('Error opening the file ' . $csv_filename); }

            $csv_columns = [
                'ID',
                'Meta: download_1_url',
                'Meta: download_1_file_name',
                'Meta: download_2_url',
                'Meta: download_2_file_name',
                'Meta: download_3_url',
                'Meta: download_3_file_name',
                'Meta: download_4_url',
                'Meta: download_4_file_name',
                'Meta: download_5_url',
                'Meta: download_5_file_name',
                'Meta: download_6_url',
                'Meta: download_6_file_name',
            ];

            /* 
                ACF Fields: Brochure
            */
            $csv_columns_brochure = [
                'ID',
                'Meta: brochure_1_title',
                'Meta: brochure_1_file',
                'Meta: brochure_2_title',
                'Meta: brochure_2_file',
                'Meta: brochure_3_title',
                'Meta: brochure_3_file',
                'Meta: brochure_4_title',
                'Meta: brochure_4_file',
                'Meta: brochure_5_title',
                'Meta: brochure_5_file',
                'Meta: brochure_6_title',
                'Meta: brochure_6_file',
                'Meta: brochure_7_title',
                'Meta: brochure_7_file',
                'Meta: brochure_8_title',
                'Meta: brochure_8_file',
            ];

            /* 
                ACF Fields: Certificate
            */
            $csv_columns_certificate = [
                'ID',
                'Meta: certificate_1_title',
                'Meta: certificate_1_file',
                'Meta: certificate_2_title',
                'Meta: certificate_2_file',
                'Meta: certificate_3_title',
                'Meta: certificate_3_file',
                'Meta: certificate_4_title',
                'Meta: certificate_4_file',
                'Meta: certificate_5_title',
                'Meta: certificate_5_file',
                'Meta: certificate_6_title',
                'Meta: certificate_6_file',
                'Meta: certificate_7_title',
                'Meta: certificate_7_file',
                'Meta: certificate_8_title',
                'Meta: certificate_8_file',
            ];

            /* 
                ACF Fields: Datasheet
            */
            $csv_columns_datasheet = [
                'ID',
                'Meta: datasheet_1_title',
                'Meta: datasheet_1_file',
                'Meta: datasheet_2_title',
                'Meta: datasheet_2_file',
                'Meta: datasheet_3_title',
                'Meta: datasheet_3_file',
                'Meta: datasheet_4_title',
                'Meta: datasheet_4_file',
                'Meta: datasheet_5_title',
                'Meta: datasheet_5_file',
                'Meta: datasheet_6_title',
                'Meta: datasheet_6_file',
                'Meta: datasheet_7_title',
                'Meta: datasheet_7_file',
                'Meta: datasheet_8_title',
                'Meta: datasheet_8_file',
            ];

            /* 
                ACF Fields: Manual
            */
            $csv_columns_manual = [
                'ID',
                'Meta: manual_1_title',
                'Meta: manual_1_file',
                'Meta: manual_2_title',
                'Meta: manual_2_file',
                'Meta: manual_3_title',
                'Meta: manual_3_file',
                'Meta: manual_4_title',
                'Meta: manual_4_file',
                'Meta: manual_5_title',
                'Meta: manual_5_file',
                'Meta: manual_6_title',
                'Meta: manual_6_file',
                'Meta: manual_7_title',
                'Meta: manual_7_file',
                'Meta: manual_8_title',
                'Meta: manual_8_file',
            ];

            fputcsv( $f, $csv_columns_brochure, ',' );

            while( $row = $result->fetch_assoc() ) :
                $pdffile_1_url = NULL;
                $pdffile_1_file_name = NULL;
                $pdffile_2_url = NULL;
                $pdffile_2_file_name = NULL;
                $pdffile_3_url = NULL;
                $pdffile_3_file_name = NULL;
                $pdffile_4_url = NULL;
                $pdffile_4_file_name = NULL;
                $pdffile_5_url = NULL;
                $pdffile_5_file_name = NULL;
                $pdffile_6_url = NULL;
                $pdffile_6_file_name = NULL;
                $pdffile_7_url = NULL;
                $pdffile_7_file_name = NULL;
                $pdffile_8_url = NULL;
                $pdffile_8_file_name = NULL;

                //echo $row['product_name'] .' - '. $row['wc_id_nl'] .' - '. $row['wc_id_en'] .'<br>';

                $q_sub  = ' SELECT att.filename_new, att.displayname FROM attachements AS att
                            WHERE att.vmid = '. $row['virtuemart_product_id'] .' AND type = "brochure"';

                if( $result_sub = $this->mysqli -> query( $q_sub ) ):
                    $counter = 1;
                    while( $row_sub = $result_sub->fetch_assoc() ):
                        if( $counter == 1):
                            $pdffile_1_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_1_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 2):
                            $pdffile_2_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_2_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 3):
                            $pdffile_3_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_3_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 4):
                            $pdffile_4_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_4_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 5):
                            $pdffile_5_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_5_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 6):
                            $pdffile_6_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_6_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 7):
                            $pdffile_7_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_7_file_name   = $row_sub['displayname'];
                        endif;
                        if( $counter == 8):
                            $pdffile_8_url         = 'https://romynox.nl/downloads/'. $row_sub['filename_new'];
                            $pdffile_8_file_name   = $row_sub['displayname'];
                        endif;

                        $counter++;
                    endwhile;

                    if( !is_null($pdffile_1_url)):
                        $csv_data = [
                            $row['wc_id_en'],
                            $pdffile_1_file_name,
                            $pdffile_1_url,
                            $pdffile_2_file_name,
                            $pdffile_2_url,
                            $pdffile_3_file_name,
                            $pdffile_3_url,
                            $pdffile_4_file_name,
                            $pdffile_4_url,
                            $pdffile_5_file_name,
                            $pdffile_5_url,
                            $pdffile_6_file_name,
                            $pdffile_6_url,
                            $pdffile_7_file_name,
                            $pdffile_7_url,
                            $pdffile_8_file_name,
                            $pdffile_8_url,
                        ];
                        fputcsv( $f, $csv_data, ',' );
                        
                        $csv_data = [
                            $row['wc_id_nl'],
                            $pdffile_1_file_name,
                            $pdffile_1_url,
                            $pdffile_2_file_name,
                            $pdffile_2_url,
                            $pdffile_3_file_name,
                            $pdffile_3_url,
                            $pdffile_4_file_name,
                            $pdffile_4_url,
                            $pdffile_5_file_name,
                            $pdffile_5_url,
                            $pdffile_6_file_name,
                            $pdffile_6_url,
                            $pdffile_7_file_name,
                            $pdffile_7_url,
                            $pdffile_8_file_name,
                            $pdffile_8_url,
                        ];
                        fputcsv( $f, $csv_data, ',' );
                    endif;
                endif;

            endwhile;

        endif;




    }






}

?>