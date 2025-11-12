<?php

declare(strict_types=1);

namespace Drupal\vvjr;

/**
 * Defines constants for the VVJR module.
 */
final class VvjrConstants {

  /**
   * Default: Box width (px).
   */
  public const DEFAULT_BOX_WIDTH = 300;

  /**
   * Default: Box height (px).
   */
  public const DEFAULT_BOX_HEIGHT = 250;

  /**
   * Default: Grid gap (px).
   */
  public const DEFAULT_GRID_GAP = 16;

  /**
   * Default: Reveal speed (seconds).
   */
  public const DEFAULT_REVEAL_SPEED = 0.9;

  /**
   * Default: Front background color.
   */
  public const DEFAULT_FRONT_BG_COLOR = '#f0fbff';

  /**
   * Default: Back background color.
   */
  public const DEFAULT_BACK_BG_COLOR = '#b8e7ff';

  /**
   * Default: Reveal style.
   */
  public const DEFAULT_REVEAL_STYLE = 'a-fade';

  /**
   * Default: Reveal method.
   */
  public const DEFAULT_REVEAL_METHOD = 'click';

  /**
   * Default: Animation easing.
   */
  public const DEFAULT_ANIMATION_EASING = 'ease';

  /**
   * Default: Available breakpoints.
   */
  public const DEFAULT_BREAKPOINT = 'all';

  /**
   * Minimum: Box width (px).
   */
  public const MIN_BOX_WIDTH = 0;

  /**
   * Minimum: Box height (px).
   */
  public const MIN_BOX_HEIGHT = 0;

  /**
   * Minimum: Grid gap (px).
   */
  public const MIN_GRID_GAP = 0;

  /**
   * Minimum: Reveal speed.
   */
  public const MIN_REVEAL_SPEED = 0.1;

  /**
   * Maximum: Reveal speed.
   */
  public const MAX_REVEAL_SPEED = 2.0;

  /**
   * Step: Reveal speed.
   */
  public const STEP_REVEAL_SPEED = 0.1;

  /**
   * Minimum: Unique ID.
   */
  public const MIN_UNIQUE_ID = 10000000;

  /**
   * Maximum: Unique ID.
   */
  public const MAX_UNIQUE_ID = 99999999;

  /**
   * Token namespace.
   */
  public const TOKEN_NAMESPACE = 'vvjr';

  /**
   * Token suffix for plain text output.
   */
  public const TOKEN_SUFFIX_PLAIN = ':plain';

  /**
   * Token validation pattern.
   */
  public const TOKEN_PATTERN = '/^[a-zA-Z0-9_]+(:plain)?$/';

  /**
   * Example view ID.
   */
  public const EXAMPLE_VIEW_ID = 'vvjr_example';

  /**
   * Plugin ID.
   */
  public const PLUGIN_ID = 'views_vvjr';

  /**
   * Theme hook.
   */
  public const THEME_HOOK = 'views_view_vvjr';

  /**
   * Theme hook for fields.
   */
  public const THEME_HOOK_FIELDS = 'views_view_vvjr_fields';

  /**
   * CSS class for wrapper.
   */
  public const CSS_CLASS_WRAPPER = 'vvj-reveal';

  /**
   * Library: JavaScript.
   */
  public const LIBRARY_JS = 'vvjr/vvjr';

  /**
   * Library: CSS styles.
   */
  public const LIBRARY_CSS = 'vvjr/vvjr-style';

  /**
   * Library: Breakpoint prefix.
   */
  public const LIBRARY_BREAKPOINT_PREFIX = 'vvjr/vvjr__';

  /**
   * Configuration key: Unique ID.
   */
  public const OPTION_UNIQUE_ID = 'unique_id';

  /**
   * Configuration key: Reveal method.
   */
  public const OPTION_REVEAL_METHOD = 'reveal_method';

  /**
   * Configuration key: Reveal style.
   */
  public const OPTION_REVEAL_STYLE = 'reveal_style';

  /**
   * Configuration key: Reveal speed.
   */
  public const OPTION_REVEAL_SPEED = 'reveal_speed';

  /**
   * Configuration key: Front background color.
   */
  public const OPTION_FRONT_BG_COLOR = 'front_bg_color';

  /**
   * Configuration key: Back background color.
   */
  public const OPTION_BACK_BG_COLOR = 'back_bg_color';

  /**
   * Configuration key: Available breakpoints.
   */
  public const OPTION_BREAKPOINTS = 'available_breakpoints';

  /**
   * Configuration key: Animation easing.
   */
  public const OPTION_ANIMATION_EASING = 'animation_easing';

  /**
   * Configuration key: Grid gap.
   */
  public const OPTION_GRID_GAP = 'grid_gap';

  /**
   * Configuration key: Enable CSS.
   */
  public const OPTION_ENABLE_CSS = 'enable_css';

  /**
   * Configuration key: Box height.
   */
  public const OPTION_BOX_HEIGHT = 'box_height';

  /**
   * Configuration key: Box width.
   */
  public const OPTION_BOX_WIDTH = 'box_width';

  /**
   * Private constructor to prevent instantiation.
   */
  private function __construct() {
  }

}
