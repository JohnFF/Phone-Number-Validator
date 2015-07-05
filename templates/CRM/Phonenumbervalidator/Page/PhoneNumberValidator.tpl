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
    <div style="padding-right: 10px; padding-left: 10px; display: inline-block; vertical-align: text-top;">
    {foreach from=$validityRuleSet key=validityRuleIndex item=validityRule}
        <input type="checkbox" class="regexSelector" value="{$validityRuleSetKey}_{$validityRuleIndex}" id="regex_id_{$validityRuleSetKey}_{$validityRuleIndex}">
        <label for="regex_id_{$validityRuleSetKey}_{$validityRuleIndex}" title="Regex: {$validityRule.regex}">{$validityRule.label}</label>
        <br/>
    {/foreach}
    </div>
{/foreach}

Countries remaining: Spain, Germany, Poland, North America, Ireland, Norway, Switzerland, Denmark, Netherlands.

<br/><br/>

<h4>Options</h4>
{foreach from=$allow_options key=rule item=label}
    <!-- {$label} -->
    <span style="padding-right: 0px; padding-left: 10px;">
        <input type="checkbox" class="allowSelector" value="{$rule}" id="allow_{$rule}_checkbox">
        <label for="allow_{$rule}_checkbox">{$label}</label>
    </span>
{/foreach}

<br/><br/>

<button type="button" id="getInvalidPhones" disabled>Get invalid phone numbers.</button>

<div id='invalidPhonesCountDisplay' style='padding-top: 10px'></div>
<div id='invalidPhonesDisplay' style='padding-top: 10px'>
</div>
<br/>

<script type="text/javascript"> var resource_base = '{$config->resourceBase}' </script>
{crmScript ext=com.civifirst.phonenumbervalidator file=templates/CRM/Phonenumbervalidator/Page/PhoneNumberValidator.js}

3. Add dozens more<br/>
4. When no rules found, display error.<br/>
7. CSS<br/>
8. Install on 4.6<br/>
9. Install on 4.2<br/>
10. Pass on TODOs<br/>
11. Review each page<br/>

<br/><br/><em>Think you've found a problem, or want another country to be added to the list? Log an issue <a href="https://github.com/JohnFF/Phone-Number-Validator">here</a>.</em>
<br/><em>Follow CiviFirst on twitter!</em>