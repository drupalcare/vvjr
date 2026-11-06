<?php

declare(strict_types=1);

namespace Drupal\vvjr\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Help hook for vvjr.
 */
final class VvjrHelpHook {

  use StringTranslationTrait;

  /**
   * Implements hook_help() for the vvjr help route.
   */
  #[Hook('help')]
  public function help(string $route_name, RouteMatchInterface $route_match): ?string {
    if ($route_name !== 'help.page.vvjr') {
      return NULL;
    }
    $output = '<h2>' . $this->t('About') . '</h2>';
    $output .= '<p>' . $this->t('The Views Vanilla JavaScript Reveal module displays the results of a view with accessible reveal-on-interaction effects. It is a Views format: it shows whatever the view lists and adds no content of its own. For more information, see the <a href=":project">online documentation for the Views Vanilla JavaScript Reveal module</a>.', [
      ':project' => 'https://www.drupal.org/project/vvjr',
    ]) . '</p>';
    $output .= '<h2>' . $this->t('Uses') . '</h2>';
    $output .= '<dl>';
    $output .= '<dt>' . $this->t('Displaying a view with reveal effects') . '</dt>';
    $output .= '<dd>' . $this->t('In the Views administration pages (<em>Structure</em> > <em>Views</em>), edit a view and, under <em>Format</em>, choose <em>Views Vanilla JavaScript Reveal</em>. Its settings cover layout, navigation, animation, accessibility and responsive behavior.') . '</dd>';
    $output .= '<dt>' . $this->t('Choosing a Pattern') . '</dt>';
    $output .= '<dd>' . $this->t('With the VVJ Patterns module (part of VVJ Core) enabled, the <em>Patterns</em> section of the format settings offers five ready-made designs: Blinds, Nocturne, Lens, Index and Cover. Choosing one applies its layout, animation and styling settings to the view.') . '</dd>';
    $output .= '<dt>' . $this->t('Trying the example view') . '</dt>';
    $output .= '<dd>' . $this->t('On a site with the Article content type, the module installs an example view, <em>VVJR Example</em>, at the path /vvjr-example.') . '</dd>';
    $output .= '</dl>';
    return $output;
  }

}
