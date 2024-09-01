<?php
    global $data;
    global $chapter_links;

    function get_book_data() {
        $file = file_get_contents( 'data/atticus.json' ); 
        $json_data = json_decode( $file, true );
        return $json_data;
    }

    function get_current_book($current = null) {
        global $data;
        // 1 - Form FR
        // 0 - Form EN
        //
        // The reference book is either the $book_array value itself, or the value of get, or 0, in that order.
        return $current !== null ? $current : $data[ ( isset( $_GET['book'] ) ? (int) $_GET['book'] : 0 ) ];
    }

    function get_book_info( $name, $attrs = array(), $book_array = null ) {
        global $data;

        $book = get_current_book( $book_array );
        
        switch ( $name ) {
            case 'author':
                return $book['author'][0];
                break;

            case 'cover':
                return '<img src="' . $book['coverImageUrl'] . '" class="cover"' . add_html_attrs( $attrs ) . '>';
                break;

            case 'publisherLogoURL':
                return '<img src="' . $book['publisherLogoURL'] . '" class="publisher-logo"' . add_html_attrs( $attrs ) . '>';
                break;

            case 'publisherName':
                return isset( $book['publisherName'] ) ? $book['publisherName'] : '';
                break;

            case 'publisherLink':
                    return isset( $book['publisherLink'] ) ? $book['publisherLink'] : '';
                    break;

            case 'isbn':
                return isset ( $book['isbn'] ) ? $book['isbn'] :  ( isset( $book['printISBN'] ) ? $book['printISBN'] : '' );
                break;
            
            case 'eisbn':
                return isset ( $book['ebookISBN'] ) ? $book['ebookISBN'] : '';
                break;

            case 'lang':
                return isset ( $book['language'] ) ? $book['language'] : 'en';
                break;

            case 'title':
                return isset ( $book['title'] ) ? $book['title'] : '';
                break;

            case 'subtitle':
                return isset( $book['subtitle'] ) ? $book['subtitle'] : '';
                break;

            case 'genericPages':
                return $book['frontMatter'];
                break;

            case 'chapters':
                return $book['chapters'];
            
            default:
                return 'Unknown ' . $name;
                break;
        }
    }

    /**
     * Loop and switch case to get the content of a "Children array".
     */
    function get_children_markup( $children, $chapt_id) {
        global $chapter_links;

        if ( ! is_array( $children ) ) return;
        $output = '';
        
        if ( ! isset( $chapter_links[ $chapt_id ] ) ) {
            // Si non, initialiser comme un tableau vide
            $chapter_links[ $chapt_id ] = [];
        } 

        // Set the heading counters to generate unique ids combined with $chapt_id
        (int) $h2 ??= 1;
        (int) $h3 ??= 1;
        (int) $h4 ??= 1;
        (int) $h5 ??= 1;

        foreach ( $children as $c ) {

            // If we don't have a type, it's a text node
            if ( ! isset( $c['type'] ) ) {

                // Text output with bold or italic (italic being used for the other lang thant the main one FR if EN, or EN if FR)
                $output .= ( isset( $c['monospace'] ) ? '<code>' : ''  ) . ( isset( $c['bold'] ) ? '<strong>' : ''  ) . ( isset( $c['italic'] ) ? '<i lang="en">' : ''  ) . nl2br( htmlentities( maybe_remove_emoji( $c['text'] ) ) ) . ( isset( $c['italic'] ) ? '</i>' : ''  ) . ( isset( $c['bold'] ) ? '</strong>' : ''  ) . ( isset( $c['monospace'] ) ? '</code>' : ''  );
                continue;
            }

            // Else we dig which type it is.
            switch ( $c['type'] ) {
                case 'align_center':
                    $output .= '<div class="text-center">' .  more_children( $c['children'], $chapt_id ) . '</div>';
                    break;

                case 'p':
                    $output .= '<p' . ( isset( $c['id'] ) ? ' id="' . get_valid_id( get_child_id ( $c ) ) . '"' : '') . '>' . more_children( $c['children'], $chapt_id ) . '</p>';
                    break;

                case 'a':
                    $output .= get_the_link_markup( $c );
                    $chapter_links[ $chapt_id ][] = array('id' => get_child_id( $c ), 'href' => $c['url'], 'text' => $c['children'][0]['text']);
                    break;

                case 'image':
                    $output .= '<figure data-size="' . $c['size'] . '"  data-flow="' . $c['flow'] . '"><img src="' . maybe_base64url( $c['url'] ) . '">' . ( isset( $c['caption'] ) ? '<figcaption>' . $c['caption'] . '</figcaption>' : '' ) . '</figure>';
                    break;

                case 'ul':
                    $output .= '<ul>' . more_children( $c['children'], $chapt_id ) . '</ul>';
                    break;

                case 'ol':
                    $output .= '<ol>' . more_children( $c['children'], $chapt_id ) . '</ol>';
                    break;

                case 'li':
                    $output .= '<li>' . more_children( $c['children'], $chapt_id ) . '</li>';
                    break;

                case 'h2':
                    $output .= '<h3 class="h2" id="' . get_valid_id( $chapt_id . '_' . $h2 ) . '">' . maybe_remove_emoji( $c['children'][0]['text'] ) . '</h3>';
                    $h2++;
                    break;

                case 'h3':
                    $output .= '<h4 class="h3" id="' . get_valid_id( $chapt_id . '_h3_' . $h3 ) . '">' .  maybe_remove_emoji( $c['children'][0]['text'] ) . '</h4>';
                    $h3++;
                    break;
                
                case 'h4':
                    $output .= '<h5 class="h4" id="' . get_valid_id( $chapt_id . '_h4_' . $h4 ) . '">' .  maybe_remove_emoji( $c['children'][0]['text'] ) . '</h5>';
                    $h4++;
                    break;

                case 'h5':
                    $output .= '<h6 class="h5" id="' . get_valid_id( $chapt_id . '_h5_' . $h5 ) . '">' .  maybe_remove_emoji( $c['children'][0]['text'] ) . '</h6>';
                    $h5++;
                    break;

                case 'blockquote':
                    $output .= '<blockquote id="' . get_valid_id( get_child_id ( $c ) ) . '">' . more_children( $c['children'], $chapt_id ) . ( isset( $c['quotee'] ) ? '<cite>' . $c['quotee'] . '</cite>' : '') . '</blockquote>';
                    break;

                case 'calloutbox':
                    $output .= '<div class="callout" style="' . get_callout_styles ( $c ) . '">' . more_children( $c['children'], $chapt_id ) . '</div>';
                    break;

                case 'code_block':
                    $output .= format_code_block( $c );
                    break;
                
                default:
                    $output .= '<div class="not-supported"><strong>' . $c['type'] . '</strong> not supported yet <pre>' . var_export( $c, true ) . '</pre></div>';
                    break;
            }
        }

        return array($output, $chapter_links);
    }

    /**
     * Infinite loop of children :D
     */
    function more_children( $children, $chapt_id ) {
        return isset( $children ) ? get_children_markup( $children, $chapt_id )[0] : '';
    }

    /**
     * Returns child ID with compatible HTML ID
     */
    function get_child_id( $child ) {
        return isset( $child['id'] ) ? 'c-' . $child['id'] : '';
    }

    /**
     * Get the styles ready for the style attribute
     */
    function get_callout_styles( $child ) {
        $style = "";
        
        if ( $child['background']['fill'] ) {
            $style .= 'background:' . hexToRgb( $child['background']['fillColor'], $child['background']['fillOpacity'] ) . ';';

        }

        if ( $child['border']['border'] ) {
            $style .= 'border:' . $child['border']['borderWidth'] . 'px ' . $child['border']['borderStyle'] . ' ' . hexToRgb($child['border']['borderColor'], $child['border']['borderOpacity'] ) . ';';
            $style .= 'border-radius:'. $child['border']['borderRadius'] . 'px;';
        } 

        return $style;
    }

    /**
     * Get the Table of Content
     */
    function get_table_of_content($subheading, $subtitle) {
        $book = get_current_book();

        if ( ! is_array( $book['chapterIds'] ) ) return;

        $chapterNamed = get_named_chapters( $book['chapters'] );

        $toc = '<ol class="toc">';
        foreach( $chapterNamed as $chapter ) {
            // Skip empty chapters? Happened in some exports.
            if ( ! isset(  $chapter['id'] ) && ! isset( $chapter['title'] ) ) continue;

            $toc .= '<li class="toc-item"><a href="#' . get_valid_id( $chapter['id'] ) . '">' . ( $chapter['number'] !== 'none' ?  '<span class="chapter-number">' . $chapter['number'] . '</span>' : '' ) . '' . $chapter['title'] . '' . ( $subtitle ? ' <span class="subtitle">' . $chapter['subtitle'] . '</span>' : '') . '</a>';

            if ( $subheading && isset( $chapter['headings'] ) && is_array( $chapter['headings'] ) && ! empty( $chapter['headings'] ) ) {
                $toc .= '<ol class="toc-subheadings">';

                foreach ( $chapter['headings'] as $heading) {
                    $toc .= '<li class="toc-subheadings-item"><a href="#' . get_valid_id( isset( $heading['id'] ) ? $heading['id'] : '' ) . '">' . $heading['title'] . '</a></li>';
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
        $chaptNb = 0;

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

            if ( is_numbered_chapter( $chapter ) ) $chaptNb++;

            $chapts[] = array(
                'id'       => $chapter['_id'],
                'title'    => $chapter['title'],
                'subtitle' => $chapter['subtitle'],
                'headings' => $headings,
                'number'   => is_numbered_chapter( $chapter ) ? $chaptNb : 'none'
            );
            
        }
        return $chapts;
    }

    /**
     * Is this chapter supposed to be numbered?
     * Function made necessary because Atticus isn't well made.
     */
    function is_numbered_chapter( $chapter ) {
        return ( ! isset( $chapter['numbered'] ) OR ( isset( $chapter['numbered'] ) && $chapter['numbered'] ) );
    }

    /**
     * Get the chapter links based on chapterID
     * NOT USED ANYWHERE AT THE MOMENT
     */
    function get_chapter_links( $all_links, $chapter_id = null ) {
        $output = '';
        if ( ! is_array( $all_links ) ) return;
        if ( ! isset( $all_links[ $chapter_id ] ) || ! is_array( $all_links[ $chapter_id ]  ) ) return;

        foreach ($all_links[ $chapter_id ] as $link) {
            war_dump($link);
        }

        return $output;
    }

    /**
     * Return a different formatting for links depending on the goal (print, web) and type of links
     */
    function get_the_link_markup( $c ) {
        $output = '';

        if ( isset( $_GET['print'] ) ) {
            $isInternal = preg_match( '#\#chapter#', $c['url'] );
            $output .= $isInternal === 0 ? 
                '<u>' .  $c['children'][0]['text'] . '</u> <span class="printable-link">(' . get_printable_url( $c['url'] ) . ')</span> ' :
                '<a class="intlink" href="#cg' . explode( '#chapter=', $c['url'] )[1] . '">' . $c['children'][0]['text'] . '</a>';
        } else {
            $output .= '<a href="' . $c['url'] . '" id="' . get_valid_id( get_child_id ( $c ) ) . '">' .  $c['children'][0]['text'] . '</a>';
        }

        return  $output;
    }

    /**
     * Return a printable URL for actual print (without URL params)
     */
    function get_printable_url( $url ) {
        $url = explode( '?utm_source' , $url );
        return $url[0];
    }

    /**
     * Remove Emoji Function because Amazon printers don't like emojis 
     */
    function maybe_remove_emoji( $text ) {

        if ( ! isset( $_GET['print'] ) ) return $text;

        $clean_text = "";
    
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
    
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
    
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
    
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
    
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
    
        return $clean_text;
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

    function format_code_block( $c ) {
        $output = '';

        if ( isset( $c['type'], $c['children'] ) &&  $c['type'] == 'code_block' && is_array( $c['children'] ) ) {
            $output .= '<pre' . ( isset( $c['id'] ) ? ' id="cgcode-' . $c['id'] : '' ) . '"><code>';

            foreach ($c['children'] as $cp) {
                $text = str_replace( array('<', '>'), array('&lt;', '&gt;'), $cp['text'] );
                $text = isset( $cp['bold'] ) ? '<strong>' . $text . '</strong>' :  $text;
                $output .= $text;
            }

            $output .= '</code></pre>';
        }

        return $output;
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

    function get_valid_id( $id, $attr = '' ) {
        return esc_attr( 'cg-' . $id . $attr);
    }

    function hexToRgb($hex, $alpha = false) {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
        if ( $alpha ) {
            $rgb['a'] = $alpha;
        }
        return 'rgba(' . $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'] . ',' . ( isset ( $rgb['a'] ) ? $rgb['a'] : '1' ) . ')';
    }

    /**
     * Get images in base64 if Print URL param is there.
     * Fallbacks to normal URL if file_get_contents doesn't work.
     */
    function maybe_base64url( $url, $mime_type = null ) {

        return $url; // temp

        if ( ! isset( $_GET['print'] ) ) return $url;

        $image = file_get_contents( $url );
        $ext = pathinfo( parse_url( $url, PHP_URL_PATH), PATHINFO_EXTENSION );
        $mime_type = $mime_type !== null ? $mime_type : 'image/' . $ext;

        if ( $image !== false ) {
            $url = 'data:' . $mime_type . ';base64,' . base64_encode( $image );
        }

        return $url;
    }

    /**
     * Formated var_dump() function
     */
    function war_dump( $data ) {
        echo '<pre>';
        var_dump(  $data );
        echo '</pre>';
    }