{* template block that contains the second last name field *}
<table>
	<tr>
<td id="com_jasontdc_secondlastname_field-td">
  <label for="com_jasontdc_secondlastname_field">{$form.com_jasontdc_secondlastname_field.label}</label><br>
  {$form.com_jasontdc_secondlastname_field.html}
</td>
</tr>
</table>
{* reposition the above block after the last name field *}
{literal}
<script type="text/javascript">
  cj('#com_jasontdc_secondlastname_field-td').insertAfter(cj('#last_name').parent());
</script>
{/literal}