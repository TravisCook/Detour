#!/bin/bash

if [ -z "$1" ]
 then
  echo ""
  echo "Usage: set-group-members.sh ip_address=group_name [ip_address=group_name] ..."
  echo ""
  exit 1
 fi

run=/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper
$run begin

readarray route_groups < <(cat /config/detour/group_list.conf | grep "^[^#;]" | cut -d = -f 1)

while test $# -gt 0
do
		# grab ip=group from command line

    echo "Processing $1 ..."
    IFS='=' read -ra ARY <<< "$1"
    IP=${ARY[0]}
    ROUTE=${ARY[1]}
    
    # delete this IP from all route groups
    for GROUP in "${route_groups[@]}"
		do
			GROUP=`echo $GROUP | tr -d '\n'`
			echo "Deleting IP $IP from route group $GROUP"
			$run delete firewall group address-group $GROUP address $IP
		done
		
		if [ $ROUTE != "default" ];
		then
			echo "Adding $IP to route group $ROUTE"
			$run set firewall group address-group $ROUTE address $IP
		fi
		
		echo ""
		
    shift
done

$run commit
$run end

# make sure permissions in active config directory are correct
chgrp -R vyattacfg /opt/vyatta/config/active/

# To enable IP forward, VLAN, or PPPoE offloading, uncomment the appropriate lines below
#sh -c "echo 1 > /proc/cavium/ipv4/fwd"
#sh -c "echo 1 > /proc/cavium/ipv4/vlan"
#sh -c "echo 1 > /proc/cavium/ipv4/pppoe"

exit 0
