# KKSR Data Faker

WordPress plugin that automatically generates and saves real ratings data to database for KK Star Ratings plugin.

## Description

KKSR Data Faker automatically generates and saves ratings data (votes, star ratings) to the WordPress database for posts. The plugin ensures consistent data generation (same post ID always gets the same numbers) and protects real data by respecting a configurable threshold.

## Features

- **Automatic Data Generation**: Generates ratings data when posts are viewed or saved
- **Real Database Storage**: Saves data directly to `wp_postmeta` table
- **Data Protection**: Won't overwrite posts with votes equal to or greater than threshold
- **Consistent Results**: Same post ID always generates the same numbers
- **Configurable Ranges**: Set min/max for votes and star ratings
- **Threshold Protection**: Protect real data by setting vote threshold

## Installation

1. Upload the plugin files to `/wp-content/plugins/kkrs-data-faker` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → KKSR Faker to configure

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- KK Star Ratings plugin (v5.4+)

## Configuration

Navigate to **Settings → KKSR Faker** to configure:

- **Minimum/Maximum Vote Count**: Range for generated votes
- **Minimum/Maximum Star Rating**: Range for generated star ratings (1.0 - 5.0)
- **Real Votes Threshold**: Posts with votes equal to or greater than this number will not be overwritten

## How It Works

1. Data is saved to `wp_postmeta` with keys: `_kksr_count_default`, `_kksr_avg_default`, `_kksr_ratings_default`
2. Real data protection: If a post already has votes equal to or greater than the real votes threshold, the plugin will NOT overwrite the data
3. Automatic generation when posts are viewed or saved
4. Data generation on plugin activation for all existing posts

## License

GPL v2 or later

## Author

MaiSyDat - https://hupuna.com

## Support

Report issues at: https://github.com/MaiSyDat/kkrs-data-faker/issues

