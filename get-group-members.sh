#!/bin/bash
 
run="/opt/vyatta/bin/vyatta-op-cmd-wrapper"

readarray route_groups < <(cat /config/detour/group_list.conf | grep "^[^#;]" | cut -d = -f 1)

for group in "${route_groups[@]}"
do
	readarray ip_list < <($run show firewall group $group | awk 'f;/Members/{f=1}' | tr -d ' ' | grep -v '^$')
	echo ${group} = ${ip_list[*]}
done

exit 0
