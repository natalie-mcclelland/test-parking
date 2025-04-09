<?php
// Debugging
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

// Include class file
require_once(dirname(__FILE__)."/../../lib/ext/simplesamlphp/vendor/autoload.php");

/**
 * External applicant application confirmation.
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
//$as->requireAuth();

// Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
\SimpleSAML\Session::getSessionFromRequest()->cleanup();

// Include page header
require_once(dirname(__FILE__) . "/../../tpl/static/page_header.php");

?>

        <div class="row">
            <div class="large-12 columns">
                <h2>Application Status</h2>
                <h3>Non University Staff / Student</h3>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns">
                <div class="panel callout">
                    <h4>Application Successfully Submitted</h4>
                    <p>Thank you for submitting your application. Your application will now be processed and an approval notification will be emailed in due course.</p>
                </div>
            </div>
        </div>

<?php

// Include page footer
require_once(dirname(__FILE__) . "/../../tpl/static/page_footer.php");

?>