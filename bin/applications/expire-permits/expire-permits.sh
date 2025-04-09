#!/bin/bash

################################################################################
# File:         expire-permits.sh
# Author:       Scott
# Date:         28th July 2015
# Description:  Marks expired permits with the status of "Expired".
# Version:      1.1.0
################################################################################
# Version History
# ---------------
# 28/07/2015	1.0.0	Script created.
# 13/08/2019    1.1.0    Updated PHP executable path
#
################################################################################
# The exit codes returned are:
#  0 - operation completed successfully
################################################################################

#
# Set variables
#
APP_PATH=/app/uos/hosting/websites/parkingpermit.sunderland.ac.uk
SOURCE_PATH=$APP_PATH/bin/applications/expire-permits



#
# Expire the permits
#
/opt/rh/rh-php71/root/usr/bin/php -f $SOURCE_PATH/expire-permits.php



#
# Tie up loose ends
#
# Exit
exit 0
