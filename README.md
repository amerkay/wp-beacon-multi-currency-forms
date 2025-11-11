# Beacon CRM Donate Plugin

A lightweight, flexible WordPress plugin for integrating BeaconCRM donation forms into your website. Display beautiful donation forms with multi-currency support, geo-location detection, and flexible placement options.

## 🌟 Features

- **Multi-Currency Support** - Configure multiple donation forms, each supporting different currencies
- **Geo-Location Currency Detection** - Automatically detect donor's currency based on location
- **Default Currency Fallback** - Set a default currency for each form when geo-detection fails
- **Two Display Modes**:
  - **Full Donation Form** - Complete embedded form for dedicated donation pages
  - **Donation CTA Box** - Compact call-to-action box for sidebars and content areas
- **Multiple Integration Options**:
  - Gutenberg Blocks (WordPress Block Editor)
  - Shortcodes
  - Elementor Widgets
  - Divi Modules
- **Per-Form Target Pages** - Each donation form can redirect to its own dedicated page
- **Preset Amount Sync** - Automatically syncs preset donation amounts from BeaconCRM
- **UTM Parameter Tracking** - Automatically tracks `utm_source`, `utm_medium`, and `utm_campaign` across all pages and passes them to donation forms (180-day cookie)

---

## 📋 Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
  - [Beacon Account Setup](#beacon-account-setup)
  - [Donation Forms Setup](#donation-forms-setup)
  - [Recommended Setup](#recommended-setup)
- [Usage Methods](#usage-methods)
  - [Gutenberg Blocks](#gutenberg-blocks)
  - [Shortcodes](#shortcodes)
  - [Elementor](#elementor)
  - [Divi Builder](#divi-builder)
- [Geo-Location Detection](#geo-location-detection)
- [UTM Parameter Tracking](#utm-parameter-tracking)
- [FAQ](#faq)
- [Support](#support)

---

## 🚀 Installation

1. Upload the `wp-beacon-crm-donate` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Settings → Beacon Donate** to configure

---

## ⚙️ Configuration

### Beacon Account Setup

1. Go to **Settings → Beacon Donate** in your WordPress admin
2. Find the **Beacon Account Name** field
3. Enter your BeaconCRM account name (e.g., `yourorg`)

#### How to Find Your Account Name:

1. Log into your BeaconCRM account
2. Navigate to any of your donation forms
3. Click the form, then click **"Embed"**
4. Look for the embed code: `<div class="beacon-form" data-account="yourorg" data-form="f0rm1d"></div>`
5. The value in `data-account` is your account name (e.g., `yourorg`)

**Note:** The form ID in `data-form` (e.g., `f0rm1d`) is what you'll use when configuring currencies below.

---

### Donation Forms Setup

The plugin allows you to create multiple donation forms, each with its own set of currencies and settings.

#### Creating Your First Form

1. In **Settings → Beacon Donate**, scroll to the **Donation Forms** section
2. You'll see one default form already created
3. Configure the following for each form:

##### Form Name
- Give your form a descriptive name (e.g., "General Donations", "Emergency Fund", "Monthly Giving")
- This name helps you identify the form when inserting it into pages

##### Donation Form Page
- Select the WordPress page where the full donation form will be displayed
- When donors click "Donate now" in the donate box, they'll be taken to this page
- Each form can have its own dedicated page

##### Supported Currencies

###### Adding Currencies:
1. Use the dropdown to select a currency
2. Enter the corresponding Beacon Form ID for that currency
3. Click **"Add Currency"**

**How to Find Beacon Form IDs:**
1. In BeaconCRM, go to each currency-specific donation form
2. Click **"Embed"**
3. Find the `data-form` value: `<div class="beacon-form" data-account="yourorg" data-form="123456"></div>`
4. The number `123456` is your Form ID

###### Setting a Default Currency:
- Click the radio button next to any currency to set it as the default
- The default currency is used when:
  - Geo-location detection fails
  - The detected currency is not supported by this form
  - The visitor is browsing from a location without currency data

**Important:** Each currency can only appear once within the same form, but multiple forms can use the same currency. This is useful for different campaigns or regional variations.

#### Adding Multiple Forms

Click **"+ Add Another Form"** to create additional donation forms for different campaigns or purposes.

**Example Setup:**
- **Form 1:** "General Donations" - Supports USD, EUR, GBP (USD default)
- **Form 2:** "Emergency Relief" - Supports USD, CAD, AUD (CAD default)
- **Form 3:** "Monthly Giving Program" - Supports USD, EUR (EUR default)

---

### Recommended Setup

#### For a Simple Site (One Type of Donation):
1. Keep the default form
2. Add all currencies you want to support
3. Set your most common currency as default
4. Create one page called "Donate" for the full form
5. Use the donate box in sidebars or content areas

#### For Multiple Campaigns:
1. Create separate forms for each campaign
2. Create dedicated pages for each campaign's full form
3. Use form-specific shortcodes or blocks to display the right form on each page

---

## 🎨 Usage Methods

### Gutenberg Blocks

The plugin provides three blocks in the WordPress Block Editor:

#### 1. Donation Form Block (Full Form)
Use this on dedicated donation pages.

**To Insert:**
1. Edit a page in the Block Editor
2. Click the **"+"** button to add a block
3. Search for **"Beacon Donation Form"**
4. Insert the block
5. In the block settings sidebar:
   - **Form Settings:** Select which form to display (or leave default for the first form)
   - **Required URL Parameters:** Add parameters that must be in the URL. If missing, users will be automatically redirected to include them.

**Use Case for Required Parameters:**
Perfect for tracking campaigns or ensuring specific data is present. For example, if you require `bcn_c_adopted_animal=12345&bcn_custom=abc`, visitors accessing the page without this parameter will be automatically redirected to include it.

**Best For:** Full-width donation pages

---

#### 2. Donation Box Block (CTA)
Use this in content areas, sidebars, or any widget area.

**To Insert:**
1. Edit a page or post in the Block Editor
2. Click the **"+"** button to add a block
3. Search for **"Beacon Donation Box"**
4. Insert the block
5. In the block settings sidebar:
   - **Form Settings:** Select which form to use (or leave default)
   - **Text Content:** Override title, subtitle, notice text, and button text
   - **Custom URL Parameters:** Add unlimited parameter rows with name/value pairs to pass to the donation form URL
   - **Frequencies:** Choose which donation frequencies to display (Single, Monthly, Annual) - backup option replaced by BeaconCRM settings
   - **Default Preset Amounts:** Set custom donation amounts for each frequency - backup option replaced by BeaconCRM settings
   - **Colors:** Customize primary and brand colors - backup option replaced by BeaconCRM settings

**Customization Options:**
- **Title:** Change "Make a donation" to your custom text
- **Subtitle:** Change "Pick your currency, frequency, and amount"
- **Notice Text:** Customize the security message
- **Button Text:** Change the donate button text (default: "Donate now →")
- **Custom Parameters:** Pass additional data to BeaconCRM forms (e.g., bcn_c_adopted_animal=12345&bcn_custom=spring2025)
- **Frequencies:** Control which frequencies are available (Single, Monthly, Annual) - Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load
- **Default Preset Amounts:** Set initial donation amounts per frequency - Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load
- **Primary Color:** Override the donate button color - Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load
- **Brand Color:** Override the color used for frequency tabs and links - Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load

**Best For:** 
- Sidebar widgets
- End of blog posts
- Campaign landing pages
- Homepage highlights

**Important Notes:**
- **Frequencies, Preset Amounts & Colors:** The settings you configure act as immediate defaults shown to visitors. When the BeaconCRM API loads (usually within a second), it will replace these with the values configured in your BeaconCRM account. This provides a seamless experience with instant display followed by API-synced accuracy.
- **Custom Parameters:** These are appended to the donation form URL and passed to BeaconCRM, allowing you to pre-fill fields, track campaigns, or pass any data your form accepts (e.g., bcn_c_adopted_animal=12345&key2=value2).

---

### Shortcodes

Perfect for classic editor users or embedding in theme files.

#### Full Donation Form Shortcode

```
[beaconcrm_donate_form]
```

**With Specific Form:**
```
[beaconcrm_donate_form form="General Donations"]
```

**With Required URL Parameters:**
```
[beaconcrm_donate_form form="Emergency Relief" params="bcn_c_adopted_animal=12345&bcn_custom=abc"]
```

If visitors access the page without these parameters in the URL, they will be automatically redirected to include them.

**Usage in Theme Files:**
```php
<?php echo do_shortcode('[beaconcrm_donate_form]'); ?>
```

---

#### Donation Box Shortcode

```
[beaconcrm_donate_box]
```

**With Specific Form:**
```
[beaconcrm_donate_box form="Emergency Relief"]
```

**With Color Customization:**
```
[beaconcrm_donate_box form="Monthly Giving" primary_color="#FF5733" brand_color="#2C3E50"]
```

**With Custom Text:**
```
[beaconcrm_donate_box 
    title="Support Our Mission" 
    subtitle="Choose your contribution" 
    notice="Secure checkout powered by Stripe"
]
```

**With Custom URL Parameters (URL-encoded format):**
```
[beaconcrm_donate_box params="bcn_c_adopted_animal=12345&bcn_custom=spring2025"]
```

**With Frequency Control:**
```
[beaconcrm_donate_box frequencies="monthly,annual"]
```

**With Custom Preset Amounts:**
```
[beaconcrm_donate_box 
    presets_single="10,20,30"
    presets_monthly="5,10,15"
    presets_annual="50,100,200"
]
```

**Complete Example:**
```
[beaconcrm_donate_box 
   form="Adopt an Elephant"
   primary_color="#34D399"
   brand_color="#465833"
   params="bcn_c_adopted_animal=6535"
   title="Adopt an Elephant"
   subtitle="Support our conservation efforts"
   notice="Your adoption helps protect endangered elephants"
   frequencies="monthly, annual"
   presets_monthly="25,50,120"
   presets_annual="100,250,500"
]
```

**Shortcode Parameters:**
- `form` - Name of the donation form to use (Choose which donation form to use)
- `title` - Custom heading text
- `subtitle` - Custom subheading text
- `notice` - Custom notice/security message
- `button_text` - Text shown on the donate button (default: `Donate now →`)
- `params` - Custom URL parameters in URL-encoded format (e.g., `bcn_c_adopted_animal=12345&key2=value2`). This will be added to the URL of the full page form on redirect.
- `frequencies` - Comma-separated list of allowed frequencies: `single`, `monthly`, `annual` (default: all three). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- `presets_single` - Comma-separated amounts for single donations (e.g., `10,20,30`). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- `presets_monthly` - Comma-separated amounts for monthly donations (e.g., `5,10,15`). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- `presets_annual` - Comma-separated amounts for annual donations (e.g., `50,100,200`). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- `primary_color` - Hex color for the donate button (e.g., `#FF5733`). Default primary color. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- `brand_color` - Hex color for tabs and links (e.g., `#2C3E50`). Default brand color. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.

**Usage in Theme Files:**
```php
<?php echo do_shortcode('[beaconcrm_donate_box form="Monthly Giving"]'); ?>
```

---

### Elementor

If you use Elementor page builder, the plugin adds two widgets.

#### Using the Widgets:

1. Edit a page with Elementor
2. Search in the widgets panel for:
   - **"Beacon Donation Form"** (full form)
   - **"Beacon Donation Box"** (donate box)
3. Drag the widget to your desired location
4. Configure the widget settings as described below

#### Donation Form Widget

**Settings:**
- **Form Settings Tab:**
  - **Form Name:** Select which form to display (Choose which donation form to use)
   - **Content Tab (Donation Box only):**
     - **Text Content:** Edit title, subtitle, notice text, and button text
     - **Custom URL Parameters:** Add parameter name/value pairs (e.g., bcn_c_adopted_animal=12345). This will be added to the URL of the full page form on redirect.
     - **Frequencies:** Toggle switches for Single, Monthly, and Annual frequencies. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
     - **Default Preset Amounts:** Text fields for custom amounts per frequency (comma-separated, e.g., 10, 20, 30). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
   - **Style Tab (Donation Box only):**
     - **Primary Color:** Default primary color picker for donate button. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
     - **Brand Color:** Default brand color picker for tabs and links. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.

**Donation Form - Required URL Parameters:**
The full-page donation form widget supports required URL parameters. Use the repeater control in the widget settings to add parameter name/value pairs. When the page loads, the form will check if those parameters exist in the URL with the correct values. If any parameter is missing or has a different value, the user will be automatically redirected to add them. This is perfect for tracking campaigns or ensuring specific data is present before a user can donate.

**Example in Elementor:**
1. Add "Beacon Donation Form" widget
2. In widget settings, find "Required URL Parameters" section
3. Click "Add Item" to add rows:
   - Parameter Name: `campaign`, Parameter Value: `spring2025`
   - Parameter Name: `source`, Parameter Value: `email`
4. When users visit the page without `?bcn_c_adopted_animal=12345&bcn_custom=abc`, they'll be automatically redirected to include these parameters.

#### Donation Box Widget

**Settings:**
- **Content Tab (Donation Box only):**
  - **Text Content:** Edit title, subtitle, notice text, and button text
  - **Custom URL Parameters:** Add parameter name/value pairs (e.g., bcn_c_adopted_animal=12345). This will be added to the URL of the full page form on redirect.
  - **Frequencies:** Toggle switches for Single, Monthly, and Annual frequencies. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
  - **Default Preset Amounts:** Text fields for custom amounts per frequency (comma-separated, e.g., 10, 20, 30). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- **Style Tab (Donation Box only):**
  - **Primary Color:** Default primary color picker for donate button. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
  - **Brand Color:** Default brand color picker for tabs and links. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.

**Donation Box - Example Use Cases:**
- Pass campaign tracking: `campaign` = `spring2025`
- Pre-select adopted animals: `bcn_c_adopted_animal` = `12345`
- Track referral sources: `source` = `newsletter`

**Styling:**
- Both widgets inherit your theme's styling
- Use Elementor's custom CSS for additional styling if needed

---

### Divi Builder

If you use Divi, the plugin adds two modules.

#### Using the Modules:

1. Edit a page with Divi Builder
2. Click **"+"** to add a module
3. Search for:
   - **"Beacon Donation Form"** (full form)
   - **"Beacon Donation Box"** (donate box)
4. Add the module to your layout
5. Configure the module settings as described below

#### Donation Form Module

**Settings:**
- **Form Name:** Select which form to display
- **Required URL Parameters:** URL-encoded format specifying parameters that must be present in the page URL (e.g., `bcn_c_adopted_animal=12345&bcn_custom=abc`). The form will automatically redirect users if these parameters are missing or have incorrect values.

#### Donation Box Module

**Settings:**
- **Form Name:** Select which form to display (Choose which donation form to use)
   - **Title:** Custom heading text
   - **Subtitle:** Custom subheading text
   - **Notice Text:** Custom security message
   - **Button Text:** Text shown on the donate button (default: `Donate now →`)
   - **Custom URL Parameters:** URL-encoded format (e.g., `bcn_c_adopted_animal=12345&bcn_custom=spring2025`). This will be added to the URL of the full page form on redirect.
   - **Show Single Frequency:** Yes/No toggle for single donation frequency option. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
   - **Show Monthly Frequency:** Yes/No toggle for monthly donation frequency option. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
   - **Show Annual Frequency:** Yes/No toggle for annual donation frequency option. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
   - **Single Preset Amounts:** Comma-separated amounts for single donations (e.g., `10, 20, 30`). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
   - **Monthly Preset Amounts:** Comma-separated amounts for monthly donations (e.g., `5, 10, 15`). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
   - **Annual Preset Amounts:** Comma-separated amounts for annual donations (e.g., `50, 100, 200`). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- **Primary Color:** Default primary color. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.
- **Brand Color:** Default brand color. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.

#### Design Options for All Modules

**Design Options:**
- Modules support Divi's design settings
- Customize spacing, borders, and shadows as needed

---

## 🌍 Geo-Location Detection

The plugin can automatically detect your donor's currency based on their location.

### Requirements

Install and activate the **[GeoIP Detection](https://wordpress.org/plugins/geoip-detection/)** plugin for automatic currency detection. Signup and get your [free Maxmind license key here](https://www.maxmind.com/en/geolite2/signup) for free accurate country/currency detection.

### How It Works

1. Visitor arrives at your donation page
2. Plugin detects their country via GeoIP
3. Automatically selects the appropriate currency if supported
4. Falls back to the default currency if:
   - Their country's currency isn't supported
   - GeoIP detection fails
   - The plugin isn't installed

### Setup

1. Install **GeoIP Detection** plugin
2. Activate it
3. Go to **Settings → Geolocation IP Detection** and choose `Maxmind GeoIP Lite City (Automatic download & update)`. Signup and get your [free Maxmind license key here](https://www.maxmind.com/en/geolite2/signup).
4. Enable the **"JavaScript API"** option
5. Save settings

**That's it!** The currency will now auto-select based on visitor location.

---

## 📊 UTM Parameter Tracking

The plugin can automatically track UTM parameters and load the Beacon SDK globally for proper cross-domain attribution.

### Two Independent Settings

**1. Load Beacon JavaScript globally** (Settings → Beacon Donate)
- Loads the Beacon SDK on all pages (required for cross-domain attribution tracking)
- When disabled: SDK only loads on pages with donate modules
- **Default: Unchecked** (opt-in)
- [Learn more about Beacon tracking](https://guide.beaconcrm.org/en/articles/5720151-tracking-forms-with-google-analytics#h_13617763bc)

**2. Enable tracking and passing UTM parameters to donation forms** (Settings → Beacon Donate)
- Tracks `utm_source`, `utm_medium`, and `utm_campaign` in a 180-day cookie
- Passes stored parameters to donation forms via data attributes
- Configure custom parameter mappings (e.g., `bcn_pay_c_utm_source`)
- **Default: Unchecked** (opt-in)

### How It Works

1. Enable one or both settings in **Settings → Beacon Donate**
2. When a visitor lands with UTM parameters (e.g., from an ad campaign), they are stored in a cookie for 180 days
3. When the visitor reaches a donation form page, the stored UTM parameters are passed to BeaconCRM via data attributes
4. Only `utm_source`, `utm_medium`, and `utm_campaign` are tracked (utm_id, utm_term, and utm_content are excluded)

### Cookie Behavior

- **Atomic replacement**: UTM values only update when `utm_source` is present in the URL (prevents partial data)
- **180-day expiry**: Cookie persists across sessions for 180 days
- **Works with caching**: JavaScript-based tracking runs on every page load, even on fully cached pages

### Example Flow

```
User clicks ad with: ?utm_source=facebook&utm_medium=social&utm_campaign=march-2025
↓
Cookie stored: {utm_source: "facebook", utm_medium: "social", utm_campaign: "march-2025"}
↓
User navigates to donation form (even 30 days later)
↓
Form receives data attributes and passes to BeaconCRM
```

---

## ❓ FAQ

### Q: Can I customize the colors of the donation box?
**A:** Yes! All integration methods (Gutenberg, Shortcode, Elementor, Divi) support custom primary and brand colors. Use native HTML5 color pickers in the block/widget settings, or hex color codes in shortcodes.

### Q: How do I pass custom parameters to the BeaconCRM form?
**A:** Use the Custom URL Parameters feature available in all integration methods:
- **Gutenberg & Elementor:** Use the visual interface to add parameter name/value pairs (no coding required)
- **Divi & Shortcode:** Enter parameters in URL-encoded format: `key1=value1&key2=value2`

This is useful for:
- Pre-selecting form fields (e.g., `bcn_c_adopted_animal=12345`)
- Passing any data that your BeaconCRM form accepts via URL parameters

### Q: Can I customize the text shown in the donation box?
**A:** Absolutely! You can customize:
- The main title (default: "Make a donation")
- The subtitle (default: "Pick your currency, frequency, and amount")
- The notice text (default: "You'll be taken to our secure donation form to complete your gift.")

All text fields support your customizations across all integration methods.

### Q: Can I control which donation frequencies are shown?
**A:** Yes! You can choose to show only specific frequencies (Single, Monthly, Annual):
- **Gutenberg:** Use the checkboxes in the "Frequencies" panel
- **Elementor:** Use the toggle switches in the "Frequencies" section
- **Divi:** Use the Yes/No buttons for each frequency
- **Shortcode:** Use the `frequencies` attribute (e.g., `frequencies="monthly,annual"`)

Note: These are initial defaults. If BeaconCRM API returns allowed frequencies, those will override your settings.

### Q: Can I set custom donation amounts?
**A:** Yes! You can set default preset amounts for each frequency:
- **Gutenberg:** Enter comma-separated amounts in the "Default Preset Amounts" panel
- **Elementor:** Use the text fields for each frequency in the "Default Preset Amounts" section
- **Divi:** Use the preset amount fields for single, monthly, and annual
- **Shortcode:** Use attributes like `presets_monthly="5,10,15"`

Note: These are fallback defaults. When the BeaconCRM form loads, it will replace these with amounts configured in your BeaconCRM account if available.

### Q: Can I use the same currency in multiple forms?
**A:** Yes! Multiple forms can use the same currency. This is useful when you have different campaigns or regional variations. However, each currency can only appear once within the same form to prevent duplicates.

### Q: What happens if a visitor's currency isn't supported?
**A:** The plugin will automatically select the default currency you've configured for that form.

### Q: Can I customize the styling?
**A:** Yes! The plugin uses your theme's styling by default. You can:
- Use the built-in color pickers to customize primary and brand colors
- Add custom CSS in **Appearance → Customize → Additional CSS**
- Use your page builder's styling options (Elementor/Divi)

Main CSS classes for advanced styling:
- `.wpbcd-wrap` - Main container
- `.wpbcd-card` - donate box card
- `.wpbcd-btn` - Buttons
- `.wpbcd-select` - Dropdowns
- `.wpbcd-tab` - Frequency and amount buttons

CSS variables you can override:
- `--wpbcd-primary` - Primary button color
- `--wpbcd-brand` - Brand color for tabs/links
- `--wpbcd-text` - Text color
- `--wpbcd-border` - Border color

### Q: Do I need a BeaconCRM account?
**A:** Yes, you need an active BeaconCRM account with configured donation forms for each currency you want to support.

### Q: Can I have multiple donation campaigns running simultaneously?
**A:** Absolutely! Create separate forms for each campaign and place them on different pages using the form name parameter.

### Q: Does this work with page builders?
**A:** Yes! The plugin supports Gutenberg (default), Elementor, and Divi. Shortcodes work with any page builder.

### Q: What if I don't have GeoIP Detection installed?
**A:** The plugin will work fine! It will just use the default currency you've set for each form instead of auto-detecting.

### Q: Can I translate the plugin?
**A:** Yes, the plugin is translation-ready. Use a plugin like Loco Translate or WPML to translate strings.

### Q: How do I remove a form?
**A:** If you have multiple forms, click the **"Remove This Form"** button at the bottom of the form settings. You must have at least one form configured.

---

## 🆘 Support

### Getting Help

1. Check the [FAQ section](#faq) above
2. Review your BeaconCRM form IDs are correct
3. Ensure your Beacon Account Name is properly formatted
4. Check browser console for JavaScript errors

### Common Issues

**Problem:** Form not displaying
- **Solution:** Verify your Beacon Account Name and Form IDs are correct in settings

**Problem:** Currency not auto-selecting
- **Solution:** Install and configure the GeoIP Detection plugin with JavaScript API enabled

**Problem:** "Please configure donation forms" message appears
- **Solution:** Add at least one currency to your form in the settings

**Problem:** Donor redirected to wrong page
- **Solution:** Check that the correct "Donation Form Page" is selected for each form

---

## 📄 License

This plugin is licensed under GPL v2 or later.

---

## 🙏 Credits

- **BeaconCRM** - Donation form platform
- **GeoIP Detection Plugin** - Geo-location functionality

---

## 📝 Changelog

### Version 0.1.2
feat: add UTM tracking, separate Beacon SDK loading, centralize version, improve settings UX
- New settings default to unchecked (opt-in for Beacon SDK and UTM tracking)
- Add separate "Load Beacon JavaScript globally" setting for cross-domain attribution
- Add "Enable tracking and passing UTM parameters" setting with configurable parameter mappings
- Extract Beacon SDK to separate loader file (`beacon-sdk-loader.js`)
- Centralize version to plugin header as single source of truth
- Add dismissible cache clearing reminder after settings save
- Add Settings link to plugins page action links
- UTM parameters stored in cookie for 180 days with atomic replacement. Only tracks utm_source, utm_medium, utm_campaign (utm_id, utm_term, utm_content excluded)

### Version 0.1.1
feat(utm): add UTM parameter tracking functionality
- Add UTM tracking enable/disable setting in admin
- Implement JavaScript-based UTM cookie storage (180 days)
- Pass UTM parameters to BeaconCRM forms via data attributes
- Add admin UI for UTM tracking toggle with description
- Enqueue utm-tracker.js globally when feature enabled
- Track utm_source, utm_medium, and utm_campaign only

### Version 0.1.0
- Initial release
- Multi-currency support
- Per-form default currencies
- Per-form target pages
- Gutenberg blocks
- Shortcodes
- Elementor widgets
- Divi modules
- Geo-location detection integration

---

**Made with ❤️ by Amer Kawar @ WildAmer.com**
