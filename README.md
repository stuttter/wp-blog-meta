# WP Blog Meta

A global, joinable meta-data table for your WordPress Multisite sites

* Seamlessly integrates into WordPress's WP_Site_Query
* Safe, secure, & designed to scale

# Installation

## WordPress

Must be installed **from main network** to ensure fully activation :

* Download and install using the built in WordPress plugin installer
* Network Activate in the "Plugins" area of your network-admin by clicking the "Activate" link
* Optionally drop the entire `wp-blog-meta` directory into `mu-plugins`
* No further setup or configuration is necessary

## Composer 

Add the repository source : `{ "type": "vcs", "url": "https://github.com/BeAPI/disable-srcset" }`

And include `"stuttter/wp-blog-meta": "dev-master"` into your composer file.

# FAQ

### Does this create new database tables?

Yes. It adds `wp_blogmeta` to `$wpdb->ms_global_tables`.

### Does this support object caching?

Yes. It uses a global `blogmeta` cache-group for all meta data.

### Does this modify existing database tables?

No. All of WordPress's core database tables remain untouched.

### Where can I get support?

The WordPress support forums: https://chat.flox.io/support/channels/wp-blog-meta

### Can I contribute?

Yes, please!

* Public - https://github.com/stuttter/wp-blog-meta/
* Bleeding - https://code.flox.io/stuttter/wp-blog-meta/
