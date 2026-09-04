<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjr\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\Entity\User;
use Drupal\views\Views;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Confirms vvjr installs, ships its example view, and appears in Views UI.
 */
#[Group('vvjr')]
#[RunTestsInSeparateProcesses]
final class VvjrInstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   *
   * @var list<string>
   */
  protected static $modules = [
    // `help` serves /admin/help/{name}; without it testHelpPageRenders can
    // only ever 404, whatever this module does.
    'help',
    'node',
    'text',
    'views',
    'views_ui',
    'image',
    'file',
    'field',
    'vvjr',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * WHY THIS FIXTURE EXISTS. The example view depends on
   * `field.storage.node.field_image`, and optional config installs only once
   * its dependencies exist. Nothing in core supplies that storage to a test —
   * it comes from the standard profile's article type on a real site, which
   * Drupal 12 moved out to a recipe. The view therefore never installed.
   *
   * The image styles the view also names ship with the image module's own
   * config/install, so they need nothing here.
   */
  protected function setUp(): void {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'field_image',
      'entity_type' => 'node',
      'type' => 'image',
    ])->save();
    $this->container->get('config.installer')->installOptionalConfig();
  }

  /**
   * The module installs and ships its example view as optional config.
   */
  public function testModuleInstallsAndShipsExampleView(): void {
    $this->assertTrue(
      $this->container->get('module_handler')->moduleExists('vvjr'),
    );
    $view = Views::getView('vvjr_example');
    $this->assertNotNull($view, 'Example view installs as optional config.');
  }

  /**
   * The style is reachable through the Views UI edit form.
   */
  public function testStyleAppearsInViewsUi(): void {
    $admin = $this->drupalCreateUser([
      'administer views',
      'access administration pages',
    ]);
    $this->assertInstanceOf(User::class, $admin);
    $this->drupalLogin($admin);

    $this->drupalGet('admin/structure/views/view/vvjr_example/edit');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('VVJR Example');
  }

  /**
   * The module help page renders with the expected summary text.
   */
  public function testHelpPageRenders(): void {
    $admin = $this->drupalCreateUser([
      'access administration pages',
      // The route requires `access help pages`, not merely
      // `access administration pages` — checked against core's
      // help.routing.yml rather than assumed.
      'access help pages',
    ]);
    $this->assertInstanceOf(User::class, $admin);
    $this->drupalLogin($admin);

    $this->drupalGet('admin/help/vvjr');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Views Vanilla JavaScript Reveal');
  }

}
