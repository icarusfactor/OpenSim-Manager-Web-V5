#!/bin/bash 
#
#
#  pkill inotifywait
#      Kill all inotifywait processes
DIR=/home/dyount/opensim.spotcheckit.org
INUSER="dyount"
INGROUP="dyount"
$DIR/opensim/bin/inotify-change-ownership.sh $DIR/backups $INUSER $INGROUP &
$DIR/opensim/bin/inotify-exec-command.sh $DIR &
