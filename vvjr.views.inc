<?php

/**
 * @file
 * Contains the hook implementations for the custom Views style plugin.
 */

/**
 * Implements hook_views_data_alter().
 */
function vvjr_views_data_alter(array &$data): void {
  // Define the style plugin schema.
  $data['views_style_plugin']['views_vvjr'] = [
    'type' => 'views_style',
    'label' => t('Views Vanilla JavaScript Reveal'),
    'mapping' => [
      'unique_id' => [
        'type' => 'string',
        'label' => t('Unique ID for the view display'),
        'description' => t('A unique numeric ID used to identify this view display.'),
      ],
      'reveal_method' => [
        'type' => 'string',
        'label' => t('Reveal Method'),
        'description' => t('The event that triggers the reveal (click or hover).'),
      ],
      'reveal_style' => [
        'type' => 'string',
        'label' => t('Reveal Style'),
        'description' => t('The style of the reveal animation (e.g., fade, zoom, top, right, bottom, left).'),
      ],
      'flip_direction' => [
        'type' => 'string',
        'label' => t('Flip Direction'),
        'description' => t('The direction of the flip (vertical or horizontal).'),
      ],
      'box_width' => [
        'type' => 'numeric',
        'label' => t('Box Width'),
        'description' => t('The minimum width for each box in the reveal grid.'),
      ],
      'box_height' => [
        'type' => 'numeric',
        'label' => t('Box Height'),
        'description' => t('The height for each reveal box.'),
      ],
      'reveal_speed' => [
        'type' => 'numeric',
        'label' => t('Reveal Speed'),
        'description' => t('The speed of the reveal animation (e.g., 0.9 for fast, 2 for slow).'),
      ],
      'front_bg_color' => [
        'type' => 'string',
        'label' => t('Displayed Side Background Color'),
        'description' => t('The background color for the displayed side of the reveal box.'),
      ],
      'back_bg_color' => [
        'type' => 'string',
        'label' => t('Revealed Side Background Color'),
        'description' => t('The background color for the revealed side of the reveal box.'),
      ],
      'animation_easing' => [
        'type' => 'string',
        'label' => t('Animation Easing'),
        'description' => t('The easing function applied to the reveal animation.'),
      ],
      'enable_css' => [
        'type' => 'boolean',
        'label' => t('Enable CSS Library'),
        'description' => t('Include the CSS library for styling the reveal elements.'),
      ],
      'grid_gap' => [
        'type' => 'numeric',
        'label' => t('Grid Gap (px)'),
        'description' => t('Set the gap between grid items in pixels.'),
      ],
      'available_breakpoints' => [
        'type' => 'string',
        'label' => t('Responsive Breakpoints'),
        'description' => t('Defines the breakpoints for responsive design.'),
      ],
    ],
  ];
}
