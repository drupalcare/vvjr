<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjr\Kernel;

use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\vvj_core\Kernel\VvjArticleImageExampleKernelTrait;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\views\Views;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Renders the shipped example view all the way to HTML.
 *
 * WHY THIS EXISTS. Nothing in this module rendered its own example view. The
 * install test installs, the style plugin test reads options and inspects the
 * build array, and neither runs Twig. That is exactly the gap that let vvjp
 * ship HTTP 500 on its example page through the whole 2.0.0 release with a
 * fully green suite, and it let cloudflare_purge be certified Ready with a
 * fatal on every API error path.
 *
 * This module is NOT known to carry vvjp's defect — measured 2026-09-08, its
 * schema and its shipped view agree. The point is that nothing was watching:
 * a render is the cheapest check that goes red when a stored option becomes
 * something the template cannot print.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
#[RunTestsInSeparateProcesses]
class VvjrExampleViewRenderTest extends KernelTestBase {

  use VvjArticleImageExampleKernelTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'file',
    'filter',
    'text',
    'image',
    'node',
    'views',
    'vvj_core',
    'vvjr',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->vvjKernelInstallArticleImageExampleEnvironment('vvjr');
  }

  /**
   * The shipped example view renders to HTML without a render-array error.
   */
  public function testExampleViewRenders(): void {
    $this->createArticlesWithImages(2);

    $view = Views::getView('vvjr_example');
    $this->assertNotNull($view, 'The example view ships as optional config.');
    $view->setDisplay('page_1');

    $build = $view->preview('page_1');
    $this->assertIsArray($build, 'The view produced a render array.');

    $renderer = $this->container->get('renderer');
    $html = (string) $renderer->renderInIsolation($build);

    $this->assertNotEmpty($view->result, 'The view returned rows.');
    $this->assertStringContainsString('class="reveal-inner"', $html, 'The wrapper rendered.');
    $this->assertStringContainsString('class="reveal-item', $html, 'A row rendered.');
  }

  /**
   * The breakpoint option is a scalar, because a single select produces one.
   *
   * The schema declares available_breakpoints as a string and the shipped view
   * stores one. vvjp released the same option declared as a sequence and stored
   * as a one-item list, which the template printed straight into Twig and the
   * renderer rejected. This pins the shape before it can drift that way.
   */
  public function testShippedBreakpointOptionIsScalar(): void {
    $displays = $this->config('views.view.vvjr_example')->get('display');
    $this->assertIsArray($displays);

    $checked = 0;
    foreach ($displays as $display_id => $display) {
      if (!is_array($display)) {
        continue;
      }
      $display_options = $display['display_options'] ?? NULL;
      if (!is_array($display_options)) {
        continue;
      }
      $style = $display_options['style'] ?? NULL;
      if (!is_array($style) || ($style['type'] ?? NULL) !== 'views_vvjr') {
        continue;
      }
      $options = $style['options'] ?? NULL;
      if (!is_array($options) || !array_key_exists('available_breakpoints', $options)) {
        continue;
      }
      $this->assertIsString(
        $options['available_breakpoints'],
        sprintf('Display "%s" stores available_breakpoints as a string.', (string) $display_id),
      );
      $checked++;
    }

    $this->assertGreaterThan(0, $checked, 'At least one display sets the option.');
  }

  /**
   * Creates published articles carrying an image, so the view has rows.
   *
   * @param int $count
   *   How many nodes to create.
   */
  private function createArticlesWithImages(int $count): void {
    $file_system = $this->container->get('file_system');
    $directory = 'public://vvjr-render-test';
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $uri = $file_system->copy(
      DRUPAL_ROOT . '/core/tests/fixtures/files/image-test.png',
      $directory . '/image-test.png',
      FileExists::Replace,
    );

    $file = File::create(['uri' => $uri, 'status' => 1]);
    $file->save();

    for ($i = 0; $i < $count; $i++) {
      Node::create([
        'type' => 'article',
        'title' => 'Render probe ' . $i,
        'status' => 1,
        'body' => ['value' => '<p>Body ' . $i . '</p>', 'format' => 'plain_text'],
        'field_image' => ['target_id' => $file->id(), 'alt' => 'Render probe'],
      ])->save();
    }
  }

}
