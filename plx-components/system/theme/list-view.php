<table class="listView">
<tpl name="header">
	<tr class="header">
<tpl name="hcol">
		<th><?=$value?></th>
</tpl>
	</tr>
</tpl>
<tpl name="item">
	<tr class="item">
<tpl name="icol">
		<td><?=$value?></td>
</tpl>
	</tr>
</tpl>
<tpl name="pagination">
	<tr class="pagination">
		<td colspan="<?=$colspan?>" align="center"><?=$pagination?></td>
	</tr>
</tpl>
</table>