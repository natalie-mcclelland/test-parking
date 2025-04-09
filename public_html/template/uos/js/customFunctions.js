jQuery(document).ready(function () {
    
    /*
     * Form functions
     */
    // Display the appropriate form fields when the permit type is selected
    function showPermitTypeFormFields(permitType) {
        switch (permitType) {
            case '2':
                // Staff Annual
                jQuery(".mandatePermit").show();
                jQuery(".notice_vehicle_no").hide();
                jQuery(".secondVehicle").show();
                jQuery(".blueBadge").hide();
                break;
            
            case '3': // Staff Accessible Blue Badge
            case '7': // Student Accessible Blue Badge
                jQuery(".mandatePermit").hide();
                jQuery(".notice_vehicle_no").hide();
                jQuery(".secondVehicle").show();
                jQuery(".blueBadge").show();
                break;
            
            case '4': // Staff Residential
            case '6': // Student Residential
                jQuery(".mandatePermit").hide();
                jQuery(".notice_vehicle_no").show();
                jQuery(".secondVehicle").hide();
                jQuery(".blueBadge").hide();
                break;
            
            default:
                // Other type
                jQuery(".mandatePermit").hide();
                jQuery(".notice_vehicle_no").hide();
                jQuery(".secondVehicle").show();
                jQuery(".blueBadge").hide();
                break;
        }
    }
    
    
    // ----------
    
    
    /*
     * Setup the form
     */
    // Display the appropriate form options when the permit type is selected
    jQuery("#permitType").change(function() {
        // Get the permit type ID
        var permitType = jQuery(this).find("option:selected").val();
        showPermitTypeFormFields(permitType);
    });
    
    // Display the appropriate for options when the form is loaded
    var permitType = $("#permitType").val();
    showPermitTypeFormFields(permitType);

});