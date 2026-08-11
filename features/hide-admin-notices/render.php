<?php

/**
 * Hide Admin Notices runtime.
 *
 * Hides the stream of plugin/marketing admin notices from regular admin
 * screens for the configured roles, and exposes them on a dedicated
 * "Notices" page reachable from the admin bar.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\HideAdminNotices;

class HideAdminNotices
{
  private const PAGE_SLUG = 'tofino-admin-notices';

  /** @var string[] Role slugs for which notices are hidden on regular admin pages. */
  private array $hide_roles = [];

  /** @var string[] Role slugs that may view the dedicated Notices page. */
  private array $panel_roles = [];

  /**
   * Constructor. Defers initialisation until after the feature registry has
   * registered its local ACF fields so unsaved defaults are available.
   */
  public function __construct()
  {
    add_action('acf/init', [$this, 'initialize'], 30);
  }

  /**
   * Reads the role settings and registers the admin hooks that power the
   * feature (hidden admin page, admin bar node, and body class).
   */
  public function initialize(): void
  {
    $this->hide_roles = $this->normalise_roles(get_field('hide_notices_roles', 'option'));
    $this->panel_roles = $this->normalise_roles(get_field('notices_panel_roles', 'option'));

    add_action('admin_menu', [$this, 'register_page']);
    add_action('admin_bar_menu', [$this, 'add_admin_bar_node'], 100);
    add_filter('admin_body_class', [$this, 'add_body_class']);
  }

  /**
   * Coerces an ACF multi-select value into a clean string[] of role slugs.
   *
   * @param mixed $value Raw value returned by get_field().
   * @return string[] Role slugs, with empties removed and keys re-indexed.
   */
  private function normalise_roles(mixed $value): array
  {
    if (!is_array($value)) {
      return [];
    }

    return array_values(array_filter(array_map('strval', $value)));
  }

  /**
   * Checks whether the current user belongs to any of the given roles.
   *
   * @param string[] $roles Role slugs to match against.
   * @return bool True when the user's roles intersect $roles. An empty
   *              $roles argument always returns false.
   */
  private function user_has_role(array $roles): bool
  {
    if (empty($roles)) {
      return false;
    }

    $user = wp_get_current_user();

    if (!$user || empty($user->roles)) {
      return false;
    }

    return (bool) array_intersect($user->roles, $roles);
  }

  /**
   * Detects whether the current request is for the dedicated Notices page.
   *
   * @return bool
   */
  private function is_notices_page(): bool
  {
    return isset($_GET['page']) && $_GET['page'] === self::PAGE_SLUG;
  }

  /**
   * Registers the hidden admin page that renders only the native notice hooks.
   * Not linked from the sidebar — the admin bar node is the entry point.
   *
   * @return void
   */
  public function register_page(): void
  {
    $hook = add_submenu_page(
      '',
      __('Notices', 'tofino'),
      __('Notices', 'tofino'),
      'read',
      self::PAGE_SLUG,
      [$this, 'render_page']
    );

    if ($hook) {
      add_action('load-' . $hook, [$this, 'authorise_page']);
    }
  }

  /**
   * Guards the Notices page at load time. Users whose roles are not in
   * notices_panel_roles receive a 403 instead of the page content.
   *
   * @return void
   */
  public function authorise_page(): void
  {
    if (!$this->user_has_role($this->panel_roles)) {
      wp_die(
        esc_html__('You do not have permission to view this page.', 'tofino'),
        '',
        ['response' => 403]
      );
    }
  }

  /**
   * Renders the Notices page body. WP fires admin_notices / all_admin_notices
   * before this callback runs, so collected notices appear above the heading
   * without any extra work here.
   *
   * @return void
   */
  public function render_page(): void
  {
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Notices', 'tofino') . '</h1>';
    echo '<p>' . esc_html__('Admin notices collected from the current request.', 'tofino') . '</p>';
    echo '</div>';
  }

  /**
   * Adds the "Notices" link to the top-right of the admin bar for users
   * in notices_panel_roles.
   *
   * @param \WP_Admin_Bar $wp_admin_bar The admin bar instance.
   * @return void
   */
  public function add_admin_bar_node(\WP_Admin_Bar $wp_admin_bar): void
  {
    if (!$this->user_has_role($this->panel_roles)) {
      return;
    }

    $wp_admin_bar->add_node([
      'id' => 'tofino-admin-notices',
      'parent' => 'top-secondary',
      'title' => __('Notices', 'tofino'),
      'href' => admin_url('admin.php?page=' . self::PAGE_SLUG),
      'meta' => ['title' => __('View admin notices', 'tofino')],
    ]);
  }

  /**
   * Appends the body class that scopes the hide stylesheet. Skipped on
   * the dedicated Notices page so notices remain visible there, and on
   * users whose roles are not in hide_notices_roles.
   *
   * @param string $classes Space-separated admin body classes.
   * @return string Classes with `tofino-hide-admin-notices` appended when active.
   */
  public function add_body_class(string $classes): string
  {
    if ($this->is_notices_page()) {
      return $classes;
    }

    if (!$this->user_has_role($this->hide_roles)) {
      return $classes;
    }

    return trim($classes . ' tofino-hide-admin-notices');
  }
}

new HideAdminNotices();
