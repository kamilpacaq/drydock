<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-tools.php
	Description:    Code for the ThornToolsDBI class, based upon the MySQL version of ThornDBI.
	ThornToolsDBI is used for things such as recentposts.php and recentpics.php.
	Its abstract interface is in dbi/ABSTRACT-tools.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornToolsDBI extends ThornDBI
{

	function ThornToolsDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		$this->ThornDBI($server, $user, $pass, $base);
	}

	function getpicscount($board)
	{
		// Filter by board.
		if( $board > 0 )
		{
			$querystring = "SELECT 
				COUNT(*)
			FROM 
				images 
			LEFT OUTER JOIN 
    			threads 
			ON 
				images.id = threads.imgidx
			LEFT OUTER JOIN 
    			replies 
			ON
				images.id = replies.imgidx
			WHERE 
				threads.board = ".intval($board)." || replies.board = ".intval($board);
		}
		else
		{
			$querystring = "SELECT COUNT(*) FROM images WHERE 1";			
		}
		
		return $this->myresult($querystring);		
	}
	
	function getpostscount($get_threads, $board, $showhidden)
	{	
		$boardquery = "";
		
		// Handle board filtering first
		if( $board > -1 )
		{
			$boardquery = "board = ".intval($board);
		}
			
		// Handle hidden filtering
		if($showhidden == false)
		{
			if( $boardquery == "" )
			{
				$boardquery = "hidden = 0";
			}
			else
			{
				$boardquery .= ",hidden = 0";
			}
		}
			
		// No filtering at all, I suppose.
		if($boardquery == "")
		{
			$boardquery = "1";
		}
		
		if($get_threads == false)
		{
			$postquery = "SELECT COUNT(*) FROM ".THreplies_table." WHERE ".$boardquery;
		}
		else
		{
			$postquery = "SELECT COUNT(*) FROM ".THthreads_table." WHERE ".$boardquery;
		}
		
		return $this->myresult($postquery);		
	}
	
	function getpics($offset, $board)
	{
		// Filter by board.
		if( $board > 0 )
		{
			$querystring = "SELECT 
				images.*,
				threads.board AS thread_board,
				threads.id AS thread_id,
				threads.globalid AS thread_globalid,
				replies.board AS reply_board,
				replies.id AS reply_id,
				replies.globalid AS reply_globalid
			FROM 
				images 
			LEFT OUTER JOIN 
    			threads 
			ON 
				images.id = threads.imgidx
			LEFT OUTER JOIN 
    			replies 
			ON
				images.id = replies.imgidx
			WHERE 
				threads.board = ".intval($board)." || replies.board = ".intval($board)."
			ORDER BY 
				id ASC LIMIT ".intval($offset).", 40;";
		}
		else
		{
			$querystring = "SELECT 
				images.*,
				threads.board AS thread_board,
				threads.id AS thread_id,
				threads.globalid AS thread_globalid,
				replies.board AS reply_board,
				replies.id AS reply_id,
				replies.globalid AS reply_globalid
			FROM 
				images 
			LEFT OUTER JOIN 
    			threads 
			ON 
				images.id = threads.imgidx
			LEFT OUTER JOIN 
    			replies 
			ON
				images.id = replies.imgidx
			WHERE 
				1
			ORDER BY 
				id ASC LIMIT ".intval($offset).", 40;";			
		}
		
		return $this->mymultiarray($querystring);
	}
	
	function getposts($offset, $get_threads, $board, $showhidden)
	{	
		$boardquery = "";
		
		// Handle board filtering first
		if( $board > -1 )
		{
			$boardquery = "board = ".intval($board);
		}
			
		// Handle hidden filtering
		if($showhidden == false)
		{
			if( $boardquery == "" )
			{
				$boardquery = "hidden = 0";
			}
			else
			{
				$boardquery .= ",hidden = 0";
			}
		}
			
		// No filtering at all, I suppose.
		if($boardquery == "")
		{
			$boardquery = "1";
		}
		
		if($get_threads == false)
		{
			$postquery = "SELECT ".THreplies_table.".*, ".THthreads_table.".globalid AS thread_globalid FROM ".THreplies_table.
			" LEFT OUTER JOIN ".THthreads_table." ON ".THreplies_table.".thread = ".THthreads_table.".id
			WHERE ".$boardquery." order by id asc LIMIT ".$offset.", 20";
		}
		else
		{
			$postquery = "SELECT * FROM ".THthreads_table." WHERE ".$boardquery." order by id asc LIMIT ".$offset.", 20";
		}
		
		return $this->mymultiarray($postquery);
	}
	
	function getnewsthreads()
	{
		return $this->mymultiarray("SELECT globalid,board,title,name,trip,body,time FROM " . THthreads_table . 
					" where board=" . THnewsboard . " ORDER BY time DESC LIMIT 0,15");
	}
	
	 function checkreportpost($post, $board)
	 {
	 	// Calculate some time/IP stuff
	 	$time_interval = time() + (THtimeoffset * 60) - 60;
	 	$longip = ip2long($_SERVER['REMOTE_ADDR']);
	 	
	 	// Save ourselves the trouble of doing this multiple times
	 	$post = intval($post);
	 	$board = intval($board);	 	
	 	
	 	// One report a minute.
	 	if( $this->myresult("SELECT COUNT(*) FROM ".THreports_table.
				" WHERE time>".$time_interval." AND ip=".$longip) > 0)
		{
			return 1;
		}
		
		// Has it already been reported by this user?
		if($this->myresult("SELECT COUNT(*) FROM ".THreports_table.
				" WHERE post=".$post." AND board=".$board." AND ip=".$longip) )
		{
			return 2;
		}
	 	
		// Has it already been handled?
		if($this->myresult("SELECT COUNT(*) FROM ".THreports_table.
				" WHERE post=".$post." AND board=".$board." AND status>0") )
		{
			return 4;
		}	 	
	 	
	 	// This abstracts looking through threads/replies for us
	 	if( $this->findpost($post, $board) == 0)
	 	{
	 		return 3; // not found
	 	}
	 	
	 	// We made it this far, so I guess we're ok
	 	return 0;
	 }
	 
	 function reportpost($post, $board, $category)
	 {
	 	$longip = ip2long($_SERVER['REMOTE_ADDR']);
	 	
	 	// calculate the current time
	 	$now = time() + (THtimeoffset * 60);
	 	
	 	// report it!
	 	if( $this->checkreportpost($post, $board) == 0)
	 	{
	 		$this->myquery("INSERT INTO ".THreports_table." set ip=".$longip.", time=".$now.", postid=".intval($post).
				", board=".intval($board).", category=".intval($category).", status=0");
	 	}
	 }

} //class ThornToolsDBI
?>