{* template block that contains the new field *}
<div id="com_jasontdc_secondlastname_field-div" class="crm-inline-edit-field">
  <label for="com_jasontdc_secondlastname_field">{$form.com_jasontdc_secondlastname_field.label}</label><br>
  {$form.com_jasontdc_secondlastname_field.html}
</div>
{* reposition the above block after #someOtherBlock *}
{literal}
<script type="text/javascript">
  cj('#com_jasontdc_secondlastname_field-div').insertAfter(cj('#last_name').parent());
  cj('#com_jasontdc_secondlastname_field').addClass('big');
</script>
{/literal}
