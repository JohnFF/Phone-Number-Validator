// set each of the select functions to have its correct initial value
cj.each(cj('select'), function(){
        cj(this).val(cj(this).attr("selectedValue"));
});

// add the functionality to save the new phone type if it's changed
cj(document).on('change', '.setPhoneType', function() {
        var phone_id = cj(this).attr("phone_id");
        var new_value = cj(this).val();
        CRM.api('Phone','update',{ id:phone_id, phone_type_id:new_value }
                ,{ success:function (data){
                }
        });
        return false;
});

// make the delete phone record links work
cj(document).on('click', '.button_delete', function(){
        var phone_id = cj(this).attr('phone_id');
        CRM.api('Phone','delete',{ id:phone_id }
                ,{ success:function (data){
                        cj("." + phone_id).fadeOut();
                }
        });
        return false;
});

// make the hide phone record links work
cj(document).on('click', '.button_hide', function() {
        var phone_id = cj(this).attr('phone_id');
        cj(this).parent().parent().fadeOut();
        return false;
});

// handle the showing and the hiding of the get
cj('.regexSelector').click(function (){
    var numCheckedRegexes = cj(':checked.regexSelector').size();
    if (numCheckedRegexes > 0){
        cj('#getInvalidPhones').removeAttr('disabled');
    } else {
        cj('#getInvalidPhones').attr('disabled','disabled');
    }
});

// the main retrieval function
cj('#getInvalidPhones').click(function(){
    // Clear old table entries and add spinner.
    cj('#invalidPhonesDisplay').empty();
    cj('#invalidPhonesDisplay').append('<img src="' + resource_base + 'i/loading.gif">');
    cj('#invalidPhonesCountDisplay').empty();

    // Get params
    var selectedRegexIds = [];
    cj('input:checked.regexSelector').each(function() {
        selectedRegexIds.push(cj(this).val());
    });

    var selectedAllowCharactersIds   = [];
    cj('input:checked.allowSelector').each(function() {
        selectedAllowCharactersIds.push(cj(this).val());
    });

    var selectedPhoneTypeId = cj('#selectedPhoneType').val();
    var selectedContactTypeId = cj('#selectedContactType').val();

    CRM.PhoneNumberValidator.retrieveInvalidPhoneNumbersCount(selectedRegexIds, selectedAllowCharactersIds, selectedPhoneTypeId, selectedContactTypeId);
});

var CRM = CRM || {};

CRM.PhoneNumberValidator = CRM.PhoneNumberValidator || {};

CRM.PhoneNumberValidator.makeTableRow = function (contactId, display_name, phoneId, phoneNumber, phoneTypeId, phoneExt){

    //var viewContactLink = '<a title="View ' + display_name + '\'s contact record." href="/civicrm/contact/view?reset=1&cid=' + contactId + '">' + display_name + '</a>';
    var viewContactLink = '<a title="View ' + display_name + '\'s contact record." href="' + CRM.url('civicrm/contact/view', {"reset":1,"cid":contactId}) + '">' + display_name + '</a>';
    
    var phoneNumberString = '<span id="phone-' + phoneId + '" class="crmf-phone crm-editable">' + phoneNumber + '</span>';

    var phoneTypeString = '<select class="setPhoneType" phone_id="' + phoneId + '" selectedValue="' + phoneTypeId + '">';

    // add phone type details from the selector at the top of the page
    cj('#selectedPhoneType').children('option').each(function (){
        if (cj(this).val() != '') {
            var selectedString = "";
            if(cj(this).val() == phoneTypeId){
                selectedString = "selected";
            }
            phoneTypeString += "<option value='" + cj(this).val() + "' " + selectedString + ">" + cj(this).text() + "</option>";
        }
    });

    phoneTypeString += '</select>';

    var phoneExtString = '<span id="phone-' + phoneId + '" class="crmf-phone-ext crm-editable">' + phoneExt + '</span>';

    var editContactUrl = CRM.url('civicrm/contact/add', {"reset":1, "action":"update", "cid":contactId});

    var actionsString = '<a title="Edit ' + display_name + '\'s contact record." href="' + editContactUrl + '">edit contact</a> | ';
        actionsString += '<a title="Remove this phone number forever from the contact\'s record. Doesn\'t touch the rest of the contact\'s details!" class="button_delete" href="#" phone_id="' + phoneId + '">delete phone</a> | ';
        actionsString += '<a title="Hide this phone number from view for now." class="button_hide" href="#" phone_id="' + phoneId+ '">hide</a>';

    return "<tr id='phone-" + phoneId + "' class='crm-entity " + phoneId + "'><td>" + viewContactLink + "</td><td>" + phoneNumberString + "</td><td>" + phoneExtString + "</td><td>" + phoneTypeString + "</td><td>" + actionsString+ "</td></tr>";

}

CRM.PhoneNumberValidator.retrieveInvalidPhoneNumbersCount = function (selectedRegexIds, selectedAllowCharactersIds, selectedPhoneTypeId, selectedContactTypeId){
    
  CRM.api3('PhoneNumberValidator', 'getinvalidphonescount', {
    "sequential": 1,
    "selectedRegexIds": selectedRegexIds,
    "selectedAllowCharactersIds": selectedAllowCharactersIds,
    "selectedPhoneTypeId": selectedPhoneTypeId,
    "selectedContactTypeId": selectedContactTypeId
  }).done(function(result) {
    if (result['is_error']){
      cj('#invalidPhonesCountDisplay').empty();
      cj('#invalidPhonesCountDisplay').append("<em>Error: " + result['error_message'] + "</em>");
      cj('#invalidPhonesDisplay').empty(); // remove spinner.
      return;
    }
    var brokenPhoneNumbersCount = parseInt(result.values[0]);

    if (brokenPhoneNumbersCount == 0) {
        cj('#invalidPhonesCountDisplay').append('<div>No broken phone numbers to display.</div>');
        cj('#invalidPhonesDisplay').empty(); // remove spinner.
    } else {
        CRM.PhoneNumberValidator.retrieveInvalidPhoneNumbers(selectedRegexIds, selectedAllowCharactersIds, selectedPhoneTypeId, selectedContactTypeId);

        if (brokenPhoneNumbersCount > 50) {
            cj('#invalidPhonesCountDisplay').append('<div>Showing first 50 of ' + brokenPhoneNumbersCount + ' broken phone numbers.</div>');
        } else {
            cj('#invalidPhonesCountDisplay').append('<div>Showing ' + brokenPhoneNumbersCount + ' broken phone numbers.</div>');
        }
    }
  });
}

CRM.PhoneNumberValidator.retrieveInvalidPhoneNumbers = function (selectedRegexIds, selectedAllowCharactersIds, selectedPhoneTypeId, selectedContactTypeId){
    // Get and insert the new entries.
    CRM.api3('PhoneNumberValidator', 'Getinvalidphones', {
        'sequential': 1,
        'selectedRegexIds': selectedRegexIds,
        'selectedAllowCharactersIds': selectedAllowCharactersIds,
        'selectedPhoneTypeId': selectedPhoneTypeId,
        'selectedContactTypeId': selectedContactTypeId
    }).done(function(result) {
        cj('#invalidPhonesDisplay').empty(); // Remove spinner.

        cj('#invalidPhonesDisplay').append("<table id='invalidPhonesTable'>");
        cj('#invalidPhonesTable').append("<tr><th>contact name</th><th>phone</th><th>extension</th><th>type</th><th>actions</th></tr>");

        cj.each(result.values, function(key, value) {
            cj('#invalidPhonesTable').append(CRM.PhoneNumberValidator.makeTableRow(value['contact_id'],value['display_name'],value['phone_id'],value['phone_number'],value['phone_type_id'],value['phone_ext']));
        });
        cj('#invalidPhonesDisplay').append("</table>");
        cj('.crm-editable').crmEditable();
    });
}
