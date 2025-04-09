<?php
// Debugging
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

// Include class files
require_once(dirname(__FILE__)."/../../lib/ext/simplesamlphp/vendor/autoload.php");


/**
 * Replacement permit notes.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2017 University of Sunderland
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


/*
 * Initialise access control and verify if an internal user is logged in
 */
$as = new SimpleSAML_Auth_Simple('default-sp');
if ($as->isAuthenticated()) {
    // Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
    \SimpleSAML\Session::getSessionFromRequest()->cleanup();
}


// Include page header
require_once(dirname(__FILE__) . "/../../tpl/static/page_header.php");

?>

        <div class="row">
            <div class="large-12 columns">
                <h2>Change of registration details</h2>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns">
                <p>If you wish to amend any details then please email <a href="mailto:parkingservices@sunderland.ac.uk">parkingservices@sunderland.ac.uk</a> for further assistance.</p>
            </div>
        </div>

<?php

// Include page footer
require_once(dirname(__FILE__) . "/../../tpl/static/page_footer.php");

?>