<?php

declare(strict_types=1);

namespace Drupal\vvjr\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\vvjr\VvjrConstants;

/**
 * Style plugin to render items in a Reveal using vanilla JavaScript.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_vvjr",
 *   title = @Translation("Views Vanilla JavaScript Reveal"),
 *   help = @Translation("Render items in a Reveal using vanilla JavaScript."),
 *   theme = "views_view_vvjr",
 *   display_types = { "normal" }
 * )
 */
class ViewsVanillaJavascriptReveal extends StylePluginBase {

  /**
   * Breakpoint constants.
   */
  public const BREAKPOINT_ALL = 'all';
  public const BREAKPOINT_576 = '576';
  public const BREAKPOINT_768 = '768';
  public const BREAKPOINT_992 = '992';
  public const BREAKPOINT_1200 = '1200';
  public const BREAKPOINT_1400 = '1400';

  /**
   * Reveal style constants.
   */
  public const REVEAL_STYLE_FADE = 'a-fade';
  public const REVEAL_STYLE_ZOOM = 'a-zoom';
  public const REVEAL_STYLE_TOP = 'a-top';
  public const REVEAL_STYLE_RIGHT = 'a-right';
  public const REVEAL_STYLE_BOTTOM = 'a-bottom';
  public const REVEAL_STYLE_LEFT = 'a-left';

  /**
   * Reveal method constants.
   */
  public const REVEAL_METHOD_HOVER = 'hover';
  public const REVEAL_METHOD_CLICK = 'click';

  /**
   * Animation easing constants.
   */
  public const EASING_EASE = 'ease';
  public const EASING_LINEAR = 'linear';
  public const EASING_EASE_IN = 'ease-in';
  public const EASING_EASE_OUT = 'ease-out';
  public const EASING_EASE_IN_OUT = 'ease-in-out';

  /**
   * Does the style plugin use a row plugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin use row classes.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Cached unique ID for this view display.
   *
   * @var int|null
   */
  protected ?int $cachedUniqueId = NULL;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();

    $options[VvjrConstants::OPTION_UNIQUE_ID] = ['default' => $this->generateUniqueId()];
    $options[VvjrConstants::OPTION_REVEAL_METHOD] = ['default' => VvjrConstants::DEFAULT_REVEAL_METHOD];
    $options[VvjrConstants::OPTION_REVEAL_STYLE] = ['default' => VvjrConstants::DEFAULT_REVEAL_STYLE];
    $options[VvjrConstants::OPTION_REVEAL_SPEED] = ['default' => VvjrConstants::DEFAULT_REVEAL_SPEED];
    $options[VvjrConstants::OPTION_FRONT_BG_COLOR] = ['default' => VvjrConstants::DEFAULT_FRONT_BG_COLOR];
    $options[VvjrConstants::OPTION_BACK_BG_COLOR] = ['default' => VvjrConstants::DEFAULT_BACK_BG_COLOR];
    $options[VvjrConstants::OPTION_BREAKPOINTS] = ['default' => VvjrConstants::DEFAULT_BREAKPOINT];
    $options[VvjrConstants::OPTION_ANIMATION_EASING] = ['default' => VvjrConstants::DEFAULT_ANIMATION_EASING];
    $options[VvjrConstants::OPTION_GRID_GAP] = ['default' => VvjrConstants::DEFAULT_GRID_GAP];
    $options[VvjrConstants::OPTION_ENABLE_CSS] = ['default' => TRUE];
    $options[VvjrConstants::OPTION_BOX_HEIGHT] = ['default' => VvjrConstants::DEFAULT_BOX_HEIGHT];
    $options[VvjrConstants::OPTION_BOX_WIDTH] = ['default' => VvjrConstants::DEFAULT_BOX_WIDTH];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    $this->setDefaultElementWeights($form);
    $this->buildWarningMessage($form);
    $this->buildResponsiveSettings($form);
    $this->buildDimensionsSettings($form);
    $this->buildRevealSettings($form);
    $this->buildColorSettings($form);
    $this->buildLibrarySettings($form);
    $this->buildTokenDocumentation($form);
  }

  /**
   * Sets weights for default Drupal form elements.
   *
   * @param array $form
   *   The form array.
   */
  protected function setDefaultElementWeights(array &$form): void {
    $default_elements = [
      'grouping' => -100,
      'row_class' => -90,
      'default_row_class' => -85,
      'uses_fields' => -80,
      'class' => -75,
      'wrapper_class' => -70,
    ];

    foreach ($default_elements as $element_key => $weight) {
      if (isset($form[$element_key])) {
        $form[$element_key]['#weight'] = $weight;
      }
    }
  }

  /**
   * Builds warning message section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildWarningMessage(array &$form): void {
    if ($this->view->storage->id() === 'vvjr_example') {
      return;
    }
    $form['warning_message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' .
      $this->t('Note: The first field will be used as the displayed content, and the rest of the fields will be used as the revealed content. To see an example, check the vvjr_example view by clicking <a href="/admin/structure/views/view/@view_id" style="display: inline;">here</a> to edit it.',
          ['@view_id' => VvjrConstants::EXAMPLE_VIEW_ID]
      ) . '</div>',
      '#weight' => -50,
    ];
  }

  /**
   * Builds responsive settings section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildResponsiveSettings(array &$form): void {
    $form['responsive'] = [
      '#type' => 'details',
      '#title' => $this->t('Responsive Settings'),
      '#open' => TRUE,
      '#weight' => -40,
    ];

    $form['responsive'][VvjrConstants::OPTION_BREAKPOINTS] = [
      '#type' => 'select',
      '#title' => $this->t('Available Breakpoints for Reveal'),
      '#options' => $this->getBreakpointOptions(),
      '#default_value' => $this->options[VvjrConstants::OPTION_BREAKPOINTS],
      '#description' => $this->t('Select the maximum screen width (in pixels) at which the Reveal should be disabled. Selecting "Active on all screens" will keep the Reveal active on all screen sizes.'),
    ];
  }

  /**
   * Builds dimensions settings section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildDimensionsSettings(array &$form): void {
    $form['dimensions'] = [
      '#type' => 'details',
      '#title' => $this->t('Dimensions'),
      '#open' => TRUE,
      '#weight' => -30,
    ];

    $form['dimensions'][VvjrConstants::OPTION_BOX_WIDTH] = [
      '#type' => 'number',
      '#title' => $this->t('Box Min Width (px)'),
      '#default_value' => $this->options[VvjrConstants::OPTION_BOX_WIDTH],
      '#description' => $this->t('Defines the minimum width for each grid box (reveal). The grid automatically adjusts the number of columns to fit the available space, ensuring responsive and flexible layout. Enter "0" to disable this field.'),
      '#step' => 1,
      '#min' => VvjrConstants::MIN_BOX_WIDTH,
      '#required' => TRUE,
    ];

    $form['dimensions'][VvjrConstants::OPTION_BOX_HEIGHT] = [
      '#type' => 'number',
      '#title' => $this->t('Box Height (in pixels)'),
      '#default_value' => $this->options[VvjrConstants::OPTION_BOX_HEIGHT],
      '#description' => $this->t('Specify the height of the revealed box in pixels. Enter "0" to disable this field.'),
      '#step' => 1,
      '#min' => VvjrConstants::MIN_BOX_HEIGHT,
      '#required' => TRUE,
    ];

    $form['dimensions'][VvjrConstants::OPTION_GRID_GAP] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Gap (px)'),
      '#default_value' => $this->options[VvjrConstants::OPTION_GRID_GAP],
      '#description' => $this->t('Set the gap between grid items in pixels.'),
      '#step' => 1,
      '#min' => VvjrConstants::MIN_GRID_GAP,
      '#required' => TRUE,
    ];
  }

  /**
   * Builds reveal settings section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildRevealSettings(array &$form): void {
    $form['reveal'] = [
      '#type' => 'details',
      '#title' => $this->t('Reveal Settings'),
      '#open' => TRUE,
      '#weight' => -20,
    ];

    $form['reveal'][VvjrConstants::OPTION_REVEAL_STYLE] = [
      '#type' => 'select',
      '#title' => $this->t('Reveal Style'),
      '#options' => $this->getRevealStyleOptions(),
      '#default_value' => $this->options[VvjrConstants::OPTION_REVEAL_STYLE],
      '#description' => $this->t('Choose the reveal style.'),
    ];

    $form['reveal'][VvjrConstants::OPTION_REVEAL_METHOD] = [
      '#type' => 'select',
      '#title' => $this->t('Reveal Method'),
      '#options' => $this->getRevealMethodOptions(),
      '#default_value' => $this->options[VvjrConstants::OPTION_REVEAL_METHOD],
      '#description' => $this->t('Choose whether the revealed box will be displayed on hover or click.'),
    ];

    $form['reveal'][VvjrConstants::OPTION_REVEAL_SPEED] = [
      '#type' => 'number',
      '#title' => $this->t('Reveal Speed'),
      '#default_value' => $this->options[VvjrConstants::OPTION_REVEAL_SPEED],
      '#description' => $this->t('Specify the speed of the reveal animation (e.g., 0.1 for fast, 2 for slow).'),
      '#step' => VvjrConstants::STEP_REVEAL_SPEED,
      '#min' => VvjrConstants::MIN_REVEAL_SPEED,
      '#max' => VvjrConstants::MAX_REVEAL_SPEED,
    ];

    $form['reveal'][VvjrConstants::OPTION_ANIMATION_EASING] = [
      '#type' => 'select',
      '#title' => $this->t('Animation Easing'),
      '#options' => $this->getEasingOptions(),
      '#default_value' => $this->options[VvjrConstants::OPTION_ANIMATION_EASING],
      '#description' => $this->t('Choose the easing function for the reveal animation.'),
    ];
  }

  /**
   * Builds color settings section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildColorSettings(array &$form): void {
    $form['colors'] = [
      '#type' => 'details',
      '#title' => $this->t('Color Settings'),
      '#open' => FALSE,
      '#weight' => -10,
    ];

    $form['colors'][VvjrConstants::OPTION_FRONT_BG_COLOR] = [
      '#type' => 'color',
      '#title' => $this->t('Displayed Side Background Color'),
      '#default_value' => $this->options[VvjrConstants::OPTION_FRONT_BG_COLOR],
      '#description' => $this->t('Select the background color for the displayed side of the box.'),
    ];

    $form['colors'][VvjrConstants::OPTION_BACK_BG_COLOR] = [
      '#type' => 'color',
      '#title' => $this->t('Revealed Side Background Color'),
      '#default_value' => $this->options[VvjrConstants::OPTION_BACK_BG_COLOR],
      '#description' => $this->t('Select the background color for the revealed side of the box.'),
    ];
  }

  /**
   * Builds library settings section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildLibrarySettings(array &$form): void {
    $form['library'] = [
      '#type' => 'details',
      '#title' => $this->t('Library Settings'),
      '#open' => FALSE,
      '#weight' => 0,
    ];

    $form['library'][VvjrConstants::OPTION_ENABLE_CSS] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable CSS Library'),
      '#default_value' => $this->options[VvjrConstants::OPTION_ENABLE_CSS],
      '#description' => $this->t('Check this box to include the CSS library for styling the reveal elements.'),
    ];
  }

  /**
   * Builds token documentation section.
   *
   * @param array $form
   *   The form array.
   */
  protected function buildTokenDocumentation(array &$form): void {
    $form['token_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Token Documentation'),
      '#open' => FALSE,
      '#weight' => 100,
    ];

    $form['token_section']['description'] = [
      '#markup' => $this->t('<p>When using <em>Global: Text area</em> or <em>Global: Unfiltered text</em> in the Views header, footer, or empty text areas, the default Twig-style tokens (e.g., <code>{{ title }}</code>) will not work with the VVJR style plugin.</p>
        <p>Instead, use the custom VVJR token format to access field values from the <strong>first row</strong> of the View result:</p>
        <ul>
          <li><code>[vvjr:field_name]</code> – The rendered output of the field (e.g., linked title, image, formatted text).</li>
          <li><code>[vvjr:field_name:plain]</code> – A plain-text version of the field, with all HTML stripped.</li>
        </ul>
        <p>Examples:</p>
        <ul>
          <li><code>{{ title }}</code> ➜ <code>[vvjr:title]</code></li>
          <li><code>{{ field_image }}</code> ➜ <code>[vvjr:field_image]</code></li>
          <li><code>{{ body }}</code> ➜ <code>[vvjr:body:plain]</code></li>
        </ul>
        <p>These tokens offer safe and flexible field output for dynamic headings, summaries, and fallback messages in VVJR-enabled Views.</p>'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state): void {
    $values = $form_state->getValue('style_options');
    $flattened_values = $this->flattenFormValues($values);
    $form_state->setValue('style_options', $flattened_values);

    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * Flattens nested form values to match original structure.
   *
   * @param array $values
   *   The nested form values.
   *
   * @return array
   *   The flattened values.
   */
  protected function flattenFormValues(array $values): array {
    $flattened = [];

    if (isset($values['responsive'])) {
      foreach ($values['responsive'] as $key => $value) {
        $flattened[$key] = $value;
      }
    }

    if (isset($values['dimensions'])) {
      foreach ($values['dimensions'] as $key => $value) {
        $flattened[$key] = $value;
      }
    }

    if (isset($values['reveal'])) {
      foreach ($values['reveal'] as $key => $value) {
        $flattened[$key] = $value;
      }
    }

    if (isset($values['colors'])) {
      foreach ($values['colors'] as $key => $value) {
        $flattened[$key] = $value;
      }
    }

    if (isset($values['library'])) {
      foreach ($values['library'] as $key => $value) {
        $flattened[$key] = $value;
      }
    }

    $flattened[VvjrConstants::OPTION_UNIQUE_ID] = $this->options[VvjrConstants::OPTION_UNIQUE_ID] ?? $this->generateUniqueId();

    return $flattened;
  }

  /**
   * Generates a unique numeric ID for the view display.
   *
   * @return int
   *   A unique ID between 10000000 and 99999999.
   *
   * @throws \Exception
   *   If an appropriate source of randomness cannot be found.
   */
  protected function generateUniqueId(): int {
    if ($this->cachedUniqueId !== NULL) {
      return $this->cachedUniqueId;
    }

    $this->cachedUniqueId = random_int(VvjrConstants::MIN_UNIQUE_ID, VvjrConstants::MAX_UNIQUE_ID);

    if ($this->cachedUniqueId < VvjrConstants::MIN_UNIQUE_ID) {
      $this->cachedUniqueId += VvjrConstants::MIN_UNIQUE_ID;
    }
    if ($this->cachedUniqueId > VvjrConstants::MAX_UNIQUE_ID) {
      $range = VvjrConstants::MAX_UNIQUE_ID - VvjrConstants::MIN_UNIQUE_ID + 1;
      $this->cachedUniqueId = $this->cachedUniqueId % $range + VvjrConstants::MIN_UNIQUE_ID;
    }

    return $this->cachedUniqueId;
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $rows = [];

    if (!empty($this->view->result)) {
      foreach ($this->view->result as $row) {
        $rendered_row = $this->view->rowPlugin->render($row);
        if ($rendered_row !== NULL) {
          $rows[] = $rendered_row;
        }
      }
    }

    $libraries = $this->buildLibraryList();

    return [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#rows' => $rows,
      '#unique_id' => $this->options[VvjrConstants::OPTION_UNIQUE_ID] ?? $this->generateUniqueId(),
      '#attached' => [
        'library' => $libraries,
      ],
    ];
  }

  /**
   * Builds the list of libraries to attach.
   *
   * @return array
   *   An array of library names to attach.
   */
  protected function buildLibraryList(): array {
    $libraries = [VvjrConstants::LIBRARY_JS];

    if ($this->options[VvjrConstants::OPTION_ENABLE_CSS]) {
      $libraries[] = VvjrConstants::LIBRARY_CSS;
    }

    if (!empty($this->options[VvjrConstants::OPTION_BREAKPOINTS])) {
      $libraries[] = VvjrConstants::LIBRARY_BREAKPOINT_PREFIX . $this->options[VvjrConstants::OPTION_BREAKPOINTS];
    }

    return $libraries;
  }

  /**
   * Get breakpoint options for the select list.
   *
   * @return array
   *   Array of breakpoint options.
   */
  protected function getBreakpointOptions(): array {
    return [
      self::BREAKPOINT_ALL => $this->t('Active on all screens'),
      self::BREAKPOINT_576 => $this->t('576px / 36rem'),
      self::BREAKPOINT_768 => $this->t('768px / 48rem'),
      self::BREAKPOINT_992 => $this->t('992px / 62rem'),
      self::BREAKPOINT_1200 => $this->t('1200px / 75rem'),
      self::BREAKPOINT_1400 => $this->t('1400px / 87.5rem'),
    ];
  }

  /**
   * Get reveal style options for the select list.
   *
   * @return array
   *   Array of reveal style options.
   */
  protected function getRevealStyleOptions(): array {
    return [
      self::REVEAL_STYLE_FADE => $this->t('Fade'),
      self::REVEAL_STYLE_ZOOM => $this->t('Zoom'),
      self::REVEAL_STYLE_TOP => $this->t('Slide from Top'),
      self::REVEAL_STYLE_RIGHT => $this->t('Slide from Right'),
      self::REVEAL_STYLE_BOTTOM => $this->t('Slide from Bottom'),
      self::REVEAL_STYLE_LEFT => $this->t('Slide from Left'),
    ];
  }

  /**
   * Get reveal method options for the select list.
   *
   * @return array
   *   Array of reveal method options.
   */
  protected function getRevealMethodOptions(): array {
    return [
      self::REVEAL_METHOD_HOVER => $this->t('Hover'),
      self::REVEAL_METHOD_CLICK => $this->t('Click'),
    ];
  }

  /**
   * Get easing options for the select list.
   *
   * @return array
   *   Array of easing options.
   */
  protected function getEasingOptions(): array {
    return [
      self::EASING_LINEAR => $this->t('Linear'),
      self::EASING_EASE => $this->t('Ease'),
      self::EASING_EASE_IN => $this->t('Ease-in'),
      self::EASING_EASE_OUT => $this->t('Ease-out'),
      self::EASING_EASE_IN_OUT => $this->t('Ease-in-out'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate(): array {
    $errors = parent::validate();

    if (!$this->usesFields()) {
      $errors[] = $this->t('Views Reveal requires Fields as row style');
    }

    return $errors;
  }

}
