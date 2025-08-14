<?php
/**
 * Plugin Name: Block AI Scrapers
 * Plugin URI:  https://github.com/norcross/block-ai-scrapers
 * Description: Add the disallow rules to the robots.txt file. Sourced here: https://github.com/ai-robots-txt
 * Version:     0.0.1
 * Author:      Andrew Norcross
 * Author URI:  https://andrewnorcross.com
 * Text Domain: block-ai-scrapers
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package     BlockAIScrapers
 */

// Declare our namespace.
namespace Norcross\BlockAIScrapers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our plugin version.
define( 'BLOCKAI_SCRAPERS_VERS', '0.0.1' );

// Set our source URL.
define( 'BLOCKAI_SOURCE_DATA_URL', 'https://raw.githubusercontent.com/ai-robots-txt/ai.robots.txt/refs/heads/main/robots.txt' );

/**
 * Start our engines.
 */
add_filter( 'robots_txt', __NAMESPACE__ . '\include_ai_blocking_rules', -1, 2 );

/**
 * Append the generated robots.txt file with the new rules.
 *
 * @param  string $robots   The existing data.
 * @param  boolean $public  Whether the site is public.
 *
 * @return string
 */
function include_ai_blocking_rules( $robots, $public ) {

	// Get our data first.
	$block_data = fetch_ai_blocking_data();

	// Return the existing data with whatever we have.
	return $robots . "\n" . $block_data;
}

/**
 * Get the data from GitHub.
 *
 * @return array
 */
function fetch_ai_blocking_data() {

	// Set the key to use in our transient.
	$ky = 'blockai_scrapers_source_data';

	// If we don't want the cache'd version, delete the transient first.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		delete_transient( $ky );
	}

	// Attempt to get the data from the cache.
	$cached_dataset = get_transient( $ky );

	// If we have none, do the things.
	if ( false === $cached_dataset ) {

		// Grab our data.
		$fetch_data = file_get_contents( BLOCKAI_SOURCE_DATA_URL );

		// Bail without having data.
		if ( empty( $fetch_data ) ) {
			return;
		}

		// Set our transient with our data for a day.
		set_transient( $ky, $fetch_data, DAY_IN_SECONDS );

		// And change the variable to do the things.
		$cached_dataset = $fetch_data;
	}

	// And return the resulting.
	return $cached_dataset;
}
