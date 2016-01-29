<?php

/*
 * @file
 * Helper functions for the fieldgroup module.
 */

use Drupal\Component\Utility\SafeMarkup;

/**
 * Calculates html classes for a group.
 */
function _field_group_get_html_classes(&$group) {

  if (isset($group->format_settings['formatter'])) {
    $group->collapsible = in_array($group->format_settings['formatter'], array('collapsible', 'collapsed'));
    // Open or closed horizontal or vertical tabs will be collapsible by default.
    if ($group->format_type == 'tab' || $group->format_type == 'htab') {
      $group->collapsible = TRUE;
    }
    $group->collapsed = in_array($group->format_settings['formatter'], array('collapsed', 'closed'));
  }

  $classes = new stdClass();

  // Prepare extra classes, required and optional ones.
  $optional = array(str_replace('_', '-', $group->group_name));
  $required = array();
  if ($group->format_type == 'multipage') {
    $required[] = 'field-group-' . $group->format_type;
  }
  else {
    $optional[] = 'field-group-' . $group->format_type;
  }

  if (isset($group->format_settings['formatter']) && $group->collapsible) {
    $required[] = 'collapsible';
    if ($group->collapsed) {
      $required[] = 'collapsed';
    }
  }

  // Add a required-fields class to trigger the js.
  if (!empty($group->format_settings['required_fields'])) {
    $required[] = 'required-fields';
  }

  // Add user selected classes.
  if (!empty($group->format_settings['classes'])) {
    $required[] = SafeMarkup::checkPlain($group->format_settings['classes']);
  }

  // Extra required classes for div.
  if ($group->format_type == 'div') {
    if ($group->format_settings['formatter'] != 'open') {

      $speed = isset($group->format_settings['speed']) ? $group->format_settings['speed'] : 'none';
      $required[] = 'speed-' . $speed;

      $effect = isset($group->format_settings['effect']) ? $group->format_settings['effect'] : 'none';
      $required[] = 'effect-' . $effect;
    }
  }

  // Extra required classes for accordions.
  elseif ($group->format_type == 'accordion') {
    $required[] = 'field-group-' . $group->format_type . '-wrapper';
    $effect = isset($group->format_settings['effect']) ? $group->format_settings['effect'] : 'none';
    $required[] = 'effect-' . $effect;
  }

  $classes->required = $required;
  $classes->optional = $optional;

  return $classes;
}

/**
 * Get the default formatter settings for a given formatter and a mode.
 */
function _field_group_get_default_formatter_settings($format_type) {
  $manager = \Drupal::service('plugin.manager.field_group.formatters');
  return $manager->getDefaultSettings($format_type);
}
