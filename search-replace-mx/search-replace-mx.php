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
            </tbody>
        </table>
        
        <p class="submit">
            <input type="button" name="submit" id="submit-search" class="button button-primary" value="Search keyword">
        </p>
    </form>
    
    <div id="search-results"></div>

    <script>
    jQuery(document).ready(function($) {
    $('#submit-search').on('click', function(e) {
        e.preventDefault();
        var search_term = $('#search-term').val();

        updateSearchResults(search_term);
    });

    $('#submit-replace').on('click', function(e) {
    e.preventDefault();
    var search_term = $('#search-term').val();
    var replace_term = $('#replace-term').val();
    var column = $(this).data('column'); // Убедитесь, что у кнопки есть data-атрибут 'column'

    $.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'sar_replace_keywords',
            search_term: search_term,
            replace_term: replace_term,
            column: column // Это значение должно быть передано в AJAX-запрос
        },
        dataType: 'json',
        success: function(response) {
            alert('Замена выполнена для колонки ' + column + ' в ' + response.replaced_count + ' постах!');
            if (response.replaced_count > 0) {
                // Теперь вызываем updateSearchResults с массивом ID
                updateSearchResults(search_term, response.replaced_posts_ids);
            }
        }
    });
});


// Обработчик для кнопок замены
$(document).on('click', '.replace-button', function() {
    var column = $(this).data('column'); // Название колонки (например, 'title', 'content')
    var replace_term = $(this).prev('.replace-term').val();
    var search_term = $('#search-term').val();

    $.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'sar_replace_keywords',
            search_term: search_term,
            replace_term: replace_term,
            column: column // Отправляем название колонки в AJAX-запросе
        },
        dataType: 'json',
        success: function(response) {
            alert('Замена выполнена для колонки ' + column + ' в ' + response.replaced_count + ' постах!');
            if (response.replaced_count > 0) {
                updateSearchResults(replace_term);
            }
        }
    });
});




});

function updateSearchResults(newSearchTerm, replacedPostsIds) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'sar_search_posts',
            search_term: newSearchTerm
        },
        success: function(searchResponse) {
            jQuery('#search-results').html(searchResponse);
        }
    });
}





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
        $html .= '<th>Title<br><input id="search-replace-title" type="text" class="small-text replace-term" data-column="title" placeholder="New title"><button class="button replace-button" data-column="title">Replace</button></th>';
        $html .= '<th>Content<br><input id="search-replace-content" type="text" class="small-text replace-term" data-column="content" placeholder="New content"><button class="button replace-button" data-column="content">Replace</button></th></th>';
        $html .= '<th>Meta Title<br><input id="search-replace-meta-title" type="text" class="small-text replace-term" data-column="meta-title" placeholder="New meta-title"><button class="button replace-button" data-column="meta-title">Replace</button></th></th>';
        $html .= '<th>Meta Description<br><input id="search-replace-meta-description" type="text" class="small-text replace-term" data-column="meta-description" placeholder="New meta-description"><button class="button replace-button" data-column="meta-description">Replace</button></th></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($posts as $post) {
            $content_cleaned = strip_shortcodes( $post->post_content );
            $content_cleaned = wp_strip_all_tags( $content_cleaned );
            $meta_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
            $meta_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

            $html .= '<tr id="post-row-' . esc_attr($post->ID) . '">'; // Добавляем идентификатор к строке
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
    global $wpdb;
    $search_term = sanitize_text_field($_POST['search_term']);
    $replace_term = sanitize_text_field($_POST['replace_term']);
    $column = isset($_POST['column']) ? sanitize_text_field($_POST['column']) : '';

    // Проверка nonce и прав пользователя должна быть здесь

    $args = array(
        'post_type' => 'post',
        'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
        's' => $search_term,
        'posts_per_page' => -1
    );

    $posts = get_posts($args);
    $count = 0;
    $replaced_posts_ids = array();

    foreach ($posts as $post) {
        switch ($column) {
            case 'title':
                $new_title = str_replace($search_term, $replace_term, $post->post_title);
                if ($new_title !== $post->post_title) {
                    wp_update_post(array(
                        'ID' => $post->ID,
                        'post_title' => $new_title
                    ));
                    $replaced_posts_ids[] = $post->ID;
                    $count++;
                }
                break;
            case 'content':
                $new_content = str_replace($search_term, $replace_term, $post->post_content);
                if ($new_content !== $post->post_content) {
                    wp_update_post(array(
                        'ID' => $post->ID,
                        'post_content' => $new_content
                    ));
                    $replaced_posts_ids[] = $post->ID;
                    $count++;
                }
                break;
            case 'meta-title':
                $new_meta_title = str_replace($search_term, $replace_term, get_post_meta($post->ID, '_yoast_wpseo_title', true));
                if ($new_meta_title !== get_post_meta($post->ID, '_yoast_wpseo_title', true)) {
                    update_post_meta($post->ID, '_yoast_wpseo_title', $new_meta_title);
                    $replaced_posts_ids[] = $post->ID;
                    $count++;
                }
                break;
            case 'meta-description':
                $new_meta_description = str_replace($search_term, $replace_term, get_post_meta($post->ID, '_yoast_wpseo_metadesc', true));
                if ($new_meta_description !== get_post_meta($post->ID, '_yoast_wpseo_metadesc', true)) {
                    update_post_meta($post->ID, '_yoast_wpseo_metadesc', $new_meta_description);
                    $replaced_posts_ids[] = $post->ID;
                    $count++;
                }
                break;
            // Добавьте другие случаи, если есть другие колонки для замены
        }
    }

    header('Content-Type: application/json');
    echo json_encode(array(
        'replaced_count' => $count,
        'replaced_posts_ids' => $replaced_posts_ids
    ));
    wp_die();
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