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
- When donors click "Donate now" in the CTA box, they'll be taken to this page
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
5. Use the CTA box in sidebars or content areas

#### For Multiple Campaigns:
1. Create separate forms for each campaign
2. Create dedicated pages for each campaign's full form
3. Use form-specific shortcodes or blocks to display the right form on each page

---

## 🎨 Usage Methods

### Gutenberg Blocks

The plugin provides two blocks in the WordPress Block Editor:

#### 1. Donation Form Block (Full Form)
Use this on dedicated donation pages.

**To Insert:**
1. Edit a page in the Block Editor
2. Click the **"+"** button to add a block
3. Search for **"Beacon Donation Form"**
4. Insert the block
5. In the block settings sidebar:
   - Select which form to display (or leave default for the first form)

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
   - Select which form to use (or leave default)

**Best For:** 
- Sidebar widgets
- End of blog posts
- Campaign landing pages
- Homepage highlights

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

**Usage in Theme Files:**
```php
<?php echo do_shortcode('[beaconcrm_donate_form]'); ?>
```

---

#### Donation CTA Box Shortcode

```
[beaconcrm_donate_box]
```

**With Specific Form:**
```
[beaconcrm_donate_box form="Emergency Relief"]
```

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
   - **"Beacon Donation Box"** (CTA box)
3. Drag the widget to your desired location
4. In the widget settings:
   - **Form Name:** Select which form to display

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
   - **"Beacon Donation Box"** (CTA box)
4. Add the module to your layout
5. In the module settings:
   - **Form Name:** Select which form to display

**Design Options:**
- Modules support Divi's design settings
- Customize spacing, borders, and shadows as needed

---

## 🌍 Geo-Location Detection

The plugin can automatically detect your donor's currency based on their location.

### Requirements

Install and activate the **[GeoIP Detection](https://wordpress.org/plugins/geoip-detection/)** plugin for automatic currency detection.

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
3. Go to **Settings → GeoIP Detection**
4. Enable the **"JavaScript API"** option
5. Save settings

**That's it!** The currency will now auto-select based on visitor location.

---

## ❓ FAQ

### Q: Can I use the same currency in multiple forms?
**A:** Yes! Multiple forms can use the same currency. This is useful when you have different campaigns or regional variations. However, each currency can only appear once within the same form to prevent duplicates.

### Q: What happens if a visitor's currency isn't supported?
**A:** The plugin will automatically select the default currency you've configured for that form.

### Q: Can I customize the styling?
**A:** Yes! The plugin uses your theme's styling by default. You can add custom CSS in **Appearance → Customize → Additional CSS**. The main CSS classes are:
- `.wpbcd-wrap` - Main container
- `.wpbcd-card` - CTA box card
- `.wpbcd-btn` - Buttons
- `.wpbcd-select` - Dropdowns

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
