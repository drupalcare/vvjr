<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjr\FunctionalJavascript;

use Drupal\Tests\vvj_core\FunctionalJavascript\VvjPatternJavascriptTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Browser smoke tests for the vvjr example view custom element.
 */
#[Group('vvjr')]
#[Group('vvj_javascript')]
final class VvjrJavascriptSmokeTest extends VvjPatternJavascriptTestBase {

  /**
   * {@inheritdoc}
   *
   * @var list<string>
   */
  protected static $modules = [
    'node',
    'block',
    'views',
    'field',
    'file',
    'filter',
    'text',
    'image',
    'vvj_core',
    'vvjr',
  ];

  /**
   * {@inheritdoc}
   */
  protected function vvjExampleViewId(): string {
    return 'vvjr_example';
  }

  /**
   * {@inheritdoc}
   */
  protected function vvjCustomElementTag(): string {
    return 'vvjr-reveal';
  }

  /**
   * {@inheritdoc}
   */
  protected function vvjRequiresArticleImageField(): bool {
    return TRUE;
  }

}
