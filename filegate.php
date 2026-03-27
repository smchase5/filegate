<?php
/**
 * Plugin Name: FileGate - Control WordPress Upload Types
 * Plugin URI: https://frontierwp.com/filegate
 * Description: Safely enable additional WordPress upload types with a simple admin interface.
 * Version: 1.0.0
 * Author: FrontierWP
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: filegate
 *
 * @package FileGate
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FILEGATE_VERSION', '1.0.0');
define('FILEGATE_FILE', __FILE__);
define('FILEGATE_PATH', plugin_dir_path(__FILE__));
define('FILEGATE_URL', plugin_dir_url(__FILE__));

require_once FILEGATE_PATH . 'includes/class-settings.php';
require_once FILEGATE_PATH . 'includes/class-mime-manager.php';
require_once FILEGATE_PATH . 'includes/class-svg-handler.php';

/**
 * Main plugin bootstrap.
 */
class FileGate
{
    /**
     * Instance.
     *
     * @var FileGate|null
     */
    private static $instance = null;

    /**
     * Settings service.
     *
     * @var FileGate_Settings
     */
    private $settings;

    /**
     * MIME manager.
     *
     * @var FileGate_Mime_Manager
     */
    private $mime_manager;

    /**
     * SVG handler.
     *
     * @var FileGate_SVG_Handler
     */
    private $svg_handler;

    /**
     * Get singleton instance.
     *
     * @return FileGate
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        $this->settings     = new FileGate_Settings();
        $this->mime_manager = new FileGate_Mime_Manager($this->settings);
        $this->svg_handler  = new FileGate_SVG_Handler($this->settings);

        $this->settings->set_mime_manager($this->mime_manager);
        $this->settings->set_svg_handler($this->svg_handler);
    }

    /**
     * Load translations.
     *
     * @return void
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('filegate', false, dirname(plugin_basename(FILEGATE_FILE)) . '/languages');
    }
}

FileGate::instance();
