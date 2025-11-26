#!/bin/sh
# Quick validation checklist for WordPress.org submission

PLUGIN_DIR="/home/kay/Desktop/PangeaTrust/wp-beacon-multi-currency-forms"
ASSETS_DIR="$PLUGIN_DIR/.wordpress-org"

echo "🔍 WordPress.org Submission Validation Checklist"
echo "================================================="
echo ""

# Check plugin main file
echo "📄 Plugin Main File:"
if [ -f "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php" ]; then
    echo "  ✅ Main plugin file exists"
    
    # Check for required headers
    if grep -q "Plugin Name:" "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php"; then
        echo "  ✅ Has Plugin Name header"
    fi
    if grep -q "Plugin URI:" "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php"; then
        echo "  ✅ Has Plugin URI header"
    fi
    if grep -q "Author:" "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php"; then
        echo "  ✅ Has Author header"
    fi
    if grep -q "License:" "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php"; then
        echo "  ✅ Has License header"
    fi
    if grep -q "Requires at least:" "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php"; then
        echo "  ✅ Has Requires at least header"
    fi
    if grep -q "Requires PHP:" "$PLUGIN_DIR/src/wp-beacon-multi-currency-forms.php"; then
        echo "  ✅ Has Requires PHP header"
    fi
else
    echo "  ❌ Main plugin file not found"
fi
echo ""

# Check readme.txt
echo "📋 Readme File:"
if [ -f "$PLUGIN_DIR/readme.txt" ]; then
    echo "  ✅ readme.txt exists"
    
    if grep -q "=== Beacon Multi-Currency Forms ===" "$PLUGIN_DIR/readme.txt"; then
        echo "  ✅ Has proper plugin name header"
    fi
    if grep -q "Contributors:" "$PLUGIN_DIR/readme.txt"; then
        echo "  ✅ Has Contributors field"
    fi
    if grep -q "Stable tag:" "$PLUGIN_DIR/readme.txt"; then
        echo "  ✅ Has Stable tag field"
    fi
    if grep -q "License:" "$PLUGIN_DIR/readme.txt"; then
        echo "  ✅ Has License field"
    fi
else
    echo "  ❌ readme.txt not found"
fi
echo ""

# Check assets
echo "🎨 Plugin Assets:"
if [ -f "$ASSETS_DIR/icon.svg" ]; then
    echo "  ✅ icon.svg exists"
else
    echo "  ❌ icon.svg missing"
fi

if [ -f "$ASSETS_DIR/icon-128x128.png" ]; then
    echo "  ✅ icon-128x128.png exists"
else
    echo "  ⚠️  icon-128x128.png missing (REQUIRED)"
fi

if [ -f "$ASSETS_DIR/icon-256x256.png" ]; then
    echo "  ✅ icon-256x256.png exists"
else
    echo "  ⚠️  icon-256x256.png missing (REQUIRED)"
fi

if [ -f "$ASSETS_DIR/banner-772x250.png" ]; then
    echo "  ✅ banner-772x250.png exists"
else
    echo "  ⚠️  banner-772x250.png missing (recommended)"
fi

# Check screenshots
echo ""
echo "📸 Screenshots:"
for i in 1 2 3 4 5 6; do
    if [ -f "$ASSETS_DIR/screenshot-$i.png" ]; then
        echo "  ✅ screenshot-$i.png exists"
    else
        echo "  ❌ screenshot-$i.png missing"
    fi
done
echo ""

# Summary
echo "================================================="
echo ""
echo "⚠️  MANUAL STEPS STILL REQUIRED:"
echo ""
echo "1. Create PNG icon files (128x128 and 256x256)"
echo "   - Use online converter: https://convertio.co/svg-png/"
echo "   - Upload .wordpress-org/icon.svg"
echo "   - Save as icon-128x128.png and icon-256x256.png"
echo ""
echo "2. Create banner image (772x250)"
echo "   - Use Canva, Photoshop, or design tool"
echo "   - Save as .wordpress-org/banner-772x250.png"
echo ""
echo "3. Validate readme.txt"
echo "   - Go to: https://wordpress.org/plugins/developers/readme-validator/"
echo ""
echo "4. Test plugin thoroughly"
echo "   - Install on clean WordPress site"
echo "   - Test all features"
echo "   - Check for errors"
echo ""
echo "📖 See WORDPRESS_ORG_SUBMISSION.md for complete guide"
echo ""
