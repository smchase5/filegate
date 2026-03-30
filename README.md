# FileGate - Control Upload Types

Contributors: smchase5  
Tags: uploads, mime types, svg, media library, file uploads  
Requires at least: 6.7  
Tested up to: 6.9  
Stable tag: 1.0.0  
License: GPLv3 or later  
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Safely enable extra upload types with presets, custom MIME rules, and built-in SVG sanitization.

## Description

FileGate gives site owners a clean settings screen for managing additional upload formats without touching code.

Features include:

- Toggle common upload types from `Settings -> FileGate`
- Enable built-in support for `SVG`, `WebP`, `AVIF`, `JSON`, `CSV`, `ICO`, `HEIC`, and `HEIF`
- Add custom file type rules with an extension, MIME type, and optional label
- Validate custom rules and skip invalid or duplicate entries
- Apply quick-start presets for common use cases
- Detect obvious MIME conflicts caused by other plugins or custom code
- Sanitize SVG uploads before WordPress stores them
- Load admin assets only on the FileGate settings page

SVG uploads are only allowed when FileGate explicitly enables them. When enabled, FileGate sanitizes SVG files by removing risky content such as script tags, foreignObject nodes, inline event handlers, and dangerous link-style attributes.

## Installation

1. Upload the `filegate` folder to `/wp-content/plugins/`, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Go to `Settings -> FileGate`.
4. Turn on the formats you want to allow and save your changes.

## Frequently Asked Questions

### What file types can FileGate enable?

FileGate includes built-in support for SVG, WebP, AVIF, JSON, CSV, ICO, HEIC, and HEIF, and it also supports custom extension and MIME pairs.

### Is SVG support safe?

SVG can be risky because it may contain active content. FileGate reduces obvious XSS risk by sanitizing SVG uploads whenever SVG support is enabled.

### Can I add my own custom file types?

Yes. You can add a custom extension, MIME type, and optional label from the plugin settings page.

## Changelog

### 1.0.0

- Initial release
