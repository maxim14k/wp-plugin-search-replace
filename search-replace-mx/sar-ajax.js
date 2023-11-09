jQuery(document).ready(function($) {
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        var search_term = $('#search-term').val();

        $.ajax({
            url: sar_ajax_object.ajax_url,
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
});

