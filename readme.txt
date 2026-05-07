=== Aide :: Switch User ===
Contributors: aide247
Tags: user, switch user, admin, login, users
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows administrators to switch to any user account and switch back safely.

== Description ==

Aide :: Switch User adds a **Switch to** action on the WordPress users list for administrators.

Use cases:

* Reproduce user-specific UI issues
* Validate role/capability behavior
* Verify account experiences without sharing passwords

How it works:

* Adds a row action in **Users -> All Users**
* Performs capability checks and nonce validation before switching
* Shows an admin notice with a secure **Switch back** link
* Returns you to your original account in one click

== Installation ==

1. Upload `aide-user-switch` to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open **Users -> All Users** and use the **Switch to** action.

== Frequently Asked Questions ==

= Who can switch users? =

Only users with appropriate capabilities (for example, administrators with access to list and edit users).

= Can I switch back easily? =

Yes. A warning notice appears while switched, with a secure **Switch back** link.

= Does this plugin expose passwords? =

No. It switches authentication context via WordPress APIs and does not reveal user passwords.

== Changelog ==

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.0 =

Initial release.
