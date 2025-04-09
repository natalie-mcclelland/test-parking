#!/bin/bash

################################################################################
# File:         export_authorised-vehicles-list.sh
# Author:       Scott
# Date:         14th July 2017
# Description:  Exports the permit application data from the system in CSV
#               format and sends an email with the file in an attached
#               ZIP file.
#
# Version:      2.1.0
################################################################################
# Version History
# ---------------
# 14/07/2017	1.0.0	Script created.
# 25/08/2017    1.1.0   Added First Parking to email recipients list.
# 25/08/2017    1.2.0   Added 'rm' command to remove the attached file from
#                       the server once the email has been sent.
#
# 21/09/2017    1.4.0   Revised text in body of email.
# 22/09/2017    1.5.0   Added timestamp to email.
#
#
# 02/08/2018    2.0.0   Various changes made:
#                       - Added new script variables.
#                       - Updated PHP script paths to use new variable.
#                       - Added code to put generated files in an encrypted ZIP
#                         file.
#                       - Updated deletion parameters.
# 13/08/2019    2.1.0    Updated PHP executable path
#
################################################################################
# The exit codes returned are:
#  0 - operation completed successfully
################################################################################

#
# Set variables
#
# Core script.
PHP_EXECUTABLE=/opt/rh/rh-php71/root/usr/bin/php
APP_PATH=/app/uos/hosting/websites/parkingpermit.sunderland.ac.uk
SOURCE_PATH=$APP_PATH/bin/applications/data-export/authorised-vehicles-list
DEST_PATH=$APP_PATH/data/generated/authorised-vehicles-list
CURR_DATE_YMD=$(date +%Y%m%d)
CURR_DATE_DMY=$(date +%d/%m/%Y)
CURR_TIMESTAMP=$(date +%H:%M:%S; date +%Z)

# Data encryption.
DATAENC_KEY="oJf!9Q1djSl5@k"
DATAENC_DEST_FILE=$DEST_PATH/authorised-vehicles-list_$CURR_DATE_YMD.zip



#
# Export the permit application data into a CSV file.
#
$PHP_EXECUTABLE -f $SOURCE_PATH/generate_authorised-vehicles-list.php



#
# Create an encrypted ZIP file that contains the permit application data CSV file.
#
# Create the encrypted ZIP file.
zip -q -j -e -P $DATAENC_KEY $DATAENC_DEST_FILE $DEST_PATH/authorised-vehicles-list_$CURR_DATE_YMD.csv > /dev/null 2>&1

# Pause to allow the previous process to finish.
sleep 15



#
# Send an email with the file as an attachment.
#
# Create email message body.
EMAIL_BODY=$(cat <<EOF
University of Sunderland
========================

Re: Authorised Vehicles List
----------------------------
Date: $CURR_DATE_DMY
Time: $CURR_TIMESTAMP

The attached file contains the authorised vehicles list for the
above date which is correct as of the above timestamp.

This email was automatically generated.
EOF
)

# Send the email with the attachments.
echo "$EMAIL_BODY" | mailx -s"Authorised Vehicles List" -a $DATAENC_DEST_FILE -r parkingservices@sunderland.ac.uk -c parkingservices@sunderland.ac.uk admin@firstparking.co.uk



#
# Delete the files that were sent as an attachment.
#
sleep 60
rm -f $DEST_PATH/authorised-vehicles-list_$CURR_DATE_YMD.csv $DATAENC_DEST_FILE



#
# Tie up loose ends.
#
# Exit.
exit 0

