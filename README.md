**✅ Aide::Switch User — WordPress Plugin**

Aide::Switch User is a lightweight admin utility plugin for WordPress that allows administrators to instantly switch into any user account and then switch back to the original admin account with one click.

This is extremely useful for:

Debugging user-specific issues

Testing role-based functionality

Supporting clients by seeing exactly what they see

Quickly navigating between multiple test accounts


**🚀 Features**
✔ Switch to Any User

Admins can impersonate any user directly from the Users → All Users list using a “Switch To” link.

✔ Switch Back to Admin

When you are impersonating another user, a warning notice appears with a link to instantly switch back to your original admin account.

✔ Safe & Secure

Uses WordPress nonces for CSRF protection

Stores admin reference using user meta

Only available to users with the administrator capability

✔ Zero Configuration

Just activate the plugin — no settings required.



**🛠️ How It Works**

Adds a “Switch To” action link in the user list.

On click, the plugin:

Saves the current admin ID in user meta.

Logs in as the selected user.

Redirects to the dashboard.

While impersonating, a notice appears with a “Switch back” link.

Switching back restores the original admin session.



**📦 Installation**

Upload the plugin folder to /wp-content/plugins/

Activate via Plugins → Installed Plugins

Go to Users → All Users

Click “Switch To” under any user



**🧑‍💻 Developer Notes**

Does not modify roles, passwords, or user permissions

Fully compatible with modern WordPress versions

Works in multisite (if administrators have sufficient permissions)

📄 License

MIT (or whichever license you use)



**🏷️ Author**

Aide247
https://aide247.com/
