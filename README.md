# Mautic Referrals Plugin

[![license](https://img.shields.io/packagist/v/maatoo/mautic-referrals-bundle.svg)](https://packagist.org/packages/maatoo/mautic-referrals-bundle) 
[![Packagist](https://img.shields.io/packagist/l/maatoo/mautic-referrals-bundle.svg)](LICENSE)
[![mautic](https://img.shields.io/badge/mautic-3-blue.svg)](https://www.mautic.org/mixin/referrals/)

This plugin enables referrals in Mautic 3.x.

Ideas and suggestions are welcome, feel free to create an issue or PR on Github.

Licensed under GNU General Public License v3.0.

## Author
This plugin is developed and maintained by [maatoo.io](https://maatoo.io)

## Installation via composer (preferred)

Execute `composer require maatoo/mautic-referrals-bundle:1.*` in the main directory of the mautic installation.

## Installation via .zip
Download the .zip file, extract it into the `plugins/` directory and rename the new directory to `MauticReferralsBundle`.

* Download for Mautic 3: [MauticReferralsBundle.zip](https://github.com/maatoo-io/mauticreferralsbundle/archive/main.zip)

Clear the cache via console command `php app/console cache:clear --env=prod` (might take a while) *OR* manually delete the `app/cache/prod` directory.

## Configuration
Navigate to the Plugins page and click "Install/Upgrade Plugins". You should now see a "Referrals" plugin. Open it and set it to published.

## Usage
Add "Referrals" field to the Form and specify number of email inputs to display and what tags to set on referrals:
![mautic form](/doc/form_preview.png?raw=true "Mautic Form with Referrals")

Use `{referrerfield=...}` tokens in emails to include contact fields from the referring contact.

## Known Issues
n/a

## Ideas
- Add `{referrerfield=...}` tokens to list of email tokens
- Dynamically add/remove input fields in the form and ability to specify a maximum in the form