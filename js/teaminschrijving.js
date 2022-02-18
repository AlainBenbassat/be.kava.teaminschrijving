CRM.$(function($) {
  function formatSelection(contactId) {
    var contactName = $("#apotheekteam label[for='teamlid" + contactId + "']").html();
    return "|" + contactName + " (" + contactId + ")|";
  }

  function addContactId(contactId) {
    var fieldContact = $("#custom_339").val();
    var newVal = "";

    if (fieldContact === "") {
      newVal = formatSelection(contactId);
    }
    else {
      newVal = fieldContact + formatSelection(contactId);
    }

    $("#custom_339").val(newVal);
  }

  function removeContactId(contactId) {
    var fieldContact = $("#custom_339").val();
    var newVal = fieldContact.replace(formatSelection(contactId), "");
    $("#custom_339").val(newVal);
  }

  function addEventHandlersToTeamMembers() {
    $("#apotheekteam input").change(function() {
      if (this.checked) {
        addContactId(this.value);
      }
      else {
        removeContactId(this.value);
      }
    });
  }

  function hasNoTeam() {
    var fieldContact = $("#custom_339").val();
    if (fieldContact === "APOTHEEKTEAM_PLACEHOLDER") {
      return true;
    }
    else {
      return false;
    }
  }

  function hideTeamMembersField() {
    $("#editrow-custom_339").hide();
  }

  if (hasNoTeam()) {
    hideTeamMembersField();
  }
  else {
    addEventHandlersToTeamMembers();
  }

});
