{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Reports</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Reports
{if $board_folder != null}
&#8212; showing only reports for {$board_folder}
{/if}
        </div>
	<br />

{* Show filtering links *}
Filter by board: 
<select name="board">
<option value="" onclick="window.location='window.location='reports.php'">All boards</option>
{foreach from=$boards item=board}
	<option value="{$board.folder}" 
		onclick="window.location='reports.php?board={$board.folder}'">
		/{$board.folder}/
	</option>
{/foreach}		
</select> {* Close off the select tag.  Heh.*}

<hr>
	
{* Show posts *}
    <div align="center">
    {if $reports!=null}
		{foreach from=$reports item=report}
			<table>
			{assign var=boardz value=`{$boards[$report.board].folder}`} {* for brevity's sake *}
						
			{* Link to thread *}
			{if $report.post.thread != 0} {* This is a reply *}	
				{if $boardz != false }				
					Post {$report.postid} in thread {$report.post.thread_globalid} on /{$boardz}/
					
					{if $THuserewrite == true}
						[<a href="{$THurl}{$boardz}/thread/{$report.post.thread_globalid}#{$report.post.globalid}">thread</a>]
					{else} 
						[<a href="{$THurl}drydock.php?b='{$boardz}&i={$report.post.thread_globalid}#{$report.post.globalid}">thread</a>]
					{/if}
				{else} {* No board found, weird *}		
					Post {$report.post.globalid} in [<a href="{$THurl}drydock.php?t=$report.post.thread">thread</a>]				
				{/if}			
			{else} 
				{* thread *}
				{if $report.post.id != 0 ) 
					Post {$report.post.globalid} in /{$boardz}/ 
					{if $THuserewrite == true}
						[<a href="{$THurl}{$boardz}/thread/{$report.post.globalid}">thread</a>]
					{else} 
						[<a href="{$THurl}drydock.php?b='{$boardz}&i={$report.post.globalid}">thread</a>]
					{/if}
					
				{else}
					[thread]
				{/if}			
			{/if}
			
			{* Show edit link *}
			<a 
			{if $THuserewrite == true}
				href="{$THurl}board={$boardz}/edit/{$report.post.globalid}"
			{else} 
				href="{$THurl}editpost.php?board={$boardz}&post={$report.post.globalid}"
			{/if}
			>edit</a>]
			
			{* Show quick-moderation panel *}
			[<a onclick="javascript:ToggItem(document.getElementById('quickmod{$report.post.id}'))">Quickmod</a>]
			<span id="quickmod{$report.post.id}" style="hidden" class="modblock">
				<form target="_blank" action="misc.php" method="POST">
					<input type="hidden" name="board" value="{$boardz}" />
					<input type="hidden" name="post" value="{$report.post.globalid}" />
					<input type="checkbox" name="doban" value="1"> Ban poster<br>
					Reason: <input type="text" name="banreason"> <br>
					Duration: <input type="text" name="duration" value="0"><br>
					<input type="checkbox" name="del" value="1"> Delete this post (requires admin)<br>
					<input type="submit" name="quickmod" value="quickmod">
				</form>
			</span>
			
			{* Mark if a post has already been moderated - shouldn't happen but let's cover the bases *}
			{if $report.post.unvisibletime != 0}
			&nbsp;<i><b>Previously moderated</b></i>
			{/if}
			
			<br>
			
			{* Show reporting info *}
			<table>
			<tr>
				<td>
					Classification of report:
					{if $report.category == 1}
					Illegal content
					{elsif $report.category == 2}
					Rule violation
					{else}
					Low-quality posting
					{/if}
				</td>
				<td>
					Number of reporters: {$report.reporter_count}
				</td>
				<td>
					First reported: {$report.earliest_report|date_format:$THdatetimestring}
				</td>
			</tr>
			</table>
			
			{* Show stuff like name, link field, etc *}
			
			{if $report.post.link}<a href="{$report.post.link}">{/if}

			{if !$report.post.trip}
				{if !$report.post.name}
						<span class="postername">Anonymous</span>
				{else}
						<span class="postername">{$report.post.name|escape:'html':'UTF-8'}</span>
				{/if}
			{else}
				<span class="postername">{$report.post.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$report.post.trip}</span>
			{/if}
			<span class="timedate">{$report.post.time|date_format:$THdatetimestring}</label></span>
			
			{if $report.post.link}</a>{/if}
			
			{if $report.post.visible == 0}
				<img src="{$THurl}static/invisible.png" alt="INVISIBLE" border="0">
			{/if}
						
			{if $report.post.title != ''}<br><span class="filetitle">{$report.post.title}</span>{/if}
			
			{* Show images *}
			{if $report.post.images}
				<table><tbody>
				{foreach from=$report.post.images item=image}
					<tr><td>
					<div class="filesize">
						File: <a href="images/{$report.post.imgidx}/{$image.name}" target="_blank">{$image.name}</a><br />
						{* Display file size, dimensions, and possible an a (for animated) *}
						(<em>{$image.fsize} K, {$image.width}x{$image.height} {if $image.anim}a{/if}</em>)
					</div>
					File: <a class="info" href="images/{$report.post.imgidx}/{$image.name}" target="_blank">
						{if $image.hash != "deleted"}
							<img src="images/{$report.post.imgidx}/{$image.tname}" width="{$image.twidth}" 
								height="{$image.theight}" alt="{$image.name}" class="thumb" />
						{else}
							<img src="{$THurl}static/file_deleted.png" alt="File deleted" width="100" height="16" class="thumb" />
						{/else}
						</a>
					<br />
					</td></tr>
				{/foreach}
				</tbody></table>
			{/if}
			</td></tr>
			<tr><td> {* Split rest of post from post body *}
				<blockquote>
					{$report.post.body|nl2br}
				</blockquote>
			</td></tr>
			
			{* Show report handling links *}
			<tr>
				<td>
					<a href="misc.php?action=handlereport&post={$report.postid}&board={$boardz}&status=1" target="_blank">
					Mark as valid
					</a>
				</td>
				<td>
					<a href="misc.php?action=handlereport&post={$report.postid}&board={$boardz}&status=2" target="_blank">
					Mark as invalid
					</a>
				</td>
				<td>
					<a href="misc.php?action=handlereport&post={$report.postid}&board={$boardz}&status=3" target="_blank">
					Mark as reviewed (neither outright valid/invalid)
					</a>
				</td>			
			</tr>
			
			</table><hr>
		{/foreach}
	{else}
	No reports match these filters.<br />
	{/if}
	</div>

</div>
{include file=admin-foot.tpl}
