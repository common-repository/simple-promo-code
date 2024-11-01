=== Simple Promo Code ===
Contributors: pkwooster
Tags: promo, short code, tracking
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.1

A very simple plugin that allows an editor to track accesses to a document by promo code.

== Description ==

This plugin allows editors to request a promo code and optional email address from users to get access to a document and track its usage.


*Features for Editors*

* a short code [promo] is provided that will display a form that requests a 
 promo code and optionally an email address from your users and then takes them to a url.
* a summary of the accesses using this code is displayed to editors by the short code
* editors can clear the logging from the short code
* there is no back end admin page 

*Short Code Arguments are:*

* code= a string that must be entered  access the document, required
* document= a url to the document that you want to track, required
* name= the name to display for the document, required
* email= 'yes' to request the user's email
* id= the id on the form tag, useful for CSS, default is 'promo'
* promolabel= the label for the promo code input field, default is 'Please enter promo code for name'
* emaillabel= the label for the email input field, default is 'Please enter your email address'
* liststyle= the style or class for the ul, default is 'style="list-style:none"'

== Installation ==

1. Use the Plugins, Add New menu in WordPress to install the plugin or upload the `simple-promo-code` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. add the [promo] short code where required

== Changelog ==

= 1.1 =
* Test for WordPress 3.8 - new wp-admin interface

= 1.0 =
* First release.

= 1.0.1 =
* Update readme

== Upgrade Notice ==
