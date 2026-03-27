<?php
/**
 * SVG upload handling.
 *
 * @package FileGate
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitizes SVG uploads when enabled.
 */
class FileGate_SVG_Handler
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

        add_filter('wp_handle_upload_prefilter', array($this, 'sanitize_svg_upload'));
    }

    /**
     * Sanitize SVG uploads before WordPress moves them.
     *
     * @param array $file Upload file data.
     * @return array
     */
    public function sanitize_svg_upload($file)
    {
        $settings = $this->settings->get_settings();

        if (empty($settings['svg_enabled']) || empty($settings['svg_sanitize'])) {
            return $file;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ('svg' !== $extension) {
            return $file;
        }

        if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            $file['error'] = __('FileGate could not inspect this SVG upload.', 'filegate');
            return $file;
        }

        $result = $this->sanitize_file($file['tmp_name']);

        if (is_wp_error($result)) {
            $file['error'] = $result->get_error_message();
        }

        return $file;
    }

    /**
     * Sanitize an SVG temp file.
     *
     * @param string $path Temporary file path.
     * @return true|WP_Error
     */
    public function sanitize_file($path)
    {
        $contents = file_get_contents($path);

        if (false === $contents || '' === trim($contents)) {
            return new WP_Error('filegate_svg_empty', __('This SVG file is empty or unreadable.', 'filegate'));
        }

        $sanitized = class_exists('DOMDocument')
            ? $this->sanitize_with_dom($contents)
            : $this->sanitize_with_fallback($contents);

        if (is_wp_error($sanitized)) {
            return $sanitized;
        }

        if (false === file_put_contents($path, $sanitized)) {
            return new WP_Error('filegate_svg_write_failed', __('FileGate could not write the sanitized SVG back to disk.', 'filegate'));
        }

        return true;
    }

    /**
     * DOM-based sanitization.
     *
     * @param string $contents SVG contents.
     * @return string|WP_Error
     */
    private function sanitize_with_dom($contents)
    {
        $previous = libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $loaded   = $document->loadXML($contents, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        if (!$loaded || !$document->documentElement || 'svg' !== strtolower($document->documentElement->localName)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return new WP_Error('filegate_svg_invalid', __('This SVG file is malformed and could not be sanitized safely.', 'filegate'));
        }

        $xpath = new DOMXPath($document);

        foreach ($xpath->query('//*[local-name()="script" or local-name()="foreignObject"]') as $node) {
            $node->parentNode->removeChild($node);
        }

        foreach ($xpath->query('//*') as $element) {
            if (!$element->hasAttributes()) {
                continue;
            }

            $remove = array();

            foreach ($element->attributes as $attribute) {
                $name  = strtolower($attribute->nodeName);
                $value = trim($attribute->nodeValue);

                if (0 === strpos($name, 'on')) {
                    $remove[] = $attribute->nodeName;
                    continue;
                }

                if (
                    in_array($name, array('href', 'xlink:href', 'src', 'style'), true) &&
                    preg_match('/(?:javascript:|data:\s*text\/html)/i', $value)
                ) {
                    $remove[] = $attribute->nodeName;
                }
            }

            foreach ($remove as $attribute_name) {
                $element->removeAttribute($attribute_name);
            }
        }

        $svg = $document->saveXML($document->documentElement);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$svg || false === strpos($svg, '<svg')) {
            return new WP_Error('filegate_svg_empty_result', __('This SVG file became invalid during sanitization and was rejected.', 'filegate'));
        }

        return $svg;
    }

    /**
     * Conservative fallback if DOM is unavailable.
     *
     * @param string $contents SVG contents.
     * @return string|WP_Error
     */
    private function sanitize_with_fallback($contents)
    {
        if (!preg_match('/<svg[\s>]/i', $contents)) {
            return new WP_Error('filegate_svg_invalid_root', __('This file does not look like a valid SVG image.', 'filegate'));
        }

        $contents = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $contents);
        $contents = preg_replace('/<foreignObject\b[^>]*>.*?<\/foreignObject>/is', '', $contents);
        $contents = preg_replace('/\s+on[a-z-]+\s*=\s*("|\').*?\1/is', '', $contents);
        $contents = preg_replace('/\s+(href|xlink:href|src|style)\s*=\s*("|\')(?:javascript:|data:\s*text\/html).*?\2/is', '', $contents);

        if (empty($contents) || !preg_match('/<svg[\s>]/i', $contents)) {
            return new WP_Error('filegate_svg_invalid_result', __('This SVG file could not be sanitized safely.', 'filegate'));
        }

        return trim($contents);
    }
}
