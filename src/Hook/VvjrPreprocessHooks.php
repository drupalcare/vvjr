<?php

declare(strict_types=1);

namespace Drupal\vvjr\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\views\Hook\ViewsThemeHooks;
use Drupal\views\ViewExecutable;
use Drupal\vvjr\Plugin\views\style\Reveal;

/**
 * Preprocess hooks for VVJR reveal.
 *
 * Rewrites row theme suggestions to vvjr-namespaced ones, exposes
 * options to the template, and adds the legacy `vvj-reveal` class
 * on the views_view wrapper for v1 contract preservation.
 */
final class VvjrPreprocessHooks {

  /**
   * Constructor.
   *
   * @param \Drupal\views\Hook\ViewsThemeHooks $viewsThemeHooks
   *   Views row / field preprocess (replaces removed procedural
   *   template_preprocess_* in Drupal 12).
   */
  public function __construct(
    private readonly ViewsThemeHooks $viewsThemeHooks,
  ) {}

  /**
   * Implements hook_preprocess_HOOK() for views_view_vvjr.
   *
   * @param array<string, mixed> $variables
   *   Template variables.
   */
  #[Hook('preprocess_views_view_vvjr')]
  public function preprocessViewsViewVvjr(array &$variables): void {
    $view = $variables['view'] ?? NULL;
    if (!($view instanceof ViewExecutable)) {
      return;
    }
    $handler = $view->style_plugin;
    if (!($handler instanceof Reveal)) {
      return;
    }
    $variables['options'] = $handler->options;

    // Rewrite per-row theme suggestions so per-row Twig overrides
    // resolve to VVJR-namespaced templates instead of generic
    // views_view_fields.
    $rows = $variables['rows'] ?? NULL;
    if (is_array($rows)) {
      foreach ($rows as $key => $row) {
        if (!is_array($row) || !isset($row['#theme']) || !is_array($row['#theme'])) {
          continue;
        }
        foreach ($row['#theme'] as $idx => $hook) {
          if (is_string($hook)) {
            $row['#theme'][$idx] = str_replace(
              'views_view_fields',
              'views_view_vvjr_fields',
              $hook,
            );
          }
        }
        $rows[$key] = $row;
      }
      $variables['rows'] = $rows;
    }

    $this->viewsThemeHooks->preprocessViewsViewUnformatted($variables);
  }

  /**
   * Implements hook_preprocess_HOOK() for views_view_vvjr_fields.
   *
   * @param array<string, mixed> $variables
   *   Template variables.
   */
  #[Hook('preprocess_views_view_vvjr_fields')]
  public function preprocessViewsViewVvjrFields(array &$variables): void {
    $this->viewsThemeHooks->preprocessViewsViewFields($variables);
  }

  /**
   * Implements hook_preprocess_HOOK() for views_view.
   *
   * Adds the legacy `vvj-reveal` class for v1 contract preservation.
   *
   * @param array<string, mixed> $variables
   *   Template variables.
   */
  #[Hook('preprocess_views_view')]
  public function preprocessViewsView(array &$variables): void {
    $view = $variables['view'] ?? NULL;
    if (!($view instanceof ViewExecutable)) {
      return;
    }
    if (!($view->style_plugin instanceof Reveal)) {
      return;
    }
    $attributes = $variables['attributes'] ?? NULL;
    if (is_array($attributes)) {
      $classes = $attributes['class'] ?? [];
      if (!is_array($classes)) {
        $classes = [];
      }
      $classes[] = 'vvj-reveal';
      $attributes['class'] = $classes;
      $variables['attributes'] = $attributes;
    }
  }

}
