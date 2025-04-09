<?php

// Define class namespace
//namespace CarParkingSystem;

// Include class files
require_once (dirname(__FILE__)."/../../lib/ext/simplesamlphp/vendor/autoload.php");
require_once(dirname(__FILE__) . "/../../lib/classes/application/ParkingApplication.php");


/**
 * Internal applicant dashboard.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.1.0
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
$displayName = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/displayname'][0];

// Parking application
$parkingApplication = new \CarParkingSystem\ParkingApplication();

/*
 * Display the page content
 */

// Include page header
require_once(dirname(__FILE__) . "/../../tpl/static/page_header.php");

?>

        <!-- START: Dashboard Header -->
        <div class="row">
            <div class="large-12 columns">
                <h2>Vehicle Register Application Dashboard</h2>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns">
                <p><strong>Welcome <?php print($displayName); ?>.</strong> &nbsp;In this section you can find the registered vehicles that you have recently applied for and their status, as well as the option to register a different vehicle.</p>
            </div>
        </div>
        <!-- END: Dashboard Header -->
        
        <!-- START: Permit List -->
        <div class="row">
            <div class="large-12 columns">
                <h3>Registration Applications</h3>
                <table role="grid" class="responsive">
                    <tr>
                        <th>Registration Type</th>
                        <th>Registration Serial Number</th>
                        <th>Valid From</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                    </tr>
<?php

try {
    // Retrieve the permits that the user has applied for
    // Get the user's applicant ID
    $userApplicantID = $parkingApplication->getApplicantIDFromUsername($userName);
    
    // Check if the applicant ID exists
    if ($userApplicantID != NULL) {
        // The applicant ID exists so get any permits they have applied for
        $parkingPermits = $parkingApplication->getApplicantPermits($userApplicantID);
        
        // Check if there are any permit applications
        if (count($parkingPermits) > 0) {
            // There are permit applications so print the details
            foreach ($parkingPermits as $parkingPermit) {
                print("                    <tr>
                        <td>{$parkingPermit['permit_type_description']}</td>
                        <td>");
                (isset($parkingPermit['permit_serial_no'])) ? print($parkingPermit['permit_serial_no']) : print("Unknown");
                print("</td>
                        <td>" . date('j F Y', strtotime($parkingPermit['start_date'])) . "</td>
                        <td>" . date('j F Y', strtotime($parkingPermit['end_date'])) . "</td>
                        <td>" . $parkingApplication->getPermitStatus($parkingPermit['status']) . "</td>
                    </tr>\n");
            }
        
        } else {
            // There are no permit applications
            print("                        <td colspan=\"5\">No recent applications found</td>\n");
        }
    
    } else {
        // The applicant ID doesn't exist
        print("                        <td colspan=\"5\">No applications found</td>\n");
    }
    
} catch (\Exception $ex) {
    print("<pre>");
    print_r($ex);
    print("</pre>");
    print("                        <td colspan=\"5\">We are temporarily unable to retrieve your applications. Please try again later.</td>\n");
}

?>
                </table>
            </div>
        </div>
        <!-- END: Permit List -->
        
        <!-- START: New Application Button -->
        <div class="row">
            <div class="large-12 columns">
                <h3>New Registration Application</h3>
                <p><strong>Please note:</strong> If you wish to amend any details on your current registered vehicle, then please refer to <a href="/replacement-permit/index.php">the relevant guidance notes</a> for further information.</p>
                <p><a href="/internal/apply/index.php" class="button uos-button radius">Submit a new application</a></p>
            </div>
        </div>
        <!-- END: New Application Button -->

<?php

// Include page footer
require_once(dirname(__FILE__) . "/../../tpl/static/page_footer.php");

?>
