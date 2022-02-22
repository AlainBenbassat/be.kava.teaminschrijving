<?php

require_once 'teaminschrijving.civix.php';
// phpcs:disable
use CRM_Teaminschrijving_ExtensionUtil as E;
// phpcs:enable

function teaminschrijving_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  if ($context == 'form' && $tplName == 'CRM/Event/Form/Registration/Register.tpl' && strpos($content,'Wie neemt er deel?')) {
    Civi::resources()->addScriptFile('be.kava.teaminschrijving', 'js/teaminschrijving.js', 200, 'html-header');

    $currentUser = CRM_Core_Session::getLoggedInContactID();
    $team = CRM_Teaminschrijving_Team::get($currentUser);
    if ($team) {
      $content = preg_replace('/APOTHEEKTEAM_PLACEHOLDER/', $team, $content, 1);
      $content = preg_replace('/APOTHEEKTEAM_PLACEHOLDER/', '', $content, 1);
    }
  }
}

function teaminschrijving_civicrm_custom( $op, $groupID, $entityID, &$params ) {
  if ($op == 'create' && $groupID == 116) {
    $participant = new CRM_Teaminschrijving_Participant();
    $participant->addOtherParticipants($entityID);
  }
}

function teaminschrijving_civicrm_alterMailParams(&$params, $context) {
  if ($context == 'singleEmail' && !empty($params['custom_339'])) {
    $participant = new CRM_Teaminschrijving_Participant();
    $params['cc'] = $participant->appendEmailOtherParticipants($params['cc'], $params['custom_339'], $params['contact_id']);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function teaminschrijving_civicrm_config(&$config) {
  _teaminschrijving_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function teaminschrijving_civicrm_xmlMenu(&$files) {
  _teaminschrijving_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function teaminschrijving_civicrm_install() {
  _teaminschrijving_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function teaminschrijving_civicrm_postInstall() {
  _teaminschrijving_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function teaminschrijving_civicrm_uninstall() {
  _teaminschrijving_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function teaminschrijving_civicrm_enable() {
  _teaminschrijving_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function teaminschrijving_civicrm_disable() {
  _teaminschrijving_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function teaminschrijving_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _teaminschrijving_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function teaminschrijving_civicrm_managed(&$entities) {
  _teaminschrijving_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function teaminschrijving_civicrm_caseTypes(&$caseTypes) {
  _teaminschrijving_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function teaminschrijving_civicrm_angularModules(&$angularModules) {
  _teaminschrijving_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function teaminschrijving_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _teaminschrijving_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function teaminschrijving_civicrm_entityTypes(&$entityTypes) {
  _teaminschrijving_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function teaminschrijving_civicrm_themes(&$themes) {
  _teaminschrijving_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function teaminschrijving_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function teaminschrijving_civicrm_navigationMenu(&$menu) {
//  _teaminschrijving_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _teaminschrijving_civix_navigationMenu($menu);
//}
