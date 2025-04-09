#!/bin/bash

################################################################################
# File:         blue-badge-tfr.sh
# Author:       Scott
# Date:         24th July 2015
# Description:  Transfers the Blue Badge file uploads to the management area
#               under 'WWWAdmin'.
#
# Version:      1.2.0
################################################################################
# Version History
# ---------------
# 24/07/2015    1.0.0   Script created.
# 14/08/2018    1.1.0   Updated destination path and server.
# 01/07/2019    1.2.0   Removed destination server as both locations now
#                       co-exist on same server.
#
################################################################################
# The exit codes returned are:
#  0 - operation completed successfully
################################################################################

#
# Set variables
#
ORIGINPATH=/app/uos/hosting/websites/parkingpermit.sunderland.ac.uk/data/uploads/
DESTPATH=/app/uos/hosting/websites/wwwadmin.sunderland.ac.uk/public_html/facilities/parking/data/uploads/


#
# Transfer the contents of the origin directory to the destination directory
# using the 'push' method.  The process will also remove the files in the
# origin directory after the synchronisation has taken place.
#
# Note:
# 	-avz	normal method (but will delete files in the destination):-
#		a : archive (same as: rlptgoD )
#		v : verbose
#		z : compression
#
#	-rlptDvz		as above but without user/group copy
#       -rlptDuogqz             as above but:-
#                                - without verbosity
#                                - non-errors suppressed ("quiet" mode)
#                                - skips files that are newer in the destination
#                                - preserves owner and group
#
#	man rsync		is your friend :-)
#
rsync -rlptDuogqz --remove-source-files --rsh=ssh $ORIGINPATH $DESTPATH > /dev/null 2>&1


#
# Tie up loose ends
#
# Exit
exit 0
