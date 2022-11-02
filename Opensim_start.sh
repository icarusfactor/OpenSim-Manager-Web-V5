#!/bin/bash
USERNAME="dyount"
cd /home/dyount/opensim.spotcheckit.org/opensim/bin/
screen -S Opensim_1 -dm bash -c "mono --desktop -O=all OpenSim.exe" -L;screen -S Opensim_1 -X multiuser on;screen -S Opensim_1 -X acladd $USERNAME
PID=$(ps aux|grep "SCREEN -S Opensim_1"| grep -v grep|tr -s ' '|cut -d " " -f2)
screen -S $PID.Opensim_1 -X logfile /home/dyount/opensim.spotcheckit.org/opensim/bin/OpenSim.Console.log
screen -S $PID.Opensim_1 -X log
# Start backend services. 
/home/dyount/opensim.spotcheckit.org/opensim/bin/inotfy-start-backend.sh
