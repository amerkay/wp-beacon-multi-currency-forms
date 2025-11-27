=== Beacon Multi-Currency Forms ===
Contributors: amerkay
Tags: donation, fundraising, beacon, crm, charity
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 0.1.2
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Embed Beacon CRM donation forms with multi-currency support, geo-location detection, and UTM tracking. Supports shortcodes, blocks, Elementor & Divi

== Description ==

**Beacon Multi-Currency Forms** integrates BeaconCRM donation forms into WordPress with advanced features for charities & nonprofits organizations. Display full-page donation forms or compact donation boxes with automatic currency selection based on visitor location.

= Key Features =

* **Multi-Currency Support** - Configure multiple currencies for each donation form
* **Automatic Geo-Detection** - Auto-select currency based on visitor's country (requires GeoIP Detection plugin)
* **Two Display Modes** - Full-page form and compact donation box (CTA)
* **Multiple Integration Methods** - Gutenberg blocks, shortcodes, Elementor widgets, Divi modules
* **UTM Campaign Tracking** - Track campaign parameters with 180-day cookie persistence
* **Customizable Appearance** - Custom colors, text, preset amounts, and frequencies
* **Page Builder Compatible** - Works with Gutenberg, Elementor, and Divi
* **Multiple Forms** - Create separate forms for different campaigns

= Perfect For =

* Nonprofit organizations
* Charities and foundations
* Educational institutions
* Political campaigns
* Any organization accepting online donations

= How It Works =

1. **Connect your BeaconCRM account** - Enter your Beacon account name
2. **Configure donation forms** - Add form names and Beacon form IDs for each currency
3. **Add to pages** - Use Gutenberg blocks, shortcodes, or page builders
4. **Accept donations** - Visitors see the form in their local currency

= Integration Methods =

**Gutenberg Blocks:**
* Beacon Donation Form - Full-page donation form
* Beacon Donation Box - Compact CTA with currency/amount selector

**Shortcodes:**
* `[beacon_donate_form]` - Full donation form
* `[beacon_donate_box]` - Compact donation box

**Page Builders:**
* Elementor widgets
* Divi modules

**Theme Files:**
* PHP function calls for custom templates

= Multi-Currency & Geo-Location =

Install the free **GeoIP Detection** plugin to enable automatic currency selection. Visitors from the US see USD, visitors from Europe see EUR, etc. Falls back to your default currency if detection fails or currency not supported.

= UTM Campaign Tracking =

Track marketing campaign performance by enabling UTM parameter tracking. Parameters are stored in a 180-day cookie and automatically passed to donation forms, helping you measure campaign effectiveness in BeaconCRM.

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Active BeaconCRM account
* GeoIP Detection plugin (optional, for automatic currency selection)

= Privacy & Data =

This plugin does not collect or store any personal data. All donation processing is handled by BeaconCRM. UTM tracking uses browser cookies to store campaign parameters locally.

= Support & Documentation =

* [GitHub Repository](https://github.com/amerkay/wp-beacon-multi-currency-forms)
* [Report Issues](https://github.com/amerkay/wp-beacon-multi-currency-forms/issues)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "Beacon Multi-Currency Forms"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Go to Plugins → Add New → Upload Plugin
4. Choose the ZIP file and click "Install Now"
5. Click "Activate Plugin"

= Configuration =

1. Go to **Settings → Beacon Multi-Currency Forms**
2. Enter your **Beacon Account Name** (found in your BeaconCRM embed code)
3. Add your first donation form:
   - Form Name (e.g., "General Donations")
   - Target Page (optional)
   - Add currencies with Beacon Form IDs
   - Set default currency
4. Click **Save Changes**

= Adding Forms to Pages =

**Using Gutenberg:**
1. Edit a page
2. Click (+) to add a block
3. Search for "Beacon Donation Form" or "Beacon Donation Box"
4. Select your form and configure options

**Using Shortcodes:**
Add to any page, post, or widget:
`[beacon_donate_form]`
or
`[beacon_donate_box]`

**Using Page Builders:**
Search for "Beacon Donation Form" or "Beacon Donation Box" in Elementor widgets or Divi modules.

= Optional: Enable Geo-Location =

1. Install and activate the **GeoIP Detection** plugin
2. Go to **Settings → Geolocation IP Detection**
3. Select "Maxmind GeoIP Lite City"
4. Get your free MaxMind license key
5. Enable "JavaScript API" option
6. Save settings

== Frequently Asked Questions ==

= Do I need a BeaconCRM account? =

Yes, this plugin integrates with BeaconCRM's donation forms. You need an active BeaconCRM account to use this plugin. Sign up at [BeaconCRM.org](https://beaconcrm.org).

= How do I find my Beacon Account Name? =

Your account name is in the BeaconCRM embed code. Look for `data-account="yourorg"` - the account name is "yourorg".

= How do I find my Beacon Form IDs? =

Form IDs are in the BeaconCRM embed code. Look for `data-form="abc123"` - the form ID is "abc123". Each currency requires a separate form in BeaconCRM.

= Can I use multiple currencies? =

Yes! Create separate forms in BeaconCRM for each currency (USD, EUR, GBP, etc.) and add them all to one donation form in the plugin settings. Visitors will see the appropriate currency based on their location.

= How does automatic currency detection work? =

Install the free GeoIP Detection plugin. The plugin detects the visitor's country and automatically selects the matching currency. If their country's currency isn't supported, it shows your default currency.

= Can I customize the appearance? =

Yes! The donation box supports custom colors, text, preset amounts, and frequencies. You can also add custom CSS to override any styling.

= Does this work with my page builder? =

Yes! The plugin includes native integrations for:
- Gutenberg (WordPress Block Editor)
- Elementor
- Divi
- Classic Editor (via shortcodes)

= How do I track campaign performance? =

Enable UTM tracking in plugin settings. When visitors arrive via campaign links (with utm_source, utm_medium, utm_campaign), those parameters are stored for 180 days and automatically passed to donation forms.

= Can I have multiple donation forms? =

Yes! Create as many forms as you need for different campaigns, projects, or purposes. Each form can have its own currencies and settings.

= Is this plugin GDPR compliant? =

The plugin itself does not collect or store personal data. UTM tracking uses browser cookies to store campaign parameters locally. All donation processing and data collection is handled by BeaconCRM according to their privacy policy.

= Where can I get support? =

For bug reports and feature requests, please visit the [GitHub repository](https://github.com/amerkay/wp-beacon-multi-currency-forms/issues).

= Is the source code available? =

Yes, the plugin is open source. View the code on [GitHub](https://github.com/amerkay/wp-beacon-multi-currency-forms).

== Screenshots ==

1. Settings page - Configure Beacon account and donation forms
2. Adding donation form currencies with Beacon Form IDs
3. Gutenberg block for donation form in the block editor
4. Frontend donation box with automatic currency detection
5. GeoIP Detection plugin settings for automatic currency selection
6. UTM tracking settings for campaign attribution

== Changelog ==

= 0.1.2 =
* Multi-currency support with geo-location detection
* UTM parameter tracking with 180-day persistence
* Gutenberg blocks for donation form and box
* Elementor widgets integration
* Divi modules integration
* Shortcode support for Classic Editor
* Customizable colors, text, and preset amounts
* Automatic currency selection based on visitor location
* Campaign tracking with BeaconCRM integration
* Settings page for easy configuration

== Upgrade Notice ==

= 0.1.2 =
Initial release with multi-currency support, geo-location detection, and UTM tracking.

== Third-Party Services ==

This plugin integrates with the following third-party services:

= BeaconCRM =
* Service: BeaconCRM donation form processing
* Website: https://beaconcrm.org
* Privacy Policy: https://beaconcrm.org/privacy
* Terms of Service: https://beaconcrm.org/terms
* Usage: Required for all donation form functionality

= GeoIP Detection (Optional) =
* Service: MaxMind GeoIP database for location detection
* Plugin: https://wordpress.org/plugins/geoip-detect/
* Privacy Policy: https://www.maxmind.com/en/privacy-policy
* Usage: Optional, only if you install the GeoIP Detection plugin for automatic currency selection

== Additional Information ==

= Source Code =
View the source code on [GitHub](https://github.com/amerkay/wp-beacon-multi-currency-forms)

= Contributing =
Contributions are welcome! Please submit pull requests on GitHub.

= Author =
Amer Kawar - [WildAmer.com](https://wildamer.com)
