<?php
require_once("config.php");
require_once("common.php");
session_start();
if($_POST['remember'])
{
	setcookie(THcookieid."-uname", $_SESSION['username'], time()+THprofile_cookietime, THprofile_cookiepath);
	setcookie(THcookieid."-id",   $_SESSION['userid'],   time()+THprofile_cookietime, THprofile_cookiepath);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />

<?php
$db=new ThornModDBI();

if($_GET['action']=="login")
{
	// Three POST parameters:
	// $_POST['name'], $_POST['password'], $_POST['remember']
	echo "<title>".THname."&#8212; Login</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';

	if(isset($_POST['name']) && isset($_POST['password']))
	{
		$query = "SELECT * FROM ".THusers_table." WHERE username=\"".mysql_real_escape_string($_POST['name']).
		"\" AND password=\"".mysql_real_escape_string(md5(THsecret_salt.$_POST['password']))."\" AND approved=1";
		$userresult = $db->myquery($query);
		$userdata=mysql_fetch_assoc($userresult);

		if($userdata != NULL)
		{
		$_SESSION['username'] 	= $userdata['username'];
		$_SESSION['userid'] 	= generateRandID();
		$_SESSION['userlevel'] 	= $userdata['userlevel'];
		$_SESSION['admin'] 		= $userdata['mod_admin'];
		$_SESSION['mod_array'] 	= $userdata['mod_array'];
		$_SESSION['mod_global'] = $userdata['mod_global'];
		
		if ($userdata['mod_global'] || $userdata['mod_array'])
		{
			$_SESSION['moderator']=true;
		}

		// Update userid field
		$db->myquery("UPDATE ".THusers_table." SET userid=\"".mysql_real_escape_string($_SESSION['userid'])."\", timestamp=".time().
		"WHERE username=\"".mysql_real_escape_string($_POST['name'])."\"");
	} else { // invalid login?
		echo "<div class=\"pgtitle\">Login error</div><br />\n";
		echo "<b>There was an error processing your request.</b><br>\n";
		echo "<u>Possible causes:</u><br>\n";
		echo "Invalid username<br>\n";
		echo "Invalid password<br>\n";
		echo "Your account has not been approved<br>\n";
		echo "Your account has been disabled\n";
		}
	}
	
	if(!isset($_SESSION['username']))
	{
	echo "<div class=\"pgtitle\">Login</div><br />\n";
	echo "<table><form action=\"profiles.php?action=login\" method=\"POST\">\n";
	echo "<tr><td>Username:</td><td><input type=\"text\" name=\"name\" maxlength=\"30\" ></td></tr>\n";
	echo "<tr><td>Password:</td><td><input type=\"password\" name=\"password\" maxlength=\"30\" ></td></tr>\n";
	echo "<tr><td><input type=\"checkbox\" name=\"remember\" ><font size=\"2\">Remember me</td>\n";
	//This checks to see if end user has even bothered to change the default email.  No use giving a link to something that won't work.  ~tyamzzz
	if(THprofile_emailaddr != "THIS IS NOT AN EMAIL") { echo "<td align=right><a href=\"".THurl."profiles.php?action=forgotpass\"><font size=\"2\">Forgot password?</a></td></tr>\n"; }
	echo "<tr><td><input type=\"submit\" value=\"Login\"></td></tr>\n";
	echo "</form></table>\n";
	} else {
		echo "<div class=\"pgtitle\">Logged in as ".$_SESSION['username']."</div><br />\n";
		echo "You are logged in as <b>".$_SESSION['username']."</b>. <br><br>\n";
		echo "[<a href=\"profiles.php?action=logout\">Logout if this is not you</a>]\n";
		echo "</div></td></tr></table>\n";
	}
}
else if($_GET['action']=="logout")
{
	echo "<title>".THname."&#8212; Logout</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';

	if(isset($_SESSION['username']))
	{
	    if(isset($_COOKIE[THcookieid.'-uname']) && isset($_COOKIE[THcookieid.'-id'])){
	         setcookie(THcookieid."-uname", "", time()-THprofile_cookietime, THprofile_cookiepath);
	         setcookie(THcookieid."-id",   "", time()-THprofile_cookietime, THprofile_cookiepath);
	    }

	    /* Unset PHP session variables */
	    unset($_SESSION['username']);
	    unset($_SESSION['userid']);
		unset($_SESSION['userlevel']);
		unset($_SESSION['admin']);
		unset($_SESSION['moderator']);
		unset($_SESSION['mod_array']);
		echo '<div class="pgtitle">Logged out</div><br />';
		echo "You are now logged out!<br><br>\n";
	}
	else
	{
		echo '<div class="pgtitle">Logged out</div><br />';
		echo "You are not logged in!<br><br>\n";
	}
	echo "[<a href=\"drydock.php\">Board index</a>]\n";
}
else if($_GET['action']=="memberlist")
{
	echo "<title>".THname."&#8212; Members</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	$can_access = 0;
	
	if(THprofile_viewuserpolicy==2) {$can_access = 1;}
	elseif(THprofile_viewuserpolicy==1 && isset($_SESSION['username'])) {$can_access = 1;}
	elseif(THprofile_viewuserpolicy==0 && ($_SESSION['admin'] || $_SESSION['moderator']) ){$can_access = 1;}
	
	if($can_access)
	{
		echo "<div class=\"pgtitle\">Members</div><br />\n";
		
		$users=array();
		$queryresult=$db->myquery("SELECT * FROM ".THusers_table);
		while ($user=mysql_fetch_assoc($queryresult)) 
		{
			$users[]=$user;
		}
		
		foreach( $users as $user_entry )
		{
			if($user_entry['username'] != "initialadmin")
			{ 
				echo "<a href=\"profiles.php?action=viewprofile&user=".$user_entry['username']."\">".
				$user_entry['username']."</a><br />\n";
			}
		}
	}
	else
	{
		echo "<div class=\"pgtitle\">Permissions error</div><br />\n";
		echo "<b>Error:</b> You are not authorized to view this page!<br><br>\n";
		echo "[<a href=\"drydock.php\">Board index</a>]\n";
	}
}
else if ($_GET['action']=="viewprofile")
{
	if(!isset($_GET['user'])){
	die("You must specify a user!");
	}
	
	if(THprofile_lcnames){
	$username = mysql_real_escape_string(strtolower($_GET['user']));
	}
	else {
	$username = mysql_real_escape_string($_GET['user']);
	}
	
	$user = mysql_fetch_array(
	$db->myquery("SELECT * FROM ".THusers_table." WHERE username='".$username."'")
	);
	
	if(!$user){
	die("Invalid user specified!");
	}

	echo "<title>".THname."&#8212; Viewing profile of ".$user['username']."</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	$can_access = 0;
	
	if(THprofile_viewuserpolicy==2) {$can_access = 1;}
	elseif(THprofile_viewuserpolicy==1 && isset($_SESSION['username'])) {$can_access = 1;}
	elseif(THprofile_viewuserpolicy==0 && ($_SESSION['admin'] || $_SESSION['moderator']) ){$can_access = 1;}
	
	if($can_access)
	{
		echo "<div class=\"pgtitle\">User profile:".$user['username']."</div><br />\n";
	
		if(canEditProfile($user['username'])){
		echo "<a href=\"profiles.php?action=edit&user=".$user['username']."\"><img src=\"".
		THurl."static/edit.png\" alt=\"Edit profile\" border=\"0\">Edit profile</a>";
		
		if($_SESSION['admin']){
		echo "   <a href=\"profiles.php?action=permissions&user=".$user['username']."\"><img src=\"".
		THurl."static/shield.png\" alt=\"Edit permissions\" border=\"0\">Edit permissions</a>";
		echo "   <a href=\"profiles.php?action=remove&user=".$user['username']."\"><img src=\"".
		THurl."static/disable.png\" alt=\"Disable user\" border=\"0\">Disable user</a>";
		}
		
		echo "<br>\n";
		}
	
		echo "        </div><br />\n<div align=\"right\">";
		if($user['has_picture']){
		echo "<img src=\"".THurl."images/profiles/".$user['username'].".".
		$user['has_picture']."\" align=\"left\" /><br />\n";
		}
		else{
		echo "<img src=\"".THurl."static/nopicture.png\" align=\"left\" />\n";
		}

		if($user['capcode']){
		$capcode = $db->myresult("SELECT capcodeto FROM ".THcapcodes_table." WHERE capcodefrom='".$user['capcode']."'");
		if($capcode)
		{
		echo "<b>Posts as:</b> ".$capcode."<br />\n";
		}
		}

		if($user['gender'] == "M" || $user['gender'] == "F"){
		echo "<b>Gender:</b> ".$user['gender']."<br />\n";
		}
		else
		{
		echo "<b>Gender:</b> Unspecified<br />\n";
		}

		if($user['age']){
		echo "<b>Age:</b> ".$user['age']."<br />\n";
		}
		else
		{
		echo "<b>Age:</b> Unspecified<br />\n";
		}

		if($user['location']){
		echo "<b>Location:</b> ".$user['location']."<br />\n";
		}
		else
		{
		echo "<b>Location:</b> Unspecified<br />\n";
		}

		if($user['mod_admin']){
		echo "<b>Position:</b> Administrator<br />\n";
		}
		else if ($user['mod_global'] || $user['mod_array'])
		{
		echo "<b>Position:</b> Moderator<br />\n";
		}

		if($user['contact']){
		echo "<b>Contact information:</b> ".$user['contact']."<br />\n";
		}
		else
		{
		echo "<b>Contact information:</b> Unspecified<br />\n";
		}

		if($user['description']){

		echo "<b>Description:</b> ".$user['description']."<br />\n";
		}
		else
		{
		echo "<b>Description:</b> None<br />\n";
		}
		
		echo "<a href=\"".THurl."profiles.php?action=memberlist\">Return to member list</a>";
		echo "</div></div>\n";
	}
	else
	{
		echo "<div class=\"pgtitle\">Permissions error</div><br />\n";
		echo "<b>Error:</b> You are not authorized to view this page!<br><br>\n";
		echo "[<a href=\"drydock.php\">Board index</a>]\n";
		echo "</td></tr></table>\n";
	}
}
else if($_GET['action']=="edit")
{
	if(!isset($_GET['user'])){
	die("You must specify a user!");
	}
	
	if(THprofile_lcnames){
	$username = strtolower($_GET['user']);
	}
	else{
	$username = $_GET['user'];
	}

	if(!$db->myresult("SELECT COUNT(*) FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'")){
	die("Invalid user specified!");
	}
	
	if(!canEditProfile($username)){
	die("You cannot edit this user's profile!");
	}
	
	$stuff_to_update = "";
	
	if(isset($_POST['capcode'])){
	
		$capcode = $db->myresult("SELECT capcodeto FROM ".THcapcodes_table." WHERE capcodefrom='".$user['capcode']."'");
		// Don't bother with the approval process if it's identical to the capcode that's already been approved
		if($capcode != $_POST['capcode'])
		{
			// First 128 characters, if they want more they'll have to use the admin panel :]
			// This is here to prevent the remote possibility of someone proposing a capcode, and then in between the time the admin views the proposed capcodes page
			// and clicks the "Approve" link, someone changes it to something malicious.
			if(!$db->myresult("SELECT proposed_capbode FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'"))
				$update_string .= "proposed_capcode='".mysql_real_escape_string(substr($_POST['capcode'],0,128))."'";
		}
	}
	
	if(isset($_POST['age'])){
	
		if($update_string != ""){
		$update_string .= ",";
		}
	
		$update_string .= "age='".mysql_real_escape_string(substr(trim($_POST['age']), 0, 3))."'";
	}
	
	if(isset($_POST['gender'])){
	
		if($update_string != ""){
		$update_string .= ",";
		}
	
		$update_string .= "gender='".mysql_real_escape_string(substr(trim($_POST['gender']), 0, 1))."'";
	}
	
	if(isset($_POST['location'])){
	
		if($update_string != ""){
		$update_string .= ",";
		}
	
		$update_string .= "location='".mysql_real_escape_string(trim($_POST['location']))."'";
	}
	
	if(isset($_POST['contact'])){
	
		if($update_string != ""){
		$update_string .= ",";
		}
	
		$update_string .= "contact='".mysql_real_escape_string(trim($_POST['contact']))."'";
	}
	
	if(isset($_POST['description'])){
	
		if($update_string != ""){
		$update_string .= ",";
		}
	
		$update_string .= "description='".mysql_real_escape_string(trim($_POST['description']))."'";
	}
	
	$passErrString = ""; // This only gets set if there is a problem with the password
	
	// Only users can edit their own passwords-while admins can edit just about anything else
	if(isset($_POST['password']) && $_SESSION['username']==$username && isset($_POST['changepass'])){

		$password = trim($_POST['password']);
		
		$passlength = strlen($password);
		
		if($passlength < 4){
		$passErrString = "Sorry, your password must be at least 4 characters.<br>\n";
		}
		else{

		if($update_string != ""){
		$update_string .= ",";
		}
		
		$update_string .= "password='".mysql_real_escape_string(md5(THsecret_salt.$password))."'";
		}

	}
	
	if(isset($_POST['remove_picture'])){
	
		$ext = $db->myresult(
		"SELECT has_picture FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'"
		);
		
		if($ext != null){
			unlink(THpath."images/profiles/".$username.$ext);
			
			if($update_string != ""){
			$update_string .= ",";
			}
			
			$update_string .= "has_picture=NULL";
		}
	}
	
	$imgErrString = ""; // This only gets set if there is a problem 

	if($_FILES['picture']['error']==0 && $_FILES['picture'])
	{
		//print_r($_FILES['picture']);
		$pending = $db->myresult(
		"SELECT pic_pending FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'"
		);
		
		if($pending){
		$imgErrString .= "Picture already pending admin approval.<br>\n";
		}
		
		if($_FILES['picture']['size']>THprofile_maxpicsize){
		$imgErrString .= "Picture must be no larger than ".THprofile_maxpicsize." bytes.<br>\n";
		}
		
		//check the MIME type, not the extention - tyam
		if($_FILES['picture']['type'] == "image/jpeg") { $filetype = "jpg"; }
		elseif($_FILES['picture']['type'] == "image/gif") { $filetype = "gif"; }
		elseif($_FILES['picture']['type'] == "image/png") { $filetype = "png"; }
		
		if($_FILES['picture'] && !in_array($filetype, array("jpg","png","gif"))){
		$imgErrString .= "Picture must be a JPG, PNG, or GIF.<br>\n";
		}
		
		if ($filetype=="jpg") {
			$theimg=imagecreatefromjpeg($_FILES['picture']['tmp_name']);
		}
		elseif ($filetype=="png" && is_callable("imagecreatefrompng")) {
			$theimg=imagecreatefrompng($_FILES['picture']['tmp_name']);
		}
		elseif ($filetype=="gif" && is_callable("imagecreatefromgif")) {
			$theimg=imagecreatefromgif($_FILES['picture']['tmp_name']);
		}
		
		if($theimg == null)
		{
			$imgErrString .= "Unknown error.<br>\n";
		}
		else
		{
			$orig_width = imagesx($theimg);
			$orig_height = imagesy($theimg);
		
			// Resize if necessary
			if($_FILES['picture'] && ($orig_height > 500 || $orig_height > 500))
			{
				//Thumbnail code.
				//Man, this code is a female canine. (Good thing I took this from post-common :])
				if ($orig_height>$orig_height)
				{
					$targh=500;
					$targw=(500/$orig_height)*$orig_width;
					if ($targw>500)
					{
						$ratio=500/$targw;
						$targw=500;
						$targh=$targh*$ratio;
					}
				} 
				else 
				{
					$targw=500;
					$targh=(500/$orig_width)*$orig_height;
					if ($targh>500)
					{
						$ratio=500/$targh;
						$targh=500;
						$targw=$targw*$ratio;
					}
				}//if width>height

				$targw=round($targw);
				$targh=round($targh);

				$resized_image=imagecreatetruecolor($targw,$targh);
				imagecopyresampled($resized_image,$theimg,0,0,0,0,$targw,$targh,$orig_width,$orig_height);
				if ($filetype=="png" || $filetype=="gif")
				{
					imagepng($resized_image,$_FILES['picture']['tmp_name']);
				} 
				else 
				{
					imagejpeg($resized_image,$_FILES['picture']['tmp_name'],THjpegqual);
				}
			}
		}
		
		if($imgErrString == ""){
			$picpath = THpath.'unlinked/'.$username.".".$filetype;
			
			if($_FILES['picture'] && !move_uploaded_file($_FILES['picture']['tmp_name'], $picpath)){
			// Error moving the file where it was supposed to be, so don't update the DB
			$imgErrString .= "Unknown error.<br>\n";
			}
		}
		
		if($imgErrString == ""){ // The reason this check is here is because if move_uploaded_file fails $imgErrString gets set to a non-null value
		
			if($update_string != ""){
			$update_string .= ",";
			}
			
			$update_string .= "pic_pending='".$filetype."'";
		}
	}

	
	if($update_string != ""){
	$updatequery = 
	"UPDATE ".THusers_table." SET ".$update_string." WHERE username='".mysql_real_escape_string($username)."'";
	$db->myquery($updatequery);
	}
	
	$user = mysql_fetch_array(
	$db->myquery("SELECT * FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'")
	);
	
	echo "<title>".THname."&#8212; Editing profile of ".$user['username']."</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	echo "<div class=\"pgtitle\">User profile:".$user['username']."</div><br />\n";
	
	// For errors involving changing of password or uploading of image
	if($imgErrString != ""){
	echo "<font color=\"#ff0000\">".$imgErrString."</font>";
	}
	if($passErrString != ""){
	echo "<font color=\"#ff0000\">".$passErrString."</font>";
	}
	
	echo "<div align=\"right\">\n";
	echo "<form id=\"profileedit\" action=\"".THurl."profiles.php?action=edit&user=".
	$username."\" method=\"post\" enctype=\"multipart/form-data\">";
	
	
	echo "<p align=\"left\">\n"; // This is for picture manipulation stuff
	echo '<table width=100% align=center><tr><td width=50%>';
	if($user['has_picture']){
	echo "<img src=\"".THurl."images/profiles/".$user['username'].".".
	$user['has_picture']."\" align=\"left\" /><br />\n";
	echo "<input type=\"checkbox\" name=\"remove_picture\" value=\"1\"> Remove picture<br />\n";
	}
	else{
	echo "<img src=\"".THurl."static/nopicture.png\" align=\"left\" />\n";
	}
	echo '</td></tr><tr><td width=50%>';
	if($user['pic_pending']){
	echo "<img src=".THurl."static/time.png>".$username." has a picture awating admin approval.<br \>\n";
	}
	else{
	echo "<b>Upload a new picture: </b></td><td><input type=\"file\" name=\"picture\" /></td></tr>\n";
	echo "<tr><td colspan=2>To be displayed on the main site, it first must be manually approved by an admin.\n";
	echo "File must be a JPEG, GIF, or PNG no larger than 500x500 or ".THprofile_maxpicsize." bytes.";
	echo "If the image is too large, it will be resized.</td></tr>\n";
	}
	
	echo '<tr><td>';
	// If user has been granted a capcode by the admins, they can specify how to display their name
	if($user['capcode']){
		$capcode = $db->myresult("SELECT capcodeto FROM ".THcapcodes_table." WHERE capcodefrom='".$user['capcode']."'");
		
		if($capcode)
		{		
			echo "<b>Current capcode displays as:</b></td><td>".$capcode;
			echo "\n</td></tr><tr><td>";
		} 
		if($user['proposed_capcode'])
		{
			echo "<b>Capcode awaiting approval:</b></td><td>".$user['proposed_capcode']."<br />\n";
		} 
		else 
		{ //HAJIME NO KAPPUKOUDO
			echo "<b>Propose a capcode:</b></td><td><input type=\"text\" name=\"capcode\""
				."length=\"128\" maxlength=\"128\" />";
			echo "<i><small>(admin approval required)</small></i><br />\n";
		}
		echo '</td></tr><tr><td>';
	}
	
	echo "<b>Gender:</b></td><td>";
	?>
	  <SELECT name="gender">
        <OPTION <?php if($user['gender'] == "U" || !$user['gender']) { echo "SELECTED"; } ?> value="U">--
        <OPTION <?php if($user['gender'] == "M" ) { echo "SELECTED"; } ?> value="M">Male
        <OPTION <?php if($user['gender'] == "F" ) { echo "SELECTED"; } ?> value="F">Female
      </SELECT>
	<?php
	echo '</td></tr><tr><td>';
	echo "<b>Age:</b></td><td><input type=\"text\" name=\"age\" value=\""
		.replacequote($user['age'])."\" length=\"3\" maxlength=\"3\" /><br />\n";
	
	echo '</td></tr><tr><td>';
	echo "<b>Location:</b></td><td><input type=\"text\" name=\"location\" value=\""
		.replacequote($user['location'])."\"/><br />\n";

	echo '</td></tr><tr><td>';
	echo "<b>Contact information:</b></td><td><input type=\"text\" name=\"contact\" value=\""
			.replacequote($user['contact'])."\"/><br />\n";

	echo '</td></tr><tr><td>';
	echo "<b>Description:</b></td><td><textarea name=\"description\" rows=\"5\" columns=\"30\">\n";
	echo replacequote($user['description']);
	echo "</textarea><br>\n";

	echo '</td></tr><tr><td>';
	if($_SESSION['username']==$username){
	echo "<b>Password:</b></td><td><input type=\"password\" name=\"password\">";
	echo "(Confirm <input type=\"checkbox\" name=\"changepass\" value=\"1\">)<br>\n";
	}
	
	echo '</td></tr></table>';
	echo "<input type=\"submit\" value=\"Submit\" id=\"subbtn\" /><br \>\n";
	echo "<a href=\"".THurl."profiles.php?action=viewprofile&user=".$username."\">Return to member profile</a>";
	echo "</div></div>\n";
	
}
else if($_GET['action']=="register")
{
	if(isset($_SESSION['username'])){
	die("But you are logged in!");
	}
	
	if(THprofile_regpolicy==0){
	die("Registration disabled.");
	}
	
	$success == 0;
	
	echo "<title>".THname."&#8212; Register</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	$errorstring = "";
	if(isset($_POST['user']))
	{	
	
		if(THprofile_lcnames){
		$username = strtolower(trim($_POST['user']));
		}
		else {
		$username = trim($_POST['user']);
		}
		
		$password = trim($_POST['password']);
		$email = trim($_POST['email']);
		
		$reserved_words = array(
		"admin","guest","root","banned","moderator","mod","administrator","trendster","trendy");
		
		$nameexists = $db->myresult(
		"SELECT COUNT(*) FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'");
		
		if($nameexists || in_array(strtolower($username),$reserved_words)){
		$errorstring .= "Sorry, an account with this name already exists.<br>\n";
		}
		
		$namelength = strlen($username);
		if($namelength < 4 || $namelength > 30){
		$errorstring .= "Sorry, your name must be between 4 and 30 characters.<br>\n";
		}
		
		if(!eregi("^([0-9a-z\.-_])+$", $username)){
        $errorstring .= "Sorry, your name must be alphanumeric and contain no spaces.<br>\n";
        }
		
		
		if($password){
			$passlength = strlen($password);
			if($passlength < 4){
			$errorstring .= "Sorry, your password must be at least 4 characters.<br>\n";
			}
		}
		else{
		$errorstring .= "You must provide a password!<br>\n";
		}
		
		if(isset($_POST['email']) && strlen($email)){

	         /* Check if valid email address */
			if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
            $errorstring .= "You must provide a valid email address!<br>\n";
			}
			
			if($db->myresult("SELECT COUNT(*) FROM ".THusers_table." WHERE email='".mysql_real_escape_string($email)."'")){
			$errorstring .= "That email has already been used to register an account!<br>\n";
			}
		}
		else{
		$errorstring .= "You must provide an email address!<br>\n";
		}
		
		if($errorstring == "") { // No errors encountered so far, attempt to register
			$pass_md5 = md5(THsecret_salt.$password);
		
			if(THprofile_regpolicy == 1) {
			$insertquery = "INSERT INTO ".THusers_table.
			" (username, password, userlevel, email, approved) VALUES ('".
			mysql_real_escape_string($username)."','".mysql_real_escape_string($pass_md5)."',".THprofile_userlevel.
			",'".mysql_real_escape_string($email)."',0)";
			}
			else{ // THprofile_regpolicy == 2
			$insertquery = "INSERT INTO ".THusers_table.
			" (username, password, userlevel, email, approved) VALUES ('".
			mysql_real_escape_string($username)."','".mysql_real_escape_string($pass_md5)."',".THprofile_userlevel.
			",'".mysql_real_escape_string($email)."',1)";
			}
			// This returns null on a successful query, so...
			$fail = @$db->myresult($insertquery);  //idk
			if($fail){
				$errorstring .= "Database error.<br>\n";
				echo $insertquery."<br>\n";
			} else if(!$fail) {
				echo "You have registered successfully.<br>\n";
			}
		}
	}
	
	if($errorstring != ""){
	echo "The following errors were encountered:<br>\n";
	echo $errorstring;
	}
	
	if(!$success){
	echo "<div class=\"pgtitle\">Register a new account</div><br />\n";
	echo "<form action=\"profiles.php?action=register\" method=\"POST\">\n";
	echo "<b>Username:</b><input type=\"text\" name=\"user\" maxlength=\"30\" ><br>\n";
	echo "<b>Password:</b><input type=\"password\" name=\"password\" maxlength=\"30\" ><br>\n";
	echo "<b>Email:</b><input type=\"text\" name=\"email\" maxlength=\"50\" ><br>\n";
	echo "<input type=\"submit\" value=\"Register\"><br>\n";
	echo "</form>\n";
	}
	else{
	
		echo "You have successfully registered an account with username <b>".$username."</b>.<br>\n";
	
		if(THprofile_regpolicy == 1) {
		
			echo "However, you must be manually approved by a moderator before logging in.<br>\n";
			
			if(THprofile_emailwelcome){
			echo "You will receive notification of your approval through email.<br>\n";
			}
			
		}
		else{ // THprofile_regpolicy == 2
		
			echo "You may log in as soon as desired.<br>\n";
			
			if(THprofile_emailwelcome){
			sendWelcome($username, $email);
			echo "An email containing your account information has been sent to your specified email address.<br>\n";
			}
		}
	
	}
	echo "[<a href=\"drydock.php\">Board index</a>]\n";
	echo "</td></tr></table>\n";
}
else if($_GET['action']=="forgotpass")
{
	if(isset($_SESSION['username'])){
	die("But you are logged in!");
	}
	
	echo "<title>".THname."&#8212; Lost password</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	echo "<div class=\"pgtitle\">Forgot password</div><br />\n";
	
	if(!isset($_POST['user'])){
	
		echo "<b>Note:</b> submitting this form will reset your password.<br>\n";
		echo "<form action=\"profiles.php?action=forgotpass\" method=\"POST\">\n";
		echo "Username:</td><td><input type=\"text\" name=\"user\" maxlength=\"30\" >\n";
		echo "<input type=\"submit\" value=\"Submit\">\n";
		echo "</form><br><br>\n";
	}
	else{
	
		if(THprofile_lcnames){
		$username = mysql_real_escape_string(strtolower($_POST['user']));
		}
		else{
		$username = mysql_real_escape_string($_POST['user']);
		}
		
		if(!$db->myresult("SELECT COUNT(*) FROM ".THusers_table." WHERE username='".$username."'")){
		die("Invalid user specified!");
		}
		
		$user = mysql_fetch_array($db->myquery("SELECT * FROM ".THusers_table." WHERE username='".$username."'"));
		$pass = generateRandStr(8);
		
		$updatestring = "UPDATE ".THusers_table. " SET password='".mysql_real_escape_string(md5($pass)).
		"' WHERE username='".$username."'";
		
		// This way, it will only send an email if the password reset was actually successful
		if($db->myresult($updatestring)){
		sendnewpass($_POST['user'],$user['email'],$pass,$_SERVER['REMOTE_ADDR']);
		echo "Your password has been reset and emailed to your specified address.<br><br>\n";
		}
		else{
		echo "There was an error resetting your password.  Please try again later.<br><br>\n";
		}
	}
	echo "[<a href=\"drydock.php\">Board index</a>]\n";
	echo "</td></tr></table>\n";
	
}
elseif($_GET['action']=="permissions")
{
	if(!isset($_GET['user'])){
	die("You must specify a user!");
	}
	
	if(THprofile_lcnames){
	$username = strtolower($_GET['user']);
	}
	else {
	$username = $_GET['user'];
	}

	if(!$db->myresult("SELECT COUNT(*) FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'")){
	die("Invalid user specified!");
	}
	
	// Adding one more requirement to canEditProfile: the user has to be an admin (canEditProfile will return true if it is the user's own profile)
	if(!canEditProfile($username) || !$_SESSION['admin']){
	die("You cannot edit this user's permissions!");
	}
	
	echo "<title>".THname."&#8212; Viewing permissions of ".$username."</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	echo "<div class=\"pgtitle\">User permissions:".$username."</div><br />\n";
	
	$boardscount = $db->myresult("SELECT COUNT(*) FROM ".THboards_table);
	if(isset($_POST['permsub']))
	{
		if($_POST['admin']){
		$admin = "mod_admin = 1,";
		}
		else{
		$admin = "mod_admin = 0,";
		}
		
		if($_POST['moderator']){
		$moderator = "mod_global = 1,";
		}
		else{
		$moderator = "mod_global = 0,";
		}
		
		$mod_array = "";
		for($i=1;$i<=$boardscount;$i++){
			// This mod_array string will be a comma-separated list of board numbers
			if($_POST['mod_board_'.$i]){
				if($mod_array==""){
				$mod_array = $i;
				}
				else {
				$mod_array = $mod_array . "," . $i;
				}
			}
		}
		
		$otherstuff = "";
		
		// Basically how this works is, the benevolent admin, in his/her infinite kindness, will grant some
		// users the ability to use capcodes.  How this happens is first, the admin will enter in the grantee's hash
		// into the capcode field.  From then on, the user will be able to customize how his/her capcode will appear
		// (pending admin approval, of course), due to the fact that any changes made and approved will automatically
		// be tied into the tripcode hash.  This allows users to change their capcode without an admin having to
		// manually edit the capcodes table or fiddle around with the admin panel.  The user edits their profile to taste,
		// and all the admin has to do is click "Approve".
		// See, I like this because it makes it easier for non-admins to have capcodes.
		if($_POST['remove_capcode'])
		{
			$otherstuff = ",capcode='',proposed_capcode=''";
		}
		else if($_POST['capcode'])
		{
			$otherstuff = ",capcode='".mysql_real_escape_string($_POST['capcode'])."'"; 
		}
		
		if($_POST['userlevel'])
		{		
			// Can the user even set their userlevel that high?
			if($_SESSION['userlevel'] >= intval($_POST['userlevel']))
				$otherstuff = $otherstuff . ",userlevel=".intval($_POST['userlevel']);
			else
				print "<span style=\"color:#ff0000;font-weight:bolder;\">You cannot raise the userlevel to one higher than your own!</span><br />\n";
		}
		
		$query = "UPDATE ".THusers_table." SET ".$admin.$moderator." mod_array='"
		.mysql_real_escape_string($mod_array)."'".$otherstuff." WHERE username='".mysql_real_escape_string($username)."'";
		$db->myquery($query);
		//echo $query; print_r($_POST); die();
	}
	
	$user = mysql_fetch_array(
	$db->myquery("SELECT * FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'"));
	
	$boards=array();
	$queryresult=$db->myquery("SELECT * FROM ".THboards_table);
	while ($board=mysql_fetch_assoc($queryresult))
	{
		$boards[]=$board;
	}

	echo "<form action=\"profiles.php?action=permissions&user=".$username."\" method=\"POST\">\n";
	
	if($user['mod_admin']){
	echo "<input type=\"checkbox\" name=\"admin\" value=\"1\" checked=\"on\" > Admin";
	}
	else {
	echo "<input type=\"checkbox\" name=\"admin\" value=\"1\"> Admin";
	}
	echo "<br>\n";
	
	if($user['mod_global']){
	echo "<input type=\"checkbox\" name=\"moderator\" value=\"1\" checked=\"on\" > Global moderator";
	}
	else {
	echo "<input type=\"checkbox\" name=\"moderator\" value=\"1\"> Global moderator";
	}
	echo "<br>\n";
	
	echo "<u>Individual boards:</u><br>\n";
	$add_new_line = 0;
	foreach($boards as $board_to_mod){
	
		$add_new_line++;
		if(canmodboard($board_to_mod['id'],$user['mod_array']))
		{
			echo "<input type=\"checkbox\" name=\"mod_board.".$board_to_mod['id'].
			"\" value=\"1\" checked=\"on\" > /".$board_to_mod['folder']."/ moderator";
		}
		else {
			echo "<input type=\"checkbox\" name=\"mod_board.".$board_to_mod['id'].
			"\" value=\"1\"> /".$board_to_mod['folder']."/ moderator";
		}

		if($add_new_line == 2){
		$add_new_line = 0;
		echo "<br>\n";
		}
		else {
		echo "    ";
		}
	}
	
	// If there are an odd number of boards, start on a new line for userlevel
	if($add_new_line == 1){
	echo "<br>\n";
	}
	
	echo "<u>Userlevel:</u><br />\n";
	echo "<input type=\"text\" name=\"userlevel\" value=\"".$user['userlevel']."\"/><br />";
	
	echo "<u>User's capcode hash:</u><br />\n";
	echo "<input type=\"text\" name=\"capcode\" value=\"".replacequote($user['capcode'])."\"/>";
	echo "<input type=\"checkbox\" name=\"remove_capcode\" value=\"1\"> Remove";
	
	echo "<input type=\"hidden\" name=\"permsub\" value=\"1\">\n";
	echo "<br \><input type=\"submit\" value=\"Submit\">\n";
	echo "</form></div>\n";
	echo "<a href=\"".THurl."profiles.php?action=viewprofile&user=".$username."\">Return to member profile</a>";
}
//this function is really dangerous because if someone doesn't use this correctly 
//they can lock themselves out of admin stuff completely, unless they have access
//to phpmyadmin.  BE CAREFUL WITH THIS, END-USER >:[                   Love, tyam
elseif($_GET['action']=="remove")
{
	if(!isset($_GET['user'])){
	die("You must specify a user!");
	}
	if(THprofile_lcnames){ $username = strtolower($_GET['user']); }
 	else { $username = $_GET['user']; }
	if(!$db->myresult("SELECT COUNT(*) FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'"))
	{
		die("Invalid user specified!");
	}
	// Only admins can do this.
	if(!$_SESSION['admin']){ die("You cannot do this!"); }
	
	//$db->myquery("DELETE FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'");
	//how about instead of deleting, we just disable them so they can't just simply rereg and if something goes wrong, we can
	//fix them.  This also allows us to just temporarily suspend their account - tyam

	$db->myquery("UPDATE ".THusers_table." SET approved = '-2' "
		."WHERE username='".mysql_real_escape_string($username)."'");

	echo "<title>".THname."&#8212; Removing user ".$username."</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	echo "<div class=\"pgtitle\">Successful removal of ".$username."</div><br />\n";
	
	//it's done, let's get them out of here
	echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL="
		.THurl."profiles.php?action=memberlist"
		."\">User deleted, returning to member list...";
} else {
	//die("Put list of supported functions here before release guys :[");
	echo "<title>".THname."&#8212; Profiles System</title>\n";
	echo "</head><body>\n";
	echo '<div id="main"><div class="box">';
	echo "<div class=\"pgtitle\">Profiles System</div><br />\n";
	echo "Profile system options:<br /><br />";
	if($_SESSION['username'])  //basically, if logged in
	{
		echo '<a href="'.THurl.'profiles.php?action=logout">Logout</a><br />';
		echo '<a href="'.THurl.'profiles.php?action=viewprofile&user='.$_SESSION['username'].'">Your profile</a><br />';
	} else {  //not logged in
		echo '<a href="'.THurl.'profiles.php?action=login">Login</a><br />';
		//if we're not taking new regs, let's not display the option - should be changed on menu bar also probably but that's for later
		if(THprofile_regpolicy != 0) { echo '<a href="'.THurl.'profiles.php?action=register">Register</a><br />'; }
	}//if logged in
	echo '<hr />';  //clear space for the next set
	//is member list available?
	if((THprofile_viewuserpolicy == 0) && ($_SESSION['admin'] || $_SESSION['moderator'] || $_SESSION['mod_array'])) //Mods only
	{
		echo '<a href="'.THurl.'profiles.php?action=memberlist">Member List</a>';
	}
	elseif((THprofile_viewuserpolicy == 1)  && ($_SESSION['username'])) //Only logged in users
	{
		echo '<a href="'.THurl.'profiles.php?action=memberlist">Member List</a>';
	}
	elseif(THprofile_viewuserpolicy == 2)  //Anyone
	{
		echo '<a href="'.THurl.'profiles.php?action=memberlist">Member List</a>';
	}//member list block
	echo '<br />';  //clear space for the next set
}
echo '        </div>
    </div>';
	include("menu.php");
?>
</body>
</html>