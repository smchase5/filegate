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

        <?php if ($is_first_run) : ?>
            <section class="filegate-onboarding">
                <div class="filegate-onboarding__copy">
                    <p class="filegate-onboarding__eyebrow"><?php esc_html_e('Welcome to FileGate', 'filegate'); ?></p>
                    <h2><?php esc_html_e('Start with a preset, then fine-tune only what your team needs.', 'filegate'); ?></h2>
                    <p>
                        <?php esc_html_e('Most sites do best with lower-risk formats first. You can always add more upload types later, and FileGate keeps SVG uploads behind sanitization when they are enabled.', 'filegate'); ?>
                    </p>
                </div>
                <div class="filegate-onboarding__tips">
                    <div class="filegate-onboarding-tip">
                        <strong><?php esc_html_e('Suggested start', 'filegate'); ?></strong>
                        <p><?php esc_html_e('Choose Safe Defaults if you want a clean, low-risk baseline in one click.', 'filegate'); ?></p>
                    </div>
                    <div class="filegate-onboarding-tip">
                        <strong><?php esc_html_e('SVG note', 'filegate'); ?></strong>
                        <p><?php esc_html_e('SVG files can contain active content. FileGate sanitizes them automatically whenever SVG uploads are turned on.', 'filegate'); ?></p>
                    </div>
                    <div class="filegate-onboarding-tip">
                        <strong><?php esc_html_e('Custom rules', 'filegate'); ?></strong>
                        <p><?php esc_html_e('Only add custom extensions when you know the exact file extension and MIME type you need.', 'filegate'); ?></p>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <div class="filegate-layout">
            <section class="filegate-panel">
                <div class="filegate-panel-heading">
                    <div>
                        <h2><?php esc_html_e('Common File Types', 'filegate'); ?></h2>
                        <p><?php esc_html_e('Turn on the formats your team needs most. FileGate only adds rules for the types you enable.', 'filegate'); ?></p>
                    </div>
                </div>

                <div class="filegate-preset-strip-heading">
                    <h3><?php esc_html_e('Quick Start Presets', 'filegate'); ?></h3>
                    <p><?php esc_html_e('Choose a preset to turn on a sensible group of upload types, then fine-tune anything you want below.', 'filegate'); ?></p>
                </div>

                <div class="filegate-preset-strip">
                    <?php foreach ($presets as $filegate_preset_key => $filegate_preset) : ?>
                        <article class="filegate-preset-card <?php echo 'safe' === $filegate_preset_key ? 'is-featured' : ''; ?>">
                            <div class="filegate-preset-card__top">
                                <div class="filegate-preset-card__title">
                                    <h3><?php echo esc_html($filegate_preset['label']); ?></h3>
                                    <?php if ('safe' === $filegate_preset_key) : ?>
                                        <span class="filegate-badge is-recommended"><?php esc_html_e('Recommended Start', 'filegate'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="button button-secondary filegate-preset-card__button" data-filegate-preset="<?php echo esc_attr($filegate_preset_key); ?>">
                                    <?php esc_html_e('Use Preset', 'filegate'); ?>
                                </button>
                            </div>
                            <p><?php echo esc_html($filegate_preset['description']); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="filegate-card-grid">
                    <?php foreach ($builtin_types as $filegate_type_key => $filegate_type) : ?>
                        <?php
                        $filegate_enabled = ('svg' === $filegate_type_key) ? !empty($settings['svg_enabled']) : !empty($settings['enabled_types'][$filegate_type_key]);
                        ?>
                        <article class="filegate-card <?php echo $filegate_enabled ? 'is-enabled' : ''; ?>">
                            <div class="filegate-card-top">
                                <div>
                                    <h3><?php echo esc_html($filegate_type['label']); ?></h3>
                                    <p><?php echo esc_html($filegate_type['description']); ?></p>
                                </div>
                                <label class="filegate-switch">
                                    <?php if ('svg' === $filegate_type_key) : ?>
                                        <input type="checkbox" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[svg_enabled]" value="1" <?php checked($filegate_enabled); ?>>
                                    <?php else : ?>
                                        <input type="checkbox" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[enabled_types][<?php echo esc_attr($filegate_type_key); ?>]" value="1" <?php checked($filegate_enabled); ?>>
                                    <?php endif; ?>
                                    <span class="filegate-slider" aria-hidden="true"></span>
                                    <span class="screen-reader-text">
                                        <?php
                                        printf(
                                            /* translators: %s: file type label */
                                            esc_html__('Enable %s uploads', 'filegate'),
                                            esc_html($filegate_type['label'])
                                        );
                                        ?>
                                    </span>
                                </label>
                            </div>

                            <div class="filegate-card-meta">
                                <span>.<?php echo esc_html($filegate_type['ext']); ?></span>
                                <code><?php echo esc_html($filegate_type['mime']); ?></code>
                            </div>

                            <div class="filegate-card-flags">
                                <?php if (!empty($filegate_type['recommended'])) : ?>
                                    <span class="filegate-badge is-recommended"><?php esc_html_e('Recommended', 'filegate'); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($filegate_type['risky'])) : ?>
                                    <span class="filegate-badge is-caution"><?php esc_html_e('Sanitized', 'filegate'); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ('svg' === $filegate_type_key) : ?>
                                <p class="filegate-card-note"><?php esc_html_e('SVGs can contain scripts and embedded content. FileGate keeps sanitization locked on to reduce obvious XSS risk.', 'filegate'); ?></p>
                            <?php elseif (in_array($filegate_type_key, array('heic', 'heif'), true)) : ?>
                                <p class="filegate-card-note"><?php esc_html_e('These formats are handy for upload intake, but front-end display support can vary by browser, editor, or theme workflow.', 'filegate'); ?></p>
                            <?php elseif ('json' === $filegate_type_key) : ?>
                                <p class="filegate-card-note"><?php esc_html_e('JSON uploads are useful for imports and exports. Allowing JSON here does not make the files executable.', 'filegate'); ?></p>
                            <?php endif; ?>
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

                <div class="filegate-inline-help">
                    <p><strong><?php esc_html_e('What is a MIME type?', 'filegate'); ?></strong> <?php esc_html_e('It is the format identifier WordPress uses to recognize a file, such as image/svg+xml or application/json.', 'filegate'); ?></p>
                    <p><strong><?php esc_html_e('Helpful tip:', 'filegate'); ?></strong> <?php esc_html_e('If you are not sure which MIME type to use, check the file format documentation before adding a custom rule.', 'filegate'); ?></p>
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
                                <?php foreach ($settings['custom_types'] as $filegate_index => $filegate_custom_type) : ?>
                                    <tr class="filegate-custom-row">
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[custom_types][<?php echo esc_attr((string) $filegate_index); ?>][ext]" value="<?php echo esc_attr($filegate_custom_type['ext']); ?>" class="regular-text filegate-ext-input" placeholder="dwg">
                                            <p class="filegate-inline-error" hidden></p>
                                        </td>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[custom_types][<?php echo esc_attr((string) $filegate_index); ?>][mime]" value="<?php echo esc_attr($filegate_custom_type['mime']); ?>" class="regular-text filegate-mime-input" placeholder="image/vnd.dwg">
                                            <p class="filegate-inline-error" hidden></p>
                                        </td>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(FileGate_Settings::OPTION_NAME); ?>[custom_types][<?php echo esc_attr((string) $filegate_index); ?>][label]" value="<?php echo esc_attr($filegate_custom_type['label']); ?>" class="regular-text" placeholder="<?php esc_attr_e('Optional label', 'filegate'); ?>">
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
                        <p><?php esc_html_e('Presets help you start faster, and reset returns FileGate to a clean baseline without removing the plugin.', 'filegate'); ?></p>
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
                            <?php foreach ($compatibility_messages as $filegate_message) : ?>
                                <li><?php echo esc_html($filegate_message); ?></li>
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
