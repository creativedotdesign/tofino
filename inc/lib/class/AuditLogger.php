<?php

/**
 * WP Security Enhancer - Audit Logger with Standardized Log Format and Monolog
 * Inspired by the WP Security Audit Log plugin.
 * https://github.com/lynt-smitka/WP-Security-Enhancer/blob/main/lynt-security-enhancer.php
 */

declare(strict_types=1);

namespace Tofino\AuditLog;

use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Handler\StreamHandler;

if (!defined('ABSPATH')) {
  exit; // Prevent direct access
}

class AuditLogger
{
  private Logger $logger;

  /**
   * Constructor to initialize the logger and hooks.
   */
  public function __construct()
  {
    $this->init();
    $this->register();
  }

  /**
   * Initialize the Monolog logger with a processor for standardized log content.
   */
  private function init(): void
  {
    $this->logger = new Logger('audit-logger');
    $log_file_path = WP_CONTENT_DIR . '/logs/audit-logger.log';

    // Create the log file directory if it doesn't exist
    if (!file_exists(dirname($log_file_path))) {
      mkdir(dirname($log_file_path), 0755, true);
    }

    $this->logger->pushHandler(new StreamHandler($log_file_path, Logger::DEBUG));

    // Add a processor to standardize log entries
    $this->logger->pushProcessor([$this, 'addStandardLogFields']);
  }

  /**
   * Standardize log entries with additional fields.
   *
   * @param array $record The Monolog log record.
   * @return array The modified log record.
   */
  public function addStandardLogFields(LogRecord $record): LogRecord
  {
    $timestamp = current_time('Y-m-d H:i:s');
    $user = wp_get_current_user();
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $domain = get_site_url();

    $extra = array_merge($record->extra, [
      'type' => $record->level->getName(),
      'site' => $domain,
      'timestamp' => $timestamp,
      'user_ip' => $user_ip,
      'user_id' => $user->ID ?? 0,
      'user_name' => $user->user_login ?? 'Guest',
      'user_privileges' => implode(', ', $user->roles ?? []),
    ]);

    return $record->with(extra: $extra);
  }

  /**
   * Register WordPress hooks for logging.
   */
  private function register(): void
  {
    add_action('wp_login', [$this, 'logSuccessfulLogin'], 10, 2);
    add_action('wp_login_failed', [$this, 'logFailedLogin']);
    add_action('transition_post_status', [$this, 'logPostStatusTransition'], 10, 3);
    add_action('profile_update', [$this, 'logProfileUpdate'], 10, 2);
    add_action('activated_plugin', [$this, 'logPluginActivation']);
    add_action('deactivated_plugin', [$this, 'logPluginDeactivation']);

    add_action('save_post', [$this, 'logPostSave'], 10, 3);
    add_action('upgrader_process_complete', [$this, 'logUpdatesRun'], 10, 2);

    add_action('acf/save_post', [$this, 'logAcfOptionsSave'], 20);
    // add_action('update_option', [$this, 'logOptionUpdate'], 10, 3);
  }

  /**
   * Log a successful login attempt.
   *
   * @param string $username The username of the user.
   * @param \WP_User $user The user object.
   */
  public function logSuccessfulLogin(string $username, \WP_User $user): void
  {
    $this->logger->info('Successful login', [
      'details' => [
        'username' => $username,
        'user_id' => $user->ID,
        'roles' => $user->roles,
      ],
    ]);
  }

  /**
   * Log a failed login attempt.
   *
   * @param string $username The username attempted.
   */
  public function logFailedLogin(string $username): void
  {
    $this->logger->warning('Failed login attempt', [
      'details' => [
        'username' => $username,
      ],
    ]);
  }

  /**
   * Log a post status transition.
   *
   * @param string $newStatus The new post status.
   * @param string $oldStatus The previous post status.
   * @param \WP_Post $post The post object.
   */
  public function logPostStatusTransition(string $newStatus, string $oldStatus, \WP_Post $post): void
  {
    $this->logger->info('Post status changed', [
      'details' => [
        'post_id' => $post->ID,
        'post_title' => $post->post_title,
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
      ],
    ]);
  }

  /**
   * Log a user profile update.
   *
   * @param int $userId The user ID.
   * @param \WP_User $oldUserData The previous user data.
   */
  public function logProfileUpdate(int $userId, \WP_User $oldUserData): void
  {
    $this->logger->info('User profile updated', [
      'details' => [
        'user_id' => $userId,
      ],
    ]);
  }

  /**
   * Log plugin activation.
   *
   * @param string $plugin The plugin file path.
   */
  public function logPluginActivation(string $plugin): void
  {
    $this->logger->info('Plugin activated', [
      'details' => [
        'plugin' => $plugin,
      ],
    ]);
  }

  /**
   * Log plugin deactivation.
   *
   * @param string $plugin The plugin file path.
   */
  public function logPluginDeactivation(string $plugin): void
  {
    $this->logger->info('Plugin deactivated', [
      'details' => [
        'plugin' => $plugin,
      ],
    ]);
  }

  /**
   * Log post creation or update.
   *
   * @param int $post_id The post ID.
   * @param \WP_Post $post The post object.
   * @param bool $update Whether this is an update.
   */
  public function logPostSave(int $post_id, \WP_Post $post, bool $update): void
  {
    if (wp_is_post_revision($post_id)) return;

    $action = $update ? 'Post updated' : 'Post created';

    $this->logger->info($action, [
      'details' => [
        'post_id' => $post_id,
        'post_title' => $post->post_title,
        'post_type' => $post->post_type,
        'post_status' => $post->post_status,
      ],
    ]);
  }

  /**
   * Log core/plugin/theme updates.
   *
   * @param \WP_Upgrader $upgrader The upgrader object.
   * @param array $hook_extra Extra arguments.
   */
  public function logUpdatesRun(\WP_Upgrader $upgrader, array $hook_extra): void
  {
    $current_user = wp_get_current_user();
    $type = $hook_extra['type'] ?? 'unknown';
    $action = $hook_extra['action'] ?? 'unknown';

    $this->logger->info('Update process executed', [
      'details' => [
        'type' => $type,
        'action' => $action,
        'user_id' => $current_user->ID,
        'user_name' => $current_user->user_login,
      ],
    ]);
  }

  /**
   * Log when ACF options page is saved.
   *
   * @param string $post_id Either 'options' or 'options_{slug}'.
   */
  public function logAcfOptionsSave(string $post_id, ): void
  {
    // Only continue for ACF Options Pages
    if (strpos($post_id, 'options') !== 0) {
      return;
    }

    // Determine which options page was saved
    $options_page = str_replace('options_', '', $post_id);
    if ($options_page === 'options') {
      $options_page = 'default';
    }

    $this->logger->info('ACF Options updated', [
      'details' => [
        'options_page' => $options_page,
        'post_id' => $post_id,
      ],
    ]);
  }

  /**
   * Log when a WordPress option is updated.
   *
   * @param string $option Option name.
   * @param mixed $old_value The old value.
   * @param mixed $value The new value.
   */
  public function logOptionUpdate(string $option, mixed $old_value, mixed $value): void
  {
    $this->logger->info('Option updated', [
      'details' => [
        'option_name' => $option,
        'changed' => $old_value !== $value,
      ],
    ]);
  }
}

// Initialize the logger
if (defined('WP_DEBUG') && WP_DEBUG) {
  new AuditLogger();
}
