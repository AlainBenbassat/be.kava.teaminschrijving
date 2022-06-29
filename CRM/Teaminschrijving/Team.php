<?php

class CRM_Teaminschrijving_Team {
  public static function get($contactId, $eventId, $teamMemberId) {
    $team = '';

    $dao = self::getOrganizations($contactId);
    while ($dao->fetch()) {
      $team .= '<optgroup label="Apotheek: ' . $dao->organization_name . '">';
      $team .= self::addTeamMembers($dao->id, $eventId, $teamMemberId);
      $team .= '</optgroup>';
    }

    if ($team) {
      $script = 'this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);';
      $team = '<div id="apotheekteam"><select onchange="' . $script . '">' . $team . '</select></div>';
    }

    return $team;
  }

  private static function addTeamMembers($orgId, $eventId, $teamMemberId) {
    $teamMembers = '';

    $contactDao = self::getTeamMembers($orgId);
    while ($contactDao->fetch()) {
      $participantDao = self::getEventRegistration($contactDao->id, $eventId);

      if ($participantDao) {
        $teamMembers .= self::addRegisteredTeamMember($contactDao, $participantDao);
      }
      else {
        $teamMembers .= self::addUnregisteredTeamMember($contactDao, $eventId, $teamMemberId);
      }
    }

    return $teamMembers;
  }

  private static function getEventRegistration($contactId, $eventId) {
    $sql = "select * from civicrm_participant where contact_id = $contactId and event_id = $eventId";
    $dao = CRM_Core_DAO::executeQuery($sql);
    if ($dao->fetch()) {
      return $dao;
    }
    else {
      return FALSE;
    }
  }

  private static function addRegisteredTeamMember($contactDao, $participantDao) {
    return '<option disabled>' . $contactDao->person_name . ' (reeds ingeschreven op ' . $participantDao->register_date . ')</option>';
  }

  private static function addUnregisteredTeamMember($contactDao, $eventId, $teamMemberId) {
    $selected = ($contactDao->id == $teamMemberId) ? 'selected' : '';

    $url = CRM_Utils_System::url('civicrm/event/register', 'reset=1&cid=0&id=' . $eventId . '&team_member_id=' . $contactDao->id);
    return '<option value="' . $url .'" ' . $selected . '>' . $contactDao->person_name . '</option>';
  }

  private static function getTeamMembers($orgId) {
    // inconsistent use of name_a_b vs. name_b_a
    // we can't retrieve all at once!
    $relListBA = '35,41'; // titularis, co-titularis
    $relListAB = '37,38,53'; // adjunct, plaatsvervangend apotheker, farmaceutisch technisch assistent

    $sql = self::getTeamMembersSQL('BA', $relListBA) . ' UNION ALL ' . self::getTeamMembersSQL('AB', $relListAB);
    $sqlParams = [
      1 => [$orgId, 'Integer'],
    ];

    return CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  private static function getTeamMembersSQL($relDirection, $relTypeIds) {
    if ($relDirection == 'AB') {
      $relPerson = 'a';
      $relOrg = 'b';
    }
    else {
      $relPerson = 'b';
      $relOrg = 'a';
    }

    $sql = "
      select
        c.id,
        concat(c.first_name, ' ', c.last_name) person_name
      from
        civicrm_contact c
      inner join
        civicrm_relationship r on r.contact_id_$relPerson = c.id
      where
        c.is_deleted = 0
      and
        r.is_active = 1
      and
        r.contact_id_$relOrg = %1
      and
        r.relationship_type_id in ($relTypeIds)
    ";

    return $sql;
  }

  private static function getOrganizations($contactId) {
    // relationships:
    //  35 - is titularis van
    // 119 - is teambeheerder van (nieuw)
    $relList = '39,41,119';

    $sql = "
      select
        c.id,
        c.organization_name
      from
        civicrm_contact c
      inner join
        civicrm_relationship r on r.contact_id_a = c.id
      where
        c.is_deleted = 0
      and
        r.is_active = 1
      and
        r.contact_id_b = %1
      and
        r.relationship_type_id in ($relList)
    ";
    $sqlParams = [
      1 => [$contactId, 'Integer'],
    ];

    return CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }
}
