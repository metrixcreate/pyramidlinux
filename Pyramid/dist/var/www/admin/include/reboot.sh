#!/bin/bash
#script to sleep and then reboot, to be run from the web interface
touch /tmp/reboot
sleep 5
/sbin/reboot
