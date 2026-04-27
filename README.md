# Asian Post Photo Card (v5.3.0)

A WordPress plugin designed to generate custom social media and news photo cards based on article links from Asian Post.

## Features

- **One-Click Generation**: Provide an article URL to automatically fetch the primary image, title, date, and QR shortlinks.
- **Visual Drag & Drop**: Interactively position the background image, QR code, and title overlay on selected templates.
- **Language Support**: Automatically detects and applies appropriate typography for Bengali and English titles (using TiroBangla and GandhiSerif fonts, respectively).
- **Responsive Generation**: Provides a scaled live preview within the admin interface while exporting native 1080x1350 resolution PNG files optimized for social media platforms.
- **Custom Image Upload**: Allows users to override automatically extracted images with custom uploads.

## Architecture

This plugin follows standard WordPress modular architecture:

- `/includes/class-apc-core.php`: Initializes the plugin, handling data localization and asset enqueuing.
- `/includes/class-apc-ajax.php`: Processes secure AJAX requests and manages the DOM parsing logic for web scraping.
- `/includes/class-apc-shortcode.php`: Manages the frontend component rendering and application state.
- `/includes/class-apc-utils.php`: Provides centralized utility functions for timestamp conversion, Schema.org URL extraction, and string manipulation.

## Security

The plugin is designed with security best practices for production WordPress environments:

- **Direct File Protection**: Script execution is restricted by validating the `ABSPATH` constant.
- **Directory Protection**: Empty index files are included in subdirectories to prevent directory traversal.
- **Data Protection**: Implements `wp_create_nonce` to secure frontend-to-server HTTP POST methods.
- **Capability Management**: API endpoints enforce `current_user_can('edit_posts')` validations, ensuring only authorized users can initiate data fetch operations.
- **Sanitization**: All inputs and extracted data, including external image URLs, are sanitized before processing or rendering.

## Usage

Activate the plugin via the WordPress administration dashboard.

The plugin will automatically create an isolated frontend render page titled `Asian Post Photo Card`. Alternatively, administrators can render the tool manually by using the following shortcode:

`[photo_card template="Default-Template-1080x1350.png"]`

## Dependencies

The plugin utilizes the following external libraries, loaded dynamically via CDN:
- HTML2Canvas (v1.4.1)
- QRCode.min.js

---
*Created by [Delwar Hossain](https://www.delwarhossain.net/)*
