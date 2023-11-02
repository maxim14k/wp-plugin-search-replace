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
                <tr>
                    <th scope="row"><label for="replace-term">Replace With</label></th>
                    <td><input name="replace-term" type="text" id="replace-term" value="" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Search and Replace">
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
        echo '<th>Состояние</th>';
        echo '<th>Название</th>';
        echo '<th>Содержание</th>';
        echo '<th>Действия</th>'; // Этот столбец для будущих кнопок, таких как "Редактировать" или "Заменить"
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($posts as $post) {
            echo '<tr>';
            echo '<td>' . esc_html($post->ID) . '</td>';
            echo '<td>' . esc_html($post->post_status) . '</td>';
            echo '<td>' . esc_html($post->post_title) . '</td>';
            echo '<td>' . esc_html($post->post_content) . '</td>';
            echo '<td></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<div class='updated'><p>Записей, содержащих '" . $keyword . "', не найдено.</p></div>";
    }
}
?>


    <?php
}



