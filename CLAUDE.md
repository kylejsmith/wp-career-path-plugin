# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Important Directives

- **When instructions are unclear, ASK for clarification** - Don't assume or guess what the user wants. If there's ambiguity, ask specific questions before implementing.

## Project Overview

This is a WordPress plugin called "Career Progression Visualizer" that allows users to visualize their career journey using interactive D3.js charts. The plugin stores career history data in a custom database table and provides both frontend visualization and admin management capabilities.

## Architecture

### Core Structure

The plugin follows WordPress plugin architecture with these key components:

- **Main Plugin File**: `career-progression.php` - Entry point that defines constants, handles activation/deactivation, and initializes the plugin
- **Database Table**: `wp_career_progression` - Stores career entries with fields for position, company, dates, skills, achievements, salary, location, and path configuration

### Class Architecture

- **Career_Progression** (`includes/class-career-progression.php`): Main singleton class that initializes the plugin, handles script enqueueing, and provides AJAX endpoints
- **CPV_Admin** (`includes/class-cpv-admin.php`): Manages WordPress admin interface, menu pages, and admin-specific AJAX handlers
- **CPV_Shortcode** (`includes/class-cpv-shortcode.php`): Implements `[career_progression]` shortcode for frontend display
- **CPV_LinkedIn** (`includes/class-cpv-linkedin.php`): LinkedIn integration functionality

### Frontend Visualization

- Uses D3.js v7 for creating interactive hierarchical tree visualizations
- Main visualization logic in `assets/js/career-visualization.js`
- Supports multiple visualization types: timeline, tree, graph, sankey
- Data is fetched via AJAX and structured as hierarchical JSON with career paths and job nodes

### Admin Interface

- Custom admin pages under "Career Progression" menu
- Views located in `admin/views/`:
  - `main-page.php` - Lists all career entries
  - `add-page.php` - Add/edit career entries
  - `json-import-export.php` - Import/export functionality
  - `linkedin-page.php` - LinkedIn integration
  - `settings-page.php` - Plugin settings

## Development Commands

This is a WordPress plugin with no build process. Development workflow:

1. **Installation**: Place plugin folder in `wp-content/plugins/` directory
2. **Activation**: Activate through WordPress admin plugins page
3. **Database**: Table is created automatically on activation via `dbDelta()`
4. **Testing**: Test in a WordPress development environment
5. **Debugging**: Enable `WP_DEBUG` in `wp-config.php` for error reporting

## Key Technical Details

- **Nonce Security**: All AJAX requests use WordPress nonces for security
- **Localization**: Text domain `career-progression` for i18n support
- **Script Dependencies**: jQuery, jQuery UI Datepicker, D3.js v7
- **Data Format**: Career data structured as hierarchical tree with root node, path nodes (career tracks), and job nodes
- **Color Coding**: Each career path can have custom colors stored in database
- **Responsive**: Visualizations adapt to container width

## WordPress Hooks

Key actions and filters:
- `plugins_loaded` - Main initialization
- `wp_enqueue_scripts` - Frontend assets
- `admin_enqueue_scripts` - Admin assets
- `wp_ajax_cpv_*` - AJAX handlers for both authenticated and public requests