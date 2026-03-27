<?php
/**
 * MIME manager.
 *
 * @package FileGate
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the plugin's managed MIME types.
 */
class FileGate_Mime_Manager
{
    /**
     * Settings service.
     *
     * @var FileGate_Settings
     */
    private $settings;

    /**
     * Constructor.
     *
     * @param FileGate_Settings $settings Settings service.
     */
    public function __construct(FileGate_Settings $settings)
    {
        $this->settings = $settings;

        add_filter('upload_mimes', array($this, 'filter_upload_mimes'), 20, 2);
    }

    /**
     * Built-in catalog.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function get_builtin_types()
    {
        return array(
            'svg'  => array(
                'label'       => __('SVG', 'filegate'),
                'ext'         => 'svg',
                'mime'        => 'image/svg+xml',
                'description' => __('Scalable vector graphics for logos and simple illustrations. Enabled only with sanitization.', 'filegate'),
                'recommended' => false,
                'risky'       => true,
            ),
            'webp' => array(
                'label'       => __('WebP', 'filegate'),
                'ext'         => 'webp',
                'mime'        => 'image/webp',
                'description' => __('Modern image format with strong compression and broad browser support.', 'filegate'),
                'recommended' => true,
                'risky'       => false,
            ),
            'avif' => array(
                'label'       => __('AVIF', 'filegate'),
                'ext'         => 'avif',
                'mime'        => 'image/avif',
                'description' => __('High-efficiency image format for smaller files and better quality.', 'filegate'),
                'recommended' => true,
                'risky'       => false,
            ),
            'json' => array(
                'label'       => __('JSON', 'filegate'),
                'ext'         => 'json',
                'mime'        => 'application/json',
                'description' => __('Useful for configuration files, exports, and structured content data.', 'filegate'),
                'recommended' => true,
                'risky'       => false,
            ),
            'csv'  => array(
                'label'       => __('CSV', 'filegate'),
                'ext'         => 'csv',
                'mime'        => 'text/csv',
                'description' => __('Spreadsheet-friendly format for imports, exports, and reporting.', 'filegate'),
                'recommended' => true,
                'risky'       => false,
            ),
            'ico'  => array(
                'label'       => __('ICO', 'filegate'),
                'ext'         => 'ico',
                'mime'        => 'image/x-icon',
                'description' => __('Classic favicon format often used for browser and app icons.', 'filegate'),
                'recommended' => true,
                'risky'       => false,
            ),
            'heic' => array(
                'label'       => __('HEIC', 'filegate'),
                'ext'         => 'heic',
                'mime'        => 'image/heic',
                'description' => __('Apple photo format that may need theme or browser support checks.', 'filegate'),
                'recommended' => false,
                'risky'       => false,
            ),
            'heif' => array(
                'label'       => __('HEIF', 'filegate'),
                'ext'         => 'heif',
                'mime'        => 'image/heif',
                'description' => __('Container image format related to HEIC with limited workflow support.', 'filegate'),
                'recommended' => false,
                'risky'       => false,
            ),
        );
    }

    /**
     * Recommended built-in types.
     *
     * @return string[]
     */
    public static function get_recommended_keys()
    {
        return array_keys(
            array_filter(
                self::get_builtin_types(),
                static function ($type) {
                    return !empty($type['recommended']);
                }
            )
        );
    }

    /**
     * Add plugin-managed mimes.
     *
     * @param array $mimes Current MIME map.
     * @return array
     */
    public function filter_upload_mimes($mimes, $user = null)
    {
        $types = $this->get_effective_types();

        foreach ($types as $type) {
            $mimes[$type['ext']] = $type['mime'];
        }

        return $mimes;
    }

    /**
     * Get the plugin's effective types.
     *
     * @return array<int,array<string,string>>
     */
    public function get_effective_types()
    {
        $settings = $this->settings->get_settings();
        $builtins = self::get_builtin_types();
        $types    = array();

        foreach ($builtins as $key => $type) {
            if ('svg' === $key) {
                if (!empty($settings['svg_enabled'])) {
                    $types[] = array(
                        'key'   => $key,
                        'ext'   => $type['ext'],
                        'mime'  => $type['mime'],
                        'label' => $type['label'],
                    );
                }

                continue;
            }

            if (!empty($settings['enabled_types'][$key])) {
                $types[] = array(
                    'key'   => $key,
                    'ext'   => $type['ext'],
                    'mime'  => $type['mime'],
                    'label' => $type['label'],
                );
            }
        }

        foreach ($settings['custom_types'] as $custom_type) {
            $types[] = array(
                'key'   => 'custom:' . $custom_type['ext'],
                'ext'   => $custom_type['ext'],
                'mime'  => $custom_type['mime'],
                'label' => !empty($custom_type['label']) ? $custom_type['label'] : strtoupper($custom_type['ext']),
            );
        }

        /**
         * Filter FileGate-managed types before they are merged into upload_mimes.
         *
         * @param array $types    Managed types.
         * @param array $settings Parsed settings.
         */
        return apply_filters('filegate_allowed_types', $types, $settings);
    }

    /**
     * Report settings-page compatibility notes.
     *
     * @return string[]
     */
    public function get_compatibility_messages()
    {
        $settings  = $this->settings->get_settings();
        $builtins  = self::get_builtin_types();
        $effective = apply_filters('upload_mimes', array(), wp_get_current_user());
        $messages  = array();

        foreach ($builtins as $key => $type) {
            $enabled = ('svg' === $key) ? !empty($settings['svg_enabled']) : !empty($settings['enabled_types'][$key]);

            if (!$enabled && isset($effective[$type['ext']])) {
                $messages[] = sprintf(
                    /* translators: 1: file extension, 2: mime type */
                    __('Another plugin or custom code currently allows .%1$s uploads (%2$s), even though FileGate has it turned off.', 'filegate'),
                    $type['ext'],
                    $effective[$type['ext']]
                );
            }

            if ($enabled && isset($effective[$type['ext']]) && $effective[$type['ext']] !== $type['mime']) {
                $messages[] = sprintf(
                    /* translators: 1: file extension, 2: expected mime type, 3: actual mime type */
                    __('The .%1$s upload rule is being altered elsewhere. FileGate expects %2$s, but WordPress is currently using %3$s.', 'filegate'),
                    $type['ext'],
                    $type['mime'],
                    $effective[$type['ext']]
                );
            }
        }

        foreach ($settings['custom_types'] as $custom_type) {
            if (isset($effective[$custom_type['ext']]) && $effective[$custom_type['ext']] !== $custom_type['mime']) {
                $messages[] = sprintf(
                    /* translators: 1: file extension, 2: expected mime type, 3: actual mime type */
                    __('Your custom .%1$s rule is being altered elsewhere. FileGate expects %2$s, but WordPress is currently using %3$s.', 'filegate'),
                    $custom_type['ext'],
                    $custom_type['mime'],
                    $effective[$custom_type['ext']]
                );
            }
        }

        return array_values(array_unique($messages));
    }
}
