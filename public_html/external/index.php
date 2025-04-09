<?php
// Debugging
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

// Include class files
require_once(dirname(__FILE__)."/../../lib/ext/simplesamlphp/vendor/autoload.php");
require_once(dirname(__FILE__)."/../../lib/classes/application/ParkingApplication.php");
require_once(dirname(__FILE__)."/../../lib/ext/ReCaptcha/autoload.php");


/**
 * External applicant application form.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.1.0
 */

// Prevent the client from caching content
header("Expires: Thu, 1 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


/*
 * Initialise access control and verify if an internal user is logged in
 */
$as = new SimpleSAML_Auth_Simple('default-sp');
if ($as->isAuthenticated()) {
    // Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
    \SimpleSAML\Session::getSessionFromRequest()->cleanup();

    // The user is already logged in so redirect to the user dashboard
    header("Location: /internal/index.php");
    exit;
}


/*
 * Initialise the API
 */
$parkingApplication = new \CarParkingSystem\ParkingApplication();


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
    $applicantTelephone = (isset($_POST['applicantTelephone'])) ? filter_input(INPUT_POST, 'applicantTelephone', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $applicantEmailAddr = (isset($_POST['applicantEmailAddr'])) ? filter_input(INPUT_POST, 'applicantEmailAddr', FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL) : NULL;
    $applicantReasonApplying = (isset($_POST['applicantReasonApplying'])) ? filter_input(INPUT_POST, 'applicantReasonApplying', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $permitType = (isset($_POST['permitType'])) ? filter_input(INPUT_POST, 'permitType', FILTER_VALIDATE_INT, FILTER_SANITIZE_NUMBER_INT) : NULL;
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
    if ($applicantReasonApplying == NULL) $formErrorMsgs[] = "Please specify why you are applying for a permit";
    if ($permitType == NULL) $formErrorMsgs[] = "Please select the type of permit you require";
    if ($vehicle1VRM == NULL) $formErrorMsgs[] = "Please specify the Vehicle Registration Mark for vehicle 1";
    if ($vehicle1Make == NULL) $formErrorMsgs[] = "Please specify the make of vehicle 1";
    if ($vehicle1Colour == NULL) $formErrorMsgs[] = "Please specify the colour of vehicle 1";
    if (strlen($vehicle2VRM) > 0 && ($vehicle2Make == NULL || $vehicle2Colour == NULL)) $formErrorMsgs[] = "Please ensure that you specify both the make and colour of vehicle 2";
    if ($permitType == "3" && ($blbgSerial == NULL || $blbgIssuer == NULL || $blbgValidFrom == NULL || $blbgValidTo == NULL || $blbgFileFront['size'] == 0 || $blbgFileBack['size'] == 0)) $formErrorMsgs[] = "Please ensure that you provide all the information that is on the front of your Blue Badge, as well as a photograph/scan of both the front and back sides.";
    if ($agreeTCs != "y") $formErrorMsgs[] = "You must read, understand and agree to be bound by the Terms and Conditions";
    
    // Verify the CAPTCHA response
    $recaptchaAPI = new \ReCaptcha\ReCaptcha("6LfULgsTAAAAAClD2E463UswlcQ4R6pg2CkgMa8W", new \ReCaptcha\RequestMethod\Curl());
    $captchaResponse = (isset($_POST['g-recaptcha-response'])) ? filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) : NULL;
    $captchaVerify = $recaptchaAPI->verify($captchaResponse, $_SERVER['REMOTE_ADDR']);
    if (!$captchaVerify->isSuccess()) $formErrorMsgs[] = "The CAPTCHA was either not completed or is incorrect";
    
    // Check if there are any errors with the form
    if (count($formErrorMsgs) == 0) {
        // There are no errors so process the data
        
        try {
            // Store the data in the database
            $applicantID = $parkingApplication->createApplicantRecord($applicantTitle, $applicantFirstName, $applicantSurname, $applicantAddrHFPN, $applicantAddrL1, $applicantAddrL2, $applicantAddrL3, $applicantAddrTown, $applicantAddrCounty, $applicantAddrPostcode, "n", $applicantTelephone, $applicantEmailAddr, "ext");
            $parkingApplication->createApplicantExtraDetailsRecord($applicantID, NULL, NULL, NULL, "n", NULL, NULL, NULL, $applicantReasonApplying);
            $permitID = $parkingApplication->createPermitRecord($applicantID, $permitType, date('Y-m-d', strtotime($permitStartDate)), date('Y-m-d', strtotime($permitEndDate)));
            $parkingApplication->createVehicleRecord($permitID, $vehicle1VRM, $vehicle1Make, $vehicle1Colour);
            if (strlen($vehicle2VRM) > 0) $parkingApplication->createVehicleRecord($permitID, $vehicle2VRM, $vehicle2Make, $vehicle2Colour);
            $parkingApplication->createPermitTransactionLogEntry($permitID, "Parking application submitted by applicant", "System");
            
            
            // Handle the Blue Badge data
            if (strlen($blbgSerial) > 0) {
                // Prepare the new filenames for the uploaded files
                $blueBadgeFrontFile = "blue-badge_{$applicantSurname}-{$applicantFirstName}_EXT-APP-{$applicantID}_{$blbgValidFrom}_front_{$blbgFileFront['name']}";
                $blueBadgeBackFile = "blue-badge_{$applicantSurname}-{$applicantFirstName}_EXT-APP-{$applicantID}_{$blbgValidFrom}_back_{$blbgFileBack['name']}";
                
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
            header("Location: /external/confirmation.php");
            exit;
        
        } catch (\Exception $ex) {
            // Display an error messge
            $formErrorMsgs[] = "There was a problem whilst creating your application. Please try again. ".$ex;
        }
    }
}


/*
 * The form has not been submitted or there has been an error.
 * 
 * Display the application form
 */

// Include page header
require_once(dirname(__FILE__) . "/../../tpl/static/page_header.php");

?>

        <!-- START: Application Form Introduction -->
        <div class="row">
            <div class="large-12 columns">
                <h2>Vehicle Register Application</h2>
                <h3>Non University Staff / Student</h3>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns">
                <div class="panel">
                    <p>Staff employed by authorised University partners, working within the University campuses are eligible to register their vehicles.</p>
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
        <form name="externalApplicantPermit" action="/external/index.php" method="post" enctype="multipart/form-data" data-abide>
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
                                <label>Reason for applying <small>e.g. employer (Required)</small>
                                    <input name="applicantReasonApplying" type="text" maxlength="255" value="<?php if (isset($applicantReasonApplying)) print($applicantReasonApplying); ?>" placeholder="" required />
                                    <small class="error">Please specify why you are applying to register your vehicle</small>
                                </label>
                            </div>
                        </div>
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
    $permitTypes = $parkingApplication->getPermitTypes(TRUE);
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
                    <div class="large-6 columns">
                        <fieldset>
                            <legend>Vehicle 2</legend>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Vehicle Registration Mark
                                        <input name="vehicle2VRM" type="text" maxlength="8" value="<?php if (isset($vehicle2VRM)) print($vehicle2VRM); ?>" placeholder="" />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Make
                                        <input name="vehicle2Make" type="text" maxlength="50" value="<?php if (isset($vehicle2Make)) print($vehicle2Make); ?>" placeholder="" />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-12 columns">
                                    <label>Colour
                                        <input name="vehicle2Colour" type="text" maxlength="50" value="<?php if (isset($vehicle2Colour)) print($vehicle2Colour); ?>" placeholder="" />
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
                            <p>You will only need to complete this section if you are applying to register a vehicle eligible for an Accessible Blue Badge.<br />When completing this section, please add a photograph or scan of the front and back sides of your Blue Badge.</p>
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
                            <input name="blbgValidFrom" maxlength="10" value="<?php if (isset($blbgValidFrom)) print($blbgValidFrom); ?>" placeholder="yyyy-mm-dd" class="dateonly" />
                        </label>
                    </div>
                    <div class="large-4 columns end">
                        <label>Date Valid To
                            <input name="blbgValidTo" maxlength="10" value="<?php if (isset($blbgValidTo)) print($blbgValidTo); ?>" placeholder="yyyy-mm-dd" class="dateonly" />
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="large-4 columns">
                        <label>Front of Blue Badge
                            <input name="blbgFileFront" type="file" maxlength="255" placeholder="" />
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
            
            <!-- START: CAPTCHA -->
            <fieldset>
                <legend>Security Check</legend>
                <div class="row">
                    <div class="large-12 columns">
                        <div class="panel callout radius">
                            <p>We ask you to confirm that you are not a robot, because need to ensure that you are a real person and not a machine that is trying to spam us.</p>
                            <p>If you are using Internet Explorer 8 or below you may not be able to complete the confirmation please use a different browser.</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="large-12 columns">
                        <div class="g-recaptcha" data-sitekey="6LfULgsTAAAAAFmfDDEcuvQzYkh0P2Ptkp0T9L2z"></div>
                    </div>
                </div>
            </fieldset>
            <!-- END: CAPTCHA -->
            
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
                </div>
            </div>
        </form>
        <!-- END: Application Form -->
        

<?php

// Include page footer
require_once(dirname(__FILE__) . "/../../tpl/static/page_footer.php");

?>