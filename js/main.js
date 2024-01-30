function destroyTinyMCE() {
    if( tinyMCE.get( 'tinymce-paragraph' ) ){
        tinymce.remove( '#tinymce-paragraph' );
    }
    if( tinyMCE.get( 'tinymceeditor' ) ){
        tinymce.remove( '#tinymceeditor' );
    }
    if( tinyMCE.get( 'tinymceeditor_nl' ) ){
        tinymce.remove( '#tinymceeditor_nl' );
    }
    if( tinyMCE.get( 'tinymce-features' ) ){
        tinymce.remove( '#tinymce-features' );
        console.log( 'succes: tinymce - removed' )
    }                         
    if( tinyMCE.get( 'tinymce-paragraph_nl' ) ){
        tinymce.remove( '#tinymce-paragraph_nl' );
    }
    if( tinyMCE.get( 'tinymce-features_nl' ) ){
        tinymce.remove( '#tinymce-features_nl' );
        console.log( 'succes: tinymce - removed' )
    }
    if( tinyMCE.get( 'tinymce-techinfo' ) ){
        tinymce.remove( '#tinymce-techinfo' );
        console.log( 'succes: tinymce - removed' )
    }                         
    if( tinyMCE.get( 'tinymce-techinfo_nl' ) ){
        tinymce.remove( '#tinymce-techinfo_nl' );
        console.log( 'succes: tinymce - removed' )
    }                             
}

function countFirstParagraph() {
    var maxLength = 15;

    $('#product_paragraph').keyup(function() {
        var textlen = maxLength - $(this).val().length;
        $('#product_paragraph_chars').text(textlen);
    });
}



function popup( state ) {
    if( state == 'show' ) {
        $( 'body' ).css( 'overflow', 'hidden' );
        $( '.popup' ).removeClass( 'popup--hidden' );
        console.log( 'succes: popup - show' );
    } else {
        $( 'body' ).css( 'overflow', 'visible' );
        $( '.popup' ).addClass( 'popup--hidden' );
        console.log( 'succes: popup - hide' );
    }
}

$(document).ready(function() {

    $( '#close' ).on( 'click', function() {
        destroyTinyMCE();

        popup( 'hide' );
    });

    var selected = localStorage.getItem( 'selected' );

    if (selected) { 
        $( '#cat-select' ).val( selected );
    }

    $( '#cat-select' ).change( function() {
        localStorage.setItem( 'selected', $( this ).val() );
        $( '#topbar-form' ).submit();
    });


    $( '.1item--pdf' ).on( 'click', function() {
        var id      = $( this ).data( 'val' );

        popup( 'show' );

        $.ajax({
            url: 'mpdf.php', 
            type: 'POST',
            data: { id: id },
            success: function( result ){
                console.log(id)
                window.open();
/*                var blob=new Blob([result], { type: "application/pdf" });
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download="pdf"+id+".pdf";
                link.click(); */
                //$( '#popup--content' ).html( result );
            } // end succes call $ajax
        
        }); // end $.ajax
    });

/*

    Edit item / Open popup

*/
/*
    $(document).on( 'change', '.quick-cat-select', function(event) { 
        alert( 'Click' );
    });
*/



    $( '.btn-searchrx' ).on( 'click', function() {
        $( '#searchrx' ).submit();
    });
 



    $( '.btn-saveitem' ).on( 'click', function() {
        var id      = $( this ).data( 'saveitemid' );
        //alert( 'Click'+id );

        $.ajax({
            url: 'saveitemcategory.php', 
            type: 'POST',
            data: $( '#form'+id ).serialize(),
            success: function( result ){
                $( '#itemstatus'+id ).html( result )
                //alert( 'saved' );
            } // end succes call $ajax
        }); // end $.ajax
    });


    // SELECT MAIN IMAGE
    $( '.fileToUpload' ).on('change',(function() {
        var id = $(this).parent( 'form' ).data( 'id' );
        $( '#formupload'+id ).submit();
    }));

    // PROCESS UPLOADED IMAGE
    $( '.formupload' ).on( 'submit', function(e) {
        e.preventDefault();
        var id = $( this ).data( 'id' );

        //alert( 'Click'+id );

        $.ajax({
            url: 'uploadimage.php', 
            type: 'POST',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data){
                //$( '#itemstatus'+id ).html( result )
                $( '.images-wrapper-'+id ).html( data );


                $( '.product-image' ).on( 'click', function() {
                    var pilinkid      = $( this ).data( 'pilink_id' );
                    var productid     = $( this ).data( 'product_id' );
                    //alert( 'Click'+pilinkid );

                    $.ajax({
                        url: 'setmainimage.php', 
                        type: 'POST',
                        data: { pilinkid: pilinkid,
                                productid: productid },
                        // data: $( '#form_images'+productid ).serialize(),
                        success: function( result ){
                            $( '.imageupdate'+productid ).html( result );
                            // alert( 'Main image set' );
                            $( '#form_images'+productid+' .product-image' ).removeClass( 'main-image' );
                            $( '.product-image-'+pilinkid).addClass( 'main-image' );
                        } // end succes call $ajax
                    }); // end $.ajax
                });


                //alert( '.images-wrapper-'+id );
                //alert( 'saved' );
            } // end succes call $ajax
        }); // end $.ajax
    });

    $( '.product-image' ).on( 'click', function() {
        var pilinkid      = $( this ).data( 'pilink_id' );
        var productid     = $( this ).data( 'product_id' );
        //alert( 'Click'+pilinkid );

        $.ajax({
            url: 'setmainimage.php', 
            type: 'POST',
            data: { pilinkid: pilinkid,
                    productid: productid },
            // data: $( '#form_images'+productid ).serialize(),
            success: function( result ){
                $( '.imageupdate'+productid ).html( result );
                // alert( 'Main image set' );
                $( '#form_images'+productid+' .product-image' ).removeClass( 'main-image' );
                $( '.product-image-'+pilinkid).addClass( 'main-image' );
            } // end succes call $ajax
        }); // end $.ajax
    });


    $( '.item--edit' ).on( 'click', function() {
        var id      = $( this ).data( 'val' );
        var cat     = $( this ).data( 'cat' );

        popup( 'show' );

        $.ajax({
            url: 'edit.php', 
            type: 'POST',
            data: { id: id,
                    cat: cat },
            success: function( result ){
                $(document).on( 'change', 'input:checkbox', function(event) { 
                    var checkbox = $(this).attr('id');

                    if( this.checked == true) {
                        $('#' + checkbox).val('on');
                    } else {
                        $('#' + checkbox).val('off');            
                    }
                });


                $( '#popup--content' ).html( result );

                $( 'textarea#tinymce-paragraph' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: false,
                    force_p_newlines : false,
                    forced_root_block : '',
                    plugins: [ 'wordcount' ],
                    toolbar: 'pasteword | undo redo bullist | removeformat',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });
                $( 'textarea#tinymce-paragraph_nl' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: false,
                    force_p_newlines : false,
                    forced_root_block : '',
                    plugins: [ 'wordcount' ],
                    toolbar: 'pasteword | undo redo bullist | removeformat',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });


                $( 'textarea#tinymceeditor' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    removed_menuitems: 'file',
                    height: 500,
                    menubar: 'edit insert view format table tools',  // skip file
                    plugins: [
                        'advlist autolink lists link charmap anchor',
                        'visualblocks code fullscreen',
                        'insertdatetime table paste code wordcount'
                    ],
                    toolbar: 'pasteword | undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });
                $( 'textarea#tinymceeditor_nl' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: 'edit insert view format table tools',  // skip file
                    plugins: [
                        'advlist autolink lists link charmap anchor',
                        'visualblocks code fullscreen',
                        'insertdatetime table paste code wordcount'
                    ],
                    toolbar: 'pasteword | undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });

                $( 'textarea#tinymce-techinfo' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: 'edit insert view format table tools',  // skip file
                    plugins: [
                        'advlist autolink lists link charmap anchor',
                        'visualblocks code fullscreen',
                        'insertdatetime table paste code wordcount'
                    ],
                    toolbar: 'pasteword | undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });
                $( 'textarea#tinymce-techinfo_nl' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: 'edit insert view format table tools',  // skip file
                    plugins: [
                        'advlist autolink lists link charmap anchor',
                        'visualblocks code fullscreen',
                        'insertdatetime table paste code wordcount'
                    ],
                    toolbar: 'pasteword | undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });
                
                $( 'textarea#tinymce-features' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: false,
                    force_p_newlines : false,
                    forced_root_block : '',
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'pasteword | undo redo bullist | removeformat',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });
                $( 'textarea#tinymce-features_nl' ).tinymce({
                    font_formats: "Poppins=Poppins",
                    content_style: "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap'); body { font-size: 12px; font-family: Poppins, sans-serif; }",
                    height: 500,
                    menubar: false,
                    force_p_newlines : false,
                    forced_root_block : '', 
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'pasteword | undo redo bullist | removeformat',
                    init_instance_callback: function (editor) {
                          $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                       }                    
                });

                countFirstParagraph();
                
                $( '#close' ).on( 'click', function() {
                    destroyTinyMCE();
                    popup( 'hide' );
                });

                $( '.edit--toggle-content').on( 'click', function() {
                    $( '.edit--content-container' ).slideToggle();
                });

                $( '.edit--toggle-pdf').on( 'click', function() {
                    $( '.details--pdf' ).slideToggle();
                });

                $( '#spec-checkbox-toggle').on( 'click', function() {
                    $( '#spec-checkbox-container' ).slideToggle();
                });

                $( '#close-notice' ).on( 'click', function() {
                    $( this ).hide();
                });

                $( '#submitform' ).on( 'click', function() {
                    var content_paragraph       = tinyMCE.get( 'tinymce-paragraph' ).getContent();                
                    var content_description     = tinyMCE.get( 'tinymceeditor' ).getContent();
                    var content_features        = tinyMCE.get( 'tinymce-features' ).getContent();
                    var content_techinfo        = tinyMCE.get( 'tinymce-techinfo' ).getContent();
                    var content_paragraph_nl    = tinyMCE.get( 'tinymce-paragraph_nl' ).getContent();                
                    var content_description_nl  = tinyMCE.get( 'tinymceeditor_nl' ).getContent();
                    var content_features_nl     = tinyMCE.get( 'tinymce-features_nl' ).getContent();
                    var content_techinfo_nl     = tinyMCE.get( 'tinymce-techinfo_nl' ).getContent();

                    localStorage.setItem( "product_paragraph", content_paragraph );
                    localStorage.setItem( "productText", content_description );
                    localStorage.setItem( "product_features", content_features );
                    localStorage.setItem( "product_techinfo", content_techinfo );
                    localStorage.setItem( "product_paragraph_nl", content_paragraph_nl );
                    localStorage.setItem( "productText_nl", content_description_nl );
                    localStorage.setItem( "product_features_nl", content_features_nl );
                    localStorage.setItem( "product_techinfo_nl", content_techinfo_nl );
                    tinyMCE.triggerSave();
                    $( '#tinymceeditor' ).tinymce().save();
                    $( '#tinymce-features' ).tinymce().save();
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_paragraph' )
                                    .attr( 'value', content_paragraph )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_desc' )
                                    .attr( 'value', content_description )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_features' )
                                    .attr( 'value', content_features )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_techinfo' )
                                    .attr( 'value', content_techinfo )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_paragraph_nl' )
                                    .attr( 'value', content_paragraph_nl )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden')
                                    .attr( 'name', 'product_desc_nl' )
                                    .attr( 'value', content_description_nl )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_features_nl' )
                                    .attr( 'value', content_features_nl )
                                    .appendTo( '#product' );
                    $( '<input />' ).attr( 'type', 'hidden' )
                                    .attr( 'name', 'product_techinfo_nl' )
                                    .attr( 'value', content_techinfo_nl )
                                    .appendTo( '#product' );

                    destroyTinyMCE();                        

                    $.ajax({
                        url: 'proces.php', 
                        type: 'POST',
                        data: $( '#product' ).serialize(),
                        success: function( result ){
                            $( '#popup--content' ).html( result )

                            $( '#close' ).on( 'click', function() {
                                destroyTinyMCE();
                          
                                popup( 'hide' );
                            });

                            $.ajax({
                                url: 'item.php', 
                                type: 'POST',
                                data: { id: id, part: 'productname' },
                                success: function( result ){
                                    $( '#item-'+id+' h2' ).html( result )

                                } // end succes call $ajax
                            }); // end $.ajax

                            $.ajax({
                                url: 'item.php', 
                                type: 'POST',
                                data: { id: id, part: 'rxcode' },
                                success: function( result ){
                                    $( '#item-'+id+' .item--rxcode' ).html( result )
                                } // end succes call $ajax
                            }); // end $.ajax
                        } // end succes call $ajax
                    }); // end $.ajax
                });

            } // end succes call $ajax
        
        }); // end $.ajax


    })  

// Close item/popup
    $( '#open-configurator' ).on( 'click', function() {
        popup( 'show' );

        $.ajax({
            url: 'configurator.php', 
            type: 'POST',
            data: { },
            success: function( result ){
                $( '#popup--content' ).html( result );

                $(document).on( 'change', 'select#hose', function(event) { 

                    var pid = $(this).val()

                    $.ajax({
                        url: 'configurator-image.php', 
                        type: 'POST',
                        data: { pid: pid },
                        success: function( result ){
                            $( '#hose-image' ).html( result );
                        }
                    });
                });

                $(document).on( 'change', 'select#fitting1', function(event) { 
                    var pid = $(this).val()

                    $.ajax({
                        url: 'configurator-image.php', 
                        type: 'POST',
                        data: { pid: pid },
                        success: function( result ){
                            $( '#fitting1-image' ).html( result );
                        }
                    });
                });

                $(document).on( 'change', 'select#fitting2', function(event) { 
                    var pid = $(this).val()

                    $.ajax({
                        url: 'configurator-image.php', 
                        type: 'POST',
                        data: { pid: pid },
                        success: function( result ){
                            $( '#fitting2-image' ).html( result );
                        }
                    });
                });


            }
        });
    });


});