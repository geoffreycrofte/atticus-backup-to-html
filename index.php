<?php 
    global $data;
    include( 'inc/utilities.php' );
    $data = get_book_data();
    $by = get_book_info('lang') === 'fr' ? 'par' : 'by';
    $toc = get_book_info('lang') === 'fr' ? 'Sommaire' : 'Table of Content';
    $chaptername = get_book_info('lang') === 'fr' ? 'Chapitre' : 'Chapter';

    // Personal themes to be used with my own books
    $themes = array('forms', 'forms', 'accessibility', 'accessibility');

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
    } else {
        echo '<link rel="stylesheet" href="assets/styles.css">';
    }
    ?>

</head>
<body class="<?php echo count( $data ) > 1 ? 'books' : 'book'; ?> theme-<?php echo isset( $_GET['book'] ) ? $themes[$_GET['book']] : ''; ?>">
    <?php if ( count( $data ) > 1 ) { ?>
    <nav class="no-pdf">
        <ul>

        <?php foreach ( $data as $k => $b ) { ?>
            <?php
                $subtitle = get_book_info('subtitle', array(), $b);
                $separator = get_book_info('lang') === 'fr' ? ' : ' : ': ';
                $title = get_book_info('title', array(), $b) . ( $subtitle !== '' ? $separator . $subtitle : '' );
            ?>
            <li>
                <a href=".?book=<?php echo $k; ?>"<?php echo isset( $_GET['book'] ) && (int) $_GET['book'] === (int) $k ? ' aria-current="true"' : ''; ?> title="<?php echo $title; ?>">
                <?php echo get_book_info('cover', array(
                    'alt' =>  $title,
                    'width' => '210',
                    'height' => '297'
                ), $b); ?>
                </a>
            </li>

        <?php } ?>

        </ul>
    </nav>
    <?php } ?>
    <header role="banner" class="no-pdf">
        <div>
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

            <?php
                $eisbn = get_book_info('eisbn');
                if ( $eisbn !== '' ) {
            ?>

            <p class="isbn">e-ISBN&nbsp;: <?php eesc_attr( $eisbn ); ?></p>

            <?php }
                $isbn = get_book_info('isbn');
                if ( $isbn !== '' ) {
            ?>
            <p class="isbn">ISBN&nbsp;: <?php eesc_attr( $isbn ); ?></p>
            <?php } ?>
        </div>
    </header>


    <main role="main">
    <?php
        $pages = get_book_info('genericPages');
        foreach ( $pages as $page ) {
    ?>
        <section id="<?php eesc_attr( $page['_id'] ); ?>" data-title="<?php eesc_attr( $page['title'] ); ?>">
            
        <?php
        if ( $page['type'] === 'image' && isset( $page['fullpageImage']['imageUrl'] ) ) {
        ?>

            <?php echo ( $page['title'] === 'Title Page' ) ? '<h1>' : ''; ?>

            <img class="full-screen-image" src="<?php eesc_attr( $page['fullpageImage']['imageUrl'] ); ?>" alt="<?php eesc_attr( get_book_info('title') . ' ' . get_book_info('subtitle') ) ?>" data-printextent="<?php eesc_attr( $page['fullpageImage']['printExtent'] ); ?>" data-verticalalign="<?php eesc_attr( $page['fullpageImage']['verticalAlignment'] ); ?>">
            
            <?php echo ( $page['title'] === 'Title Page' ) ? '</h1>' : ''; ?>

        <?php 
        } elseif ( $page['type'] === 'toc') {
        ?>
            <div class="chapter-header">
                <h2 class="chapter-title"><?php echo $toc; ?></h2>
            </div>
            <div class="chapter-content">

            <?php
                $options = $page['toc']['options'][0];
                echo get_table_of_content( $options['showSubheading'], $options['showSubtitle'] );
            ?>

            </div>
    <?php
        } elseif ( ! empty( $page['children'] ) ) {

            $child_content = get_children_markup( $page['children'], $page['_id'] );
            echo $child_content[0]; //0 = content, 1 = links

        }
    ?>

        </section> 
    
    <?php
        } //end of foreach $pages

        $chapters = get_book_info('chapters');
        $chaptNumber = 1;

        foreach ( $chapters as $chapter ) {
    ?>
        <section id="<?php echo get_valid_id( $chapter['_id'] ); ?>" data-title="<?php eesc_attr( $chapter['title'] ); ?>">
            
            <div class="chapter-header">
                <?php
                    if ( isset( $chapter['numbered'] ) && $chapter['numbered'] === true ) {
                ?>
                        <p class="chapter-counter" id="chapter-<?php echo $chaptNumber; ?>"><?php echo $chaptername; ?> <?php echo $chaptNumber; ?></p>
                <?php
                        $chaptNumber++;
                    }
                ?>
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
        }
    ?>
    </main>

    <footer role="contentinfo" class="no-pdf">

        <a href="<?php echo get_book_info('publisherLink'); ?>">
                
            <?php echo get_book_info('publisherLogoURL', array(
                'alt' => get_book_info('publisherName') . ', Ninja Lead Designer',
                'width' => 236,
                'height' => 56,
            )); ?>
        </a>
    </footer>

    <script async src="assets/main.js"></script>

</body>
</html>