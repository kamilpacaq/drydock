
        <a name="{$thread.globalid}">
    <div class="box">
    <div class="medtitle">
{if $thread.pin}
		<img src="{$THurl}static/sticky.png" alt="HOLY CRAP STICKY">
{/if}
{if $thread.lawk}
		<img src="{$THurl}static/lock.png" alt="LOCKED">
{/if}
{if $thread.permasage}
		<img src="{$THurl}static/permasage.png" alt="THIS THREAD SUCKS">
{/if}

<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}">
{if $binfo.forced_anon == "0"}
{$thread.title|escape|default:"No Subject"}
{else}
No Subject
{/if}
</a> ({$thread.rcount+1})



    </div>
    <div><span class="medtitle">{$thread.globalid}</span> 


{		if $binfo.forced_anon == "0"} {* begin forced_anon *}

 Name: 
{			if $thread.link}<a href="{$thread.link}">{/if}
{			if $thread.name == "CAPCODE"}
		<span class="postername">{$thread.trip|capcode}</span>
{			/if}{* name = capcode? *}
{			if $thread.name != "CAPCODE"}
{				if !$thread.trip}
{					if !$thread.name}
		<span class="postername">{$THdefaultname}</span>
{					else}
		<span class="postername">{$thread.name|escape}</span>
{					/if} {* name used? *}
{				else}
		<span class="postername">{$thread.name|escape|default:""}</span><span class="postertrip">!{$thread.trip}</span>
{				/if} {* trip used? *}
{			/if} {* name not capcode? *}

{		/if} {* end forced_anon *}
:
		<span class="timedate">{$thread.time|date_format:$THdatetimestring}</span>
{			if $thread.link}</a>{/if}
    </div>
    <div class="postbody"><blockquote>
        {assign value=$thread.body|THtrunc:2000 var=bodey}



{	if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter=="0"}
{		$bodey.text|nl2br|wrapper|quotereply:"$binfo":"$post":"$thread"}
{	else}
{		$bodey.text|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
{	/if}
{if $bodey.wastruncated}<em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}#{$post.globalid}" class="ssmed">[more...]</a></em>{/if}
</blockquote>
    </div>
{	if $comingfrom=="board"}
{	if $thread.rcount>$binfo.perth}
    <div class="ssmed"><span class="name">Showing only last {$binfo.perth} {if $binfo.perth==1}reply{else}replies{/if}&rarr;</span></div><br />
{/if}
{/if}

{	if $comingfrom=="board"}
{		assign value=$thread.reps var="location"}
{else}
{		assign value=$posts var="location"}
{	/if}
{foreach from=$location item=post}
    <div class="sslarge"><span class="medtitle">{$post.globalid}</span> 
{		if $binfo.forced_anon == "0"} {* begin forced_anon *}
 Name: 
{			if $post.link}<a href="{$post.link}">{/if}
{			if $post.name == "CAPCODE"}
		<span class="postername">{$post.trip|capcode}</span>
{			/if}{* name = capcode? *}
{			if $post.name != "CAPCODE"}
{				if !$post.trip}
{					if !$post.name}
		<span class="postername">{$THdefaultname}</span>
{					else}
		<span class="postername">{$post.name|escape}</span>
{					/if} {* name used? *}
{				else}
		<span class="postername">{$post.name|escape|default:""}</span><span class="postertrip">!{$post.trip}</span>
{				/if} {* trip used? *}
{			/if} {* name not capcode? *}

{		/if} {* end forced_anon *}
:
		<span class="timedate">{$post.time|date_format:$THdatetimestring}</span>
{			if $post.link}</a>{/if}
    </div>
    <div class="postbody">
	<blockquote>
        {assign value=$post.body|THtrunc:2000 var=bodey}

{	if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter=="0"}
{		$bodey.text|nl2br|wrapper|quotereply:"$binfo":"$post":"$thread"}
{	else}
{		$bodey.text|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
{	/if}
{if $bodey.wastruncated}<em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}#{$post.globalid}" class="ssmed">[more...]</a></em>{/if}
</blockquote>
        </div>

{/foreach}{*For each reply*}

{include file="postblock.tpl" comingfrom="thread"}
{if $comingfrom=="board"}
 <a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}">View thread</a>
{$thread.rcount+1}</span> total post{if $thread.rcount==1}{else}s{/if}.{if $thread.scount>0} Last <span class="name">{$thread.scount}</span> {if $thread.scount==1}reply{else}replies{/if} shown.{/if}
{/if}
 </div>
</div>
