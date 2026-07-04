<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjr\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\vvj_core\Kernel\VvjArticleImageExampleKernelTrait;
use Drupal\views\Views;
use Drupal\vvjr\Plugin\views\style\Reveal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies the views_vvjr style plugin loads and provides expected defaults.
 */
#[Group('vvjr')]
#[RunTestsInSeparateProcesses]
final class VvjrStylePluginTest extends KernelTestBase {

  use VvjArticleImageExampleKernelTrait;

  /**
   * {@inheritdoc}
   *
   * @var list<string>
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
   * The style plugin is registered with the expected class and theme.
   */
  public function testStylePluginIsRegistered(): void {
    /** @var \Drupal\views\Plugin\ViewsPluginManager $manager */
    $manager = $this->container->get('plugin.manager.views.style');
    $definition = $manager->getDefinition('views_vvjr');
    $this->assertIsArray($definition);

    $this->assertSame(Reveal::class, $definition['class']);
    $this->assertSame('views_view_vvjr', $definition['theme']);
  }

  /**
   * The example view loads and initializes the style plugin.
   */
  public function testExampleViewLoadsWithStyle(): void {
    $view = Views::getView('vvjr_example');
    $this->assertNotNull($view, 'vvjr_example view ships as optional config.');

    $view->setDisplay('default');
    $view->initStyle();

    $this->assertInstanceOf(Reveal::class, $view->style_plugin);
    $this->assertSame('views_vvjr', $view->style_plugin->getPluginId());
  }

  /**
   * The style plugin defines the expected option defaults.
   */
  public function testStyleDefinesExpectedOptionDefaults(): void {
    $view = Views::getView('vvjr_example');
    $this->assertNotNull($view);
    $view->setDisplay('default');
    $view->initStyle();

    $options = $view->style_plugin->options;

    $this->assertArrayHasKey('reveal_method', $options);
    $this->assertArrayHasKey('reveal_style', $options);
    $this->assertArrayHasKey('reveal_speed', $options);
    $this->assertArrayHasKey('unique_id', $options);
    // Reveal state is per-card and transient, so the pattern opts out of
    // deep-linking. The base class only defines these options when
    // supportsDeeplinking() returns TRUE, which Reveal overrides to FALSE.
    $this->assertArrayNotHasKey('enable_deeplink', $options);
    $this->assertArrayNotHasKey('deeplink_identifier', $options);
  }

  /**
   * Rendering produces cache metadata and attaches the pattern library.
   */
  public function testRenderProducesCacheableOutput(): void {
    $view = Views::getView('vvjr_example');
    $this->assertNotNull($view);
    $view->setDisplay('default');
    $view->execute();

    $build = $view->style_plugin->render();
    $this->assertIsArray($build);

    $this->assertArrayHasKey('#cache', $build);
    $cache = $build['#cache'];
    $this->assertIsArray($cache);
    $this->assertArrayHasKey('tags', $cache);
    $this->assertArrayHasKey('contexts', $cache);
    $this->assertArrayHasKey('max-age', $cache);

    $this->assertArrayHasKey('#attached', $build);
    $attached = $build['#attached'];
    $this->assertIsArray($attached);
    $this->assertArrayHasKey('library', $attached);
    $library = $attached['library'];
    $this->assertIsArray($library);
    $this->assertContains('vvjr/vvjr', $library);
  }

}
