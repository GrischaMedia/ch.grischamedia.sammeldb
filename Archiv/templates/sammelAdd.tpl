{capture assign='pageTitle'}{lang}wcf.sammel.item.{@$action}{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.sammel.item.{@$action}{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
	
	<li><a href="{link controller='Sammel'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.sammel.list{/lang}</span></a></li>
{/capture}

{include file='header'}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

{if $categoryWarning}
	<p class="error">{lang}wcf.sammel.item.add.category.warning{/lang}</p>
{else}
	<form id="formContainer" class="jsFormGuard" method="post" action="{if $action == 'add'}{link controller='SammelAdd'}{/link}{else}{link controller='SammelEdit' id=$sammel->sammelID}{/link}{/if}">
		<div class="section">
			<h2 class="sectionTitle">{lang}wcf.sammel.item.general{/lang}</h2>
			
			<!-- title -->
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{lang}{SAMMEL_TABLE_TITLE}{/lang}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$title}" maxlength="80" class="long" />
					
					{if $errorField == 'title'}
						<small class="innerError">
							{lang}wcf.sammel.item.title.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
			
			<!-- icon -->
			<dl id="sammelIconUpload" class="sammelIconUpload{if $errorField == 'icon'} formError{/if}">
				<dt>{lang}{SAMMEL_TABLE_ICON}{/lang}</dt>
				<dd>
					{if $iconLocation}
						<img src="{$iconLocation}" alt="" id="sammelIcon">
					{/if}
					<ul class="buttonList">
						<li>
							<div id="sammelIconUploadButton"></div>
						</li>
						<li>
							<button type="button" class="button" id="deleteSammelIcon" {if !$iconLocation} style="display: none;"{/if}>{lang}wcf.sammel.icon.delete{/lang}</button>
						</li>
					</ul>
					{if $errorField == 'icon'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.sammel.icon.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.sammel.icon.description{/lang}</small>
				</dd>
			</dl>
			
			<!-- category -->
			<dl{if $errorField == 'categoryID'} class="formError"{/if}>
				<dt><label for="categoryID">{lang}wcf.sammel.item.categoryID{/lang}</label></dt>
				<dd>
					<select id="categoryID" name="categoryID">
						
						{include file='categoryOptionList'}
					</select>
					
					{if $errorField == 'categoryID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.sammel.item.categoryID.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'details'} class="formError"{/if}>
				<dt><label for="details">{lang}{SAMMEL_TABLE_DETAILS}{/lang}</label></dt>
				<dd>
					<textarea id="details" name="details" class="wysiwygTextarea" data-disable-media="1">{$details}</textarea>
					
					{if $errorField == 'details'}
						<small class="innerError">
							{lang}wcf.sammel.item.details.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		</div>
		
		{include file='messageFormTabs' wysiwygContainerID='details'}
		<div class="section"></div>
		
		<div class="section">
			<h2 class="sectionTitle">{lang}wcf.sammel.item.data{/lang}</h2>
			
			<!-- data -->
			<dl{if $errorField == 'number'} class="formError"{/if}>
				<dt><label for="number">{lang}{SAMMEL_TABLE_NUMBER}{/lang}</label></dt>
				<dd>
					<input type="text" id="number" name="number" value="{$number}" maxlength="192" class="long" />
					
					{if $errorField == 'number'}
						<small class="innerError">
							{lang}wcf.sammel.item.number.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="online">{lang}{SAMMEL_TABLE_ONLINE}{/lang}</label></dt>
				<dd>
					<label><input type="radio" name="online" value="0"{if $online == 0} checked{/if} /> {lang}wcf.sammel.item.online.no{/lang}</label>
					<label><input type="radio" name="online" value="1"{if $online == 1} checked{/if} /> {lang}wcf.sammel.item.online.yes{/lang}</label>
				</dd>
			</dl>
			
			<dl{if $errorField == 'url'} class="formError"{/if}>
				<dt><label for="url">{lang}{SAMMEL_TABLE_URL}{/lang}</label></dt>
				<dd>
					<input type="text" id="url" name="url" value="{$url}" class="long" />
					
					{if $errorField == 'url'}
						<small class="innerError">
							{lang}wcf.sammel.item.url.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		</div>
		
		{if $labelGroups|count}
			<div class="section" id="sammelLabelContainer">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.sammel.labels{/lang}</h2>
				</header>
				
				{foreach from=$labelGroups item=labelGroup}
					{if $labelGroup|count}
						<dl{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset} class="formError"{/if}>
							<dt><label>{$labelGroup->getTitle()}</label></dt>
							<dd>
								<ul class="labelList jsOnly" data-object-id="{@$labelGroup->groupID}">
									<li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}" data-force-selection="{if $labelGroup->forceSelection}true{else}false{/if}">
										<div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
										<div class="dropdownMenu">
											<ul class="scrollableDropdownMenu">
												{foreach from=$labelGroup item=label}
													<li data-label-id="{@$label->labelID}"><span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{$label->getTitle()}</span></span></li>
												{/foreach}
											</ul>
										</div>
									</li>
								</ul>
								<noscript>
									<select name="labelIDs[{@$labelGroup->groupID}]">
										{foreach from=$labelGroup item=label}
											<option value="{@$label->labelID}">{$label->getTitle()}</option>
										{/foreach}
									</select>
								</noscript>
								{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset}
									<small class="innerError">
										{if $errorType[$labelGroup->groupID] == 'missing'}
											{lang}wcf.label.error.missing{/lang}
										{else}
											{lang}wcf.label.error.invalid{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
					{/if}
				{/foreach}
			</div>
		{/if}
		
		<div class="formSubmit">
			<input id="saveButton" type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
			<input type="hidden" name="tmpHash" value="{$tmpHash}">
		</div>
	</form>
{/if}

<script data-relocate="true" src="{@$__wcf->getPath()}/js/SAMMEL.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.label.none': 							'{lang}wcf.label.none{/lang}',
			'wcf.sammel.icon.error.invalidExtension':	'{lang}wcf.sammel.icon.error.invalidExtension{/lang}',
			'wcf.sammel.icon.error.noImage':			'{lang}wcf.sammel.icon.error.noImage{/lang}',
			'wcf.sammel.icon.error.tooSmall':			'{lang}wcf.sammel.icon.error.tooSmall{/lang}',
			'wcf.sammel.icon.error.tooLarge':			'{lang}wcf.sammel.icon.error.tooLarge{/lang}',
			'wcf.sammel.icon.error.uploadFailed':		'{lang}wcf.sammel.icon.error.uploadFailed{/lang}',
			'wcf.sammel.icon.delete.confirmMessage':	'{lang}wcf.sammel.icon.delete.confirmMessage{/lang}'
		});
		{if !$labelGroups|empty}
			new SAMMEL.SammelLabelChooser({ {implode from=$labelGroupsToCategories key=__labelCategoryID item=labelGroupIDs}{@$__labelCategoryID}: [ {implode from=$labelGroupIDs item=labelGroupID}{@$labelGroupID}{/implode} ] {/implode} }, { {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#formContainer');
		{/if}
		
		new SAMMEL.IconUpload({if $action == 'edit'}{@$sammel->sammelID}{else}0{/if}, '{$tmpHash}');
	});
</script>

{include file='footer'}

{include file='wysiwyg' wysiwygSelector='details'}
