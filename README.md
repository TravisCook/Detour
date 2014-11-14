Detour
======

Easily route devices on your LAN through different VPNs and Interfaces on your EdgeMAX router.

##Description
Detour was created so I could easily switch some of my media devices to the US versions of Netflix and Amazon Prime (I live in Canada).  A friend uses it to route his AppleTV through Canada, USA, or Europe so he can bypass the geographic restrictions of NHL GameCenter.  It can also be used to selectively route different devices through a different WAN connection or VPN.

##How It Works
Detour is a PHP script that allows you to easily manage which route to the internet a particular device on your network will take.  It does this by adding the IP addresses of these devices to an address group.  You then create firewall rules to route the members of these groups through the different interfaces.

##Installation and Setup
There are two parts.  The first is installing Detour.  The second is to add the necessary interface and firewall rules to route the IPs of an address group through a particular interface.

###Installing Detour


