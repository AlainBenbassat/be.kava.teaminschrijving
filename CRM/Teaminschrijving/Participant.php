<?php

class CRM_Teaminschrijving_Participant {
  public function addOtherParticipants($participantId) {
    $otherContactIds = $this->extractOtherContactIds($participantId);
    if ($otherContactIds) {
      $mainContactId = $this->getContactIdFromParticipantId($participantId);
      $eventId = $this->getEventIdFromParticipantId($participantId);

      $mainContactIdIsAlsoInOtherContactIds = FALSE;
      foreach ($otherContactIds as $otherContactId) {
        if ($otherContactId == $mainContactId) {
          $mainContactIdIsAlsoInOtherContactIds = TRUE;
        }
        else {
          if (!$this->hasRegistration($eventId, $otherContactId)) {
            $this->registerParticipant($eventId, $participantId, $otherContactId);
          }
        }
      }

      if ($mainContactIdIsAlsoInOtherContactIds == FALSE) {
        $this->changeMainContactRole($participantId);
      }
    }

  }

  private function getContactIdFromParticipantId($participantId) {
    return CRM_Core_DAO::singleValueQuery("select contact_id from civicrm_participant where id = $participantId");
  }

  private function getEventIdFromParticipantId($participantId) {
    return CRM_Core_DAO::singleValueQuery("select event_id from civicrm_participant where id = $participantId");
  }

  private function extractOtherContactIds($participantId) {
    $otherContactIds = CRM_Core_DAO::singleValueQuery("select wie_neemt_er_deel__339 from civicrm_value_inschrijving__116 where entity_id = $participantId");

    $parsedContactIds = [];
    if ($otherContactIds) {
      $parsedContactIds = $this->parseContactIds($otherContactIds);
    }

    return $parsedContactIds;
  }

  private function changeMainContactRole($participantId) {
    CRM_Core_DAO::singleValueQuery("update civicrm_participant set role_id = 5 where id = $participantId");
  }

  private function parseContactIds($otherContactIds) {
    $matches = [];
    $foundContactIds = [];
    if (preg_match_all("/\([\d]+\)/", $otherContactIds, $matches)) {
      $idsWithParenthesis = $matches[0];
      foreach ($idsWithParenthesis as $idWithParenthesis) {
        $foundContactIds[] = str_replace(['(', ')'], '', $idWithParenthesis);
      }
    }

    return $foundContactIds;
  }

  private function registerParticipant($eventId, $mainContactParticipantId, $otherContactId) {
    $params = [
      'event_id' => $eventId,
      'status_id' => 1,
      'role_id' => 1,
      'contact_id' => $otherContactId,
      'registered_by_id' => $mainContactParticipantId,
    ];
    civicrm_api3('Participant', 'create', $params);
  }

  private function hasRegistration($eventId, $otherContactId) {
    $id = CRM_Core_DAO::singleValueQuery("select id from civicrm_participant where event_id = $eventId and contact_id = $otherContactId");
    if ($id) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
