# WHMCS Paypalych Gateway Module #

## Summary ##

Gateway modules allow you to integrate Paypalych with the WHMCS
platform.

## Module Content ##

```
 modules/gateways/
  |- callback/paypalych.php
  |  paypalych.php
 modules/widgets/
  |  PaypalychBalance.php
```

## Minimum Requirements ##
* WHMCS installation (tested on version 8.6.0).
* Paypalych merchant account (https://paypalych.com)

## Installation ##
1. Copy modules folder to the root folder of your WHMCS installation.
2. Activate the Paypalych module in your WHMCS admin panel (Setup -> Payment Gateways -> All Payment Gateways).
3. Configure `Shop ID`.
4. Configure `API token`.
5. Configure `result url` in the paypalych account (https://your.domain/modules/gateways/callback/paypalych.php).

## Useful Resources
* [Developer Resources](https://developers.whmcs.com/)
* [Hook Documentation](https://developers.whmcs.com/hooks/)
* [API Documentation](https://developers.whmcs.com/api/)
* [Paypalych API Documentation](https://paypalych.com/reference/api)