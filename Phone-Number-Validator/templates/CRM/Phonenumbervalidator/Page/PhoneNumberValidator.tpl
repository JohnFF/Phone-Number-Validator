{include file="CRM/common/crmeditable.tpl"}

Check <select id="selectedPhoneType" selectedValue="{$selected_show_phone_type}">
        <option value="">all phone types</option>
        {crmAPI var="OptionValues" entity="OptionValue" action="get" sequential="1" option_group_name="phone_type" option_sort="weight"}
        {foreach from=$OptionValues.values item=OptionValue}
                <option value="{$OptionValue.value}">{$OptionValue.label}</option>
        {/foreach}
</select>
from 
<select id="selectedContactType" selectedValue="{$selected_show_contact_type}">
        <option value="">all contact types</option>
        {crmAPI var="ContactTypes" entity="ContactType" action="get" sequential="1" is_active="1"}
        {foreach from=$ContactTypes.values item=ContactType}
                <option value="{$ContactType.id}">{$ContactType.label}s</option>
        {/foreach}
</select>

<br/><br/>

<h4>Use rules</h4>
{foreach from=$regex_rules key=validityRuleSetKey item=validityRuleSet}
    <div class="regex_rule">
    {foreach from=$validityRuleSet key=validityRuleIndex item=validityRule}
        <input type="checkbox" class="regexSelector" value="{$validityRuleSetKey}_{$validityRuleIndex}" id="regex_id_{$validityRuleSetKey}_{$validityRuleIndex}">
        <label for="regex_id_{$validityRuleSetKey}_{$validityRuleIndex}" title="Regex: {$validityRule.regex}">{$validityRule.label}</label>
        <br/>
    {/foreach}
    </div>
{/foreach}

<br/><br/>

<h4>Options</h4>
{foreach from=$allow_options key=rule item=label}
    <!-- {$label} -->
    <span class="allow_options">
        <input type="checkbox" class="allowSelector" value="{$rule}" id="allow_{$rule}_checkbox">
        <label for="allow_{$rule}_checkbox">{$label}</label>
    </span>
{/foreach}

<br/><br/>

<button type="button" id="getInvalidPhones" disabled title="Use the rules that you've selected to find invalid numbers. If this is greyed out then select some rules.">Find invalid phone numbers.</button>

<div id='invalidPhonesCountDisplay'></div>
<div id='invalidPhonesDisplay'>
</div>
<br/>

<script type="text/javascript"> var resource_base = '{$config->resourceBase}' </script>
{crmScript ext=com.civifirst.phonenumbervalidator file=templates/CRM/Phonenumbervalidator/Page/PhoneNumberValidator.js}
{crmStyle ext=com.civifirst.phonenumbervalidator file=templates/CRM/Phonenumbervalidator/Page/PhoneNumberValidator.css}

<br/><br/>
<div id='phonenumbervalidator_footer'>
    <em>Think you've found a problem, or want another country to be added to the list? Log an issue <a href="https://github.com/JohnFF/Phone-Number-Validator">here</a>.</em>
    <br/>
    <em>Follow CiviFirst on <a href src='https://twitter.com/civifirst'>twitter</a>!</em>
</div>