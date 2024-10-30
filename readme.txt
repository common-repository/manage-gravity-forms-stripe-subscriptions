=== GravityStripe Subscription Manager ===
Contributors: cncrrnt
Author: cncrrnt
Tags: Gravity Forms, Stripe, Frontend Management, Subscription, Addon
Requires PHP at least: 5.2.4
License: GPLv2 or later
Tested up to: 6.5.5
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Website: https://www.gravitystripe.com/

Description: An easy way for people to manage their stripe subscriptions made through Gravity Forms. Subscriptions are shown using a shortcode. Even includes an admin shortcode to manage ALL subscriptions and see any overdue subscription payments that failed to process in Stripe.com. Make sure your use the gravity forms registration addon so subscribers can log in and manage their subscriptions. If first_name, last_name or full_name metadata is set on stripe feed, name will be fetched from there instead of user’s first and last name.

== Description ==

An easy way for people to manage their stripe subscriptions made through Gravity Forms. Subscriptions are shown using a shortcode. Even includes an admin shortcode to manage ALL subscriptions and see any overdue subscription payments that failed to process in Stripe.com. Make sure your use the gravity forms registration addon so subscribers can log in and manage their subscriptions. If first_name, last_name or full_name metadata is set on stripe feed, name will be fetched from there instead of user’s first and last name.

== Installation ==

1. Manually upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly (Plugins > Add New > Upload).
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Once activated, the plugin automatically connects to the Stripe API keys set under your Gravity Forms Stripe Add-on

== Basic Features ==
- Enable Subscriptions Stripe using Gravity Forms
- Admin initiated subscription cancellation
- Admin list of active, failed payment, and cancelled subscribers
- A page where subscribers can see details of their subscription
- Translation Ready

== Pro Features ==
- Easily allow subscribers to upgrade a subscription on their own which will cancel the old one in stripe (requires the stripe embedded fields NOT the stripe checkout page)
- Ability for subscribers to update payment card info on their own 
- Allow subscribers to cancel their subscriptions on their own
- Auto-cancel subscription if payment fails x number of times
- Decide if cancellations end the subscription immediately or at the end of the billing cycle
- Downgrade subscriber role automatically if cancelled
- Issue refunds

== Videos ==

What's the difference?
[youtube https://youtu.be/Mej7MTI8-IE]

Upgrade/Downgrade Subscriptions
[youtube https://youtu.be/X3YNi7Mkjyo]

Updating Credit Card Info
[youtube https://youtu.be/bMpl89MPW3o]

Automatically Cancel Subscriptions for Failed Payments
[youtube https://youtu.be/L2tA0ffAuLE]

Cancel Immediately vs End of Period 
[youtube https://youtu.be/zQH2evNc7l0]

Downgrade User Role on Cancellation
[youtube https://youtu.be/FMQppyYt4qs]

Cancel and Refund the Last Payment
[youtube https://youtu.be/sV3yjQ_Ewjk]

== Changelog ==
Version 4.1.6
Added support for php 8.0
Added some hooks to ease developers to write actions. 
Enhanced flow for faster table loading. 

Version 4.1.4
Upgraded compatibility with PHP to PHP 8.0+
Reworked coding to allow for faster loading times of edit entry pages
Readjusted GravityForms back-end navigation to sidebar.

== Resources ==
Website: [https://www.gravitystripe.com/](https://www.gravitystripe.com/)
See Changelog & Upcoming Releases: [https://trello.com/b/XWM44IVi/gravitystripe-subscription-manager](https://trello.com/b/XWM44IVi/gravitystripe-subscription-manager)

== Screenshots ==

1. https://nimb.ws/zMfYXl - List of all subscriptions for the logged in user
2. https://nimb.ws/ZTiVAh - List of all site subscriptions for admin view
3. https://nimb.ws/LAGmxZ - Responsive modeiew
3. https://nimb.ws/LAGmxZ - Responsive mode

== Frequently Asked Questions ==

= What are the shortcodes =

For Subscribers to view their subscriptions:
[user-subscriptions formids='formid1, formid2, ....'] (use the formid(s) used to create the subscription (must contain a stripe feed))

For the admin side:
[subscription-list formids='formid1, formid2, ....'] (use the formid(s) used to create the subscription (must contain a stripe feed))

= How do you get your gravity form ids =

Log into your WordPress admin dashboard.
Click ‘Forms’ 1
The ID’s are listed to the right of the form names you’ve created

Reference: https://docs.gravityforms.com/shortcodes/#how-to-find-the-form-id



