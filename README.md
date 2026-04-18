# Simple Google AdSense Inject

A lightweight WordPress plugin that loads the Google AdSense script on every public page of your site — except on a configurable list of excluded post IDs.

## Features

- Loads the AdSense loader script (`adsbygoogle.js`) site-wide.
- Exclude individual posts, pages, or custom post types by ID.
- Skips feeds and preview requests.
- Async loading via the modern `strategy` arg (WP 6.3+) with a filter-based fallback for older versions.
- Input sanitization for both the Client ID and the excluded-ID list.
- Cleans up its options on uninstall.

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- A valid Google AdSense publisher ID (format `ca-pub-xxxxxxxxxxxxxxxx`)

## Installation

1. Copy the plugin folder into `wp-content/plugins/` (or upload the ZIP via **Plugins → Add New → Upload Plugin**).
2. Activate **Simple Google AdSense Inject** in the WordPress admin.
3. Go to **Settings → AdSense Excluded Posts**.
4. Enter your AdSense Client ID (e.g. `ca-pub-1234567890123456`).
5. Optionally enter a comma-separated list of post IDs where AdSense should **not** be loaded (e.g. `7658,971590`).
6. Save.

## How it works

On every front-end request (except feeds and previews), the plugin enqueues:

```
https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-...
```

If the current request is a singular view (`is_singular()`) and the post ID appears in the excluded list, the script is not enqueued for that request.

## Uninstall

Deleting the plugin via the WordPress admin removes both stored options (`adsense_post_ids`, `adsense_client_id`) via `uninstall.php`.

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).

## Author

Viktor Dite — [mizine.de](https://mizine.de)
