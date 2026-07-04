<?php

declare(strict_types=1);

namespace Drupal\Tests\vvjr\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;
use Drupal\views\Views;
use PHPUnit\Framework\Attributes\Group;

/**
 * Confirms vvjr installs, ships its example view, and appears in Views UI.
 */
#[Group('vvjr')]
final class VvjrInstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   *
   * @var list<string>
   */
  protected static $modules = [
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
    ]);
    $this->assertInstanceOf(User::class, $admin);
    $this->drupalLogin($admin);

    $this->drupalGet('admin/help/vvjr');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Views Vanilla JavaScript Reveal');
  }

}
