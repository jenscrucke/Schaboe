<?php
/**
 * @file
 * Contains \Drupal\rsvplist\Form\RSVPForm
 */
namespace Drupal\rsvplist\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a RSVP Email form.
 */
class RSVPForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rsvplist_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->nid->value;
    $form['email'] = array(
      '#title' => t('Email address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("Vul emailadres in."),
      '#required' => TRUE,
    );
    $form['geslacht'] = array(
      '#title' => t('Geslacht'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("Kies het geslacht."),
      '#required' => TRUE,
    );
    $form['datum'] = array(
      '#title' => t('Datum'),
      '#type' => 'date',
      '#size' => 25,
      '#description' => t("Datum dat u zou langskomen"),
      '#required' => TRUE,
    );
    $form['uur'] = array(
      '#title' => t('Uur'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("Uur dat u zou langskomen ."),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Koop'),
    );
    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');
    if ($value == !\Drupal::service('email.validator')->isValid($value)) {
      $form_state->setErrorByName('email', t('Geen geldig email formaat.', array('%mail' => $value)));
      return;
    }
    $node = \Drupal::routeMatch()->getParameter('node');
    // Check if email already is set for this node
    $select = Database::getConnection()->select('rsvplist', 'r');
    $select->fields('r', array('nid'));
    $select->condition('nid', $node->id());
    $select->condition('mail', $value);
    $results = $select->execute();
    

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    db_insert('rsvplist')
      ->fields(array(
        'mail' => $form_state->getValue('email'),
        'nid' => $form_state->getValue('nid'),
        'geslacht' => $form_state->getValue('geslacht'),
        'datum' => $form_state->getValue('datum'),
        'uur' => $form_state->getValue('uur'),
        'uid' => $user->id(),
        'created' => time(),
       ))
      ->execute();
    drupal_set_message(t('Bedankt voor de aankoop, tot dan!'));
  }
}
