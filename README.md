# Justifi Payments

This is a custom WordPress/WooCommerce plugin developed specifically for adding a payment gateway and other features utilizing the Justifi API.

> **Note:** This plugin is site-specific and not intended for general public use. It is part of my development portfolio and is shared for demonstration purposes only.

## What It Does

This plugin includes:

- A custom payment gateway utilizing the Justifi API `/justifi-payments/includes/gateway/`
- Webhooks listening for specific actions like refunds and changes in account status `/justifi-payments/includes/webhooks/`
- Shortcodes to use for a client dashboard that pull in data from a client's Justifi Account `/justifi-payments/includes/shortcodes/`
- Shotcodes used to display Justifi Web Components like Charts and Data Tables `/justifi-payments/includes/shortcodes/`
- Admin page settings for adding API keys `/justifi-payments/admin/`

## File Overview

- The core plugin is a vanilla template that has been modified for this specific use
- Most of the plugin functionality including the custom gateway, webhooks and shortcodes can be found in the `/justifi-payments/includes/` directory.

## Development Notes

- Certain references within the code have been obfuscated for client security

## License

This plugin is not licensed for redistribution or commercial use.