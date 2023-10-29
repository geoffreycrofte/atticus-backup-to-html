<?php
    global $data;

    function get_book_data() {
        $file = file_get_contents( 'data/atticus.json' ); 
        $json_data = json_decode( $file, true );
        return $json_data;
    }

    function get_book_info( $name, $attrs = array() ) {
        global $data;

        switch ($name) {
            case 'author':
                return $data[0]['author'][0];
                break;

            case 'cover':
                return '<img src="' . $data[0]['coverImageUrl'] . '" class="cover"' . add_html_attrs( $attrs ) . '>';
                break;

            case 'publisherLogo':
                return '<img src="' . $data[0]['publisherLogoURL'] . '" class="publisher-logo"' . add_html_attrs( $attrs ) . '>';
                break;

            case 'publisherName':
                return $data[0]['publisherName'];
                break;

            case 'publisherLink':
                    return $data[0]['publisherLink'];
                    break;

            case 'isbn':
                return $data[0]['isbn'];
                break;
            
            case 'eisbn':
                return $data[0]['ebookISBN'];
                break;

            case 'lang':
                return $data[0]['language'];
                break;

            case 'title':
                return $data[0]['title'];
                break;

            case 'subtitle':
                return $data[0]['subtitle'];
                break;

            case 'genericPages':
                return $data[0]['frontMatter'];
                break;

            case 'chapters':
                return $data[0]['chapters'];
            
            default:
                return 'Unknown $name';
                break;
        }
    }

    /**
     * Loop and switch case to get the content of a "Children array".
     */
    function get_children_markup( $children, $prev = null ) {
        if ( ! is_array( $children ) ) return;
        $output = '';

        foreach ( $children as $c ) {

            // If we don't have a type, it's a text node
            if ( ! isset( $c['type'] ) ) {
                //TODO: il y a un truc chelou avec les BR et les prev elements.
                $output .= ( isset ( $prev ) && $prev === 'text' ? '<br>' : '' ) . ( isset( $c['bold'] ) ? '<strong>' : ''  ) . htmlentities( $c['text'] ) . ( isset( $c['bold'] ) ? '</strong>' : ''  );
                $prev = ( isset( $c['bold'] ) ? 'bold' : 'text'  );
                continue;
            }

            // Else we dig which type it is.
            switch ( $c['type'] ) {
                case 'align_center':
                    $output .= '<div class="text-center">' .  more_children( $c['children'], 'center' ) . '</div>';
                    $prev = 'center';
                    break;

                case 'p':
                    $output .= '<p id="' . get_child_id ( $c ) . '">' . more_children( $c['children'], 'p' ) . '</p>';
                    $prev = 'p';
                    break;

                case 'a':
                    $output .= '<a href="' . $c['url'] . '" id="' . get_child_id ( $c ) . '">' .  $c['children'][0]['text'] . '</a>';
                    $prev = 'a';
                    break;

                case 'image':
                    $output .= '<figure data-size="' . $c['size'] . '"  data-flow="' . $c['flow'] . '"><img src="' . $c['url'] . '">' . ( isset( $c['caption'] ) ? '<figcaption>' . $c['caption'] . '</figcaption>' : '' ) . '</figure>';
                    $prev = 'image';
                    break;

                case 'ul':
                    $output .= '<ul>' . more_children( $c['children'], 'ul' ) . '</ul>';
                    $prev = 'ul';
                    break;

                case 'li':
                    $output .= '<li>' . more_children( $c['children'], 'li' ) . '</li>';
                    $prev = 'li';
                    break;

                case 'h2':
                    $output .= '<h3 class="h2" id="shouldcomelater">' .  $c['children'][0]['text'] . '</h3>';
                    $prev = 'h2';
                    break;

                case 'h3':
                    $output .= '<h4 class="h3">Les titres de niveau 3 buggouillent</h4>';
                    //$output .= '<h4 class="h3">' .  $c['children'][0]['text'] . '</h4>';
                    $prev = 'h3';
                    break;
                
                case 'h4':
                    $output .= '<h5 class="h4">' .  $c['children'][0]['text'] . '</h5>';
                    $prev = 'h4';
                    break;

                case 'blockquote':
                    $output .= '<blockquote id="' . get_child_id ( $c ) . '">' . more_children( $c['children'], 'blockquote' ) . ( isset( $c['quotee'] ) ? '<cite>' . $c['quotee'] . '</cite>' : '') . '</blockquote>';
                    $prev = 'blockquote';
                    break;
                
                default:
                    $output .= '<div class="not-supported"><strong>' . $c['type'] . '</strong> not supported yet <pre>' . var_export( $c, true ) . '</pre></div>';
                    $prev = 'not';
                    break;
            }
        }

        return $output;
    }

    /**
     * Infinite loop of children :D
     */
    function more_children( $children, $prev ) {
        return isset( $children ) ? get_children_markup( $children, $prev ) : '';
    }

    /**
     * Returns child ID with compatible HTML ID
     */
    function get_child_id( $child ) {
        return isset( $child['id'] ) ? 'c-' . $child['id'] : '';
    }

    /**
     * Get the Table of Content
     */
    function get_table_of_content($subheading, $subtitle) {
        global $data;

        if ( ! is_array( $data[0]['chapterIds'] ) ) return;

        $chapterNamed = get_named_chapters( $data[0]['chapters'] );

        $toc = '<ol class="toc">';
        foreach( $chapterNamed as $chapter ) {
            $toc .= '<li class="toc-item"><a href="#' . $chapter['id'] . '">' . $chapter['title'] . '' . ( $subtitle ? ' <span class="subtitle">' . $chapter['subtitle'] . '</span>' : '') . '</a>';

            if ( $subheading && isset( $chapter['headings'] ) && is_array( $chapter['headings'] ) ) {
                $toc .= '<ol class="toc-subheadings">';

                foreach ( $chapter['headings'] as $heading) {
                    $toc .= '<li class="toc-subheadings-item"><a href="' . $heading['id'] . '">' . $heading['title'] . '</a></li>';
                }

                $toc .= '</ol>';
            }

            $toc .= '</li>';
        }
        $toc .= '</ol>';

        return $toc;
    }

    /**
     * Get the name of all the chapters, per ID 
     */
    function get_named_chapters ( $chapters ) {

        if ( ! is_array( $chapters ) ) return;

        $chapts = array();
        foreach( $chapters as $chapter ) {

            $headings = array();
            $n = 1;
            foreach( $chapter['children'] as $child ) {
                if ( $child['type'] !== 'h2' ) continue;
                $headings[] = array(
                    'id'    => $chapter['_id'] . '_' . $n, // :/
                    'title' => $child['children'][0]['text'],
                );
                $n++;
            }

            $chapts[] = array(
                'id'       => $chapter['_id'],
                'title'    => $chapter['title'],
                'subtitle' => $chapter['subtitle'],
                'headings' => $headings
            );
        }
        return $chapts;
    }


    /**
     * Loops on the attrs available in an array to make a printable HTML string.
     */
    function add_html_attrs( $attrs ) {
        if ( ! is_array( $attrs ) ) return;
        $html_attrs = '';

        foreach ( $attrs as $k => $v ) {
            $html_attrs .= strip_tags( $k ) . '="' . strip_tags( $v ) . '" '; 
        }

        return ' ' . trim( $html_attrs );
    }

    /**
     * Strips the tags of a string and return the value
     */
    function esc_attr( $string ) {
        return strip_tags( $string );
    }

    /**
     * Strips the tags of a string and print the value
     */
    function eesc_attr( $string ) {
        echo esc_attr( $string );
    }

    /**
     * Formated var_dump() function
     */
    function war_dump( $data ) {
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
    }