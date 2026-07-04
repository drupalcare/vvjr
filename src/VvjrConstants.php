<?php

declare(strict_types=1);

namespace Drupal\vvjr;

/**
 * Vvjr-specific constants — reveal pattern-only values.
 *
 * Cross-cutting constants live in vvj_core's `ValidationBounds` and
 * `AnimationDirection`.
 *
 * @internal Final, non-instantiable.
 */
final class VvjrConstants {

  /**
   * Reveal trigger values.
   */
  public const string METHOD_HOVER = 'hover';
  public const string METHOD_CLICK = 'click';
  public const string DEFAULT_REVEAL_METHOD = self::METHOD_CLICK;

  /**
   * Animation easing options.
   */
  public const string EASING_EASE = 'ease';
  public const string EASING_LINEAR = 'linear';
  public const string EASING_EASE_IN = 'ease-in';
  public const string EASING_EASE_OUT = 'ease-out';
  public const string EASING_EASE_IN_OUT = 'ease-in-out';
  public const string DEFAULT_ANIMATION_EASING = self::EASING_EASE;

  /**
   * Breakpoint values — `all` keeps reveal active at every viewport size.
   */
  public const string BREAKPOINT_ALL = 'all';
  public const string DEFAULT_BREAKPOINT = self::BREAKPOINT_ALL;

  /**
   * Defaults.
   */
  public const string DEFAULT_REVEAL_STYLE = 'a-fade';
  public const float DEFAULT_REVEAL_SPEED = 0.9;
  public const string DEFAULT_FRONT_BG_COLOR = '#f0fbff';
  public const string DEFAULT_BACK_BG_COLOR = '#b8e7ff';
  public const int DEFAULT_GRID_GAP = 16;
  public const int DEFAULT_BOX_HEIGHT = 250;
  public const int DEFAULT_BOX_WIDTH = 300;

  /**
   * Bounds.
   */
  public const float MIN_REVEAL_SPEED = 0.1;
  public const float MAX_REVEAL_SPEED = 2.0;
  public const float REVEAL_SPEED_STEP = 0.1;
  public const int MIN_BOX_HEIGHT = 0;
  public const int MIN_BOX_WIDTH = 0;
  public const int MIN_GRID_GAP = 0;

  /**
   * Prevent instantiation.
   */
  private function __construct() {
  }

}
