#!/bin/bash
# WP Booking System - Plugin Build Script (Unix/Linux/macOS)
# Creates a distributable ZIP file for WordPress upload

set -e  # Exit on error

# Configuration
PLUGIN_NAME="wp-booking-system"
VERSION="1.0.0"
BUILD_DIR="build"
ZIP_FILE="${PLUGIN_NAME}-v${VERSION}.zip"

# Parse command line arguments
CLEANUP=false
if [[ "$1" == "--cleanup" ]] || [[ "$1" == "-c" ]]; then
    CLEANUP=true
fi

echo "========================================"
echo "WP Booking System - Build Script"
echo "========================================"
echo ""

# Clean previous build
if [ -d "$BUILD_DIR" ]; then
    echo "Cleaning previous build..."
    rm -rf "$BUILD_DIR"
fi

if [ -f "$ZIP_FILE" ]; then
    echo "Removing old ZIP file..."
    rm -f "$ZIP_FILE"
fi

# Create build directory
echo "Creating build directory..."
mkdir -p "$BUILD_DIR/$PLUGIN_NAME"

# Files and directories to include
INCLUDE_FILES=(
    "wp-booking-system.php"
    "index.php"
    "uninstall.php"
    "LICENSE"
    "readme.txt"
)

INCLUDE_DIRS=(
    "includes"
    "assets"
)

# Copy main plugin files
echo "Copying plugin files..."
for file in "${INCLUDE_FILES[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$BUILD_DIR/$PLUGIN_NAME/"
        echo "  [OK] $file"
    else
        echo "  [MISSING] $file"
    fi
done

# Copy directories
for dir in "${INCLUDE_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        cp -r "$dir" "$BUILD_DIR/$PLUGIN_NAME/"
        echo "  [OK] $dir/"
    else
        echo "  [MISSING] $dir/"
    fi
done

# Create lang directory structure (for future translations)
echo "Creating language directory..."
mkdir -p "$BUILD_DIR/$PLUGIN_NAME/lang"
cp "index.php" "$BUILD_DIR/$PLUGIN_NAME/lang/"

# Verify essential files
echo ""
echo "Verifying essential files..."
ESSENTIAL_FILES=(
    "$BUILD_DIR/$PLUGIN_NAME/wp-booking-system.php"
    "$BUILD_DIR/$PLUGIN_NAME/includes/class-wp-booking-system-luca.php"
    "$BUILD_DIR/$PLUGIN_NAME/assets/css/frontend.css"
    "$BUILD_DIR/$PLUGIN_NAME/assets/js/frontend.js"
)

ALL_PRESENT=true
for file in "${ESSENTIAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  [OK] $(basename "$file")"
    else
        echo "  [MISSING] $(basename "$file")"
        ALL_PRESENT=false
    fi
done

if [ "$ALL_PRESENT" = false ]; then
    echo ""
    echo "ERROR: Essential files are missing!"
    exit 1
fi

# Create ZIP file
echo ""
echo "Creating ZIP archive..."
cd "$BUILD_DIR"
if zip -r "../$ZIP_FILE" "$PLUGIN_NAME" > /dev/null 2>&1; then
    cd ..
    
    # Verify ZIP file was created
    if [ ! -f "$ZIP_FILE" ]; then
        echo "  [ERROR] ZIP file was not created!"
        exit 1
    fi
    
    echo "  [OK] ZIP file created: $ZIP_FILE"
    
    # Get file size
    if command -v stat > /dev/null 2>&1; then
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            ZIP_SIZE=$(stat -f%z "$ZIP_FILE" 2>/dev/null || echo "0")
        else
            # Linux
            ZIP_SIZE=$(stat -c%s "$ZIP_FILE" 2>/dev/null || echo "0")
        fi
        ZIP_SIZE_MB=$(echo "scale=2; $ZIP_SIZE / 1024 / 1024" | bc 2>/dev/null || echo "0")
        echo "  [OK] File size: ${ZIP_SIZE_MB} MB"
    fi
else
    cd ..
    echo "  [ERROR] Failed to create ZIP"
    echo ""
    echo "Make sure 'zip' command is available:"
    echo "  - Ubuntu/Debian: sudo apt-get install zip"
    echo "  - macOS: zip should be pre-installed"
    echo "  - CentOS/RHEL: sudo yum install zip"
    exit 1
fi

# Cleanup build directory (optional)
if [ "$CLEANUP" = true ]; then
    echo ""
    echo "Removing build directory..."
    rm -rf "$BUILD_DIR"
    echo "  [OK] Build directory removed"
else
    echo ""
    echo "Build directory preserved: $BUILD_DIR"
    echo "  Use --cleanup or -c parameter to remove it automatically"
fi

echo ""
echo "========================================"
echo "Build Complete!"
echo "========================================"
echo ""
echo "Plugin ZIP: $ZIP_FILE"
echo ""
echo "To install:"
echo "  1. Go to WordPress Admin → Plugins → Add New"
echo "  2. Click 'Upload Plugin'"
echo "  3. Choose file: $ZIP_FILE"
echo "  4. Click 'Install Now'"
echo "  5. Activate the plugin"
echo ""

