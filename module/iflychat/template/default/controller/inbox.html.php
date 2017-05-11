{foreach from=$aThreads item=aThread}
<div style="display:block;border-bottom: 1px solid #ccc; padding: 10px;">
<div style="font-size:130%; display: inline;">
<a href="{php}echo Phpfox::getLib('url')->makeUrl('iflychat.messages');{/php}id_{$aThread.uid}/">{$aThread.name}</a>
</div>
<div style="float:right;color:#AAA; font-size: 70%;">{$aThread.timestamp}</div>
<div style="display: block; padding: 10px;">{$aThread.message}</div>
</div>
{/foreach} 
