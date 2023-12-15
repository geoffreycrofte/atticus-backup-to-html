<?php 
    global $data;
    include_once( 'inc/utilities.php' );
    $data = get_book_data();
    $by = get_book_info('lang') === 'fr' ? 'par' : 'by';
    $sommaire = get_book_info('lang') === 'fr' ? 'Sommaire' : 'Table of Content';
    $chaptername = get_book_info('lang') === 'fr' ? 'Chapitre' : 'Chapter';
?><!DOCTYPE html>
<html lang="<?php echo get_book_info('lang'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_book_info('title'); ?> : <?php echo get_book_info('subtitle'); ?> - ebook <?php echo $by; ?> <?php echo get_book_info('author'); ?></title>

    <link rel="stylesheet" href="assets/styles.css">

</head>
<body>
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
        <p class="isbn">ISBN : <?php echo get_book_info('eisbn'); ?></p>
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
            echo get_children_markup( $page['children'], $page['_id'] );
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
                <?php echo get_children_markup( $chapter['children'], $chapter['_id'] ); ?>
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