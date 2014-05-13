{foreach from=$aMessages item=aMessage}
{if $oldname == $aMessage.from_name} 
<div style="display: block; padding-top: 0%; padding-bottom: 0%;">{$aMessage.message}</div>
{else}
<div style="display:block;border-bottom: 1px solid #ccc; padding: 1% 0% 1% 0%;"></div>
<div style="display:block; padding-top: 1%; padding-bottom: 0%">
<div style="font-size:100%; display: inline;">
<a href="#">{$aMessage.from_name}</a>
</div>
<div style="float:right;font-size: 70%;">{$aMessage.timestamp}</div>
<div style="display: block; padding-top: 1%; padding-bottom: 0%">{$aMessage.message}</div>
</div>
{oldname=aMessage.from_name}
{/if}
{/foreach} 
