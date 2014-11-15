Detour
======

Easily route devices on your LAN through different VPNs and Interfaces on your EdgeMAX router.

##Description
Detour was created so I could easily switch some of my media devices to the US versions of Netflix and Amazon Prime (I live in Canada).  A friend uses it to route his AppleTV through Canada, USA, or Europe so he can bypass the geographic restrictions of NHL GameCenter.  It can also be used to selectively route different devices through a different WAN connection or VPN.

##How It Works
Detour is a PHP script that allows you to easily manage which route to the internet a particular device on your network will take.  It does this by adding the IP addresses of these devices to an address group.  You then create firewall rules to route the members of these groups through the different interfaces.

##Installation and Setup
There are two parts.  The first part is to add the necessary interface and firewall rules to route the IPs of an address group through a particular interface.  Once that works you can install Detour.

###Setting up a PPTP VPN Connection and Routing

SSH into the EdgeMAX and enter configuration mode...

<code>$ configure</code>

Add a PPTP client ...

>	edit interfaces pptp-client pptpc0
>	set server-ip **VPN-SERVER-IP.COM**
>	set user-id **USERNAME**
>	set password **PASSWORD**
>	set description "VPN Description"
>	set default-route none
>	set require-mppe
>	exit

		pptp-client pptpc0 {
			default-route none
			description "US East VPN"
			mtu 1500
			name-server auto
			password ****
			require-mppe
			server-ip us-east.privateinternetaccess.com
			user-id ****
		}

set protocols static table 1 interface-route 0.0.0.0/0 next-hop-interface pptpc0

set firewall group address-group vpn_diddles

edit firewall modify detour rule 10
set description "Detour route to US VPN pptpc0"
set source group address-group vpn_diddles
set modify table 1
exit


edit service nat rule 5004
set description "Masquerade for pptpc0"
set outbound-interface pptpc0
set type Masquerade
exit


rule 5003 {
	 description "masquerade for pptpc0"
	 log disable
	 outbound-interface pptpc0
	 type masquerade
}



set interfaces ethernet eth0 firewall in modify detour

commit
save

###Installing Detour

SSH in to your EdgeMAX router and run the following commands ...

cd /config
curl -Lk https://github.com/TravisCook/Detour/archive/master.tar.gz | tar xz
mv Detour-master detour
cd detour
sudo ./install.sh
vi group_list.conf
vi ip_list.conf


