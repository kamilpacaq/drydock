<?php
	session_start();
	//Configure script still here?  Crap, this isn't good, let's deny access, just in case someone didn't read the directions
	if (file_exists("configure.php"))
	{
		if(file_exists("config.php"))
		{
			die("This script cannot be run with the configuration utility still sitting here!  Please delete the configuration script!");
		} 
		else 
		{
			header("Location: configure.php");
		}
	}
	require_once("common.php");
	$db=new ThornDBI();
	//Drop them out right now if they are banned! - tyam
	if ($db->checkban())
	{
		THdie("PObanned");
	} 
	else 
	{ //whole file
		if (isset($_GET['b'])==true) //Check a board by its name
		{
			//Does the board even exist?
			if($db->myresult("select count(*) from ".THboards_table." where folder='".mysql_real_escape_string($_GET['b'])."'")=="0")
			{
				THdie("Board not found.");
			}
			$boardid = $db->myresult("select id from ".THboards_table." where folder='".mysql_real_escape_string($_GET['b'])."'");   //Welp, let's get the board's ID number.

			$template = $db->myresult("select boardlayout from ".THboards_table." where id=".$boardid); //what is our template
			if ($boardid==getboardname(THmodboard)) //check for mod access
			{
				if(!$_SESSION['moderator'] && !$_SESSION['admin'])
				{
					THdie("You are not authorized to access this board.");
				}
			}
			if ($db->myresult("select requireregistration from ".THboards_table." where id=".$boardid)=="1") //let's check for registration required boards here - tyam
			{
				if(!$_SESSION['username'])
				{
					THdie("You must register to view this board.");
				}
			}
			
			if (isset($_GET['i'])==true) //Looking for a bento box.
			{
				$threadtpl = "thread.tpl";  //oh boy let's split it up more
				$cid="t".$boardid."-".(int)$_GET['i']."-".$template;
				$sm=sminit($threadtpl,$cid,$template);
				$sm->assign('boardmode',$boardmode);
				$sm->assign('template', $template);
				//here we go with a bunch of retarded variables that later we can turn into an array
				$sm->assign('username',$_SESSION['username']);
				$sm->assign('mod_global',$_SESSION['moderator']);
				$sm->assign('mod_admin',$_SESSION['admin']);
				$modvar = canmodboard($boardid, $_SESSION['mod_array']);
				$sm->assign('mod_thisboard', $modvar);
				$sm->assign('comingfrom',"thread");
				//OOPS!  This will let us pull the thread we WANT not the thread we ASKED FOR.  -tyam
				$db=new ThornGlobalThreadDBI(intval($_GET['i']), $boardid);
				if($db->myresult("select count(*) from ".THthreads_table." where globalid=".intval($_GET['i'])." and board=".$boardid)=="0")
				{
					THdie("Sorry, this thread does not exist.");
				}
				$sm->register_object("it",$db,array("getreplies","getindex","binfo","head","blotterentries"));
				$sm->display($threadtpl,$cid);
				die();
			}
			elseif (isset($_GET['g'])==true)
			{
				//This page of the board...
				$page=abs((int)$_GET['g']);
			} 
			else 
			{
				$page=0;
			}
			$tpl="board.tpl";
			$cid="b".$boardid."-".$page."-".$template;
			$sm=sminit($tpl,$cid,$template);

			//var_dump($obj);
			$db=new ThornBoardDBI($boardid,$page,$on);
			$sm->register_object("it",$db,array("getallthreads","getsthreads","getindex","binfo","page","allthreadscalmerge","blotterentries"));
			$sm->assign('template', $template);
			//here we go with a bunch of retarded variables that later we can turn into an array  (looks like i kopiped this)
                        $sm->assign('username',$_SESSION['username']);
                        $modvar = canmodboard($boardid, $_SESSION['mod_array']);
                        $sm->assign('mod_thisboard', $modvar);
                        $sm->assign('mod_global',$_SESSION['moderator']);
                        $sm->assign('mod_admin',$_SESSION['admin']);
			$sm->assign('comingfrom',"board");
			if (isset($ogd)==true)
			{
				$sm->assign("on",$ogd);
			}
			$sm->display($tpl,$cid);
			die();    
		} //get=b
		else 
		{ 
			include("news.php");
		}//no argument given after index
	}//ban check ends here

?>