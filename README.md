# FileGate

FileGate is a WordPress plugin for safely enabling additional upload types from a clean settings screen. It gives site owners a toggle-based UI for common formats, supports custom extension and MIME rules, and keeps SVG uploads behind built-in sanitization.

## Features

- Toggle common upload types from `Settings -> FileGate`
- Enable built-in support for `SVG`, `WebP`, `AVIF`, `JSON`, `CSV`, `ICO`, `HEIC`, and `HEIF`
- Add custom file type rules with an extension, MIME type, and optional label
- Validate custom rules and skip invalid or duplicate entries
- Apply a safe preset for lower-risk file formats
- Detect obvious MIME conflicts caused by other plugins or custom code
- Sanitize SVG uploads before WordPress stores them
- Load admin assets only on the FileGate settings page

## Built-In File Types

FileGate ships with these built-in options:

| Type | Extension | MIME Type | Notes |
| --- | --- | --- | --- |
| SVG | `svg` | `image/svg+xml` | Disabled by default and treated as higher risk |
| WebP | `webp` | `image/webp` | Recommended |
| AVIF | `avif` | `image/avif` | Recommended |
| JSON | `json` | `application/json` | Recommended |
| CSV | `csv` | `text/csv` | Recommended |
| ICO | `ico` | `image/x-icon` | Recommended |
| HEIC | `heic` | `image/heic` | Optional |
| HEIF | `heif` | `image/heif` | Optional |

## SVG Safety

SVG uploads are only allowed when FileGate explicitly enables them. In the current version, basic SVG sanitization is automatically locked on whenever SVG uploads are enabled.

During upload, FileGate sanitizes SVG files by removing risky content such as:

- `<script>` elements
- `<foreignObject>` elements
- inline event handler attributes like `onclick`
- dangerous `href`, `xlink:href`, `src`, or `style` values that try to inject script or HTML payloads

## Installation

1. Copy the `filegate` plugin folder into `wp-content/plugins/`.
2. Activate **FileGate - Control WordPress Upload Types** in the WordPress admin.
3. Go to `Settings -> FileGate`.
4. Turn on the file types you want to allow and save your changes.

## Usage

### Common File Types

Use the toggle cards to enable or disable FileGate's built-in formats. Recommended types are labeled in the interface, and the safe preset enables lower-risk formats in one click.

### Custom File Types

Add your own upload rules by entering:

- an extension like `dwg`
- a MIME type like `image/vnd.dwg`
- an optional admin label

FileGate validates each custom row before saving. Invalid entries, duplicates, and extensions already managed by FileGate are skipped.

### Compatibility Notes

The settings page includes a compatibility section that warns when another plugin or custom code is changing the same MIME rules FileGate manages.

## Technical Overview

- Main bootstrap: [`filegate.php`](./filegate.php)
- Settings controller: [`includes/class-settings.php`](./includes/class-settings.php)
- MIME handling: [`includes/class-mime-manager.php`](./includes/class-mime-manager.php)
- SVG sanitization: [`includes/class-svg-handler.php`](./includes/class-svg-handler.php)
- Admin UI template: [`templates/settings-page.php`](./templates/settings-page.php)

FileGate stores its settings in a single WordPress option named `filegate_settings`.

## Requirements

- WordPress with admin access to `manage_options`
- PHP environment with standard WordPress upload support

If the `DOMDocument` extension is available, FileGate uses it for stronger SVG parsing. Otherwise it falls back to a more conservative string-based sanitizer.

## License

This plugin is licensed under `GPL-3.0-or-later`. See [`LICENSE`](./LICENSE) for details.
