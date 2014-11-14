#!/bin/bash

echo "Adding Detour sudo file to /etc/sudoers.d/ ... "
cp /config/detour/install.d/100detour /etc/sudoers.d/

echo "Creating backup of lighttpd.conf in /etc/lighttpd/ ..."
cp /etc/lighttpd/lighttpd.conf /etc/lighttpd/lighttpd.detour_bak

echo "Copying Detour's lighttpd.conf file to /etc/lighttpd/ ..."
cp /config/detour/install.d/lighttpd.conf /etc/lighttpd/

echo "Restarting lighttpd ..."
PID=`cat /var/run/lighttpd.pid`
kill $PID
/usr/sbin/lighttpd -f /etc/lighttpd/lighttpd.conf

echo "Done. You should now be able to access Detour at http://ROUTER_IP/detour."


