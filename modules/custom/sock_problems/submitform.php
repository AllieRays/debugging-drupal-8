<?php

/***** original code ****/
//
///**
// * {@inheritdoc}
// */
//public function submitForm(array &$form, FormStateInterface $form_state) {
//  drupal_set_message($this->t('Your favorite sock is @fav_sock', array('@fav_sock' => $form_state->getValue('fav_sock'))));
//
//  if ($form_state->getValue('fav_sock') == 'Ankle Biters') {
//    $form_state->setRedirect('socks.knee_highs_controller_content');
//  }
//  else {
//    if ($form_state->getValue('fav_sock') == 'Old Fashions') {
//      $form_state->setRedirect('socks.old_fashions_controller_content');
//    }
//    else {
//      $form_state->setRedirect('socks.knee_highs_controller_content');
//    }
//  }
//}
//

/**** break code *****/
// drupal_set_message($form_state['values']['fav_sock']);

// We are using an interface and the interface is the blueprint of our method.
