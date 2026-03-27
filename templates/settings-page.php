<?php
/**
 * FileGate settings page template.
 *
 * @package FileGate
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap filegate-admin">
    <div class="filegate-page-title">
        <span class="filegate-page-title__logo" aria-hidden="true">
            <img src="<?php echo esc_url(FILEGATE_URL . 'assets/filegate-logo.png'); ?>" alt="">
        </span>
        <h1><?php esc_html_e('FileGate', 'filegate'); ?></h1>
        <span class="filegate-save-indicator is-saved" id="filegate-save-indicator">
            <?php esc_html_e('All changes saved', 'filegate'); ?>
        </span>
    </div>
    <p class="filegate-intro">
        <?php esc_html_e('Choose which extra file types your site can upload, keep risky formats behind clear safeguards, and manage custom rules without touching code.', 'filegate'); ?>
    </p>

    <div class="filegate-toast-stack" id="filegate-toast-stack" aria-live="polite" aria-atomic="true"></div>

    <?php settings_errors(FileGate_Settings::OPTION_NAME); ?>

    <form method="post" action="options.php" id="filegate-settings-form">
        <?php settings_fields('filegate_settings_group'); ?>
        <input type="hidden" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[_action]" id="filegate-action" value="">

        <div class="filegate-layout">
            <section class="filegate-panel">
                <div class="filegate-panel-heading">
                    <div>
                        <h2><?php esc_html_e('Common File Types', 'filegate'); ?></h2>
                        <p><?php esc_html_e('Turn on the formats your team needs most. FileGate only adds rules for the types you enable.', 'filegate'); ?></p>
                    </div>
                    <button type="button" class="button button-secondary" id="filegate-apply-preset">
                        <?php esc_html_e('Apply Safe Preset', 'filegate'); ?>
                    </button>
                </div>

                <div class="filegate-card-grid">
                    <?php foreach ($builtin_types as $key => $type) : ?>
                        <?php
                        $enabled = ('svg' === $key) ? !empty($settings['svg_enabled']) : !empty($settings['enabled_types'][$key]);
                        ?>
                        <article class="filegate-card <?php echo $enabled ? 'is-enabled' : ''; ?>">
                            <div class="filegate-card-top">
                                <div>
                                    <h3><?php echo esc_html($type['label']); ?></h3>
                                    <p><?php echo esc_html($type['description']); ?></p>
                                </div>
                                <label class="filegate-switch">
                                    <?php if ('svg' === $key) : ?>
                                        <input type="checkbox" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[svg_enabled]" value="1" <?php checked($enabled); ?>>
                                    <?php else : ?>
                                        <input type="checkbox" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[enabled_types][<?php echo esc_attr($key); ?>]" value="1" <?php checked($enabled); ?>>
                                    <?php endif; ?>
                                    <span class="filegate-slider" aria-hidden="true"></span>
                                    <span class="screen-reader-text">
                                        <?php
                                        printf(
                                            /* translators: %s: file type label */
                                            esc_html__('Enable %s uploads', 'filegate'),
                                            esc_html($type['label'])
                                        );
                                        ?>
                                    </span>
                                </label>
                            </div>

                            <div class="filegate-card-meta">
                                <span>.<?php echo esc_html($type['ext']); ?></span>
                                <code><?php echo esc_html($type['mime']); ?></code>
                            </div>

                            <div class="filegate-card-flags">
                                <?php if (!empty($type['recommended'])) : ?>
                                    <span class="filegate-badge is-recommended"><?php esc_html_e('Recommended', 'filegate'); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($type['risky'])) : ?>
                                    <span class="filegate-badge is-caution"><?php esc_html_e('Sanitized', 'filegate'); ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="filegate-panel">
                <div class="filegate-panel-heading">
                    <div>
                        <h2><?php esc_html_e('Custom File Types', 'filegate'); ?></h2>
                        <p><?php esc_html_e('Add your own extension and MIME combinations. FileGate validates each row and skips duplicates.', 'filegate'); ?></p>
                    </div>
                    <button type="button" class="button button-secondary" id="filegate-add-row">
                        <?php esc_html_e('Add Custom Type', 'filegate'); ?>
                    </button>
                </div>

                <div class="filegate-table-wrap">
                    <table class="widefat striped filegate-custom-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Extension', 'filegate'); ?></th>
                                <th><?php esc_html_e('MIME Type', 'filegate'); ?></th>
                                <th><?php esc_html_e('Label', 'filegate'); ?></th>
                                <th><?php esc_html_e('Action', 'filegate'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="filegate-custom-types">
                            <?php if (!empty($settings['custom_types'])) : ?>
                                <?php foreach ($settings['custom_types'] as $index => $custom_type) : ?>
                                    <tr class="filegate-custom-row">
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[custom_types][<?php echo esc_attr((string) $index); ?>][ext]" value="<?php echo esc_attr($custom_type['ext']); ?>" class="regular-text filegate-ext-input" placeholder="dwg">
                                            <p class="filegate-inline-error" hidden></p>
                                        </td>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[custom_types][<?php echo esc_attr((string) $index); ?>][mime]" value="<?php echo esc_attr($custom_type['mime']); ?>" class="regular-text filegate-mime-input" placeholder="image/vnd.dwg">
                                            <p class="filegate-inline-error" hidden></p>
                                        </td>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[custom_types][<?php echo esc_attr((string) $index); ?>][label]" value="<?php echo esc_attr($custom_type['label']); ?>" class="regular-text" placeholder="<?php esc_attr_e('Optional label', 'filegate'); ?>">
                                        </td>
                                        <td>
                                            <button type="button" class="button-link-delete filegate-remove-row"><?php esc_html_e('Remove', 'filegate'); ?></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr class="filegate-empty-state">
                                    <td colspan="4"><?php esc_html_e('No custom types yet. Add one when you need a format outside the built-in list.', 'filegate'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="filegate-panel">
                <div class="filegate-panel-heading">
                    <div>
                        <h2><?php esc_html_e('Advanced', 'filegate'); ?></h2>
                        <p><?php esc_html_e('Safety notes, compatibility hints, and quick actions for returning to a known-good setup.', 'filegate'); ?></p>
                    </div>
                </div>

                <div class="filegate-advanced-grid">
                    <div class="filegate-callout">
                        <h3><?php esc_html_e('SVG Safety', 'filegate'); ?></h3>
                        <p><?php esc_html_e('When SVG uploads are enabled, FileGate sanitizes every SVG file before WordPress stores it. This protection is always required in v1.', 'filegate'); ?></p>
                        <label class="filegate-locked-check">
                            <input type="checkbox" checked disabled>
                            <span><?php esc_html_e('Basic SVG sanitization is locked on when SVG uploads are enabled.', 'filegate'); ?></span>
                        </label>
                    </div>

                    <div class="filegate-callout">
                        <h3><?php esc_html_e('Quick Actions', 'filegate'); ?></h3>
                        <p><?php esc_html_e('Safe Preset turns on lower-risk formats only. Reset returns FileGate to a clean baseline without removing the plugin.', 'filegate'); ?></p>
                        <div class="filegate-action-row">
                            <button type="button" class="button" id="filegate-reset-defaults">
                                <?php esc_html_e('Reset to Defaults', 'filegate'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($compatibility_messages)) : ?>
                    <div class="filegate-compatibility">
                        <h3><?php esc_html_e('Compatibility Notes', 'filegate'); ?></h3>
                        <ul>
                            <?php foreach ($compatibility_messages as $message) : ?>
                                <li><?php echo esc_html($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else : ?>
                    <div class="filegate-compatibility is-clear">
                        <h3><?php esc_html_e('Compatibility Notes', 'filegate'); ?></h3>
                        <p><?php esc_html_e('FileGate did not detect any obvious MIME conflicts on this site.', 'filegate'); ?></p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <?php submit_button(__('Save Changes', 'filegate')); ?>
    </form>

    <section class="filegate-brand-panel">
        <div class="filegate-brand-copy">
            <p class="filegate-brand-eyebrow"><?php esc_html_e('Built by FrontierWP', 'filegate'); ?></p>
            <h2><?php esc_html_e('Need help beyond upload settings?', 'filegate'); ?></h2>
            <p>
                <?php esc_html_e('FileGate is free and designed to solve this one job well. If you need help with WordPress maintenance, security, performance, or custom development, FrontierWP is here for that too.', 'filegate'); ?>
            </p>
        </div>
        <div class="filegate-brand-actions">
            <a class="button button-secondary" href="https://frontierwp.com/filegate" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Docs & Support', 'filegate'); ?>
            </a>
            <a class="button button-primary" href="https://frontierwp.com/contact" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Talk to FrontierWP', 'filegate'); ?>
            </a>
        </div>
    </section>
</div>
