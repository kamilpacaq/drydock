{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Manager Post to /{$binfo.folder}/</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Board Settings
        </div>
<form name="postform" id="postform" action="{$THurl}thread.php" method="post" enctype="multipart/form-data">
<table>
	<tbody>
		<tr>
					<td class="postblock">Name</td>
					<td><input type="text" name="nombre" size="28" />
					</td>
			<td>
				<input type="submit" value="Submit" id="subbtn" />
			</td>
		</tr>

				<tr>
					<td class="postblock">Subject</td>
					<td colspan="2"><input type="text" name="subj" size="35" /></td>
				</tr>
			<tr>
				<td class="postblock">Comment</td>
				<td colspan="2"><textarea name="body" cols="48" rows="4" id="cont"></textarea></td>
			</tr>
				<tr><td class="postblock">Files</td><td colspan="2">
				<script type="text/javascript">
					<!--
						document.write('\
						{foreach from=$THfilecount item=lastfile}<div id="file{$lastfile}"{if $lastfile!=0} style="display:none;"{/if}>File {$lastfile+1}: <input type="file" name="file{$lastfile}" onchange="visfile({$lastfile})" /><br /></div>\
						{/foreach}');
					// /-->
				</script>
				<noscript>
					{foreach from=$THfilecount item=lastfile}
						<div id="file{$lastfile}">File {$lastfile+1}: <input type="file" name="file{$lastfile}" /><br /></div>
					{/foreach}
				</noscript>     
				</td></tr>
			<tr><td class="postblock">Then</td><td colspan="2"><select name="todo">
				<option value="thread">Go to the new thread</option>
				<option value="board" selected="selected">Return to board</option>
			</select></td></tr>
		<tr>
			<td class="postblock">
				<input type="checkbox" name="pin" checked="checked" value="on"/>Pin 
				<input type="checkbox" name="lock" checked="checked" value="on"/>Lock
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="board" value="{$binfo.id}" />
</form>
{include file=admin-foot.tpl}