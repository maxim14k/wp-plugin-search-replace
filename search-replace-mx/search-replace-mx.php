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
    if (isset($_POST['search-term'])) { //var_dump($_POST);
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
            echo '<th>Title<br>';
            echo '<form action="" method="post">';
            echo '<input type="hidden" name="search-term" value="' . esc_attr($_POST['search-term']) . '" />';
            echo '<input type="text" name="replace-term" placeholder="New keyword" />';
            echo '<input type="submit" name="replace_all" value="Replace All">';
            echo '</form>';
            echo '</th>';
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
                echo '<td>';
                echo esc_html($post->post_title);
                echo '</td>';
                echo '';

                echo '<td>' . esc_html($content_cleaned) . '</td>';
                echo '<td>' . esc_html( $meta_title ) . '</td>';
                echo '<td>' . esc_html( $meta_description ) . '</td>';

                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "<div class='updated'><p>Posts, with '" . $keyword . "', not found.</p></div>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['replace_all'])) {
        $search_term = sanitize_text_field($_POST['search-term']);
        $replace_term = sanitize_text_field($_POST['replace-term']);
    
        $args = array(
            's' => $search_term,
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
    
        $query = new WP_Query($args);
        $posts = $query->posts;
        $count = 0;
    
        foreach ($posts as $post) {
            // Замените в контенте
            $replaced_content = str_replace($search_term, $replace_term, $post->post_content);
            // Замените в заголовке
            $replaced_title = str_replace($search_term, $replace_term, $post->post_title);
    
            // Если что-то было заменено, обновите пост
            if ($replaced_content !== $post->post_content || $replaced_title !== $post->post_title) {
                wp_update_post(array(
                    'ID'           => $post->ID,
                    'post_content' => $replaced_content,
                    'post_title'   => $replaced_title
                ));
                $count++;
            }
        }
    
        echo "<div class='updated'><p>Updated " . $count . " posts.</p></div>";
    }
    
?>


<?php
}



