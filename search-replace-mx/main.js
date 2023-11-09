jQuery(document).ready(function($){

    $('#submit-search').on('click', function(e) {
        e.preventDefault();
        var search_term = $('#search-term').val();
        updateSearchResults(search_term);
    });

    $('#submit-replace').on('click', function(e) {
    e.preventDefault();
    var search_term = $('#search-term').val();
    var replace_term = $('#replace-term').val();
    var column = $(this).data('column');

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'sar_replace_keywords',
                search_term: search_term,
                replace_term: replace_term,
                column: column
            },
            dataType: 'json',
            success: function(response) {
                alert('Replaced for ' + column + ' in ' + response.replaced_count + ' Posts');
                if (response.replaced_count > 0) {
                    updateSearchResults(search_term, response.replaced_posts_ids);
                }
            }
        });
    });


    $(document).on('click', '.replace-button', function() {
        var column = $(this).data('column');
        var replace_term = $(this).prev('.replace-term').val();
        var search_term = $('#search-term').val();

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'sar_replace_keywords',
                search_term: search_term,
                replace_term: replace_term,
                column: column
            },
            dataType: 'json',
            success: function(response) {
                alert('Replaced for ' + column + ' in ' + response.replaced_count + ' Posts');
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