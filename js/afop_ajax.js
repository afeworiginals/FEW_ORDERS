// JavaScript Document

jQuery(document).ready(function($) {

    var selectedAction;  // Declare selectedAction at a higher scope


    $('#bulk-action-selector-top').change(function() {
        selectedAction = $(this).val(); // Update it when the dropdown changes
    });

    
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
	

    $('select[name="vinyl_type"]').on('change', function() {
        var vinylType = $(this).val();
        var rowId = $(this).data('row-id');
        var isGlobalChange = $('#check-all-vinyl-type').prop('checked');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_vinyl_colors',
                vinyl_type: vinylType
            },
            success: function(response) {
                if (isGlobalChange) {
                    // Update all vinyl_color dropdowns
                    $('select[name="vinyl_color"]').html(response);
                    // Update all vinyl_type dropdowns to the selected value
                    $('select[name="vinyl_type"]').val(vinylType);
                } else {
                    // Only update the vinyl_color dropdown that has the same data-row-id
                    $('select[name="vinyl_color"][data-row-id="' + rowId + '"]').html(response);
                }
            }
        });
    });


    $('select[name="vinyl_color"]').on('change', function() {
        var vinylColor = $(this).val();
        var isGlobalChange = $('#check-all-vinyl-color').prop('checked');

        if (isGlobalChange) {
            // Update all vinyl_color dropdowns to the selected value
            $('select[name="vinyl_color"]').val(vinylColor);
        }
    });

    $('select[name="variations_value_1"]').on('change', function() {
        var selectedFont = $(this).val();
        var isGlobalChange = $('#check-all-font').prop('checked');

        if (isGlobalChange) {
            // Update all font dropdowns to the selected value
            $('select[name="variations_value_1"]').val(selectedFont);
        }
    });

    $('input[name="decal_text"]').on('input', function() {
        var decalText = $(this).val();
        var isGlobalChange = $('#check-all-decal-text').prop('checked');
        console.log(decalText);
        console.log(isGlobalChange);
        if (isGlobalChange) {
            // Update all decal_text input fields to the entered value
            $('input[name="decal_text"]').val(decalText);
        }
    });

    $('#check-all-decal-text').on('change', function() {
        var isChecked = $(this).prop('checked');
        // Add logic here if needed
    });

    $('#check-all-font').on('change', function() {
        var isChecked = $(this).prop('checked');
        // Add logic here if needed
    });

    $('#check-all-vinyl-type').on('change', function() {
        var isChecked = $(this).prop('checked');
        // Add logic here if needed
    });

    $('#check-all-vinyl-color').on('change', function() {
        var isChecked = $(this).prop('checked');
        // Add logic here if needed
    });
    




    $('.check-all-vinyl-type').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.check-item-vinyl-type').prop('checked', isChecked);
    });
    
    $('.check-all-vinyl-color').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.check-item-vinyl-color').prop('checked', isChecked);
    });

    
    
    $('#doaction').click(function(e) {
        e.preventDefault();
        selectedAction = $('#bulk-action-selector-top').val();
        
         console.log(selectedAction);

    });



    $('#order-items-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        var formData = $(this).serialize();
        console.log(formData);
        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'process_bulk_action',
                formData: formData
            },
            success: function(response) {
                // Handle success
                console.log('Success:', response);
            },
            error: function(error) {
                // Handle error
                console.log('Error:', error);
            }
        });
    });




});
