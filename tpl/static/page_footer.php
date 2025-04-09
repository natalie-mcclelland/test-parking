<?php
/**
 * Page footer template.
 * 
 * @author Scott Sweeting <scott.sweeting@sunderland.ac.uk>
 * @copyright 2015 University of Sunderland
 * @license Proprietary
 * @version 1.1.0
 */
?>
            <!-- END: Content -->
        </div>
        <!-- END: Content wrapper -->
        
        
        <!-- START: Footer wrapper -->
        <footer id="colophon" class="site-footer" role="contentinfo">
            <div class="iesite-footer">
                <div class="inner">
                    <div class="what-we-do">
                        <p class="footer-title">The University of Sunderland</p>
                        <p>We are an innovative, forward-thinking university with high standards of teaching, research and support. We have strong links with industry and business, and work closely with some of the world's leading companies.</p>
                    </div>
                    <div class="address-info">
                        <p class="tel">Telephone:<br /><a href="tel:+441915153366">+44 (0)191 515 3366</a></p>
                        <p class="email">Email:<br /><a href="mailto:parkingservices@sunderland.ac.uk">parkingservices@sunderland.ac.uk</a></p>
                    </div>
                </div>
            </div>
        </footer>
        <!-- END: Footer wrapper -->
    </div>
    <!-- END: Page wrapper -->
    
    
    <!-- START: Mobile navigation menu -->
    <nav id="tray-navigation" role="navigation">
        <div id="tray-overflow">
            <div class="tray-header">
                <ul>
                    <li style="color:#fff;" class="tray-single-line">My Sunderland</li>
                    <li><a href="https://my.sunderland.ac.uk/display/STAFF/Staff+home"><img class="icon-lock" src="/template/uos/img/lock-icon.png" alt="Security lock icon">Staff</a></li>
                    <li><a href="https://my.sunderland.ac.uk/display/infosupresources/Student+home"><img class="icon-lock" src="/template/uos/img/lock-icon.png" alt="Security lock icon">Students</a></li>
                </ul>
                <img class="burgercrosstray" src="/template/uos/img/tray-close.png" alt="close tray menu">
            </div>
            <div class="tray-logo-section">
                <img class="tray-logo" src="/template/uos/img/logo/Sunderland_uni_logo_mobile.png" alt="University of Sunderland transparent mobile logo">
            </div>
            <div class="menu-tray-menu-container">
                <ul id="menu-primary-menu-1" class="menu">
                    <li><a href="/">Home</a></li>
                    <li><a href="/internal/index.php">University Staff / Student Permit</a></li>
                    <li><a href="/external/index.php">Non University Staff / Student Permit</a></li>
                    <li><a href="/replacement-permit/index.php">Change of Details/Lost or Replacement Permit</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- END: Mobile navigation menu -->
    
    
    <script src="/template/foundation/js/vendor/jquery.js" type="text/javascript"></script>
    <script src="/template/foundation/js/foundation.min.js" type="text/javascript"></script>
    <script src="/template/uos/js/global-min.js" type="text/javascript"></script>
    <!--<script src="/template/uos/js/fastclick.js" type="text/javascript"></script>-->
    <script src="/template/foundation/js/foundation/foundation.abide.js" type="text/javascript"></script>
    <script src="/template/uos/js/rem.js" type="text/javascript"></script>
    <script src="/template/uos/js/responsive-tables.js" type="text/javascript"></script>
    <script src="/template/uos/js/customFunctions.js" type="text/javascript"></script>
    
    <!--<script type="text/javascript">
      window.addEventListener('load', function() {
            new FastClick(document.body);
        }, false);

        svgeezy.init('nocheck', 'png');
    </script>-->
    <script type="text/javascript">
        $(document).foundation();
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".menu-child-arrow").click(function () {
                $(this).parent('.menu-item-has-children').find('>.multilevel-linkul-0').toggle();
                $('.tray-plus', this).toggleClass('rotate-plus1 rotate-plus2');
            });
        });
    </script>
    
    
    <!-- Date/Time picker assets -->
    <link href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" />
    <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="/template/uos/js/datepicker.js"></script>
    
    <!-- IE Alert -->
    <!--[if lt IE 9]>
        <link rel="stylesheet" type="text/css" href="/template/uos/js/iealert/style.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="/template/uos/iealert/iealert.js"></script>
        <script>
            $(document).ready(function() {
                $("body").iealert({support:"ie8"});
            });
        </script>
    <![endif]--> 

</body>
</html>
