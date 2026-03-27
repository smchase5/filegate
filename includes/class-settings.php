<?php
/**
 * Plugin settings.
 *
 * @package FileGate
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin settings controller.
 */
class FileGate_Settings
{
    /**
     * Option name.
     */
    const OPTION_NAME = 'filegate_settings';

    /**
     * Page slug.
     */
    const PAGE_SLUG = 'filegate';

    /**
     * Cached settings.
     *
     * @var array<string,mixed>|null
     */
    private $settings = null;

    /**
     * MIME manager.
     *
     * @var FileGate_Mime_Manager|null
     */
    private $mime_manager = null;

    /**
     * SVG handler.
     *
     * @var FileGate_SVG_Handler|null
     */
    private $svg_handler = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_filegate_save_settings', array($this, 'ajax_save_settings'));
    }

    /**
     * Set MIME manager dependency.
     *
     * @param FileGate_Mime_Manager $mime_manager MIME manager.
     * @return void
     */
    public function set_mime_manager(FileGate_Mime_Manager $mime_manager)
    {
        $this->mime_manager = $mime_manager;
    }

    /**
     * Set SVG handler dependency.
     *
     * @param FileGate_SVG_Handler $svg_handler SVG handler.
     * @return void
     */
    public function set_svg_handler(FileGate_SVG_Handler $svg_handler)
    {
        $this->svg_handler = $svg_handler;
    }

    /**
     * Add admin page.
     *
     * @return void
     */
    public function add_settings_page()
    {
        add_options_page(
            __('FileGate', 'filegate'),
            __('FileGate', 'filegate'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin setting.
     *
     * @return void
     */
    public function register_settings()
    {
        register_setting(
            'filegate_settings_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default'           => $this->get_defaults(),
            )
        );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin hook.
     * @return void
     */
    public function enqueue_admin_assets($hook)
    {
        if ('settings_page_' . self::PAGE_SLUG !== $hook) {
            return;
        }

        $css_path = FILEGATE_PATH . 'assets/admin.css';
        $js_path  = FILEGATE_PATH . 'assets/admin.js';
        $version  = FILEGATE_VERSION;

        wp_enqueue_style(
            'filegate-admin',
            FILEGATE_URL . 'assets/admin.css',
            array(),
            file_exists($css_path) ? (string) filemtime($css_path) : $version
        );

        wp_enqueue_script(
            'filegate-admin',
            FILEGATE_URL . 'assets/admin.js',
            array(),
            file_exists($js_path) ? (string) filemtime($js_path) : $version,
            true
        );

        wp_localize_script(
            'filegate-admin',
            'filegateAdmin',
            array(
                'ajaxUrl'             => admin_url('admin-ajax.php'),
                'saveNonce'           => wp_create_nonce('filegate_save_settings'),
                'reservedExtensions' => $this->get_reserved_extensions(),
                'recommendedKeys'    => FileGate_Mime_Manager::get_recommended_keys(),
                'strings'            => array(
                    'invalidExtension' => __('Use lowercase letters and numbers only, with optional single separators like dash, dot, or underscore.', 'filegate'),
                    'invalidMime'      => __('Use a valid MIME type like image/svg+xml or application/json.', 'filegate'),
                    'duplicateExt'     => __('This extension is already in use.', 'filegate'),
                    'removeRow'        => __('Remove', 'filegate'),
                    'resetConfirm'     => __('Reset FileGate back to its default settings?', 'filegate'),
                    'saving'           => __('Saving...', 'filegate'),
                    'saved'            => __('Settings saved.', 'filegate'),
                    'savedWithNotes'   => __('Settings saved with a few notes.', 'filegate'),
                    'resetDone'        => __('FileGate was reset to its defaults.', 'filegate'),
                    'saveFailed'       => __('FileGate could not save your changes.', 'filegate'),
                    'networkError'     => __('A network error prevented FileGate from saving.', 'filegate'),
                    'allSaved'         => __('All changes saved', 'filegate'),
                    'unsavedChanges'   => __('Unsaved changes', 'filegate'),
                    'savingStatus'     => __('Saving changes...', 'filegate'),
                    'leaveWarning'     => __('You have unsaved FileGate changes. Are you sure you want to leave this page?', 'filegate'),
                ),
            )
        );
    }

    /**
     * AJAX save handler.
     *
     * @return void
     */
    public function ajax_save_settings()
    {
        check_ajax_referer('filegate_save_settings', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                array(
                    'message' => __('You do not have permission to manage FileGate.', 'filegate'),
                ),
                403
            );
        }

        $raw_settings = isset($_POST[ self::OPTION_NAME ]) ? wp_unslash($_POST[ self::OPTION_NAME ]) : array();

        global $wp_settings_errors;
        $wp_settings_errors = array();

        $settings = $this->sanitize_settings($raw_settings);
        update_option(self::OPTION_NAME, $settings);

        $notices = get_settings_errors(self::OPTION_NAME);
        $action  = !empty($raw_settings['_action']) ? sanitize_text_field($raw_settings['_action']) : 'save';
        $type    = empty($notices) ? 'success' : 'warning';

        wp_send_json_success(
            array(
                'action'  => $action,
                'message' => $this->get_ajax_success_message($action, $type),
                'type'    => $type,
                'notices' => $this->prepare_notices_for_response($notices),
                'settings' => $settings,
            )
        );
    }

    /**
     * Render settings page.
     *
     * @return void
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage FileGate.', 'filegate'));
        }

        $settings               = $this->get_settings();
        $builtin_types          = FileGate_Mime_Manager::get_builtin_types();
        $recommended_keys       = FileGate_Mime_Manager::get_recommended_keys();
        $compatibility_messages = $this->mime_manager ? $this->mime_manager->get_compatibility_messages() : array();

        require FILEGATE_PATH . 'templates/settings-page.php';
    }

    /**
     * Get defaults.
     *
     * @return array<string,mixed>
     */
    public function get_defaults()
    {
        $enabled = array();

        foreach (FileGate_Mime_Manager::get_builtin_types() as $key => $type) {
            if ('svg' === $key) {
                continue;
            }

            $enabled[$key] = false;
        }

        return array(
            'enabled_types' => $enabled,
            'custom_types'  => array(),
            'svg_enabled'   => false,
            'svg_sanitize'  => false,
        );
    }

    /**
     * Get parsed settings with request-level cache.
     *
     * @return array<string,mixed>
     */
    public function get_settings()
    {
        if (null !== $this->settings) {
            return $this->settings;
        }

        $raw            = get_option(self::OPTION_NAME, array());
        $this->settings = $this->normalize_settings($raw, false);

        return $this->settings;
    }

    /**
     * Sanitize and normalize settings on save.
     *
     * @param mixed $input Raw option payload.
     * @return array<string,mixed>
     */
    public function sanitize_settings($input)
    {
        $normalized = $this->normalize_settings($input, true);

        $this->settings = $normalized;

        return $normalized;
    }

    /**
     * Normalize settings into the saved shape.
     *
     * @param mixed $input         Raw value.
     * @param bool  $report_errors Whether to surface admin notices.
     * @return array<string,mixed>
     */
    private function normalize_settings($input, $report_errors)
    {
        $input    = is_array($input) ? $input : array();
        $defaults = $this->get_defaults();

        if (!empty($input['_action']) && 'reset' === $input['_action']) {
            if ($report_errors) {
                add_settings_error(
                    self::OPTION_NAME,
                    'filegate-reset',
                    __('FileGate settings were reset to their defaults.', 'filegate'),
                    'updated'
                );
            }

            return $defaults;
        }

        $settings = $defaults;

        foreach ($defaults['enabled_types'] as $key => $enabled) {
            $settings['enabled_types'][$key] = !empty($input['enabled_types'][$key]);
        }

        $settings['svg_enabled']  = !empty($input['svg_enabled']);
        $settings['svg_sanitize'] = !empty($settings['svg_enabled']);

        $reserved_extensions = $this->get_reserved_extensions();
        $seen_extensions     = array();
        $custom_types        = isset($input['custom_types']) && is_array($input['custom_types']) ? $input['custom_types'] : array();

        foreach ($custom_types as $index => $row) {
            $row       = is_array($row) ? $row : array();
            $extension = $this->normalize_extension(isset($row['ext']) ? $row['ext'] : '');
            $mime      = isset($row['mime']) ? sanitize_text_field($row['mime']) : '';
            $label     = isset($row['label']) ? sanitize_text_field($row['label']) : '';

            if ('' === $extension && '' === $mime && '' === $label) {
                continue;
            }

            if (!$this->is_valid_extension($extension)) {
                if ($report_errors) {
                    add_settings_error(
                        self::OPTION_NAME,
                        'filegate-invalid-ext-' . $index,
                        sprintf(
                            /* translators: %s: invalid extension */
                            __('Skipped custom type because the extension "%s" is invalid.', 'filegate'),
                            isset($row['ext']) ? sanitize_text_field($row['ext']) : ''
                        )
                    );
                }
                continue;
            }

            if (!$this->is_valid_mime($mime)) {
                if ($report_errors) {
                    add_settings_error(
                        self::OPTION_NAME,
                        'filegate-invalid-mime-' . $index,
                        sprintf(
                            /* translators: 1: extension, 2: mime type */
                            __('Skipped custom type .%1$s because the MIME type "%2$s" is invalid.', 'filegate'),
                            $extension,
                            $mime
                        )
                    );
                }
                continue;
            }

            if (isset($reserved_extensions[$extension])) {
                if ($report_errors) {
                    add_settings_error(
                        self::OPTION_NAME,
                        'filegate-duplicate-builtin-' . $index,
                        sprintf(
                            /* translators: %s: file extension */
                            __('Skipped custom type .%s because that extension is already managed by FileGate.', 'filegate'),
                            $extension
                        )
                    );
                }
                continue;
            }

            if (isset($seen_extensions[$extension])) {
                if ($report_errors) {
                    add_settings_error(
                        self::OPTION_NAME,
                        'filegate-duplicate-custom-' . $index,
                        sprintf(
                            /* translators: %s: file extension */
                            __('Skipped duplicate custom type for .%s.', 'filegate'),
                            $extension
                        )
                    );
                }
                continue;
            }

            $seen_extensions[$extension] = true;

            $settings['custom_types'][] = array(
                'ext'   => $extension,
                'mime'  => strtolower($mime),
                'label' => $label,
            );
        }

        return $settings;
    }

    /**
     * Build a human-readable AJAX success message.
     *
     * @param string $action Save action.
     * @param string $type   Toast type.
     * @return string
     */
    private function get_ajax_success_message($action, $type)
    {
        if ('reset' === $action) {
            return __('FileGate was reset to its defaults.', 'filegate');
        }

        if ('warning' === $type) {
            return __('Settings saved with a few notes.', 'filegate');
        }

        return __('Settings saved.', 'filegate');
    }

    /**
     * Simplify settings errors for the browser.
     *
     * @param array<int,array<string,string>> $notices Raw notices.
     * @return array<int,array<string,string>>
     */
    private function prepare_notices_for_response($notices)
    {
        $prepared = array();

        foreach ($notices as $notice) {
            $prepared[] = array(
                'message' => isset($notice['message']) ? wp_strip_all_tags($notice['message']) : '',
                'type'    => isset($notice['type']) ? sanitize_key($notice['type']) : 'info',
            );
        }

        return $prepared;
    }

    /**
     * Normalize an extension.
     *
     * @param string $extension Raw extension.
     * @return string
     */
    private function normalize_extension($extension)
    {
        $extension = strtolower(sanitize_text_field($extension));
        return ltrim($extension, '.');
    }

    /**
     * Validate extension format.
     *
     * @param string $extension Extension.
     * @return bool
     */
    public function is_valid_extension($extension)
    {
        return 1 === preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $extension);
    }

    /**
     * Validate MIME type.
     *
     * @param string $mime MIME type.
     * @return bool
     */
    public function is_valid_mime($mime)
    {
        return 1 === preg_match('/^[a-z0-9!#$&^_.+-]+\/[a-z0-9!#$&^_.+-]+$/i', $mime);
    }

    /**
     * Built-in extensions reserved by FileGate.
     *
     * @return array<string,bool>
     */
    private function get_reserved_extensions()
    {
        $extensions = array();

        foreach (FileGate_Mime_Manager::get_builtin_types() as $type) {
            $extensions[$type['ext']] = true;
        }

        return $extensions;
    }
}
