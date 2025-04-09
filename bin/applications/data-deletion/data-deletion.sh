#!/bin/bash

################################################################################
# File:         data-deletion.sh
# Author:       Scott
# Date:         19th February 2018
# Description:  Deletes stale applicant, permit, vehicle and associated data.
# Version:      1.2.0
################################################################################
# Version History
# ---------------
# 24/08/2017    1.0.0    Script created.
# 19/02/2018    1.1.0    Modified file paths due to hosting changes.
# 13/08/2019    1.2.0    Updated PHP executable path
#
################################################################################
# The exit codes returned are:
#  0 - operation completed successfully
################################################################################

#
# Set variables
#
APP_PATH=/app/uos/hosting/websites/parkingpermit.sunderland.ac.uk
SOURCE_PATH=$APP_PATH/bin/applications/data-deletion
DATA_PATH=$APP_PATH/data/generated/data-deletion



#
# Delete the data from the database
#
/opt/rh/rh-php71/root/usr/bin/php -f $SOURCE_PATH/data-deletion.php



#
# Delete the Blue Badge images
# Note: This will only trigger if the data file is present
#
SBBI_LOCAL_DATA_FILE=stale-blue-badge-images.txt
SBBI_REMOTE_PATH=/app/uos/hosting/websites/wwwadmin.sunderland.ac.uk/public_html/facilities/parking/data/uploads

if [ -f "$DATA_PATH/$SBBI_LOCAL_DATA_FILE" ]
then
    # Delete the Blue Badge images from remote server
    
    # Adjust the IFS (Internal Field Separator) for this run; use new lines ONLY, NOT space/tab/new line etc.
    # Note: We need to keep the original IFS value so we can revert the adjustment later.
    SYS_OLD_IFS=$IFS
    IFS=$'\n'
    
    # Iterate over the data file
    # For each file in the data file ...
    for SBBI_IMG_NAME in $(cat $DATA_PATH/$SBBI_LOCAL_DATA_FILE)
    do
        # ... delete it from the server
        rm "$SBBI_REMOTE_PATH/$SBBI_IMG_NAME" > /dev/null 2>&1
    done
    
    # Reset the IFS to the original value
    IFS=$SYS_OLD_IFS
    
    # Delete the data file
    rm $DATA_PATH/$SBBI_LOCAL_DATA_FILE
fi



#
# Tie up loose ends
#
# Exit
exit 0

