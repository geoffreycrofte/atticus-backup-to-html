<?php 
    global $data;
    include( 'inc/utilities.php' );
    $data = get_book_data();
    $by = get_book_info('lang') === 'fr' ? 'par' : 'by';
    $sommaire = get_book_info('lang') === 'fr' ? 'Sommaire' : 'Table of Content';
    $chaptername = get_book_info('lang') === 'fr' ? 'Chapitre' : 'Chapter';
?><!DOCTYPE html>
<html lang="<?php echo get_book_info('lang'); ?>"<?php echo isset( $_GET['print'] ) ? ' class="print"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_book_info('title'); ?> : <?php echo get_book_info('subtitle'); ?> - ebook <?php echo $by; ?> <?php echo get_book_info('author'); ?></title>

    <meta name="author" content="<?php echo get_book_info('author'); ?>"/>
    <meta name="subject" content="<?php echo get_book_info('title'); ?> : <?php echo get_book_info('subtitle'); ?>"/>
    <meta name="keywords" content="accessibility, html to pdf, wcag, section 508"/>
    <meta name="date" content="<?php echo date('Y-m-d'); ?>"/>
    <meta name="generator" content="Web Browser"/>

    <?php 
    if ( isset( $_GET['print'] ) ) {
        echo '<style>' . file_get_contents("assets/styles.css") . '</style>';
        //echo '<link rel="stylesheet" href="assets/paged.interface.css" media="screen">';
        //echo '<script src="assets/paged.polyfill.js"></script>';
    } else {
        echo '<link rel="stylesheet" href="assets/styles.css">';
    }
    ?>

</head>
<body>

    <?php
    
    //echo '<br><br>losange<br><br>' . maybe_base64url( 'assets/img/losange.png' );
    //echo '<br><br>losange2<br><br>' . maybe_base64url( 'assets/img/losange-2.png' );
    /*echo '<br><br>regularwoff2<br><br>' . maybe_base64url( 'assets/fonts/poppins-regular.woff2', 'font/woff2' );
    echo '<br><br>regularwoff<br><br>' . maybe_base64url( 'assets/fonts/poppins-regular.woff', 'font/woff' );
    echo '<br><br>italicwoff2<br><br>' . maybe_base64url( 'assets/fonts/poppins-italic.woff2', 'font/woff2' );
    echo '<br><br>italicwoff<br><br>' . maybe_base64url( 'assets/fonts/poppins-italic.woff', 'font/woff' );
    echo '<br><br>boldwoff2<br><br>' . maybe_base64url( 'assets/fonts/poppins-bold.woff2', 'font/woff2' );
    echo '<br><br>boldwoff<br><br>' . maybe_base64url( 'assets/fonts/poppins-bold.woff', 'font/woff' );
    echo '<br><br>blackwoff2<br><br>' . maybe_base64url( 'assets/fonts/poppins-black.woff2', 'font/woff2' );
    echo '<br><br>blackwoff<br><br>' . maybe_base64url( 'assets/fonts/poppins-black.woff', 'font/woff' );
    exit;
    */
    ?>

    <header role="banner" class="no-pdf">
        <h1 class="sr-only">
            <?php echo get_book_info('title'); ?>
            <span><?php echo get_book_info('subtitle'); ?></span>
            <span class="author"><?php echo $by; ?> <?php echo get_book_info('author'); ?></span>
        </h1>
        <?php echo get_book_info('cover', array(
            'alt' => '',
            'width' => '210',
            'height' => '297'
        )); ?>
        <p class="isbn">e-ISBN : <?php echo get_book_info('eisbn'); ?></p>
        <p class="isbn">ISBN : <?php echo get_book_info('isbn'); ?></p>
    </header>


    <main role="main">
    <?php
        $pages = get_book_info('genericPages');
        foreach ( $pages as $page ) {
    ?>
        <section id="<?php eesc_attr( $page['_id'] ); ?>" data-title="<?php eesc_attr( $page['title'] ); ?>">
            
        <?php if ( $page['type'] === 'image' && isset( $page['fullpageImage']['imageUrl'] ) ) { ?>

            <?php echo ( $page['title'] === 'Title Page' ) ? '<h1>' : ''; ?>

            <img class="full-screen-image" src="<?php eesc_attr( $page['fullpageImage']['imageUrl'] ); ?>" alt="<?php eesc_attr( get_book_info('title') . ' ' . get_book_info('subtitle') ) ?>" data-printextent="<?php eesc_attr( $page['fullpageImage']['printExtent'] ); ?>" data-verticalalign="<?php eesc_attr( $page['fullpageImage']['verticalAlignment'] ); ?>">
            
            <?php echo ( $page['title'] === 'Title Page' ) ? '</h1>' : ''; ?>

        <?php } elseif ( ! empty( $page['children'] ) ) {

            $child_content = get_children_markup( $page['children'], $page['_id'] );
            echo $child_content[0]; //0 = content, 1 = links

        } elseif ( $page['type'] === 'toc') {
        ?>
            <div class="chapter-header">
                <h2 class="chapter-title"><?php echo $sommaire; ?></h2>
            </div>
            <div class="chapter-content">
            <?php
                $options = $page['toc']['options'][0];
                echo get_table_of_content( $options['showSubheading'], $options['showSubtitle'] );
            } 
            ?>
            </div>

        </section>
    <?php
        }

        $chapters = get_book_info('chapters');
        $chptNb = -2;

        foreach ( $chapters as $chapter ) {
    ?>
        <section id="<?php eesc_attr( $chapter['_id'] ); ?>" data-title="<?php eesc_attr( $chapter['title'] ); ?>">
            
            <div class="chapter-header">
                <?php if ( $chptNb > 0 ) { ?><p class="chapter-counter" id="chapter-<?php echo $chptNb; ?>"><?php echo $chaptername; ?> <?php echo $chptNb; ?></p><?php } ?>
                <h2 class="chapter-title"><?php echo $chapter['title']; ?></h2>
            </div>
            
            <div class="chapter-content">
                <?php
                    $child_content = get_children_markup( $chapter['children'], $chapter['_id'] );
                    echo $child_content[0];
                ?>
                <?php /*if ( isset( $_GET['print'] ) ) {*/ ?>
                <?php 
                /*<aside class="chapter-links">
                    <h3>Ressources de ce chapitre</h3>
                    <?php echo get_chapter_links( $child_content[1], $chapter['_id']); ?>
                </aside>*/
                ?>
                <?php /*}*/ ?>
            </div>

        </section>
    <?php
            $chptNb++;
        }
    ?>
    </main>

    <footer role="contentinfo" class="no-pdf">
        <a href="<?php echo get_book_info('publisherLink'); ?>">
                
            <?php echo get_book_info('publisherLogo', array(
                'alt' => get_book_info('publisherName') . ', Ninja Lead Designer',
                'width' => 236,
                'height' => 56,
            )); ?>
        </a>
    </footer>

</body>
</html>