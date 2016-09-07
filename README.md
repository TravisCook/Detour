![Detour Icon](../images/icon.png?raw=true)

Detour
======

Easily route devices on your LAN through different VPNs and Interfaces on your [EdgeMAX](http://www.ubnt.com/edgemax/edgerouter-lite/) router.

Detour was created so I could easily switch some of my media devices to the US versions of Netflix and Amazon Prime (I live in Canada).  A friend uses it to route his AppleTV through Canada, USA, or Europe so he can bypass the geographic restrictions of NHL GameCenter.  It can also be used to selectively route different devices through a different WAN connection or VPN.

###Screenshot
Below is a screenshot of the Web Interface once it's been added to my home screen and launched as an app.

![Detour Screenshot](../images/screenshot.png?raw=true)

##How It Works
Detour is a PHP script that allows you to easily manage which route to the internet a particular device on your network will take.  It does this by adding the IP addresses of these devices to an address group.  You then create firewall rules to route the members of these groups through the different interfaces.

##Installation and Setup
First you need to create the interface to route the data through.  If this is a VPN you need to create the VPN client interface.  Next you have to setup a routing table and firewall rule to route data through the interface based on an address group.  Lastly, add the modify firewall to your LAN interface.

####Adding a PPTP VPN Client Interface
Create the VPN Client.  Here we're using *pptpc0* as the interface name.

	configure
	edit interfaces pptp-client pptpc0
	set server-ip us-east.privateinternetaccess.com
	set user-id **USERNAME**
	set password **PASSWORD**
	set description "USA VPN"
	set default-route none
	set require-mppe
	exit

**NOTE:** In the above example I'm using Private Internet Access as my VPN provider.  If you are too, make sure you use the random username and password generated in your control panel, NOT your account username and password.

####Adding an OpenVPN Client Interface
Create the VPN Client.  Here we're using *vtun0* as the interface name.

	configure
	edit interfaces openvpn vtun0
	set config-file /config/auth/expressvpn_washington_dc.ovpn
	set description "ExpressVPN OpenVPN"
	set mode client
	exit

**NOTE:** In the above example I'm using ExpressVPN as my VPN provider.  By default, their OpenVPN config overrides the default route so all traffic passes through the VPN.  I had to edit their .ovpn file, remove the "route-method exe" and "route-delay 2" lines, and replace them with a "route-noexec" line.

If you want to see if the vpn is connected run the commands below.  You should see an IP address and S/L states of u/u for the pptpc0 interface.
	
	commit
	exit
	show interfaces

Enable NAT masquerade for the interface.  Here we are using rule number 5004.  You can use the next rule number available on your system, as long as it's greater than 5000.

	configure
	edit service nat rule 5004
	set description "Masquerade for pptpc0"
	set outbound-interface pptpc0
	set type masquerade
	exit

####Creating a Routing Table for the New Interface
If you already have a table 1 use the next available table number.

	set protocols static table 1 interface-route 0.0.0.0/0 next-hop-interface pptpc0

####Creating the Firewall Rules
First we need to create the address group.
	
	set firewall group address-group vpn_usa

Next we create the firewall modify rule to route the data through the table above if the source IP address is in the address group.  

	edit firewall modify detour rule 10 <- Increment this number for additional interfaces
	set description "Detour route to US VPN pptpc0"
	set source group address-group vpn_usa    <- Use the group name you created above
	set modify table 1       <- Use the table number you used above
	exit

####Add the Firewall Modify rule to your LAN Interface
You only need to do this once.  Here we're using *eth0* as the LAN interface.  Replace that with your LAN's interface or bridge name.

	set interfaces ethernet eth0 firewall in modify detour

####DNS Forwarding
If you have set your router to forward the DNS queries, those queries will still be sent through your default internet connection, not through the VPN. This was making Netflix think I was still using a proxy even though all my other traffic was being sent through the VPN.  You may need to disable DNS forwarding or set DNS manually on the device so queries go directly to the name servers and not to the router.

####Commit and Save

	commit
	save

##Installing Detour

	cd /config
	curl -Lk https://github.com/TravisCook/Detour/archive/master.tar.gz | tar xz
	mv Detour-master detour
	cd detour
	sudo ./install.sh
	
####Configuring Detour
There are two configuration files you need to edit before you can use Detour.

#####group_list.conf
Enter your address group names and descriptions into the group_list.conf file.  In our example above the address group name is *vpn_usa*.

	vi group_list.conf

Add one address group per line in the format *address_group_name = Description*.

######An example entry:
vpn_usa = USA VPN

#####ip_list.conf
Enter the IP addresses and device names for the device's you'd like to use with Detour into the ip_list.conf file.  These IP addresses should be statically assigned to each device.

	vi ip_list.conf

Add one IP address per line in the format *IP_address = Device Description*.

######An example entry:
192.168.1.75 = Apple TV

####Re-Enabling Offloading
When the EdgeMax encounters a *modify table* in the firewall it disables offloading.  Some users with fast internet connections were reporting very high CPU and slow network performance after using Detour.  See [this thread](http://community.ubnt.com/t5/EdgeMAX/Detour-An-EdgeMax-app-to-selectively-route-different-devices/m-p/1175162#M56400) for information.

You can force the router to re-enable offloading after Detour runs by editing the /config/detour/set-group-members.sh script.  At the bottom are a few lines you can uncomment to re-enable offloading:

	# To enable IP forward, VLAN, or PPPoE offloading, uncomment the appropriate lines below
	#sh -c "echo 1 > /proc/cavium/ipv4/fwd"
	#sh -c "echo 1 > /proc/cavium/ipv4/vlan"
	#sh -c "echo 1 > /proc/cavium/ipv4/pppoe"

##Using Detour
Connect to Detour at http://ROUTER_IP/detour

If you're using an iOS device, add Detour to your home screen for quick access.

![Add To Home Screen](../images/add_to_home.png?raw=true)
