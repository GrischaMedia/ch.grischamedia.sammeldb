{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{/capture}

{capture assign='contentTitle'}{$__wcf->getActivePage()->getTitle()} <span class="badge">{#$items}</span>{/capture}

{capture assign='contentHeaderNavigation'}
	{if $__wcf->session->getPermission('user.sammel.canEdit')}
		<li><a href="{link controller='SammelAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.sammel.item.add{/lang}</span></a></li>
	{/if}
{/capture}

{include file='header'}

<form method="post" action="{link controller='Sammel'}{/link}">
	<div class="formSubmit" style="text-align:left;">
		<input type="text" id="search" name="search" value="{$search}" placeholder="{lang}wcf.sammel.search.string{/lang}" class="medium">
		<input type="submit" value="{lang}wcf.sammel.search{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $search}{capture append=linkParameters}&search={@$search|rawurlencode}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="Sammel" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $items}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					{if $__wcf->session->getPermission('user.sammel.canEdit')}
						<th class="columnID columnSammelID{if $sortField == 'sammelID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='Sammel'}sortField=sammelID&sortOrder={if $sortField == 'sammelID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.sammel.item.id{/lang}</a></th>
					{/if}
					<th class="columnText columnThumb{if $sortField == 'iconPath'} active {@$sortOrder}{/if}"><a href="{link controller='Sammel'}pageNo={@$pageNo}&sortField=iconPath&sortOrder={if $sortField == 'iconPath' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}{SAMMEL_TABLE_ICON}{/lang}</a></th>
					<th class="columnText columnTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='Sammel'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}{SAMMEL_TABLE_TITLE}{/lang}</a></th>
					<th class="columnText columnDetails{if $sortField == 'details'} active {@$sortOrder}{/if}"><a href="{link controller='Sammel'}pageNo={@$pageNo}&sortField=details&sortOrder={if $sortField == 'details' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}{SAMMEL_TABLE_DETAILS}{/lang}</a></th>
					<th class="columnText columnNumber{if $sortField == 'number'} active {@$sortOrder}{/if}"><a href="{link controller='Sammel'}pageNo={@$pageNo}&sortField=number&sortOrder={if $sortField == 'number' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}{SAMMEL_TABLE_NUMBER}{/lang}</a></th>
					<th class="columnText columnOnline{if $sortField == 'online'} active {@$sortOrder}{/if}"><a href="{link controller='Sammel'}pageNo={@$pageNo}&sortField=online&sortOrder={if $sortField == 'online' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}{SAMMEL_TABLE_ONLINE}{/lang}</a></th>
					<th class="columnText columnUrl{if $sortField == 'url'} active {@$sortOrder}{/if}"><a href="{link controller='Sammel'}pageNo={@$pageNo}&sortField=url&sortOrder={if $sortField == 'url' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}{SAMMEL_TABLE_URL}{/lang}</a></th>
					
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$itemsToCategory key='categoryID' item='itemIDs'}
					<tr>
					{if $__wcf->session->getPermission('user.sammel.canEdit')}
							<th class="sammelSubtitle" colspan="8">{if $categoryID}{lang}{$categories[$categoryID]->title}{/lang}{if $categories[$categoryID]->description} - {$categories[$categoryID]->description}{/if}{else}{lang}wcf.sammel.category.none{/lang}{/if}</th>
						{else}
							<th class="sammelSubtitle" colspan="6">{if $categoryID}{lang}{$categories[$categoryID]->title}{/lang}{if $categories[$categoryID]->description} - {$categories[$categoryID]->description}{/if}{else}{lang}wcf.sammel.category.none{/lang}{/if}</th>
						{/if}
					</tr>
					
					{foreach from=$itemIDs item=item}
						<tr class="jsItemRow">
							{if $__wcf->session->getPermission('user.sammel.canEdit')}
								<td class="columnIcon">
									<span class="icon icon16 fa-{if !$item->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $item->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$item->sammelID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}"></span>
									<a href="{link controller='SammelEdit' object=$item}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
									<span class="icon icon16 fa-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$item->sammelID}" data-confirm-message="{lang}wcf.sammel.item.delete.sure{/lang}"></span>
								</td>
								<td class="columnID">{@$item->sammelID}</td>
							{/if}
							<td class="columnText columnThumb">
								{if $item->iconPath}
									<span><img src="{$item->iconPath}" alt="icon" class="sammelIcon pointer jsSammelIcon" data-object-id="{@$item->sammelID}"></span>
								{else}
									<span class="icon icon64 fa-{SAMMEL_ICON_ICON}"></span>
								{/if}
							</td>
							<td class="columnText columnTitle sammelItem">
								{if $item->hasLabels}
									<span class="sammelLabels">
									{foreach from=$item->getLabels() item=label}
										<span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span>
									{/foreach}
									</span>
									<br>
								{/if}
								{$item->title}
							</td>
							<td class="columnText columnDetails htmlContent" id="{@$item->sammelID}">
								{if $item->truncated}
									{@$item->truncated} <span class="icon icon16 fa-arrow-right jsOpenButton jsTooltip pointer" title="{lang}wcf.sammel.show{/lang}" data-object-id="{@$item->sammelID}"></span>
								{else}
									{@$item->details}
								{/if}
							</td>
							<td class="columnText columnNumber">{$item->number}</td>
							<td class="columnText columnOnline">{if $item->online}{lang}wcf.sammel.item.online.yes{/lang}{else}{lang}wcf.sammel.item.online.no{/lang}{/if}</td>
							<td class="columnText columnUrl">{@$item->url}</td>
							
						</tr>
					{/foreach}
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('user.sammel.canEdit')}
						<li><a href="{link controller='SammelAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.sammel.item.add{/lang}</span></a></li>
					{/if}
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	require(['GRISCHAMEDIA/Sammel/Open'], function(SammelOpen) {
		new SammelOpen();
	});
	require(['Language', 'GRISCHAMEDIA/Sammel/ShowIcon'], function(Language, SammelShowIcon) {
		Language.addObject({
			'wcf.sammel.preview':	'{lang}wcf.sammel.preview{/lang}'
		});
		
		new SammelShowIcon();
	});
	
</script>

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\sammel\\SammelAction', '.jsItemRow');
		new WCF.Action.Toggle('wcf\\data\\sammel\\SammelAction', $('.jsItemRow'));
	});
</script>

{include file='footer'}
