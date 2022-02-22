<?php

class CRM_Teaminschrijving_Team {
  public static function get($contactId) {
    $team = '';

    $dao = self::getOrganizations($contactId);
    while ($dao->fetch()) {
      $team .= '<h3>' . $dao->organization_name . '</h3>';
      $team .= self::addTeamMembers($dao->id);
    }

    if ($team) {
      $team = '<div id="apotheekteam">' . $team . '</div>';
    }

    return $team;
  }

  private static function addTeamMembers($orgId) {
    $teamMembers = '';

    $dao = self::getTeamMembers($orgId);
    while ($dao->fetch()) {
      $teamMembers .= '<input type="checkbox" id="teamlid' . $dao->id . '" value="' . $dao->id . '">';
      $teamMembers .= '<label for="teamlid'. $dao->id . '">' . $dao->person_name . '</label><br>';
    }
    return $teamMembers;
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
