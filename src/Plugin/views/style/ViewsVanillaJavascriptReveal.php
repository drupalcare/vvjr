<?php

namespace Drupal\vvjr\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\style\StylePluginBase;

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
   * Does the style plugin use a row plugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesRowClass = TRUE;

  /**
   * Set default options.
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();
    $options['unique_id'] = ['default' => $this->generateUniqueId()];
    $options['reveal_method'] = ['default' => 'click'];
    $options['reveal_style'] = ['default' => 'a-fade'];
    $options['reveal_speed'] = ['default' => '0.9'];
    $options['front_bg_color'] = ['default' => '#f0fbff'];
    $options['back_bg_color'] = ['default' => '#b8e7ff'];
    $options['available_breakpoints'] = ['default' => '576'];
    $options['animation_easing'] = ['default' => 'ease'];
    $options['grid_gap'] = ['default' => 16];
    $options['enable_css'] = ['default' => TRUE];
    $options['box_height'] = ['default' => 250];
    $options['box_width'] = ['default' => 300];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    $form['warning_message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' . $this->t('Note: The first field will be used as the displayed content, and the rest of the fields will be used as the revealed content. To see an example, check the vvjr_example view by clicking <a href="/admin/structure/views/view/vvjr_example" style="display: inline;">here</a> to edit it.', ['@url' => Url::fromRoute('entity.view.edit_form', ['view' => 'vvjr_example'])->toString()]) . '</div>',
    ];

    $form['available_breakpoints'] = [
      '#type' => 'select',
      '#title' => $this->t('Available Breakpoints for Parallax'),
      '#options' => [
        'all' => $this->t('Active on all screens'),
        '576' => $this->t('576px / 36rem'),
        '768' => $this->t('768px / 48rem'),
        '992' => $this->t('992px / 62rem'),
        '1200' => $this->t('1200px / 75rem'),
        '1400' => $this->t('1400px / 87.5rem'),
      ],
      '#default_value' => $this->options['available_breakpoints'],
      '#description' => $this->t('Select the maximum screen width (in pixels) at which the Reveal should be disabled. Selecting "Active on all screens" will keep the Reveal active on all screen sizes.'),
    ];

    $form['box_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Box Min Width (px)'),
      '#default_value' => $this->options['box_width'],
      '#description' => $this->t('Defines the minimum width for each grid box (reveal). The grid automatically adjusts the number of columns to fit the available space, ensuring responsive and flexible layout. Enter "0" to disable this field.'),
      '#step' => 1,
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['box_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Box Height (in pixels)'),
      '#default_value' => $this->options['box_height'],
      '#description' => $this->t('Specify the height of the revealed box in pixels. Enter "0" to disable this field.'),
      '#step' => 1,
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['grid_gap'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid Gap (px)'),
      '#default_value' => $this->options['grid_gap'],
      '#description' => $this->t('Set the gap between grid items in pixels.'),
      '#step' => 1,
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['reveal_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Reveal Style'),
      '#options' => [
        'a-fade' => $this->t('Fade'),
        'a-zoom' => $this->t('Zoom'),
        'a-top' => $this->t('Slide from Top'),
        'a-right' => $this->t('Slide from Right'),
        'a-bottom' => $this->t('Slide from Bottom'),
        'a-left' => $this->t('Slide from Left'),
      ],
      '#default_value' => $this->options['reveal_style'],
      '#description' => $this->t('Choose the reveal style.'),
    ];

    $form['reveal_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Reveal Method'),
      '#options' => [
        'hover' => $this->t('Hover'),
        'click' => $this->t('Click'),
      ],
      '#default_value' => $this->options['reveal_method'],
      '#description' => $this->t('Choose whether the revealed box will be displayed on hover or click.'),
    ];

    $form['reveal_speed'] = [
      '#type' => 'number',
      '#title' => $this->t('Reveal Speed'),
      '#default_value' => $this->options['reveal_speed'],
      '#description' => $this->t('Specify the speed of the reveal animation (e.g., 0.1 for fast, 2 for slow).'),
      '#step' => 0.1,
      '#min' => 0.1,
      '#max' => 2,
    ];

    $form['front_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Displayed Side Background Color'),
      '#default_value' => $this->options['front_bg_color'],
      '#description' => $this->t('Select the background color for the displayed side of the box.'),
    ];

    $form['back_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Revealed Side Background Color'),
      '#default_value' => $this->options['back_bg_color'],
      '#description' => $this->t('Select the background color for the revealed side of the box.'),
    ];

    $form['animation_easing'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation Easing'),
      '#options' => [
        'ease' => $this->t('Ease'),
        'linear' => $this->t('Linear'),
        'ease-in' => $this->t('Ease-in'),
        'ease-out' => $this->t('Ease-out'),
        'ease-in-out' => $this->t('Ease-in-out'),
      ],
      '#default_value' => $this->options['animation_easing'],
      '#description' => $this->t('Choose the easing function for the reveal animation.'),
    ];

    // Add enable_css checkbox to the form.
    $form['enable_css'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable CSS Library'),
      '#default_value' => $this->options['enable_css'],
      '#description' => $this->t('Check this box to include the CSS library for styling the reveal elements.'),
    ];

    $form['vvjr_token_info'] = [
      '#type' => 'details',
      '#title' => $this->t('VVJR Tokens'),
      '#open' => TRUE,
    ];

    $form['vvjr_token_info']['description'] = [
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
   * Generates a unique numeric ID for the view display.
   *
   * @throws \Random\RandomException
   */
  protected function generateUniqueId(): int {
    // 8 digit unique ID
    return random_int(10000000, 99999999);
  }

  /**
   * Renders the view with the Reveal style.
   *
   * @return array
   *   A render array for the reveal.
   */
  public function render(): array {
    $rows = [];
    foreach ($this->view->result as $row) {
      $rows[] = $this->view->rowPlugin->render($row);
    }

    $libraries = [
      'vvjr/vvjr',
    ];

    // Conditionally include the CSS library based on the option.
    if ($this->options['enable_css']) {
      $libraries[] = 'vvjr/vvjr-style';
    }

    // Conditionally include a CSS breakpoint library based on the option.
    if ($this->options['available_breakpoints']) {
      // Include the vvjr-style breakpoint-specific library.
      $libraries[] = 'vvjr/vvjr__' . $this->options['available_breakpoints'];
    }

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#rows' => $rows,
      '#unique_id' => $this->options['unique_id'],
      '#attached' => [
        'library' => $libraries,
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = parent::validate();
    if (!$this->usesFields()) {
      $errors[] = $this->t('Views Reveal requires Fields as row style.');
    }
    return $errors;
  }

}
