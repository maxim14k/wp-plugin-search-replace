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


function sar_display_content() {
    global $wpdb;
    ?>
    <h1>Search and Replace Keywords</h1>
    
    <form method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="search-term">Search For</label></th>
                    <td><input name="search-term" type="text" id="search-term" value="" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Search keyword">
        </p>
    </form>
    
    <?php
    if (isset($_POST['search-term'])) {
        $keyword = sanitize_text_field($_POST['search-term']);

        $args = array(
            'post_type' => 'post',
            'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
            's' => $keyword,
            'posts_per_page' => -1
        );

        $posts = get_posts($args);

        if ($posts) {
            echo '<table class="wp-list-table widefat fixed striped table-view-list">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Status</th>';
            echo '<th>Title</th>';
            echo '<th>Content</th>';
            echo '<th>Meta Title</th>';
            echo '<th>Meta Description</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($posts as $post) {
                $content_cleaned = strip_shortcodes( $post->post_content );
                $content_cleaned = wp_strip_all_tags( $content_cleaned );
                $meta_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
                $meta_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

                
                echo '<tr>';
                echo '<form action="" method="post">';
                echo '<td>' . esc_html($post->ID) . '</td>';

                echo '<td>' . esc_html($post->post_status) . '</td>';

                echo '<td>';
                echo esc_html($post->post_title);
                echo '<input type="text" name="new_title" placeholder="New title" />';
                echo '<input type="hidden" name="post_id" value="' . esc_attr($post->ID) . '" />';
                echo '<input type="submit" name="update_title" value="Replace">';
                echo '</td>';

                echo '<td>' . esc_html($content_cleaned) . '</td>';
                echo '<td>' . esc_html( $meta_title ) . '</td>';
                echo '<td>' . esc_html( $meta_description ) . '</td>';

                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "<div class='updated'><p>Записей, содержащих '" . $keyword . "', не найдено.</p></div>";
        }
    }

    // В начале вашего файла плагина или там, где у вас обрабатываются формы
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_title'])) {
    // Проверка на безопасность, например, проверка nonce
    // ...

    // Обновление title
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $new_title = sanitize_text_field($_POST['new_title']);

    // Убедитесь, что $post_id не равен нулю и $new_title не пустой
    if ($post_id && !empty($new_title)) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));
    }
    }

    
?>


    <?php
}



