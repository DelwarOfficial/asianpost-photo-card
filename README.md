# Asian Post Photo Card (v5.3.0)

A high-performance WordPress plugin designed to instantly generate custom social media/news photo cards based on article links from Asian Post.

## 🚀 Features
- **One-Click Generation**: Paste an article URL to automatically fetch the News Image, Title, Date, and QR shortlinks.
- **Visual Drag & Drop**: Freely drag the background image, QR code, and overlay Title precisely where you need them over the chosen templates.
- **Language Support**: Automatically detects and styles Bangla vs English titles using specific typography adjustments (TiroBangla vs GandhiSerif fonts).
- **Responsive Generation**: Renders a proportional scaled live preview for the admin, but exports true native 1080x1350 resolution PNG files perfectly formatted for Facebook/Instagram stories and feed posts.
- **Custom Image Upload**: Fully supports overriding external scraped images via built-in uncompressed object mapping.

## 🏗️ Architecture
This plugin is built following modern WordPress Modular standard architecture. 
- `/includes/class-opc-core.php`: Bootstraps the localized data handling and enqueue behaviors.
- `/includes/class-opc-ajax.php`: Handles the securely hardened nonced data requests and robust web scraping DOM parser logic.
- `/includes/class-opc-shortcode.php`: Manages the plugin's frontend component rendering state logic.
- `/includes/class-opc-utils.php`: Centralized functional utilities handling timestamp conversion logic, schema.org URL extraction engines, and Bengali string token swaps.

## 🔒 Security Posture
Constructed originally for standalone usage, this system has been structurally reviewed and defensively coded for safe deployment onto production WordPress environments:
- **Direct File Protection**: Every script execution tree starts with `ABSPATH` validations.
- **Directory Traversal Blocks**: Index isolation endpoints exist at every sub-folder topology sequence.
- **Data Protection**: `wp_create_nonce` implementations secure all frontend-to-server HTTP POST methods via `$rpcData` array localization.
- **Capability Management**: API data fetches enforce `current_user_can('edit_posts')` validations securely verifying contributor authority before fetching HTTP streams. 
- **Defensive Sanitization**: Full spectrum deep input sanitization arrays (including schema image URL escaping routines).

## 🎛️ Usage
Simply activate the plugin via your wp-admin area.

The system will automatically initialize and deploy an isolated frontend render page titled `Asian Post Photo Card`. Alternatively, administrators can invoke the raw deployment tool securely by implementing the specific hook payload anywhere:

`[photo_card template="Default-Template-1080x1350.png"]`

## ⚙️ Dependencies 
The plugin securely enqueues HTML2Canvas (version 1.4.1) & QRCode.min.js across CDN streams dynamically at runtime. None of your logic or WordPress environments will suffer performance issues due to heavy localized library hosting loads.

---
*Created by [Delwar Hossain](https://www.delwarhossain.net/)*
