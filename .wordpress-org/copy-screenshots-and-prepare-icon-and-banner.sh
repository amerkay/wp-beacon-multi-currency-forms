#!/bin/sh
# Script to copy and organize screenshots for WordPress.org

PLUGIN_DIR="../"
PUBLIC_DIR="$PLUGIN_DIR/public"
ASSETS_DIR="$PLUGIN_DIR/.wordpress-org"

echo "Copying screenshots to .wordpress-org directory..."

# Check if public directory exists
if [ ! -d "$PUBLIC_DIR" ]; then
    echo "Error: Public directory not found at $PUBLIC_DIR"
    exit 1
fi

# Create .wordpress-org directory if it doesn't exist
mkdir -p "$ASSETS_DIR"

# Find and sort image files:
# 1. Find all .png, .jpg, .jpeg files (case-insensitive)
# 2. Sort by: frontend- prefix first, then page- prefix, then others
# 3. Within each group, sort by modification time (newest first)
{
    find "$PUBLIC_DIR" -maxdepth 1 -type f \( -iname "*.png" -o -iname "*.jpg" -o -iname "*.jpeg" \) -printf "%T@ %p\n" | grep "/frontend-" | sort -rn
    find "$PUBLIC_DIR" -maxdepth 1 -type f \( -iname "*.png" -o -iname "*.jpg" -o -iname "*.jpeg" \) -printf "%T@ %p\n" | grep "/page-" | sort -rn
    find "$PUBLIC_DIR" -maxdepth 1 -type f \( -iname "*.png" -o -iname "*.jpg" -o -iname "*.jpeg" \) -printf "%T@ %p\n" | grep -v "/frontend-" | grep -v "/page-" | sort -rn
} | cut -d' ' -f2- | while IFS= read -r file; do
    counter=$((${counter:-0} + 1))
    filename=$(basename "$file")
    cp "$file" "$ASSETS_DIR/screenshot-$counter.png"
    echo "✓ Screenshot $counter: $filename"
done

echo ""
echo "Screenshots copied successfully!"
echo ""

# Convert SVG files to PNG using flatpak Inkscape
echo "Converting SVG files to PNG..."

if [ -f "$ASSETS_DIR/icon.svg" ]; then
    echo "Converting icon.svg..."
    flatpak run --branch=stable --arch=x86_64 --command=inkscape org.inkscape.Inkscape \
        "$ASSETS_DIR/icon.svg" --export-filename="$ASSETS_DIR/icon-128x128.png" -w 128 -h 128
    echo "✓ Created icon-128x128.png"
    
    flatpak run --branch=stable --arch=x86_64 --command=inkscape org.inkscape.Inkscape \
        "$ASSETS_DIR/icon.svg" --export-filename="$ASSETS_DIR/icon-256x256.png" -w 256 -h 256
    echo "✓ Created icon-256x256.png"
else
    echo "⚠ Warning: icon.svg not found at $ASSETS_DIR/icon.svg"
fi

if [ -f "$ASSETS_DIR/banner.svg" ]; then
    echo "Converting banner.svg..."
    flatpak run --branch=stable --arch=x86_64 --command=inkscape org.inkscape.Inkscape \
        "$ASSETS_DIR/banner.svg" --export-filename="$ASSETS_DIR/banner-772x250.png" -w 772 -h 250
    echo "✓ Created banner-772x250.png"
    
    flatpak run --branch=stable --arch=x86_64 --command=inkscape org.inkscape.Inkscape \
        "$ASSETS_DIR/banner.svg" --export-filename="$ASSETS_DIR/banner-1544x500.png" -w 1544 -h 500
    echo "✓ Created banner-1544x500.png (retina)"
else
    echo "⚠ Warning: banner.svg not found at $ASSETS_DIR/banner.svg"
fi

echo ""
echo "All assets prepared successfully!"
echo ""
echo "See .wordpress-org/README.md for detailed instructions."
