#!/bin/bash

################################################################################
# File:         export-permit-data-printing.sh
# Author:       paul
# Date:         16th August 2023
# Description:  Exports newly approved/reissued permits from the system in CSV
#               format and sends an email with the files in an attached
#               ZIP file.
#
# Version:      2.5.0
################################################################################
# Version History
# ---------------
# 27/07/2015	1.0.0	Script created.
# 29/07/2015    1.1.0   Added data export/attachment for "Staff Residential".
# 29/07/2015    1.2.0   Added additional recipient and changed sender on email.
# 13/08/2015    1.3.0   Removed the "Please do not reply to this message" text.
# 03/09/2015    1.4.0   Added data export/attachment for "Clanny Hospital".
# 22/08/2016    1.5.0   Removed "admin@workflowdynamic.com" as recipient and
#                       added new list of recipients.
#
# 24/08/2016    1.6.0   Removed Facilities Helpdesk and Emma True from email.
# 26/08/2016    1.7.0   Removed "Staff Residential" and "Clanny Hospital" from
#                       export and email attachments.
#
# 06/09/2016    1.8.0   Added Johnathon Mihalop as a recipient.
# 15/12/2016    1.9.0   Replaced Mark Summers with Oliver Hughes on the
#                       email recipient list.
#
# 14/07/2017    1.10.0  Tweaked "DMY" date format and minor modifications to
#                       email body.
#
# 25/08/2017    1.11.0  Removed Nick Michael from recipients list.
# 25/08/2017    1.12.0  Added 'rm' command to remove the attached files from
#                       the server once the email has been sent.
#
# 31/08/2017    1.13.0  Removed Johnathon Mihalop from recipients list.
# 21/09/2017    1.14.0  Revised text in body of email.
# 22/09/2017    1.15.0  Added timestamp to email.
# 23/04/2018    1.16.0  Updated the recipients list be replacing
#                       Courtney Pretlove with Sathiyakeerthy Balachandran.
#
# 30/05/2018    1.17.0  Removed Sathiyakeerthy Balachandran from recipients.
# 18/06/2018    1.18.0  Removed all Liberty Services email addresses.
#
#
# 02/08/2018    2.0.0   Various changes made:
#                       - Removed "admin@firstparking.co.uk" from email
#                         recipients list; adjusted other parameters.
#                       - Added new script variables.
#                       - Updated PHP script paths to use new variable.
#                       - Added logic to check if the CSV files have been
#                         generated, otherwise the script will exit.
#                       - Added code to put generated files in an encrypted ZIP
#                         file.
#                       - Added code to send generated files via SFTP to the
#                         printing contractor.
#                       - Updated deletion parameters.
#
# 26/04/2019    2.1.0   Changed the IP address of the destination SFTP server.
# 13/08/2019    2.2.0   Updated PHP executable path
#
# 04/08/2021    2.3.0   Disabled FTP upload of zip file to parking contractor.
#
# 07/08/2023    2.4.0   Disabled staff & student _paydisplay_rep.php scripts 
#                       and merged their contents into staff_paydisplay.php & 
#                       student_paydisplay.php
# 
# 16/08/2023    2.5.0   Removed FTP stuff and included newly created script
#                       generate_all.php which combines of all of the 
#                       original generate_x.php files
################################################################################
# The exit codes returned are:
#  0 - operation completed successfully
################################################################################

#
# Set variables.
#
# Core script.
PHP_EXECUTABLE=/opt/rh/rh-php71/root/usr/bin/php
APP_PATH=/app/uos/hosting/websites/parkingpermit.sunderland.ac.uk
SOURCE_PATH=$APP_PATH/bin/applications/data-export/permit-printing
DEST_PATH=$APP_PATH/data/generated/permit-printing
DOCS_PATH=$APP_PATH/docs/system/applications/data-export/permit-printing
CURR_DATE_YMD=$(date +%Y%m%d)
CURR_DATE_DMY=$(date +%d/%m/%Y)
CURR_TIMESTAMP=$(date +%H:%M:%S; date +%Z)

# Data encryption.
DATAENC_KEY="2s5AyV!jaKz6@T"
DATAENC_DEST_FILE=$DEST_PATH/New_UOS_Permits_$CURR_DATE_YMD.zip



#
# Export the permit application data.
#
$PHP_EXECUTABLE -f $SOURCE_PATH/generate_all.php



#
# Check if any permit type files exist.
#
if [ "$(ls -A $DEST_PATH)" ];
then
    # Permit type files exist.
    
    
    #
    # Create an encrypted ZIP file that contains the permit type files.
    #
    # Create the encrypted ZIP file.
    zip -q -j -e -P $DATAENC_KEY $DATAENC_DEST_FILE $DEST_PATH/*.csv > /dev/null 2>&1
    
    # Pause to allow the previous process to finish.
    sleep 15
    
    #
    # Send an email with the files as attachments.
    #
    # Create email message body.
    EMAIL_BODY=$(cat <<EOF
University of Sunderland
========================

Re: New Parking Permits
-------------------------------
Date: $CURR_DATE_DMY
Time: $CURR_TIMESTAMP

The attached file contains the approved and reissued parking permits
from the export on the above date and time.

This email was automatically generated.
EOF
)
    
    # Send the email with the attachments.
    echo "$EMAIL_BODY" | mailx -s"New Parking Permits" -a $DATAENC_DEST_FILE -r no-reply@sunderland.ac.uk -c parkingservices@sunderland.ac.uk admin@firstparking.co.uk   
    
    
    #
    # Delete the files that were sent as an attachment.
    #
    sleep 60
    rm -f $DEST_PATH/*_$CURR_DATE_YMD.csv $DATAENC_DEST_FILE


# End of permit type file existence check.
fi



#
# Tie up loose ends.
#
# Exit.
exit 0
