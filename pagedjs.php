<?php 
    global $data;
    include( 'inc/utilities.php' );
    $data = get_book_data();
    $by = get_book_info('lang') === 'fr' ? 'par' : 'by';
    $toc = get_book_info('lang') === 'fr' ? 'Sommaire' : 'Table of Content';
    $chaptername = get_book_info('lang') === 'fr' ? 'Chapitre' : 'Chapter';
?><!DOCTYPE html>
<html lang="<?php echo get_book_info('lang'); ?>" class="print">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_book_info('title'); ?> : <?php echo get_book_info('subtitle'); ?> - Livre <?php echo $by; ?> <?php echo get_book_info('author'); ?></title>

    <meta name="author" content="<?php echo get_book_info('author'); ?>"/>
    <meta name="subject" content="<?php echo get_book_info('title'); ?> : <?php echo get_book_info('subtitle'); ?>"/>
    <meta name="keywords" content="accessibility, html to pdf, wcag, section 508"/>
    <meta name="date" content="<?php echo date('Y-m-d'); ?>"/>
    <meta name="generator" content="Web Browser"/>

    <link rel="stylesheet" href="assets/styles-pagedjs.css">
    <link rel="stylesheet" href="assets/paged.interface.css" media="screen">
    <script src="assets/paged.polyfill.js"></script>

</head>
<body class="<?php echo count( $data ) > 1 ? 'books' : 'book'; ?>">
    <?php
        $pages = get_book_info('genericPages');
        foreach ( $pages as $page ) {
    ?>
            
        <?php if ( $page['type'] === 'image' && isset( $page['fullpageImage']['imageUrl'] ) ) { ?>

            <?php echo ( $page['title'] === 'Title Page' ) ? '<h1>' : ''; ?>

            <?php echo esc_attr( get_book_info('title') ) . ' <span>' . esc_attr( get_book_info('subtitle') ) . '</span>'; ?>

            <?php echo '<cite>' . $by . ' ' . get_book_info('author') . '</cite>'; ?>
            
            <?php echo ( $page['title'] === 'Title Page' ) ? '</h1>' : ''; ?>

        <?php } elseif ( $page['type'] === 'toc') { ?>

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
        }
        elseif ( ! empty( $page['children'] ) ) { ?>

        <div class="other-types-like-copyright">
        
        <?php
            $child_content = get_children_markup( $page['children'], $page['_id'] );
            echo $child_content[0]; //0 = content, 1 = links
        ?>

        </div>

        <?php 
            } 
        }//end of foreach $pages

        $chapters = get_book_info('chapters');
        $chaptNumber = 1;

        foreach ( $chapters as $chapter ) {
            // For some reasons, an unset numbered array key can mean the chapter is numbered... Thank you Atticus :/
            if ( is_numbered_chapter( $chapter ) ) $chptNb++;
    ?>
        <section data-title="<?php eesc_attr( $chapter['title'] ); ?>">
            
            <div class="chapter-header">
                
                <?php
                    if ( isset( $chapter['numbered'] ) && $chapter['numbered'] === true ) {
                ?>
                        <p class="chapter-counter" id="chapter-<?php echo $chaptNumber; ?>"><?php echo $chaptername; ?> <?php echo $chaptNumber; ?></p>
                <?php
                        $chaptNumber++;
                    }
                ?>

                <h2 id="<?php echo get_valid_id( $chapter['_id'] ); ?>" class="chapter-title"><?php echo $chapter['title']; ?></h2>
            </div>
            
            <div class="chapter-content">
                <?php
                    $child_content = get_children_markup( $chapter['children'], $chapter['_id'] );
                    echo $child_content[0];
                ?>
            </div>

        </section>
    <?php
        }
    ?>
</body>
</html>