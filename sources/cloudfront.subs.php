<?php

/**
 * @package "Cloudfront CDN for Elkarte" Addon for Elkarte
 * @author Spuds
 * @copyright (c) 2017-2021 Spuds
 * @license This Source Code is subject to the terms of the Mozilla Public License
 * version 1.1 (the "License"). You can obtain a copy of the License at
 * http://mozilla.org/MPL/1.1/.
 *
 * @version 1.0.1
 *
 */

/**
 * igms_cloud_front()
 *
 * - Admin Hook, integrate_modify_cache_settings, called from ManageServer.controller.php
 * - used to add items to the server cache settings page
 *
 * @param array $config_vars
 */
function imcs_cloud_front(&$config_vars)
{
	global $txt;

	loadLanguage('cloudfront');

	$config_vars = array_merge($config_vars, array(
		'',
		array('cloudfront', $txt['cloudfront'], 'db', 'check', false, $txt['cloudfront_desc']),
		array('cloudfront_url', $txt['cloudfront_url'], 'db', 'text', 20, $txt['cloudfront_url']),
		array('cloudfront_avatars', $txt['cloudfront_avatars'], 'db', 'check'),
		array('cloudfront_theme', $txt['cloudfront_theme'], 'db', 'check'),
		array('cloudfront_js', $txt['cloudfront_js'], 'db', 'check'),
		array('cloudfront_css', $txt['cloudfront_css'], 'db', 'check'),
	));
}

/**
 * ob_cloud_front()
 *
 * - buffer hook, integrate_buffer, called from ob_exit via call_integration_buffer
 * - used to modify the contents of the output buffer before its sent to the browser
 *
 * @param string $buffer
 * @return string
 */
function ob_cloud_front($buffer)
{
	global $modSettings;

	$new_buffer = $buffer;

	// The addon is "on"
	if (!empty($modSettings['cloudfront']))
	{
		// Get avatar and/or theme images?
		if (!empty($modSettings['cloudfront_avatars']) || !empty($modSettings['cloudfront_theme']))
		{
			$new_buffer = cloudfront_images($new_buffer);
		}

		// CSS files
		if (!empty($modSettings['cloudfront_css']))
		{
			$new_buffer = cloudfront_css($new_buffer);
		}

		// Javascript files
		if (!empty($modSettings['cloudfront_js']))
		{
			$new_buffer = cloudfront_js($new_buffer);
		}
	}

	// All done
	return !empty($new_buffer) ? $new_buffer : $buffer;
}

/**
 * Uses a regex to find all <img tags in the buffer string
 *
 * - Checks if found <img tag is for an avatar or a theme image
 * - Swaps boardurl for the cdn url for matched images on enabled addon features
 *
 * @param string $buffer
 * @return string
 */
function cloudfront_images($buffer)
{
	global $modSettings, $boardurl;

	$reg_ex = '~(<img[^>]+src=["\']?)(' . preg_quote($boardurl, '~') . '[^"\'>]+)["\']?\s*~s';

	$new_buffer = preg_replace_callback(
		$reg_ex,
		function ($match) use ($boardurl, $modSettings) {
			if (!empty($modSettings['cloudfront_avatars']) && strpos($match[2], $modSettings['custom_avatar_url']) !== false)
			{
				return str_replace($boardurl, $modSettings['cloudfront_url'], $match[0]);
			}

			if (!empty($modSettings['cloudfront_theme']) && strpos($match[2], $boardurl . '/themes/') !== false)
			{
				return str_replace($boardurl, $modSettings['cloudfront_url'], $match[0]);
			}

			return $match[0];
		},
		$buffer
	);

	// All done
	return !empty($new_buffer) ? $new_buffer : $buffer;
}

/**
 * Uses a regex to find all <link tags in the buffer string
 *
 * - Checks if found <link tag is a .css file
 * - Swaps boardurl for the cdn url for matched link
 *
 * @param string $buffer
 * @return string
 */
function cloudfront_css($buffer)
{
	global $modSettings, $boardurl;

	$reg_ex = '~(<link[^>]+href=["\']?)(' . preg_quote($boardurl, '~') . '[^"\'>]+)["\']?\s*~s';
	$cache = trim(str_replace(BOARDDIR, '', CACHEDIR), '/');

	$new_buffer = preg_replace_callback(
		$reg_ex,
		function ($match) use ($boardurl, $modSettings, $cache) {
			if (substr($match[2], -4) === '.css')
			{
				return str_replace($boardurl, $modSettings['cloudfront_url'], $match[0]);
			}

			if (strpos($match[2], $cache . '/hive') !== false)
			{
				return str_replace($boardurl, $modSettings['cloudfront_url'], $match[0]);
			}

			return $match[0];
		},
		$buffer
	);

	// All done
	return !empty($new_buffer) ? $new_buffer : $buffer;
}

/**
 * Uses a regex to find all <script tags, from this domain, in the buffer string
 *
 * - Swaps boardurl for the cdn url for matched images on enabled addon features
 *
 * @param string $buffer
 * @return string
 */
function cloudfront_js($buffer)
{
	global $modSettings, $boardurl;

	// This will get the hive files as well.
	$reg_ex = '~(<script[^>]+src=["\']?)(' . preg_quote($boardurl, '~') . '[^"\'>]+)["\']?\s*~s';

	$new_buffer = preg_replace_callback(
		$reg_ex,
		function ($match) use ($boardurl, $modSettings) {
			if (strpos($match[2], 'jslocale') === false && strpos($match[2], 'sa=.js') === false && strpos($match[2], 'sa=js') === false)
			{
				return str_replace($boardurl, $modSettings['cloudfront_url'], $match[0]);
			}

			return $match[0];
		},
		$buffer
	);

	// All done
	return !empty($new_buffer) ? $new_buffer : $buffer;
}
