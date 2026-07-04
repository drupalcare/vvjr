<?php

declare(strict_types=1);

namespace Drupal\vvjr\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\vvj_core\Constants\AnimationDirection;
use Drupal\vvj_core\Plugin\views\style\VvjStylePluginBase;
use Drupal\vvjr\VvjrConstants;

/**
 * Renders Views rows as a vanilla-JavaScript reveal-card grid.
 *
 * @ingroup views_style_plugins
 */
#[ViewsStyle(
  id: 'views_vvjr',
  title: new TranslatableMarkup('Views Vanilla JavaScript Reveal'),
  help: new TranslatableMarkup('Render items in a Reveal using vanilla JavaScript.'),
  theme: 'views_view_vvjr',
  display_types: ['normal'],
)]
class Reveal extends VvjStylePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleSlug(): string {
    return 'vvjr';
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomElementTag(): string {
    return 'vvjr-reveal';
  }

  /**
   * Restores v1's pattern-specific "Enable CSS Library" description.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The description.
   */
  protected function getEnableCssDescription(): TranslatableMarkup {
    return $this->t('Check this box to include the CSS library for styling the reveal elements.');
  }

  /**
   * {@inheritdoc}
   *
   * Reveal state is per-card and transient — no panels/slides to deep-link to.
   */
  protected function supportsDeeplinking(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * Reveal offers six animation styles (omits "None" — reveals always animate).
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   *   Preset key → translatable label.
   */
  protected function getAnimationPresets(): array {
    return [
      AnimationDirection::FADE => $this->t('Fade'),
      AnimationDirection::ZOOM => $this->t('Zoom'),
      AnimationDirection::TOP => $this->t('Slide from Top'),
      AnimationDirection::RIGHT => $this->t('Slide from Right'),
      AnimationDirection::BOTTOM => $this->t('Slide from Bottom'),
      AnimationDirection::LEFT => $this->t('Slide from Left'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function definePatternOptions(array $options): array {
    $options['reveal_method'] = ['default' => VvjrConstants::DEFAULT_REVEAL_METHOD];
    $options['reveal_style'] = ['default' => VvjrConstants::DEFAULT_REVEAL_STYLE];
    $options['reveal_speed'] = ['default' => VvjrConstants::DEFAULT_REVEAL_SPEED];
    $options['front_bg_color'] = ['default' => VvjrConstants::DEFAULT_FRONT_BG_COLOR];
    $options['back_bg_color'] = ['default' => VvjrConstants::DEFAULT_BACK_BG_COLOR];
    $options['available_breakpoints'] = ['default' => VvjrConstants::DEFAULT_BREAKPOINT];
    $options['animation_easing'] = ['default' => VvjrConstants::DEFAULT_ANIMATION_EASING];
    $options['grid_gap'] = ['default' => VvjrConstants::DEFAULT_GRID_GAP];
    $options['box_height'] = ['default' => VvjrConstants::DEFAULT_BOX_HEIGHT];
    $options['box_width'] = ['default' => VvjrConstants::DEFAULT_BOX_WIDTH];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildPatternSections(array &$form): void {
    $this->buildResponsiveSection($form);
    $this->buildDimensionsSection($form);
    $this->buildRevealSection($form);
    $this->buildColorSection($form);
  }

  /**
   * Responsive section — breakpoint at which reveal is gated/disabled.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildResponsiveSection(array &$form): void {
    $form['responsive'] = [
      '#type' => 'details',
      '#title' => $this->t('Responsive Settings'),
      '#open' => TRUE,
      '#weight' => -40,
    ];

    $form['responsive']['available_breakpoints'] = [
      '#type' => 'select',
      '#title' => $this->t('Available Breakpoints for Reveal'),
      '#options' => [
        VvjrConstants::BREAKPOINT_ALL => $this->t('Active on all screens'),
        '576' => $this->t('576 px / 36 rem'),
        '768' => $this->t('768 px / 48 rem'),
        '992' => $this->t('992 px / 62 rem'),
        '1200' => $this->t('1200 px / 75 rem'),
        '1400' => $this->t('1400 px / 87.5 rem'),
      ],
      '#default_value' => $this->options['available_breakpoints'],
      '#description' => $this->t('Select the maximum screen width (in pixels) at which the Reveal should be disabled. Selecting "Active on all screens" will keep the Reveal active on all screen sizes.'),
    ];
  }

  /**
   * Dimensions section — width, height, gap.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildDimensionsSection(array &$form): void {
    $form['dimensions'] = [
      '#type' => 'details',
      '#title' => $this->t('Dimensions'),
      '#open' => TRUE,
      '#weight' => -30,
    ];

    $form['dimensions']['box_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Box Min Width (px)'),
      '#default_value' => $this->options['box_width'],
      '#description' => $this->t('Defines the minimum width for each grid box (reveal). The grid automatically adjusts the number of columns to fit the available space, ensuring responsive and flexible layout. Enter "0" to disable this field.'),
      '#step' => 1,
      '#min' => VvjrConstants::MIN_BOX_WIDTH,
      '#required' => TRUE,
    ];

    $form['dimensions']['box_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Box Height (in pixels)'),
      '#default_value' => $this->options['box_height'],
      '#description' => $this->t('Specify the height of the revealed box in pixels. Enter "0" to disable this field.'),
      '#step' => 1,
      '#min' => VvjrConstants::MIN_BOX_HEIGHT,
      '#required' => TRUE,
    ];

    $form['dimensions']['grid_gap'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Gap (px)'),
      '#default_value' => $this->options['grid_gap'],
      '#description' => $this->t('Set the gap between grid items in pixels.'),
      '#step' => 1,
      '#min' => VvjrConstants::MIN_GRID_GAP,
      '#required' => TRUE,
    ];
  }

  /**
   * Reveal section — style, method, speed, easing.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildRevealSection(array &$form): void {
    $form['reveal'] = [
      '#type' => 'details',
      '#title' => $this->t('Reveal Settings'),
      '#open' => TRUE,
      '#weight' => -20,
    ];

    $reveal_style = $this->buildAnimationSelect('reveal_style');
    $reveal_style['#title'] = $this->t('Reveal Style');
    $reveal_style['#description'] = $this->t('Choose the reveal style. Reduced-motion users see no animation regardless.');
    $form['reveal']['reveal_style'] = $reveal_style;

    $form['reveal']['reveal_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Reveal Method'),
      '#options' => [
        VvjrConstants::METHOD_HOVER => $this->t('Hover'),
        VvjrConstants::METHOD_CLICK => $this->t('Click'),
      ],
      '#default_value' => $this->options['reveal_method'],
      '#description' => $this->t('Choose whether the revealed box will be displayed on hover or click.'),
    ];

    $form['reveal']['reveal_speed'] = [
      '#type' => 'number',
      '#title' => $this->t('Reveal Speed (seconds)'),
      '#default_value' => $this->options['reveal_speed'],
      '#description' => $this->t('Specify the speed of the reveal animation (e.g., 0.1 for fast, 2 for slow). Range: @min - @max.', [
        '@min' => VvjrConstants::MIN_REVEAL_SPEED,
        '@max' => VvjrConstants::MAX_REVEAL_SPEED,
      ]),
      '#step' => VvjrConstants::REVEAL_SPEED_STEP,
      '#min' => VvjrConstants::MIN_REVEAL_SPEED,
      '#max' => VvjrConstants::MAX_REVEAL_SPEED,
    ];

    $form['reveal']['animation_easing'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation Easing'),
      '#options' => [
        VvjrConstants::EASING_LINEAR => $this->t('Linear'),
        VvjrConstants::EASING_EASE => $this->t('Ease'),
        VvjrConstants::EASING_EASE_IN => $this->t('Ease-in'),
        VvjrConstants::EASING_EASE_OUT => $this->t('Ease-out'),
        VvjrConstants::EASING_EASE_IN_OUT => $this->t('Ease-in-out'),
      ],
      '#default_value' => $this->options['animation_easing'],
      '#description' => $this->t('Choose the easing function for the reveal animation.'),
    ];
  }

  /**
   * Color section — front + back background colors.
   *
   * @param array<int|string, mixed> $form
   *   The form, modified in place.
   */
  protected function buildColorSection(array &$form): void {
    $form['colors'] = [
      '#type' => 'details',
      '#title' => $this->t('Color Settings'),
      '#open' => FALSE,
      '#weight' => -10,
    ];

    $form['colors']['front_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Displayed Side Background Color'),
      '#default_value' => $this->options['front_bg_color'],
      '#description' => $this->t('Select the background color for the displayed side of the box.'),
    ];

    $form['colors']['back_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Revealed Side Background Color'),
      '#default_value' => $this->options['back_bg_color'],
      '#description' => $this->t('Select the background color for the revealed side of the box.'),
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Add the breakpoint-specific responsive library on top of the
   * shared library list.
   *
   * @return list<string>
   *   Library identifiers.
   */
  protected function buildLibraryList(): array {
    $libraries = parent::buildLibraryList();
    $breakpoint = $this->options['available_breakpoints'] ?? VvjrConstants::DEFAULT_BREAKPOINT;
    $breakpoint = is_scalar($breakpoint)
      ? (string) $breakpoint
      : VvjrConstants::DEFAULT_BREAKPOINT;
    $libraries[] = 'vvjr/vvjr__' . $breakpoint;
    return $libraries;
  }

  /**
   * {@inheritdoc}
   *
   * @param array<int|string, mixed> $form
   *   The Views style options form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state): void {
    $values = $form_state->getValue('style_options');
    if (!is_array($values)) {
      $values = [];
    }
    $form_state->setValue('style_options', $this->flattenFormValues($values));
    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * Flatten the sectioned form values back into the persisted shape.
   *
   * @param array<int|string, mixed> $values
   *   Submitted form values keyed by section then by option name.
   *
   * @return array<string, mixed>
   *   Flattened option array.
   */
  protected function flattenFormValues(array $values): array {
    $flattened = [];

    foreach (['responsive', 'dimensions', 'reveal', 'colors'] as $section) {
      $section_values = $values[$section] ?? NULL;
      if (!is_array($section_values)) {
        continue;
      }
      foreach ($section_values as $key => $value) {
        $flattened[(string) $key] = $value;
      }
    }

    $advanced = $values['advanced_section'] ?? NULL;
    if (is_array($advanced)) {
      $flattened['enable_css'] = $advanced['enable_css'] ?? TRUE;
    }

    $flattened['unique_id'] = $this->options['unique_id'] ?? $this->generateUniqueId();

    return $flattened;
  }

}
