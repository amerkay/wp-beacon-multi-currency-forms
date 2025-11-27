# WordPress.org Assets Setup Instructions

## Icon Files (REQUIRED)

The SVG icon has been created at `.wordpress-org/icon.svg`.

You need to create PNG versions for browser compatibility:

```bash
# Using Inkscape (if installed)
inkscape icon.svg --export-png=icon-128x128.png --export-width=128 --export-height=128
inkscape icon.svg --export-png=icon-256x256.png --export-width=256 --export-height=256

# OR using ImageMagick (if installed)
convert -background none -resize 128x128 icon.svg icon-128x128.png
convert -background none -resize 256x256 icon.svg icon-256x256.png

# OR using rsvg-convert (if installed)
rsvg-convert -w 128 -h 128 icon.svg -o icon-128x128.png
rsvg-convert -w 256 -h 256 icon.svg -o icon-256x256.png

# OR use an online converter
# Upload icon.svg to: https://convertio.co/svg-png/
# Download at 128x128 and 256x256 sizes
```

## Screenshot Files (REQUIRED)

Copy and rename your screenshots from `public/` to `.wordpress-org/`. See .sh file.

## Banner Image (RECOMMENDED)

Create a banner image at `.wordpress-org/banner-772x250.png` (and optionally `banner-1544x500.png` for retina displays).

The banner should:
- Feature your plugin branding
- Include the plugin name "Beacon Multi-Currency Forms"
- Show donation/beacon visual elements
- Match the icon color scheme (purple gradient with gold accents)
- Be eye-catching but professional

You can create this using:
- Canva (recommended for non-designers)
- Photoshop/GIMP
- Figma
- Any graphic design tool

## File Checklist

Before submitting to WordPress.org, ensure you have:

- [x] `.wordpress-org/icon.svg` (created)
- [ ] `.wordpress-org/icon-128x128.png` (needs creation)
- [ ] `.wordpress-org/icon-256x256.png` (needs creation)
- [ ] `.wordpress-org/screenshot-1.png` (needs copy)
- [ ] `.wordpress-org/screenshot-2.png` (needs copy)
- [ ] `.wordpress-org/screenshot-3.png` (needs copy)
- [ ] `.wordpress-org/screenshot-4.png` (needs copy)
- [ ] `.wordpress-org/screenshot-5.png` (needs copy)
- [ ] `.wordpress-org/screenshot-6.png` (needs copy)
- [ ] `.wordpress-org/banner-772x250.png` (needs creation)
- [ ] `.wordpress-org/banner-1544x500.png` (optional, for retina)

## Image Size Limits

- Icons: 1MB maximum (but smaller is better)
- Banners: 4MB maximum (but smaller is better)
- Screenshots: 10MB maximum (but smaller is better)

## Validation

After creating all assets, validate your readme.txt at:
https://wordpress.org/plugins/developers/readme-validator/

## Notes

- All filenames must be lowercase
- PNG and JPG formats are supported for screenshots/banners
- SVG can be used for icons (with PNG fallback required)
- Images are served through CDN and heavily cached
