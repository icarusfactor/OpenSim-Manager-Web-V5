#!/bin/sh
# Usage:
# inotify-change-ownership <dir> <user> <group>
# Arguments:
# dir
#     The directory name to watch
# user
#     The user to which to 'chown' the file
# group
#     The group to which to 'chgrp' the file
#
#  pkill inotifywait
#     To remove all inotifywait processes. 

inotifywait -mrq -e create -e modify --format %w%f "$1" | while read FILE
do
  chown -v $2 "$FILE"
  chgrp $3 "$FILE"
  if [ -d "$FILE" ]; then
    chmod 775 "$FILE"
  elif [ -f "$FILE" ]; then
    chmod 664 "$FILE"
  fi
done
