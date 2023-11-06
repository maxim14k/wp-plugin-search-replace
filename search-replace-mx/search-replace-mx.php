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
                    <td><input name="search-term" type="text" id="search-term" value="<?php echo $_POST['search-term'] ?>" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Search keyword">
        </p>
    </form>
    
    <?php
    if (isset($_POST['search-term'])) { var_dump($_POST);
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
                
                echo '<td>' . esc_html($post->ID) . '</td>';

                echo '<td>' . esc_html($post->post_status) . '</td>';

                echo '';
                echo '<td><form action="" method="post">';
                echo esc_html($post->post_title);
                echo '<input type="text" name="new_title" placeholder="New title" />';
                echo '<input type="hidden" name="post_id" value="' . esc_attr($post->ID) . '" />';
                echo '<input type="hidden" name="search-term" value="' . $_POST['search-term'] . '" />';
                echo '<input type="submit" name="update_title" value="Replace">';
                echo '</form></td>';
                echo '';

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

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_title'])) { var_dump($_POST);
        $search_term = sanitize_text_field($_POST['search-term']);
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $new_title_part = sanitize_text_field($_POST['new_title']);
    
        // Получаем текущий заголовок поста
        $current_post = get_post($post_id);
        $current_title = $current_post->post_title;

        echo '<br>';
        echo $post_id .'<br>';
        echo $new_title_part .'<br>';
        echo $current_title .'<br>';
        echo $search_term .'<br>';
    
        // Заменяем искомое слово на новое в заголовке
        if ($post_id && !empty($new_title_part) && strpos($current_title, $search_term) !== false) { echo 'update title';
            $updated_title = str_replace($search_term, $new_title_part, $current_title);
    
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $updated_title
            ));
        }
    }
    

    
?>


    <?php
}



