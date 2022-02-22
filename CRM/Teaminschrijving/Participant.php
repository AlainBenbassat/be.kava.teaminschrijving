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

  public function appendEmailOtherParticipants($commaSeparatedemailAddresses, $otherContactIds, $excludeId) {
    $parsedContactIds = $this->parseContactIds($otherContactIds);
    foreach ($parsedContactIds as $parsedContactId) {
      if ($parsedContactId != $excludeId) {
        $email = $this->getEmailAddress($parsedContactId);
        $commaSeparatedemailAddresses = $this->appendEmailAddress($commaSeparatedemailAddresses, $email);
      }
    }

    return $commaSeparatedemailAddresses;
  }

  private function appendEmailAddress($commaSeparatedemailAddresses, $newEmailAddress) {
    if (empty($newEmailAddress)) {
      return $commaSeparatedemailAddresses;
    }

    if (empty($commaSeparatedemailAddresses)) {
      return $newEmailAddress;
    }

    return $commaSeparatedemailAddresses . ',' . $newEmailAddress;
  }

  private function getEmailAddress($contactId) {
    return CRM_Core_DAO::singleValueQuery("select email from civicrm_email where contact_id = $contactId and is_primary = 1");
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
    $result = civicrm_api3('Participant', 'create', $params);

    $this->duplicateInvoicingDetails($mainContactParticipantId, $result['id']);
  }

  private function duplicateInvoicingDetails($fromParticipantId, $toParticipantId) {
    $fromInvoicingDetails = $this->getInvoicingDetails($fromParticipantId);
    if ($fromInvoicingDetails) {
      $toInvoicingDetails = $this->getInvoicingDetails($toParticipantId);

      if ($toInvoicingDetails) {
        $this->updateInvoicingDetails($fromInvoicingDetails, $toInvoicingDetails);
      }
      else {
        $this->insertInvoicingDetails($fromInvoicingDetails, $toParticipantId);
      }
    }
  }

  private function getInvoicingDetails($participantId) {
    $sql = "select * from civicrm_value_facturatie_deelname where entity_id = $participantId";
    $dao = CRM_Core_DAO::executeQuery($sql);
    if ($dao->fetch()) {
      return $dao;
    }
    else {
      return FALSE;
    }
  }

  private function insertInvoicingDetails($fromInvoicingDetails, $toParticipantId) {
    $id = $fromInvoicingDetails->id;
    $sql = "
      insert into
        civicrm_value_facturatie_deelname
      (
         entity_id, wie_is_de_betaler, betaler_voor_boekhouding, facturatiegegevens
      )
      select
        $toParticipantId, wie_is_de_betaler, betaler_voor_boekhouding, facturatiegegevens
      from
        civicrm_value_facturatie_deelname
      where
        id = $id
    ";
    CRM_Core_DAO::executeQuery($sql);
  }

  private function updateInvoicingDetails($fromInvoicingDetails, $toInvoicingDetails) {
    CRM_Core_DAO::executeQuery("delete from civicrm_value_facturatie_deelname where id = " . $toInvoicingDetails->id);
    $this->insertInvoicingDetails($fromInvoicingDetails, $toInvoicingDetails->entity_id);
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
