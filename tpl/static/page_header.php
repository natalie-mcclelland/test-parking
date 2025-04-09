<?php
/**
 * Page header template.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.1.0
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>University of Sunderland :: Car Parking Gateway</title>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge"> -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link href="/template/foundation/css/normalize.css" rel="stylesheet" type="text/css" />
    <link href="/template/foundation/css/foundation.min.css" rel="stylesheet" type="text/css" />
    <link href="/template/uos/css/responsive-tables.css" type="text/css" media="screen" rel="stylesheet" />
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300" rel="stylesheet" property="stylesheet" />
    <link href="/template/uos/css/global.css" rel="stylesheet" type="text/css" />
    <script src="/template/foundation/js/vendor/modernizr.js" type="text/javascript"></script>
    <!--[if lt IE 9]>
      <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js" type="text/javascript"></script>
      <script src="//s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js" type="text/javascript"></script>
      <script src="//html5base.googlecode.com/svn-history/r38/trunk/js/selectivizr-1.0.3b.js" type="text/javascript"></script>
      <script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.1.0/respond.min.js" type="text/javascript"></script>
      <link href="/template/uos/css/ie-fix.css" type="text/css" media="screen, projection" rel="stylesheet" property="stylesheet" />
    <![endif]-->
    <script async src="https://www.google.com/recaptcha/api.js"></script>
</head>
<body>
    
    <div id="abovehead"></div>
    <a class="skip-link screen-reader-text" href="#content">Skip to content</a>
    
    <!-- START: Page wrapper -->
    <div id="page" class="hfeed site">
        <!-- START: Header wrapper -->
        <div class="above-head-options">
            <div class="inner">
                <div class="login-options">
                    <ul id="topbar">
                        <li style="color: #fff;">Sharepoint</li>
                        <li><a href="https://sunderlandac.sharepoint.com/sites/home"><img class="icon-lock" src="/template/uos/img/lock-icon.png" alt="Security lock icon">Login</a></li>
                    </ul>
                </div>
                <div class="accessibility-options">
                    <ul id="topaccess">
                        <li><a href="https://www.sunderland.ac.uk/accessibility/">Accessibility</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="mobile-scrolled static-head-spacing"></div>
        <header id="masthead" class="js site-header" role="banner">
            <div id="iemasthead">
                <div class="inner">
                    <div id="main-nav-contain">
                        <div id="site-title" class="vert-center-outer">
                            <div class="vert-center-inner">
                                <div class="vert-center">
                                    <a class="header-home-url" href="/index.php" rel="home">
                                        <img class="header-logo" src="/template/uos/img/logo/Sunderland_uni_logo.png" alt="University of Sunderland transparent logo" />
                                        <img class="header-logo-mobile" src="/template/uos/img/logo/Sunderland_uni_logo_mobile.png" alt="University of Sunderland transparent mobile logo" />
                                    </a>
                                    <img class="burger js-burger mainmenuclick" src="/template/uos/img/burger.png" alt="Menu burger" />
                                    <img class="burgercross mainmenuclick" src="/template/uos/img/close.png" alt="Close menu" />
                                </div>
                            </div>
                        </div>
                        <hr class="line-above-nav" />
                        <div id="main-navigation" class="vert-center-outer">
                            <div class="vert-center-inner">
                                <div class="vert-center">
                                    <nav id="site-navigation" role="navigation">
                                        <div class="menu-primary-menu-container">
                                            <ul id="menu-primary-menu" class="menu">
                                                <li><a href="/index.php">Home</a></li>
                                                <li><a href="/internal/login.php">University Staff / Student Register</a></li>
                                                <li><a href="/external/index.php">Non University Staff / Student Register</a></li>
                                                <li><a href="/replacement-permit/index.php">Change of Registration Details</a></li>
                                            </ul>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <div class="menucolor"></div>
                    </div>
                </div>
            </div>
        </header>
        <!-- END: Header wrapper -->

<?php

// if (!isset($as)) {
//     // Initialise class
//     require_once (dirname(__FILE__)."/../../lib/ext/simplesamlphp/vendor/autoload.php");
//     $as = new SimpleSAML_Auth_Simple('default-sp');
// }

// Check if the navigation menu should be displayed
// if ($as->isAuthenticated()) {
//     // Revert to PHP session (above call replaces PHP session with SimpleSAMLphp session)
//     \SimpleSAML\Session::getSessionFromRequest()->cleanup();

//     $attributes = $as->getAttributes();
//     $userName = explode("@", $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0])[0];    
//     $logoutUrl = $as->getLogoutURL();
// ?>
        <!-- START: Sub navigation menu -->
        <div id="navmenu" class="row">
            <div class="large-12 columns" style="padding-left:0; padding-right: 0;">
                
            </div>
        </div>
        <!-- END: Sub navigation menu -->
<?php
// }
?>

        <!-- START: Content wrapper -->
        <div id="content" class="row">
            <!-- START: Content -->
