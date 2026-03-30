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

🚀 V2 Roadmap

FileGate V2 should focus on improving confidence, clarity, and day-to-day usability before expanding into more advanced policy controls.

The main goal for V2:

Make it easier for non-technical users to understand what each setting does, verify that uploads will work, and troubleshoot conflicts without needing custom code.

Quick Wins

1. Preset Profiles

Add one-click presets such as:

Safe Defaults
Content Team
Designer
Developer

Why it matters:

Reduces decision fatigue
Helps new users get started faster
Makes FileGate feel smarter out of the box

2. Better Save Feedback

After saving, show a summary of:

Which built-in types were enabled
Which custom types were added
Which rows were skipped and why

Why it matters:

Builds trust
Makes changes easier to verify
Improves clarity when validation rejects something

3. Upload Tester

Add a lightweight admin tool that lets users test:

An extension
A MIME type
Whether FileGate currently allows it
Whether another plugin may be overriding it

Why it matters:

Answers “will this upload work?” before frustration happens
Useful for support and troubleshooting

4. First-Run Guidance

Improve the empty state and onboarding flow with:

A short intro
Recommended starting presets
Simple safety guidance for SVG and custom MIME rules

Why it matters:

Makes the plugin friendlier for first-time users
Reduces uncertainty

5. Stronger Inline Help

Add clearer help text beside risky or confusing settings:

Why SVG is treated differently
When HEIC/HEIF may not display everywhere
What a MIME type is

Why it matters:

Better education
Less reliance on docs

6. Search and Sorting

Add search and filtering to:

Built-in file types
Custom type rows
Compatibility notes

Why it matters:

Keeps the interface manageable as FileGate grows

7. Import and Export Settings

Let admins export and import FileGate settings between sites.

Why it matters:

Very helpful for agencies
Useful for staging-to-production workflows

Medium Effort Features

1. Role-Based Upload Permissions

Allow specific file types for:

Administrators only
Editors
Custom roles

Why it matters:

Improves security
Supports real-world editorial workflows

2. Per-Type Restrictions

Add optional controls such as:

Maximum upload size per type
Warning labels for riskier formats
Optional notes or descriptions per custom type

Why it matters:

Gives more nuanced control without overcomplicating the main UI

3. Better Compatibility Diagnostics

Expand compatibility reporting to surface:

Potential conflicting plugins
Rules being overridden
Unexpected MIME mappings

Why it matters:

Makes FileGate much easier to debug
Reduces support friction

4. Upload Troubleshooter

Add a guided helper for failed uploads that can suggest likely causes:

File type not enabled
Incorrect MIME type
Server restriction
Theme/plugin conflict
Insufficient permissions

Why it matters:

Turns frustration into guided problem solving

5. Activity Logging

Track:

Blocked uploads
Sanitized SVG uploads
Settings changes

Why it matters:

Useful for admins, support, and audit trails

6. Multisite Support

Support:

Network defaults
Per-site overrides
Centralized policy options

Why it matters:

Strong fit for agencies and WordPress professionals

7. Custom Type Templates

Offer quick-add templates for common formats such as:

Fonts
Design assets
Developer config files
Document exchange formats

Why it matters:

Speeds up setup
Reduces input errors

Bigger Bets

1. Advanced SVG Protection

Move beyond basic sanitization with:

A stricter sanitizer
More granular rule enforcement
Clearer risk labeling

2. Audit Mode

Scan the active configuration and highlight:

Risky combinations
Conflicting MIME rules
Potentially unnecessary allowances

3. Team Approval Flow

For higher-risk file types, add optional approval or warning flows before settings are enabled.

4. Site Health Integration

Expose FileGate findings inside WordPress Site Health.

5. Smart Recommendations

Suggest file types or presets based on the site’s use case and existing stack.

Suggested Implementation Order

Phase 1:

Preset profiles
Better save feedback
First-run guidance
Stronger inline help

Phase 2:

Upload tester
Import/export settings
Search and sorting

Phase 3:

Role-based permissions
Compatibility diagnostics
Upload troubleshooter

Phase 4:

Activity logging
Multisite support
Custom type templates

Phase 5:

Advanced SVG protection
Audit mode
Site Health integration

Recommended Next Build

The best next milestone is a “confidence and onboarding” release focused on:

Preset profiles
Clearer save summaries
First-run empty state improvements
Inline explanations for risky formats

Why this should come first:

It improves the experience for every user
It keeps the plugin simple
It creates a stronger base before adding advanced permissions or logging
