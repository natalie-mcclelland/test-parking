#!/bin/bash

################################################################################
# File:         export-staff-annual-payroll.sh
# Author:       Scott
# Date:         27th July 2015
# Description:  Exports the staff data for those who selected an annual permit
#               and sends an email to Facilities with the relevant data in CSV
#               format within an attached ZIP file.
#
# Version:      2.2.0
################################################################################
# Version History
# ---------------
# 27/07/2015	1.0.0	Script created.
# 29/07/2015    1.1.0   Changed sender on email.
# 13/08/2015    1.2.0   Removed the "Please do not reply to this message" text.
# 14/07/2017    1.3.0   Tweaked "DMY" date format and minor modifications to
#                       email body.
#
# 25/08/2017    1.4.0   Added 'rm' command to remove the attached file from
#                       the server once the email has been sent.
#
# 21/09/2017    1.5.0   Revised text in body of email.
# 22/09/2017    1.6.0   Added timestamp to email.
#
#
# 02/08/2018    2.0.0   Various changes made:
#                       - Added new script variables.
#                       - Updated PHP script paths to use new variable.
#                       - Added code to put generated files in an encrypted ZIP
#                         file.
#                       - Adjusted email parameters.
#                       - Updated deletion parameters.
# 13/08/2019    2.2.0   Updated PHP executable path
#
################################################################################
# The exit codes returned are:
#  0 - operation completed successfully
################################################################################

#
# Set variables.
#
PHP_EXECUTABLE=/opt/rh/rh-php71/root/usr/bin/php
APP_PATH=/app/uos/hosting/websites/parkingpermit.sunderland.ac.uk
SOURCE_PATH=$APP_PATH/bin/applications/data-export/staff-annual-payroll
DEST_PATH=$APP_PATH/data/generated/staff-annual-payroll
CURR_DATE_YMD=$(date +%Y%m%d)
CURR_DATE_DMY=$(date +%d/%m/%Y)
CURR_TIMESTAMP=$(date +%H:%M:%S; date +%Z)

# Data encryption.
DATAENC_KEY="pbTQt!C5QU9q%z"
DATAENC_DEST_FILE=$DEST_PATH/staff-payroll_$CURR_DATE_YMD.zip



#
# Export the staff data into a CSV file.
#
$PHP_EXECUTABLE -f $SOURCE_PATH/generate_staff_payroll.php



#
# Create an encrypted ZIP file that contains the staff data CSV file.
#
# Create the encrypted ZIP file.
zip -q -j -e -P $DATAENC_KEY $DATAENC_DEST_FILE $DEST_PATH/staff-payroll_$CURR_DATE_YMD.csv > /dev/null 2>&1

# Pause to allow the previous process to finish.
sleep 15



#
# Send an email with the file as an attachment.
#
# Create email message body.
EMAIL_BODY=$(cat <<EOF
University of Sunderland
========================

Staff Data For Payroll - Annual Car Parking Permit
--------------------------------------------------
Date: $CURR_DATE_DMY
Time: $CURR_TIMESTAMP

The attached file contains the staff data for Staff Annual car parking
permits with information for Payroll to facilitate the salary mandates,
which was exported from the system on the above date and time.

This email was automatically generated.
EOF
)

# Send the email with the attachments.
echo "$EMAIL_BODY" | mailx -s"Staff Data For Payroll - Annual Car Parking Permit" -a $DATAENC_DEST_FILE -r no-reply@sunderland.ac.uk parkingservices@sunderland.ac.uk



#
# Delete the files that was sent as an attachment.
#
sleep 60
rm -f $DEST_PATH/staff-payroll_$CURR_DATE_YMD.csv $DATAENC_DEST_FILE



#
# Tie up loose ends.
#
# Exit.
exit 0
