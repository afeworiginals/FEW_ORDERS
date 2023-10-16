// JavaScript Document

jQuery(document).ready(function($) {
    $('#hello-world-btn').click(function() {
        $.ajax({
            type: "POST",
            url: afop_ajax_object.ajax_url,
            data: {
                action: "afop_hello_world"
            },
            success: function(response) {
                $("#result").html(response);
            }
			
        });
    });
	
	
	
	
	
	function fetchOptionsData(selectedTable) {
        $.ajax({
            url: afop_ajax_object.ajax_url,
            type: 'POST',
            data: {
                'action': 'afop_fetch_options',
                'table_name': selectedTable
            },
            success: function(response) {
				console.log(response);  // Debug line
                $('#options_data').html(response);
            },
            error: function() {
                $('#options_data').html('<p>Something went wrong.</p>');
            }
        });
    }

    // Initial load
    fetchOptionsData($('#option_table_selector').val());

    // On dropdown change
    $('#option_table_selector').change(function() {
        fetchOptionsData($(this).val());
    });
});
