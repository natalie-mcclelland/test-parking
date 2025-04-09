<?php
/**
 * Home page.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.6.0
 */

// Include class file
//require_once(dirname(__FILE__)."/../lib/ext/simplesamlphp/vendor/autoload.php");

/*
 * Initialise access control and verify if an internal user is logged in
 */
// $as = new SimpleSAML_Auth_Simple('default-sp');
// if ($as->isAuthenticated()) {
//     // Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
//     \SimpleSAML\Session::getSessionFromRequest()->cleanup();

//     // The user is already logged in so redirect to the user dashboard
//     header("Location: /internal/index.php");
//     exit;
// }

// Include page header
require_once(dirname(__FILE__) . "/../tpl/static/page_header.php");
?>

        <div class="row">
            <div class="large-12 columns">
                <div class="panel">
                    <h2>Welcome to the Car Parking Gateway</h2>
                    <p>Staff and students who want to take advantage of reduced parking tariffs on site must register their vehicle with the University. We are now accepting applications for 24/25 from staff, students and University partners. Staff and students will be required to login in order to access the applications.</p>
                    <p>Motorcycle parking is free of charge and membership of the University’s Car Parking Scheme is required.</p>
                    <p>For additional information, please <a href="https://www.sunderland.ac.uk/help/contact-us/parking">read the car parking information</a> or contact Parking Services on <a href="tel:+441915153366">0191 515 3366</a>. Information on parking tariffs are displayed on parking signs around the University estate.</p>
                    <p>If you are considering coming to work by alternative modes of travel you can <a href="https://sunderlandac.sharepoint.com/sites/cs_sustainability/SitePages/Travel.aspx" target="_blank">view more information on SharePoint</a>. 
                    If you are not sure about the options available to you, please 
                    <a href="mailto:sustainability@sunderland.ac.uk" target="_blank">email your home postcode to sustainability@sunderland.ac.uk</a> 
                    to receive a personalised journey plan.</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns">
                <h3>What do you require?</h3>
            </div>
        </div>
        <div class="row">
            <div class="large-12 columns large-centered">
                <a href="/internal/login.php" class="button uos-button large radius">University Staff / Student Register</a>
                <a href="/external/index.php" class="button uos-button large radius">Non University Staff / Student Register</a>
                <a href="/replacement-permit/index.php" class="button uos-button large radius">Change of Registration Details</a>
            </div>
        </div>

<?php

// Include page footer
require_once(dirname(__FILE__) . "/../tpl/static/page_footer.php");

?>
