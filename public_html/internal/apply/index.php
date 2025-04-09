<?php

// Define class namespace
//namespace CarParkingSystem;

// Debugging
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

// Include class file
require_once(dirname(__FILE__)."/../../../lib/ext/simplesamlphp/vendor/autoload.php");
require_once(dirname(__FILE__)."/../../../lib/classes/core/AdUtils.php");
require_once(dirname(__FILE__)."/../../../lib/classes/application/ParkingApplication.php");


/**
 * Internal applicant application form.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.0.0
 */


/*
 * Cache control
 */
// Prevent the client from caching content
header("Expires: Thu, 1 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


// Initialise classes
$as = new SimpleSAML_Auth_Simple('default-sp');

// Require user to authenticate
$as->requireAuth();

// Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
\SimpleSAML\Session::getSessionFromRequest()->cleanup();

// Get attributes of authenticated user
$attributes = $as->getAttributes();
$userName = explode("@", $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0])[0];


/*
 * Initialise the API and retrieve data from the APIs
 */
// Parking Application
$parkingApplication = new \CarParkingSystem\ParkingApplication();

// User account properties
$adUtils = new \UosCore\AdUtils();
$userAccountProperties = $adUtils->getAccountProperties($userName);


/*
 * Calculate the date range the parking permit will be valid for
 */
$permitStartDate = date('j F Y');
$permitEndDate = (strtotime($permitStartDate) < strtotime(date('j F Y', strtotime("1st July this year")))) ? date('j F Y', strtotime("31st July this year")) : date('j F Y', strtotime("31st July next year"));


/*
 * Check if the form has been submitted
 */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // The form has been submitted
    
    // Get the form data and sanitise it
    $applicantID = (isset($_POST['applicantID'])) ? filter_input(INPUT_POST, 'applicantID', FILTER_VALIDATE_INT, FILTER_SANITIZE_NUMBER_INT) : NULL;
    $applicantTitle = (isset($_POST['applicantTitle'])) ? filter_input(INPUT_POST, 'applicantTitle', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantFirstName = (isset($_POST['applicantFirstName'])) ? filter_input(INPUT_POST, 'applicantFirstName', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantSurname = (isset($_POST['applicantSurname'])) ? filter_input(INPUT_POST, 'applicantSurname', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrHFPN = (isset($_POST['applicantAddrHFPN'])) ? filter_input(INPUT_POST, 'applicantAddrHFPN', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrL1 = (isset($_POST['applicantAddrL1'])) ? filter_input(INPUT_POST, 'applicantAddrL1', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrL2 = (isset($_POST['applicantAddrL2'])) ? filter_input(INPUT_POST, 'applicantAddrL2', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrL3 = (isset($_POST['applicantAddrL3'])) ? filter_input(INPUT_POST, 'applicantAddrL3', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrTown = (isset($_POST['applicantAddrTown'])) ? filter_input(INPUT_POST, 'applicantAddrTown', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrCounty = (isset($_POST['applicantAddrCounty'])) ? filter_input(INPUT_POST, 'applicantAddrCounty', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAddrPostcode = (isset($_POST['applicantAddrPostcode'])) ? filter_input(INPUT_POST, 'applicantAddrPostcode', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $isApplicantTermAddress = (isset($_POST['isApplicantTermAddress'])) ? "y" : "n";
    $applicantTelephone = (isset($_POST['applicantTelephone'])) ? filter_input(INPUT_POST, 'applicantTelephone', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantEmailAddr = (isset($_POST['applicantEmailAddr'])) ? filter_input(INPUT_POST, 'applicantEmailAddr', FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL) : NULL;
    $applicantIDNumber = (isset($_POST['applicantIDNumber'])) ? filter_input(INPUT_POST, 'applicantIDNumber', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantAttendanceMode = (isset($_POST['applicantAttendanceMode'])) ? filter_input(INPUT_POST, 'applicantAttendanceMode', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantSwipeCardNumber = (isset($_POST['applicantSwipeCardNumber'])) ? filter_input(INPUT_POST, 'applicantSwipeCardNumber', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $permitType = (isset($_POST['permitType'])) ? filter_input(INPUT_POST, 'permitType', FILTER_VALIDATE_INT, FILTER_SANITIZE_NUMBER_INT) : NULL;
    //$permitStaffAuthPayroll = (isset($_POST['permitStaffAuthPayroll'])) ? "y" : "n";
    $permitStaffAuthPayroll = "y";
    $vehicle1VRM = (isset($_POST['vehicle1VRM'])) ? filter_input(INPUT_POST, 'vehicle1VRM', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $vehicle1Make = (isset($_POST['vehicle1Make'])) ? filter_input(INPUT_POST, 'vehicle1Make', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $vehicle1Colour = (isset($_POST['vehicle1Colour'])) ? filter_input(INPUT_POST, 'vehicle1Colour', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $vehicle2VRM = (isset($_POST['vehicle2VRM'])) ? filter_input(INPUT_POST, 'vehicle2VRM', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $vehicle2Make = (isset($_POST['vehicle2Make'])) ? filter_input(INPUT_POST, 'vehicle2Make', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $vehicle2Colour = (isset($_POST['vehicle2Colour'])) ? filter_input(INPUT_POST, 'vehicle2Colour', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $blbgSerial = (isset($_POST['blbgSerial'])) ? filter_input(INPUT_POST, 'blbgSerial', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $blbgIssuer = (isset($_POST['blbgIssuer'])) ? filter_input(INPUT_POST, 'blbgIssuer', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $blbgValidFrom = (isset($_POST['blbgValidFrom'])) ? filter_input(INPUT_POST, 'blbgValidFrom', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $blbgValidTo = (isset($_POST['blbgValidTo'])) ? filter_input(INPUT_POST, 'blbgValidTo', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $blbgFileFront = $_FILES['blbgFileFront'];
    $blbgFileBack = $_FILES['blbgFileBack'];
    $agreeTCs = (isset($_POST['agreeTCs'])) ? "y" : "n";
    
    
    // Check that the required fields have been completed
    $formErrorMsgs = array();
    if ($applicantTitle == NULL) $formErrorMsgs[] = "Please specify your title";
    if ($applicantFirstName == NULL) $formErrorMsgs[] = "Please specify your first name";
    if ($applicantSurname == NULL) $formErrorMsgs[] = "Please specify your surname";
    if ($applicantAddrHFPN == NULL) $formErrorMsgs[] = "Please specify your house or flat number, or property name";
    if ($applicantAddrL1 == NULL) $formErrorMsgs[] = "Please specify the first line of your address";
    if ($applicantAddrTown == NULL) $formErrorMsgs[] = "Please specify your post town";
    if ($applicantAddrPostcode == NULL) $formErrorMsgs[] = "Please specify your postcode";
    if ($applicantTelephone == NULL) $formErrorMsgs[] = "Please specify your telephone number";
    if ($applicantEmailAddr == NULL) $formErrorMsgs[] = "Please specify a valid email address";
    if ($applicantIDNumber == NULL) $formErrorMsgs[] = "Please specify your staff payroll or student ID number";
    if ($applicantAttendanceMode == NULL) $formErrorMsgs[] = "Please specify your attendance mode";
    if ($permitType == NULL) $formErrorMsgs[] = "Please select the type of permit you require";
    if ($vehicle1VRM == NULL) $formErrorMsgs[] = "Please specify the Vehicle Registration Mark for vehicle 1";
    if ($vehicle1Make == NULL) $formErrorMsgs[] = "Please specify the make of vehicle 1";
    if ($vehicle1Colour == NULL) $formErrorMsgs[] = "Please specify the colour of vehicle 1";
    if (strlen($vehicle2VRM) > 0 && ($vehicle2Make == NULL || $vehicle2Colour == NULL)) $formErrorMsgs[] = "Please ensure that you specify both the make and colour of vehicle 2";
    if ($permitType == "2" && $permitStaffAuthPayroll == "n") $formErrorMsgs[] = "For a 'Staff Annual (Mandate)' permit, you must authorise Payroll to deduct the appropriate amount from your salary every month";
    if (($permitType == "3" || $permitType == "7") && ($blbgSerial == NULL || $blbgIssuer == NULL || $blbgValidFrom == NULL || $blbgValidTo == NULL || $blbgFileFront['size'] == 0 || $blbgFileBack['size'] == 0)) $formErrorMsgs[] = "Please ensure that you provide all the information that is on the front of your Blue Badge, as well as a photograph/scan of both the front and back sides.";
    if ($agreeTCs != "y") $formErrorMsgs[] = "You must read, understand and agree to be bound by the Terms and Conditions";
    
    
    // Check if there are any errors with the form
    if (count($formErrorMsgs) == 0) {
        // There are no errors so process the data
        
        try {
            // Store the data in the database
            
            // Check if the applicant ID has been provided
            if ($applicantID != NULL) {
                // The applicant ID has been provided so update the existing applicant records
                $parkingApplication->updateApplicantRecord($applicantID, $applicantTitle, $applicantFirstName, $applicantSurname, $applicantAddrHFPN, $applicantAddrL1, $applicantAddrL2, $applicantAddrL3, $applicantAddrTown, $applicantAddrCounty, $applicantAddrPostcode, $isApplicantTermAddress, $applicantTelephone, $applicantEmailAddr);
                $parkingApplication->updateApplicantExtraDetailsRecord($applicantID, $applicantIDNumber, $applicantAttendanceMode, $permitStaffAuthPayroll, NULL, NULL, $applicantSwipeCardNumber, NULL);
            
            } else {
                // The applicant ID has not been provided so create new applicant records
                $applicantID = $parkingApplication->createApplicantRecord($applicantTitle, $applicantFirstName, $applicantSurname, $applicantAddrHFPN, $applicantAddrL1, $applicantAddrL2, $applicantAddrL3, $applicantAddrTown, $applicantAddrCounty, $applicantAddrPostcode, $isApplicantTermAddress, $applicantTelephone, $applicantEmailAddr, "int");
                $parkingApplication->createApplicantExtraDetailsRecord($applicantID, $userName, $applicantIDNumber, $applicantAttendanceMode, $permitStaffAuthPayroll, NULL, NULL, $applicantSwipeCardNumber, NULL);
            }
            
            // Create other records
            $permitID = $parkingApplication->createPermitRecord($applicantID, $permitType, date('Y-m-d', strtotime($permitStartDate)), date('Y-m-d', strtotime($permitEndDate)));
            $parkingApplication->createVehicleRecord($permitID, $vehicle1VRM, $vehicle1Make, $vehicle1Colour);
            if (strlen($vehicle2VRM) > 0) $parkingApplication->createVehicleRecord($permitID, $vehicle2VRM, $vehicle2Make, $vehicle2Colour);
            $parkingApplication->createPermitTransactionLogEntry($permitID, "Parking application submitted by applicant", "System");
            
            
            // Handle the Blue Badge data
            if (strlen($blbgSerial) > 0) {
                // Prepare the new filenames for the uploaded files
                $blueBadgeFrontFile = "blue-badge_{$applicantSurname}-{$applicantFirstName}_INT-APP-{$applicantID}_{$blbgValidFrom}_front_{$blbgFileFront['name']}";
                $blueBadgeBackFile = "blue-badge_{$applicantSurname}-{$applicantFirstName}_INT-APP-{$applicantID}_{$blbgValidFrom}_back_{$blbgFileBack['name']}";
                
                // Move the uploaded files to the storage location
                move_uploaded_file($blbgFileFront['tmp_name'], "C:/hosting/websites/parking-admin.sunderland.ac.uk/public_html/data/uploads/{$blueBadgeFrontFile}");
                move_uploaded_file($blbgFileBack['tmp_name'], "C:/hosting/websites/parking-admin.sunderland.ac.uk/public_html/data/uploads/{$blueBadgeBackFile}");
                
                // Store the data in the database
                $parkingApplication->createBlueBadgeRecord($applicantID, $blbgSerial, $blbgIssuer, $blbgValidFrom, $blbgValidTo, $blueBadgeFrontFile, $blueBadgeBackFile);
            }
            
            
            // Generate confirmation email
            $permitTypeDetails = $parkingApplication->getPermitTypeFromID($permitType);
            $permitTypeDescription = $permitTypeDetails['description'];
            $temporaryPermitExpiry = date('Y-m-d', strtotime("$permitStartDate +2 weeks"));
            $parkingApplication->generateEmailApplicationSubmitted($applicantTitle, $applicantFirstName, $applicantSurname, $applicantEmailAddr, $permitTypeDescription, $permitStartDate, $temporaryPermitExpiry, $vehicle1VRM, $vehicle2VRM);
            
            
            // Display confirmation message
            header("Location: /internal/apply/confirmation.php");
            exit;
        
        } catch (\Exception $ex) {
            // Display an error messge
            $formErrorMsgs[] = "There was a problem whilst creating your application. Please try again.";
        }
    }
    
    
    // There has been an error so display the application form with a list of errors.

    
} else {
    // The form has not been submitted
    // Pre fill the fields with data that we already know about the user
    
    try {
        // Check if the user has applied before
        if ($applicantID = $parkingApplication->getApplicantIDFromUsername($userName)) {
            // The user has applied before
            // Retrieve the data from the database from the last application
            $applicantDetailsRecord = $parkingApplication->getApplicantDetails($applicantID);
            $applicantExtraDetailsRecord = $parkingApplication->getApplicantExtraDetails($applicantID);

            // Set the values
            $applicantTitle = $applicantDetailsRecord['title'];
            $applicantFirstName = $applicantDetailsRecord['first_name'];
            $applicantSurname = $applicantDetailsRecord['surname'];
            $applicantAddrHFPN = $applicantDetailsRecord['house_flat_property'];
            $applicantAddrL1 =  $applicantDetailsRecord['address_1'];
            $applicantAddrL2 = $applicantDetailsRecord['address_2'];
            $applicantAddrL3 = $applicantDetailsRecord['address_3'];
            $applicantAddrTown = $applicantDetailsRecord['post_town'];
            //$applicantAddrCounty = $applicantDetailsRecord['county'];
            $applicantAddrPostcode = $applicantDetailsRecord['postcode'];
            $isApplicantTermAddress = $applicantDetailsRecord['is_term_address'];
            $applicantTelephone = $applicantDetailsRecord['telephone'];
            $applicantEmailAddr = $applicantDetailsRecord['email_addr'];
            $applicantIDNumber = $applicantExtraDetailsRecord['id_num'];
            $applicantAttendanceMode = $applicantExtraDetailsRecord['attendance'];
            //$applicantSwipeCardNumber = $applicantExtraDetailsRecord['swipe_card_no'];
        
        } else {
            // The user has not applied before
            // Retrieve the data from Active Directory and set the values
            $applicantFirstName = $userAccountProperties['firstName'];
            $applicantSurname = $userAccountProperties['surname'];
            $applicantEmailAddr = $userAccountProperties['emailAddr'];
            $applicantIDNumber = (isset($userAccountProperties['employeeNumber']) && $userAccountProperties['employeeNumber'] != NULL) ? $userAccountProperties['employeeNumber'] : $userAccountProperties['studentNumber'];
        }
    
    } catch (\Exception $ex) {
        // Display an error messge
        $formErrorMsgs[] = "There was a problem whilst retrieving your details. Please try again.";
    }
}


/*
 * Display the application form
 */

// Include page header
require_once(dirname(__FILE__) . "/../../../tpl/static/page_header.php");

?>

        <!-- START: Application Form Introduction -->
        <div class="row">
            <div class="large-12 columns">
                <h2>Vehicle Register Application</h2>
                <h3>University Staff / Student</h3>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns">
                <div class="panel">
                    <p>Please complete the below application taking care to select the correct registration type.</p>
                    <p>Note: The form cannot be submitted if required fields are not completed.</p>
                    <p>On completion you will receive an email notification that your application has been successfully submitted.</p>                
                </div>
            </div>
        </div>
        <hr />
        <!-- END: Application Form Introduction -->
<?php

// Check if there is any error messages to display
if (isset($formErrorMsgs) && count($formErrorMsgs) > 0) {
    print("        <div class=\"alert-box alert radius\">\n");
    foreach ($formErrorMsgs as $formErrorMsg) print("<p>{$formErrorMsg}</p>\n");
    print("        </div>\n");
}

?>

        <!-- START: Application Form -->
        <form name="internalApplicantPermit" action="/internal/apply/index.php" method="post" enctype="multipart/form-data" data-abide>
            <!-- START: Applicant Details -->
            <fieldset>
                <legend>Applicant Details</legend>
                <!-- START: Top Row -->
                <div class="row">
                    <div class="large-2 columns">
                        <label>Title <small>(Required)</small>
                            <select name="applicantTitle" required>
                                <option value="">Please select</option>
<?php

    // Get and check if there are any applicant titles available
    $applicantTitles = $parkingApplication->getApplicantTitles();
    if (count($applicantTitles) > 0) {
        // Print the applicant titles
        foreach ($applicantTitles as $applctTitle) {
            print("                                <option value=\"{$applctTitle}\"");
            if (isset($applicantTitle) && $applctTitle == $applicantTitle) print(" selected");
            print(">{$applctTitle}</option>\n");
        }
    }

?>
                            </select>
                        </label>
                        <small class="error">Please specify your title</small>
                    </div>
                    <div class="large-5 columns">
                        <label>First Name <small>(Required)</small>
                            <input name="applicantFirstName" type="text" maxlength="100" value="<?php if (isset($applicantFirstName)) print($applicantFirstName); ?>" placeholder="" required />
                        </label>
                        <small class="error">Please specify your first name</small>
                    </div>
                    <div class="large-5 columns">
                        <label>Surname <small>(Required)</small>
                            <input name="applicantSurname" type="text" maxlength="100" value="<?php if (isset($applicantSurname)) print($applicantSurname); ?>" placeholder="" required />
                        </label>
                        <small class="error">Please specify your surname</small>
                    </div>
                </div>
                <!-- END: Top Row -->
                
                <div class="row">
                    <!-- START: Left Column -->
                    <div class="large-6 columns">
                        <div class="row">
                            <div class="large-12 columns">
                                <label>House / Flat No. or Property Name <small>(Required)</small>
                                    <input name="applicantAddrHFPN" type="text" maxlength="50" value="<?php if (isset($applicantAddrHFPN)) print($applicantAddrHFPN); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify your house or flat number, or property name</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Address Line 1 <small>(Required)</small>
                                    <input name="applicantAddrL1" type="text" maxlength="100" value="<?php if (isset($applicantAddrL1)) print($applicantAddrL1); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify the first line of your address</small>
                            </div>
                        </div>
                        <div class="row">   
                            <div class="large-12 columns">
                                <label>Address Line 2
                                    <input name="applicantAddrL2" type="text" maxlength="100" value="<?php if (isset($applicantAddrL2)) print($applicantAddrL2); ?>" placeholder="" />
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Address Line 3
                                    <input name="applicantAddrL3" type="text" maxlength="100" value="<?php if (isset($applicantAddrL3)) print($applicantAddrL3); ?>" placeholder="" />
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Town/City <small>(Required)</small>
                                    <input name="applicantAddrTown" type="text" maxlength="30" value="<?php if (isset($applicantAddrTown)) print($applicantAddrTown); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify your post town</small>
                            </div>
                        </div>
                        <!--
                        <div class="row">
                            <div class="large-12 columns">
                                <label>County
                                    <input name="applicantAddrCounty" type="text" maxlength="50" value="" placeholder="" />
                                </label>
                            </div>
                        </div>
                        -->
                        <div class="row">
                            <div class="large-4 columns end">
                                <label>Postcode <small>(Required)</small>
                                    <input name="applicantAddrPostcode" type="text" maxlength="8" value="<?php if (isset($applicantAddrPostcode)) print($applicantAddrPostcode); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify your postcode</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Term Time Address?
                                    <input name="isApplicantTermAddress" type="checkbox" value="y" <?php if (isset($isApplicantTermAddress) && $isApplicantTermAddress == "y") print("checked"); ?> /> Yes
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- END: Left Column -->
                    
                    <!-- START: Right Column -->
                    <div class="large-6 columns">
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Telephone Number <small>(Required)</small>
                                    <input name="applicantTelephone" type="text" maxlength="13" value="<?php if (isset($applicantTelephone)) print($applicantTelephone); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify your telephone number</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Email Address <small>(Required)</small>
                                    <input name="applicantEmailAddr" type="email" maxlength="255" value="<?php if (isset($applicantEmailAddr)) print($applicantEmailAddr); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify a valid email address</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-12 columns">
                                <label>Staff Payroll or Student ID Number <small>(Required)</small>
                                    <input name="applicantIDNumber" type="text" maxlength="14" value="<?php if (isset($applicantIDNumber)) print($applicantIDNumber); ?>" placeholder="" required />
                                </label>
                                <small class="error">Please specify your staff payroll or student ID number</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="large-6 columns end">
                                <label>Attendance Mode <small>(Required)</small>
                                    <select name="applicantAttendanceMode" required>
                                        <option value="">Please select</option>
<?php

    // Get and check if there are any applicant attendance modes available
    $applicantAttendanceModes = $parkingApplication->getApplicantAttendanceModes();
    if (count($applicantAttendanceModes) > 0) {
        // Print the applicant attendance modes
        foreach ($applicantAttendanceModes as $applctAttendMode => $applctAttendModeDesc) {
            print("                                        <option value=\"{$applctAttendMode}\"");
            if (isset($applicantAttendanceMode) && $applctAttendMode == $applicantAttendanceMode) print(" selected");
            print(">{$applctAttendModeDesc}</option>\n");
        }
    }

?>
                                    </select>
                                </label>
                                <small class="error">Please specify your attendance mode</small>
                            </div>
                        </div>
                        <!--
                        <div class="row">
                            <div class="large-6 columns" end>
                                <label>Swipe Card Number <strong>(For staff permits only.)</strong>
                                    <input name="applicantSwipeCardNumber" type="text" maxlength="6" value="<?php if (isset($applicantSwipeCardNumber)) print($applicantSwipeCardNumber); ?>" placeholder="" />
                                </label>
                            </div>
                        </div>
                        -->
                    </div>
                    <!-- END: Right Column -->
                </div>
            </fieldset>
            <!-- END: Applicant Details -->
            
            <!-- START: Permit Details -->
            <fieldset>
                <legend>Register Details</legend>
                <div class="row">
                    <div class="large-6 columns">
                        <label>Register Type <small>(Required)</small>
                            <select name="permitType" id="permitType" required>
                                <option value="">Please select</option>
<?php

    // Get and check if there are any permit types available
    $permitTypes = $parkingApplication->getPermitTypes(FALSE);
    if (count($permitTypes) > 0) {
        // Print the permit types
        foreach ($permitTypes as $prmType) {
            print("                                <option value=\"{$prmType['permit_type_id']}\"");
            if (isset($permitType) && $prmType['permit_type_id'] == $permitType) print(" selected");
            print(">{$prmType['description']}</option>\n");
        }
    }

?>
                            </select>
                        </label>
                        <small class="error">Please select the type of permit you require</small>
                    </div>
                    <div class="large-3 columns">
                        <label>Start Date
                            <p><?php print($permitStartDate); ?></p>
                        </label>
                    </div>
                    <div class="large-3 columns">
                        <label>End Date
                            <p><?php print($permitEndDate); ?></p>
                        </label>
                    </div>
                </div>
                <!--<div class="row mandatePermit">
                    <div class="large-12 columns">
                        <div class="panel callout radius">
                            <label><strong>For <em>Staff Annual (Mandate)</em> permits only.</strong><br />I hereby authorise University Payroll to deduct the appropriate amount from my salary every month.
                                <input name="permitStaffAuthPayroll" type="checkbox" value="y" <?php // if (isset($permitStaffAuthPayroll)) print("checked"); ?> /> Yes
                            </label>
                        </div>
                    </div>
                </div>-->
                <div class="row notice_vehicle_no">
                    <div class="large-12 columns">
                        <div class="panel callout radius">
                            <label>
                                <strong>For residential registrations:</strong> You can only register <strong><u>one</u></strong> vehicle.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- START: Vehicle 1 Details -->
                    <div class="large-6 columns">
                        <fieldset>
                            <legend>Vehicle 1</legend>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Vehicle Registration Mark <small>(Required)</small>
                                        <input name="vehicle1VRM" type="text" maxlength="8" value="<?php if (isset($vehicle1VRM)) print($vehicle1VRM); ?>" placeholder="" required />
                                    </label>
                                    <small class="error">Please specify the Vehicle Registration Mark for vehicle 1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Make <small>(Required)</small>
                                        <input name="vehicle1Make" type="text" maxlength="50" value="<?php if (isset($vehicle1Make)) print($vehicle1Make); ?>" placeholder="" required />
                                    </label>
                                    <small class="error">Please specify the make of vehicle 1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Colour <small>(Required)</small>
                                        <input name="vehicle1Colour" type="text" maxlength="50" value="<?php if (isset($vehicle1Colour)) print($vehicle1Colour); ?>" placeholder="" required />
                                    </label>
                                    <small class="error">Please specify the colour of vehicle 1</small>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <!-- END: Vehicle 1 Details -->
                    
                    <!-- START: Vehicle 2 Details -->
                    <div class="large-6 columns secondVehicle">
                        <fieldset>
                            <legend>Vehicle 2</legend>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Vehicle Registration Mark
                                        <input name="vehicle2VRM" type="text" maxlength="8" value="<?php // if (isset($vehicle2VRM)) print($vehicle2VRM); ?>" placeholder="" />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Make
                                        <input name="vehicle2Make" type="text" maxlength="50" value="<?php // if (isset($vehicle2Make)) print($vehicle2Make); ?>" placeholder="" />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Colour
                                        <input name="vehicle2Colour" type="text" maxlength="50" value="<?php // if (isset($vehicle2Colour)) print($vehicle2Colour); ?>" placeholder="" />
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <!-- END: Vehicle 2 Details -->
                </div>
            </fieldset>
            <!-- END: Permit Details -->

            <!-- START: Blue Badge Details -->
            <fieldset class="blueBadge">
                <legend>Blue Badge Details</legend>
                <div class="row">
                    <div class="large-12 columns">
                        <div class="panel callout radius">
                            <p>You will only need to complete this section if you are applying for an <strong>Accessible Blue Badge</strong> register.<br />When completing this section, please add a photograph or scan of the front and back sides of your Blue Badge.</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="large-6 columns">
                        <label>Serial Number
                            <input name="blbgSerial" type="text" maxlength="25" value="<?php if (isset($blbgSerial)) print($blbgSerial); ?>" placeholder="" />
                        </label>
                    </div>
                    <div class="large-6 columns">
                        <label>Issuer
                            <input name="blbgIssuer" type="text" maxlength="50" value="<?php if (isset($blbgIssuer)) print($blbgIssuer); ?>" placeholder="" />
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="large-4 columns">
                        <label>Date Valid From
                            <input name="blbgValidFrom" maxlength="10" value="<?php if (isset($blbgValidFrom)) print($blbgValidFrom); ?>" placeholder="yyyy-mm-dd" class="dateonly"/>
                        </label>
                    </div>
                    <div class="large-4 columns end">
                        <label>Date Valid To
                            <input name="blbgValidTo" maxlength="10" value="<?php if (isset($blbgValidTo)) print($blbgValidTo); ?>" placeholder="yyyy-mm-dd" class="dateonly"/>
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="large-4 columns">
                        <label>Front of Blue Badge
                            <input name="blbgFileFront" id="frontBlueBadge" type="file" maxlength="255" placeholder="" />
                        </label>
                    </div>
                    <div class="large-4 columns end">
                        <label>Back of Blue Badge
                            <input name="blbgFileBack" type="file" maxlength="255" placeholder="" />
                        </label>
                    </div>
                </div>
            </fieldset>
            <!-- END: Blue Badge Details -->
            
            <hr />
            
            <!-- START: Legal Statement -->
            <div class="row">
                <div class="large-12 columns">
                    <div class="panel">
                        <h3>Data Protection Statement and Terms and Conditions</h3>
                        <p><a href="/docs/Parking_Policy_and_Regs_2024.pdf" target="_blank">University of Sunderland Parking Policy</a><p>
                            <label>I agree to abide by the University of Sunderland Vehicle Access and Parking Policy and Regulations
                                <input name="agreeTCs" type="checkbox" value="y" required />
                            </label>
                        </p>
                    </div>
                </div>
            </div>
            <!-- END: Legal Statement -->
            
            <div class="row">
                <div class="large-12 columns">
                    <input name="submit" type="submit" value="Submit Application" placeholder="" class="button uos-button radius" />
<?php

// Provide the applicant's ID, if present
if ($applicantID != NULL) print("                    <input name=\"applicantID\" type=\"hidden\" value=\"{$applicantID}\" />\n");

?>
                </div>
            </div>
        </form>
        <!-- END: Application Form -->

<?php

// Include page footer
require_once(dirname(__FILE__) . "/../../../tpl/static/page_footer.php");

?>