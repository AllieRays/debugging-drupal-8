<?php

/**
 * @file
 * Contains \Drupal\socks\Form\FavoriteSockForm.
 */

namespace Drupal\socks\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FavoriteSockForm.
 *
 * @package Drupal\socks\Form
 */
class FavoriteSockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'favorite_sock_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['fav_sock'] = array(
      '#type' => 'radios',
      '#options' => array(
        'Ankle Biters' => $this->t('Ankle Biters'),
        'Old Fashions' => $this->t('Old Fashions'),
        'Knee Highs' => $this->t('Knee Highs')
      ),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $result = $form_state->getValue('fav_sock');

    drupal_set_message($this->t('Your favorite sock is @fav_sock', array('@fav_sock' => $result)));

    if ($result == 'Ankle Biters') {
      $form_state->setRedirect('socks.knee_highs_controller_content');
    }
    else {
      if ($result == 'Old Fashions') {
        $form_state->setRedirect('socks.old_fashions_controller_content');
      }
      else {
        $form_state->setRedirect('socks.knee_highs_controller_content');
      }
    }
  }


}
