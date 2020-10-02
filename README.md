# Mautic Referrals Plugin

[![license](https://img.shields.io/packagist/v/maatoo/mautic-referrals-bundle.svg)](https://packagist.org/packages/maatoo/mautic-referrals-bundle) 
[![Packagist](https://img.shields.io/packagist/l/maatoo/mautic-referrals-bundle.svg)](LICENSE)
[![mautic](https://img.shields.io/badge/mautic-3-blue.svg)](https://www.mautic.org/mixin/referrals/)

This plugins extends Mautic forms with a 'Referrals' field that enables Mautic to build Referral campaigns. New or existing contacts can invite additional contacts. Invitees are added as contacts to Mautic with a freely selectable tag.

## How it works
The plugin adds a new form field type ‘Referrals’. This field has a configuration to specify how many text inputs are shown in the form and what tags should be set on referred contacts.
Once the form is submitted and the email addresses are valid, a new contact is created for each specified email address, or updated if one already exists with the same email address.

The plugins keeps then a reference which contact invited the new contact (Referral). Additionally, you can use {referrerfield=...} tokens in emails to access contact fields of the referring contact. That enables you to build a campaign based on a segment that includes referred contacts (by filtering with the specified tag) and send a welcome email to those new contacts including for example the first name of the referring contact or a personal message.

## Feedback
Ideas and suggestions are welcome, feel free to create an issue or PR on Github.

## License
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