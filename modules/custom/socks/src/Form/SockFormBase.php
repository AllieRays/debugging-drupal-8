<?php

/**
 * @file
 * Contains Drupal\socks\Form\SockFormBase.
 */

namespace Drupal\socks\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SockFormBase.
 *
 * Typically, we need to build the same form for both adding a new entity,
 * and editing an existing entity. Instead of duplicating our form code,
 * we create a base class. Drupal never routes to this class directly,
 * but instead through the child classes of SockAddForm and SockEditForm.
 *
 * @package Drupal\socks\Form
 *
 * @ingroup socks
 */
class SockFormBase extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * Construct the SockFormBase.
   *
   * For simple entity forms, there's no need for a constructor. Our sock form
   * base, however, requires an entity query factory to be injected into it
   * from the container. We later use this query factory to build an entity
   * query for the exists() method.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the sock entity type.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * Factory method for SockFormBase.
   *
   * When Drupal builds this class it does not call the constructor directly.
   * Instead, it relies on this method to build the new object. Why? The class
   * constructor may take multiple arguments that are unknown to Drupal. The
   * create() method always takes one parameter -- the container. The purpose
   * of the create() method is twofold: It provides a standard way for Drupal
   * to construct the object, meanwhile it provides you a place to get needed
   * constructor parameters from the container.
   *
   * In this case, we ask the container for an entity query factory. We then
   * pass the factory to our class as a constructor parameter.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'));
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   *
   * Builds the entity add/edit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An associative array containing the sock add/edit form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    // Drupal provides the entity to us as a class variable. If this is an
    // existing entity, it will be populated with existing values as class
    // variables. If this is a new entity, it will be a new object with the
    // class of our entity. Drupal knows which class to call from the
    // annotation on our Sock class.
    $sock = $this->entity;

    // Build the form.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $sock->label(),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $sock->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ),
      '#disabled' => !$sock->isNew(),
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $sock->description,
      '#description' => $this->t("Description of the Sock."),
      '#required' => TRUE,
    );

    $form['fabric'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Fabric'),
      '#maxlength' => 255,
      '#default_value' => $sock->fabric,
      '#description' => $this->t("Fabric of the Sock."),
      '#required' => TRUE,
    );

    $form['rating'] = array(
      '#type' => 'select',
      '#title' => t('Rating'),
      '#options' => array(
        0 => t('1'),
        1 => t('2'),
        2 => t('3'),
        3 => t('4'),
        4 => t('5'),
      ),
      '#default_value' => $sock->rating,
      '#description' => t('Rate your socks!'),
    );

    // Return the form.
    return $form;
  }

  /**
   * Checks for an existing sock.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new sock entity query.
    $query = $this->entityQueryFactory->get('sock');

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   *
   * To set the submit button text, we need to override actions().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actins from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::validate().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    // Add code here to validate your config entity's form elements.
    // Nothing to do here.
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * Saves the entity. This is called after submit() has built the entity from
   * the form values. Do not override submit() as save() is the preferred
   * method for entity form controllers.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function save(array $form, FormStateInterface $form_state) {
    // EntityForm provides us with the entity we're working on.
    $sock = $this->getEntity();

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $sock->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $sock->urlInfo();

    // Create an edit link.
    $edit_link = $this->l($this->t('Edit'), $url);

    if ($status == SAVED_UPDATED) {
      // If we edited an existing entity...
      drupal_set_message($this->t('Sock %label has been updated.', array('%label' => $sock->label())));
      $this->logger('contact')->notice('Sock %label has been updated.', ['%label' => $sock->label(), 'link' => $edit_link]);
    }
    else {
      // If we created a new entity...
      drupal_set_message($this->t('Sock %label has been added.', array('%label' => $sock->label())));
      $this->logger('contact')->notice('Sock %label has been added.', ['%label' => $sock->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('entity.sock.list');
  }

}
