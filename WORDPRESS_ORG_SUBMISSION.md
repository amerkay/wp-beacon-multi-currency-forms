# WordPress.org Plugin Submission Guide

## ✅ Completed Tasks

Your plugin has been prepared for WordPress.org submission with the following changes:

### 1. Updated Main Plugin File Headers
**File:** `src/wp-beacon-multi-currency-forms.php`

Added all required WordPress.org headers:
- ✅ Plugin URI: https://github.com/amerkay/wp-beacon-multi-currency-forms
- ✅ Description: Under 140 characters
- ✅ Requires at least: 6.0
- ✅ Requires PHP: 7.4
- ✅ Tested up to: 6.7
- ✅ Author: Amer Kawar
- ✅ Author URI: https://wildamer.com
- ✅ License: GPL v2 or later
- ✅ License URI: https://www.gnu.org/licenses/gpl-2.0.html
- ✅ Text Domain: wp-beacon-multi-currency-forms
- ✅ Domain Path: /languages

### 2. Created readme.txt
**File:** `readme.txt` (in root directory)

Comprehensive WordPress.org-compliant readme with:
- ✅ Proper header format
- ✅ Contributors: amerkay
- ✅ Tags: donation, fundraising, beacon, crm, charity
- ✅ Short description (under 150 chars)
- ✅ Detailed description with features
- ✅ Installation instructions
- ✅ FAQ section (15 common questions)
- ✅ Screenshots section (6 screenshots mapped)
- ✅ Changelog
- ✅ Third-party services disclosure

### 3. Created Plugin Assets
**Directory:** `.wordpress-org/`

- ✅ icon.svg - Beautiful gradient icon with heart and beacon waves
- ✅ screenshot-1.png - Settings page
- ✅ screenshot-2.png - Form configuration
- ✅ screenshot-3.png - Gutenberg block editor
- ✅ screenshot-4.png - Frontend donation box
- ✅ screenshot-5.png - GeoIP settings
- ✅ screenshot-6.png - UTM tracking settings
- ✅ copy-screenshots.sh - Automation script
- ✅ README.md - Asset preparation instructions

## 📋 Manual Steps Required

### Step 1: Convert Icon to PNG (REQUIRED)

You need PNG fallback versions of the icon. Choose one method:

**Option A: Online Converter (Easiest)**
1. Go to https://convertio.co/svg-png/
2. Upload `.wordpress-org/icon.svg`
3. Download at 128x128 pixels, save as `icon-128x128.png`
4. Download at 256x256 pixels, save as `icon-256x256.png`
5. Place both files in `.wordpress-org/` directory

**Option B: Command Line (If tools installed)**
```bash
cd .wordpress-org

# Using Inkscape
inkscape icon.svg --export-png=icon-128x128.png --export-width=128 --export-height=128
inkscape icon.svg --export-png=icon-256x256.png --export-width=256 --export-height=256

# OR using ImageMagick
convert -background none -resize 128x128 icon.svg icon-128x128.png
convert -background none -resize 256x256 icon.svg icon-256x256.png

# OR using rsvg-convert
rsvg-convert -w 128 -h 128 icon.svg -o icon-128x128.png
rsvg-convert -w 256 -h 256 icon.svg -o icon-256x256.png
```

### Step 2: Create Banner Image (RECOMMENDED)

Create a banner image for the plugin header:

**Requirements:**
- Size: 772 × 250 pixels (required)
- Retina: 1544 × 500 pixels (optional but recommended)
- Format: PNG or JPG
- Max size: 4MB (but keep it under 500KB)

**Design Recommendations:**
- Use purple gradient background (matching icon: #4F46E5 to #7C3AED)
- Include plugin name: "Beacon Multi-Currency Forms"
- Add tagline: "Multi-Currency Donations for WordPress"
- Feature heart/beacon visual elements
- Keep text readable and professional

**Tools:**
- Canva.com (easiest, has templates)
- Photoshop/GIMP (for designers)
- Figma (for designers)

**Save as:**
- `.wordpress-org/banner-772x250.png`
- `.wordpress-org/banner-1544x500.png` (optional retina)

### Step 3: Validate Your Readme

Before submitting, validate your readme.txt:

1. Go to: https://wordpress.org/plugins/developers/readme-validator/
2. Copy contents of `readme.txt`
3. Paste and click "Validate"
4. Fix any errors or warnings

### Step 4: Test Your Plugin

Before submission, test thoroughly:

- [ ] Install on clean WordPress 6.0+ site
- [ ] Activate plugin successfully
- [ ] Configure settings page
- [ ] Test Gutenberg blocks
- [ ] Test shortcodes
- [ ] Test with Elementor (if available)
- [ ] Test with Divi (if available)
- [ ] Check for PHP errors in debug mode
- [ ] Test on mobile devices
- [ ] Test browser compatibility

### Step 5: Prepare for Submission

**WordPress.org Account:**
1. Create account at https://login.wordpress.org/register
2. Username should match contributor in readme.txt (amerkay)

**Plugin Slug:**
Your plugin slug will be: `beacon-multi-currency-forms`
(Note: WordPress.org may modify this if already taken)

**Submission Checklist:**
- [ ] Plugin tested on WordPress 6.0+
- [ ] Plugin tested with PHP 7.4+
- [ ] All code follows WordPress Coding Standards
- [ ] No security vulnerabilities
- [ ] No trademark violations
- [ ] GPL-compatible license
- [ ] Proper text domain used throughout
- [ ] All strings are translatable
- [ ] No encoded/obfuscated code
- [ ] No tracking without user consent

## 🚀 Submission Process

### Submit Your Plugin

1. **Zip Your Plugin:**
   ```bash
   cd /home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms
   
   # Create a clean zip (exclude .git, test_wp_docker_envs, etc.)
   zip -r beacon-multi-currency-forms.zip . \
     -x "*.git*" \
     -x "*test_wp_docker_envs*" \
     -x "*.wordpress-org/*" \
     -x "*node_modules*" \
     -x "*.DS_Store"
   ```

2. **Submit to WordPress.org:**
   - Go to: https://wordpress.org/plugins/developers/add/
   - Upload your zip file
   - Wait for automated review (usually 1-3 days)

3. **After Approval:**
   - You'll receive SVN repository access
   - Commit your code to SVN
   - Commit assets to `/assets` directory

### SVN Workflow (After Approval)

```bash
# Checkout SVN repository
svn co https://plugins.svn.wordpress.org/beacon-multi-currency-forms beacon-multi-currency-forms-svn
cd beacon-multi-currency-forms-svn

# Add plugin files to trunk
cp -r /home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms/src/* trunk/
cp /home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms/readme.txt trunk/

# Add assets
mkdir -p assets
cp /home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms/.wordpress-org/icon*.* assets/
cp /home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms/.wordpress-org/banner*.* assets/
cp /home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms/.wordpress-org/screenshot*.* assets/

# Set proper MIME types for images
svn propset svn:mime-type image/png assets/*.png
svn propset svn:mime-type image/svg+xml assets/icon.svg

# Commit to SVN
svn add trunk/* assets/*
svn ci -m "Initial commit of Beacon Multi-Currency Forms v0.1.2"

# Create first stable tag
svn cp trunk tags/0.1.2
svn ci -m "Tagging version 0.1.2"
```

## 📚 Additional Resources

### WordPress.org Guidelines
- Plugin Guidelines: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- Readme Standards: https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/
- Plugin Headers: https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
- Plugin Assets: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/

### Review Process
- Expect review within 1-14 days (usually 1-3 days)
- Reviews are done by human volunteers
- Common reasons for rejection:
  - Security issues
  - Trademark violations
  - Calling external services without disclosure
  - Code obfuscation
  - Phone-home functionality

### After Approval
- Your plugin will appear in WordPress.org directory
- Users can install directly from WordPress admin
- You'll maintain via SVN (no GitHub integration)
- Updates are instant once committed to SVN

## 🛠️ Maintenance

### Releasing Updates

1. Update version in `src/wp-beacon-multi-currency-forms.php`
2. Update changelog in `readme.txt`
3. Update "Tested up to" if needed
4. Commit to SVN trunk
5. Tag new version: `svn cp trunk tags/X.X.X`
6. Plugin directory updates automatically

### Support

- Support forum: https://wordpress.org/support/plugin/beacon-multi-currency-forms
- GitHub: https://github.com/amerkay/wp-beacon-multi-currency-forms
- Response time: Try to respond within 48 hours

## ✨ What's Been Prepared

Your plugin is now WordPress.org ready with:

1. ✅ Proper plugin headers (all required fields)
2. ✅ Comprehensive readme.txt (validated structure)
3. ✅ Beautiful SVG icon with donation theme
4. ✅ All 6 screenshots organized and renamed
5. ✅ Asset preparation instructions
6. ✅ Automation scripts for workflows

**Still need manually:**
- PNG icon conversions (128×128 and 256×256)
- Banner image creation (772×250)
- Final testing before submission
- WordPress.org account creation

## 🎉 You're Almost Ready!

Once you complete the manual steps above, your plugin will be ready for WordPress.org submission. Good luck! 🚀
