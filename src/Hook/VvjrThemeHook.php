<?php

declare(strict_types=1);

namespace Drupal\vvjr\Hook;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme hook for vvjr — declares views_view_vvjr + views_view_vvjr_fields.
 */
final class VvjrThemeHook {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   For resolving the module's templates path.
   */
  public function __construct(
    private readonly ModuleExtensionList $moduleExtensionList,
  ) {}

  /**
   * Implements hook_theme().
   *
   * @param array<string, mixed> $existing
   *   Existing theme hooks.
   * @param string $type
   *   The type of extension invoking the hook.
   * @param string $theme
   *   Name of the theme.
   * @param string $path
   *   Path to the extension.
   *
   * @return array<string, array{
   *   variables?: array<string, mixed>,
   *   template: string,
   *   path: string,
   *   }>
   *   Theme registry entries.
   */
  #[Hook('theme')]
  public function theme(array $existing, string $type, string $theme, string $path): array {
    $template_path = $this->moduleExtensionList->getPath('vvjr') . '/templates';
    return [
      'views_view_vvjr_fields' => [
        'variables' => [
          'view' => NULL,
          'options' => [],
          'row' => NULL,
          'field_alias' => NULL,
          'attributes' => [],
          'title_attributes' => [],
          'content_attributes' => [],
          'title_prefix' => [],
          'title_suffix' => [],
          'fields' => [],
        ],
        'template' => 'views-view-vvjr-fields',
        'path' => $template_path,
      ],
      'views_view_vvjr' => [
        'variables' => [
          'view' => NULL,
          'rows' => [],
          'options' => [],
        ],
        'template' => 'views-view-vvjr',
        'path' => $template_path,
      ],
    ];
  }

}
