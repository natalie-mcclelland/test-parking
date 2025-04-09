<?php

// Define class namespace
//namespace CarParkingSystem;

// Include class files
require_once (dirname(__FILE__)."/../../lib/ext/simplesamlphp/vendor/autoload.php");

/**
 * Internal applicant system login.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.2.0
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

if ($as->isAuthenticated()) {
    // Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
    \SimpleSAML\Session::getSessionFromRequest()->cleanup();

    // The user is already logged in so redirect to the user dashboard
    header("Location: /internal/index.php");
    exit;
}

// Get login URL
$url = htmlspecialchars($as->getLoginURL());

// Include page header
require_once(dirname(__FILE__) . "/../../tpl/static/page_header.php");
?>
        <div class="row">
            <div class="large-12 columns">
                <h2>Login</h2>
            </div>
        </div>
<?php
    // Check if there is a logout message to display
    if (isset($_GET['lo'])) {
?>
        <div class="alert-box success radius">
            <p>You have been logged out successfully.</p>
        </div>
<?php
    }
?>
         <div class="row">
            <div class="large-12 columns">
                <p>To use this site you must login via the following link. You will be taken to a page 
                where you should enter your standard University user ID (in the format 
                <strong>xx0xxx@sunderland.ac.uk</strong>) and password. You will also be asked to 
                verify it is you by approving the request with Microsoft Authenticator.</p>
                <p><a href="<?php print($url); ?>">Car Parking Gateway Login</a></p>               
            </div>
        </div>

<?php
// Include page footer
require_once(dirname(__FILE__) . "/../../tpl/static/page_footer.php");
?>