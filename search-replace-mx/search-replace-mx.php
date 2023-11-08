<?php
/*
Plugin Name: Search and Replace MX
Plugin URI: 
Description: Search and Replace Keywords
Version: 1.0
Author: Maxim Khudenko
Author URI: 
*/

if ( ! defined( 'ABSPATH' ) ) exit;

function sar_menu_page() {
    add_menu_page( 
        'Search and Replace Keywords', 
        'Search Keywords', 
        'manage_options', 
        'search-and-replace', 
        'sar_display_content', 
        'dashicons-search'
    );
}

add_action( 'admin_menu', 'sar_menu_page' );
add_action( 'wp_ajax_sar_search_posts', 'sar_search_posts_callback' );
add_action( 'wp_ajax_sar_replace_keywords', 'sar_replace_keywords_callback' );

function sar_display_content() {
    ?>
    <h1>Search and Replace Keywords</h1>
    
    <form id="search-form" method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="search-term">Search For</label></th>
                    <td><input name="search-term" type="text" id="search-term" value="" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="replace-term">Replace With</label></th>
                    <td><input name="replace-term" type="text" id="replace-term" value="" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="button" name="submit" id="submit-search" class="button button-primary" value="Search keyword">
            <input type="button" name="submit" id="submit-replace" class="button button-secondary" value="Replace keyword">
        </p>
    </form>
    
    <div id="search-results"></div>

    <script>
    jQuery(document).ready(function($) {
    $('#submit-search').on('click', function(e) {
        e.preventDefault();
        var search_term = $('#search-term').val();

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'sar_search_posts',
                search_term: search_term
            },
            success: function(response) {
                $('#search-results').html(response);
            }
        });
    });

    $('#submit-replace').on('click', function(e) {
        e.preventDefault();
        var search_term = $('#search-term').val();
        var replace_term = $('#replace-term').val();

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'sar_replace_keywords',
                search_term: search_term,
                replace_term: replace_term
            },
            dataType: 'json',
            success: function(response) {
                alert('Замена выполнена в ' + response.replaced_count + ' постах!');
                if (response.replaced_count > 0) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'sar_search_posts',
                            search_term: response.new_search_term
                        },
                        success: function(searchResponse) {
                            $('#search-results').html(searchResponse);
                        }
                    });
                }
            }
        });
    });
});

    </script>
    <?php
}

function sar_search_posts_callback() {
    global $wpdb;
    $keyword = sanitize_text_field($_POST['search_term']);

    $args = array(
        'post_type' => 'post',
        'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
        's' => $keyword,
        'posts_per_page' => -1
    );

    $posts = get_posts($args);
    $html = '';

    if ($posts) {
        $html .= '<table class="wp-list-table widefat fixed striped table-view-list">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>ID</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Title</th>';
        $html .= '<th>Content</th>';
        $html .= '<th>Meta Title</th>';
        $html .= '<th>Meta Description</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($posts as $post) {
            $content_cleaned = strip_shortcodes( $post->post_content );
            $content_cleaned = wp_strip_all_tags( $content_cleaned );
            $meta_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
            $meta_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

            $html .= '<tr>';
            $html .= '<td>' . esc_html($post->ID) . '</td>';
            $html .= '<td>' . esc_html($post->post_status) . '</td>';
            $html .= '<td>' . esc_html($post->post_title) . '</td>';
            $html .= '<td>' . esc_html($content_cleaned) . '</td>';
            $html .= '<td>' . esc_html( $meta_title ) . '</td>';
            $html .= '<td>' . esc_html( $meta_description ) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
    } else {
        $html = "<div class='updated'><p>Posts with the term '" . $keyword . "' not found.</p></div>";
    }

    echo $html;
    wp_die(); // this is required to terminate immediately and return a proper response
}

function sar_replace_keywords_callback() {
    global $wpdb; // глобальный объект базы данных WordPress
    $search_term = sanitize_text_field($_POST['search_term']);
    $replace_term = sanitize_text_field($_POST['replace_term']);

    // Проверка nonce и прав пользователя должна быть здесь

    $args = array(
        'post_type' => 'post',
        'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
        's' => $search_term,
        'posts_per_page' => -1
    );

    $posts = get_posts($args);
    $count = 0;

    foreach ($posts as $post) {
        // Замена в контенте
        $replaced_content = str_replace($search_term, $replace_term, $post->post_content);
        // Замена в заголовке
        $replaced_title = str_replace($search_term, $replace_term, $post->post_title);
        // Замена в мета-данных
        $replaced_meta_title = str_replace($search_term, $replace_term, get_post_meta($post->ID, '_yoast_wpseo_title', true));
        $replaced_meta_description = str_replace($search_term, $replace_term, get_post_meta($post->ID, '_yoast_wpseo_metadesc', true));

        if ($replaced_content !== $post->post_content || $replaced_title !== $post->post_title) {
            // Обновляем пост
            wp_update_post(array(
                'ID' => $post->ID,
                'post_content' => $replaced_content,
                'post_title' => $replaced_title
            ));
            $count++;
        }
        
        // Обновляем мета-данные
        if ($replaced_meta_title !== get_post_meta($post->ID, '_yoast_wpseo_title', true)) {
            update_post_meta($post->ID, '_yoast_wpseo_title', $replaced_meta_title);
        }
        if ($replaced_meta_description !== get_post_meta($post->ID, '_yoast_wpseo_metadesc', true)) {
            update_post_meta($post->ID, '_yoast_wpseo_metadesc', $replaced_meta_description);
        }
    }

    header('Content-Type: application/json'); // Указываем, что возвращается JSON
    echo json_encode(array(
        'replaced_count' => $count,
        'new_search_term' => $replace_term // Возвращаем новый термин для поиска
    ));
    wp_die(); // Завершаем ajax-запрос, возвращая управление браузеру
    
}



function sar_enqueue_scripts($hook) {
    // Only add to the admin.php page, otherwise it's loaded on every admin page
    if ('admin.php' != $hook) {
        return;
    }
    wp_enqueue_script('sar-ajax-script', plugin_dir_url(__FILE__) . 'sar-ajax.js', array('jquery'), null, true);
    // Localize the script with new data
    wp_localize_script('sar-ajax-script', 'sar_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}

add_action('admin_enqueue_scripts', 'sar_enqueue_scripts');
?>