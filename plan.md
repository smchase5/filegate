🧩 Plugin Name

FileGate – Control WordPress Upload Types

Ask any needed questions.

🎯 Goal

Provide a simple, clean, and performant way to:

Enable/disable file upload types (like SVG, WebP, etc.)
Safely allow additional MIME types
Add custom file types dynamically
Give non-technical users a toggle-based UI
🚀 V1 Core Features
1. Enable Common File Types (Toggle UI)

Provide a list of common restricted or useful file types with simple toggles:

Default toggles:

SVG (image/svg+xml)
WebP (image/webp)
AVIF (image/avif)
JSON (application/json)
CSV (text/csv)
ICO (image/x-icon)
HEIC (image/heic)
HEIF (image/heif)

👉 Each item:

Toggle on/off
Description tooltip
File extension + MIME displayed
2. Custom File Type Support

Allow users to define their own file types.

Fields:

Extension (e.g. dwg)
MIME type (e.g. image/vnd.dwg)
Label (optional, for UI display)

Behavior:

Add/remove dynamically
Validate inputs
Prevent duplicates
3. WordPress Upload Filter Integration

Hook into:

upload_mimes
Merge enabled toggles + custom types
Respect existing WordPress defaults
Avoid overriding core unnecessarily
4. SVG Safe Mode (Basic)

SVG is risky—so V1 should include a minimal safety option.

Options:

Enable SVG uploads
“Basic sanitization” toggle (future expandable)

V1 approach:

Strip <script> tags
Remove on* event handlers (onclick, etc.)

(Keep this simple for now, don’t over-engineer yet)

5. Clean Admin UI

Location:

Settings → FileGate

Layout:

Section: “Common File Types”
Section: “Custom File Types”
Section: “Advanced”

UX goals:

Minimal
Fast
No clutter
Toggle-first design
6. Capability Check

Only allow access to:

manage_options
7. Lightweight + Performant
No heavy frameworks
Vanilla JS (or minimal)
No frontend assets
Only load admin assets on plugin page
🧱 Architecture
File Structure
filegate/
├── filegate.php
├── includes/
│   ├── class-settings.php
│   ├── class-mime-manager.php
│   ├── class-svg-handler.php
├── assets/
│   ├── admin.css
│   ├── admin.js
├── templates/
│   ├── settings-page.php
├── plan.md
Key Classes
1. FileGate_Mime_Manager
Handles:
Default toggles
Custom types
upload_mimes filter
2. FileGate_Settings
Registers settings
Renders admin UI
Saves options
3. FileGate_SVG_Handler
Handles:
SVG sanitization
Upload validation
Options Structure (single option)
filegate_settings = [
  'enabled_types' => [
    'svg' => true,
    'webp' => true,
    'json' => false,
  ],
  'custom_types' => [
    [
      'ext' => 'dwg',
      'mime' => 'image/vnd.dwg',
      'label' => 'AutoCAD Drawing'
    ]
  ],
  'svg_sanitize' => true
];
🎨 UI/UX Notes
Toggle switches (not checkboxes)
Inline add/remove for custom types
Clean spacing, modern feel
No WordPress “clunkiness”

Optional:

Use subtle card sections
Use inline validation messages
🔐 Security Considerations
Validate all inputs
Sanitize:
extension (letters only)
MIME (strict format)
Escape all output
Nonces on save
SVG sanitization (basic in V1)
⚙️ Hooks & Filters
Core Hook
add_filter('upload_mimes', [...]);
Future-proof filters
apply_filters('filegate_allowed_types', $types);
🧪 Edge Cases
Duplicate extensions
Invalid MIME types
Conflicts with other plugins
Multisite compatibility (future)
📦 V1 Out of Scope (but planned)
Role-based permissions (per user role)
File size restrictions
Upload logging
Drag/drop validation UI
Deep SVG sanitization (library-based)
REST API support
Import/export settings
🧠 Future Feature Ideas (V2+)
Role-based file permissions
Upload restrictions per post type
Media library filtering by type
Security audit warnings
Cloud storage compatibility
UI presets (developer vs beginner mode)
“Recommended safe config” button
🏁 MVP Definition

FileGate V1 is complete when:

Users can toggle common file types
Users can add custom types
Uploads work correctly in media library
SVG uploads don’t introduce obvious XSS risk
UI is clean and intuitive