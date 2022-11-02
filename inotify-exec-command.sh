#
#
# pkill inotifywait
#      This will kill all inotifywait processes


inotifywait -mq -e create -e modify --format %w%f "$1" | while read FILE
do

[ -f $1/exec/opensim_start ] && cd $1/opensim/bin && ./Opensim_start.sh && rm -f $1/exec/opensim_start && echo "started" 

[ -f $1/exec/opensim_stop ]  && cd $1/opensim/bin && ./Opensim_stop.sh && rm -f $1/exec/opensim_stop && echo "stopping" 

done

