<?php
// Debugging
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

// Include class files
require_once(dirname(__FILE__)."/../lib/ext/simplesamlphp/vendor/autoload.php");

/**
 * Maintenance message.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2016 University of Sunderland
 * @license Proprietary
 * @version 1.4.0
 */

/*
 * Initialise access control and verify if an internal user is logged in
 */
$as = new SimpleSAML_Auth_Simple('default-sp');
if ($as->isAuthenticated()) {
    // Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
    \SimpleSAML\Session::getSessionFromRequest()->cleanup();
}


// Include page header
require_once(dirname(__FILE__) . "/../tpl/static/page_header.php");

?>

        <div class="row">
            <div class="large-12 columns">
                <div class="panel">
                    <h2>Welcome to the Car Parking Gateway</h2>
                    <p>Parking permits are available to staff, students and University partners that work on Campus.</p>
                </div>
            </div>
        </div>
        <div class="row">
             <div class="large-12 columns">
                <div class="panel">
                    <h4>Important information regarding parking applications for 2022/23</h4>
                    <!--<p>We are currently making preparations to accept permit applications for the 2020/21 period, therefore the application system has been temporarily suspended. Please check again soon for details of when we will be accepting new applications.</p>
                    <p><strong>Please note:</strong> If you wish to amend any details on your current permit or obtain a replacement permit, then please email <a href="mailto:parkingservices@sunderland.ac.uk">parkingservices@sunderland.ac.uk</a> for further assistance. &nbsp;Please <strong><u>do not</u></strong> apply for another permit.</p>
                    <p>For additional information, please <a href="https://www.sunderland.ac.uk/help/contact-us/parking">read the car parking information</a> or contact Parking Services on <a href="tel:+441915153366">extension 3366</a>.</p>-->
                    <p>We are no longer accepting parking applications for 22/23 so if you are a member of staff or a current student and you need to park or have a general enquiry then please email <a href="mailto:parkingservices@sunderland.ac.uk">parkingservices@sunderland.ac.uk</a>.</p>
                    <!--<p>Parking on site is currently free. --><p>Please keep checking the <a href="https://sunderlandac.sharepoint.com/sites/StaffNews/" target="_blank">USOnline (staff)</a> or <a href="https://sunderlandac.sharepoint.com/sites/cs_AboutUS" target="_blank">AboutUS (students)</a> bulletins for regular updates on parking.</p>
                </div>
            </div>
        </div>
<?php

// Include page footer
require_once(dirname(__FILE__) . "/../tpl/static/page_footer.php");

?>
