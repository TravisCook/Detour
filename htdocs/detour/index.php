<?php
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
?>
<html>
<head>
	<title>Detour</title>
  <meta http-equiv="Content-Type" content="text/html;" />
  <meta http-equiv="Content-Language" content="en-us" />
  <meta name="viewport" content="width=device-width, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes" />	
	<link rel="apple-touch-icon-precomposed" sizes="76x76" href="img/apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon-precomposed" sizes="120x120" href="img/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="img/apple-touch-icon-152x152.png" />
	<link rel="apple-touch-icon-precomposed" sizes="180x180" href="img/apple-touch-icon-180x180.png" />
</head>
<style>
	body { 
		padding: 0;
		margin: 0; 
		font-family: proxima-nova, sans-serif;
		font-size: 18px;
	}
	button {
		background-color: #FE8600;
		border-radius: 5px;
		border: 0;
		cursor: pointer;
		display: inline-block;	
		font-size: 18px;
		height: 35px;
		margin: 0;
		padding: 8px 16px;
		text-align: center;
		width: 150px;
	}
	fieldset {
		border: 0;
	}
	select { 
		font-size:18px; 
		min-width: 120px;
	}
	table {
		width: 90%;
		margin: 0 auto;
	}
	table td {
		padding: 10px 5px;
		width: 50%;
	}
	.header {
		background-color:#FE8600; 
		width: 100%;
		margin:0 0 1em 0;
		position: relative;
		border-bottom: 1px solid #000;
	}
	.title { 
		font-size: 36px; 
		font-weight: bold;
		padding: 10px 15px;
		cursor:pointer;
		display:inline-block;
	}
	.version { 
		color: gray;
		position: absolute;
		right: 15px;
		bottom: 10px;
		font-size:14px;
		display:inline-block;
	}
	
	.titlebox { }
	.titleit {color: #d8d8d8; }
	.deviceName { font-size:18px; text-align:right;}
	.vpnList { }
	.saveButton { margin:2em auto; text-align:center; }
	.status { font-weight: bold; margin: .2em auto; display:block; text-align:center;}
	.saving { color: orange; }
	.saved { color: green; }
	.error { color: red; }
	.reading {color: gray; }
	
	
</style>
<body>

<div class="header">
	<div class="title" onclick="location.reload();">
		<span class="titlebox">DETOUR</span>
	</div>
	<div class="version">1.0</div>
</div>
<div id="status" class="status">
<div id="spinner"><img src="img/spinner.gif"></div>

<?php 

	// Set debug mode
	$debug = FALSE;
	if(array_key_exists('debug', $_GET))
	{
		unset($_GET['debug']);
		$debug = TRUE;
	}

	// Super flush command for safari
	function sflush() {
		echo str_pad('',2048); // for safari
		echo "&nbsp;\n";
		flush();
	}

	// Our exec wrapper for easy debugging
	function runCmd($cmd) {
	
		global $debug;
		
		if($debug) {
			echo "$cmd:<br/>";
			sflush();
		}
		
		$output = null;
		exec($cmd, $output);	
		
		if($debug) {	
			foreach($output as $line)
				echo "$line<br/>";
			
			echo "<br/>";
			sflush();
		}
	
		return $output;
	}

	// flush out the start of the page to let the client know we're working
	sflush();
	sflush();

	// Read our VPN list config file
	$output = runCmd('cat /config/detour/group_list.conf | grep "^[^#;]" 2>&1');
	$group_list = array();
	
	foreach ($output as $val)
	{
		list($key,$val) = explode("=", $val);
		$group_list[trim($key)] = trim($val);
	}

	// Read our list of IPs eligable for VPN routing
	$output = runCmd('cat /config/detour/ip_list.conf | grep "^[^#;]" 2>&1');
	$ip_list = array();
	foreach ($output as $val)
	{
		list($key,$val) = explode("=", $val);
		$ip_list[trim($key)] = trim($val);	
	}

	// Check if we have should be updating any VPN groups
	if(count($_GET) > 0)
	{
		$fishy = FALSE;
	
		$changes = "";
		foreach($_GET as $ip => $vpn)
		{
			$ip = str_replace("_",".",$ip);
			
			// make sure the key is an IP that's in our list
			if(!key_exists($ip, $ip_list))
				$fishy = TRUE;
			
			// make sure the value is a vpn name that's in our list, or "default"
			if(!key_exists($vpn, $group_list) && strcmp($vpn,"default") !== 0)
				$fishy = TRUE;
				
			$changes .= " " . $ip . "=" . $vpn;
		}
	
		echo '<div class="status saving">Saving Changes</div>';
		sflush();
	
		if(!$fishy)
		{
			$output = runCmd('sudo /config/detour/set-group-members.sh ' . $changes);
			echo '<div class="status saved">Changes Saved</div>';
		}
		else
			echo '<div class="status error">ARGUMENT ERROR!</div>';
		
		sflush();
	}

	echo "<div class='status reading'>Reading Config</div>";
	sflush();
	
	// Run our helper script to get a list of IPs currently in each VPN group	
	$output = runCmd('sudo /config/detour/get-group-members.sh');
	$current_list = array();
	foreach ($output as $value)
	{
		list($key,$value) = explode("=", $value);
		$current_list[trim($key)] = trim($value) . " ";	
	}

?>
</div>

<?php if(count($ip_list) == 0) { ?>
	<div class="status error">Edit the /config/detour/ip_list.conf file to add your devices.</div>
<?php } ?>

<?php if(count($group_list) == 0) { ?>
	<div class="status error">Edit the /config/detour/group_list.conf file to add your route address groups.</div>
<?php } ?>

<form method="get">
    <fieldset>
    	<table>
    	<?php foreach ($ip_list as $ip => $ipDesc) { ?>
    		<tr>
    		<td class="deviceName"><label><? echo $ipDesc; ?>:</label></td>
    		<td class="vpnList">
					<select name="<?php echo $ip; ?>">
							<option value="default">Default Route</option>
							<?php foreach ($group_list as $group => $groupDesc) {
								echo '<option value="' . $group . '"';
								if(strpos($current_list[$group], "$ip ") !== false)
									echo ' selected';
								echo ">$groupDesc</option>\n";
							} ?>
					</select>
					</td>
				</tr>
			<?php } ?>
				</table>	
				<div class="saveButton">
        	<button type="submit">DETOUR</button>
				</div>
				
    </fieldset>
</form>

<?php if(!$debug) { ?>
<script type="text/javascript">
	// Hide the spinner and status 
	document.getElementById('status').style.display = 'none';
</script>
<?php } ?>
</body>
</html>
