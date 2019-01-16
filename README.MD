SilverStripe eid module
==================

This module provide a belgian card reader authentication method.

The integration on the LoginForm expects that the method updateExternalAuth is called in order to add the button to the form.

Useful links:
- Based on : https://github.com/e-Contract/eid-idp-php-example
- Also read : http://www.uvcw.be/no_index/e-communes/dossier_eid/Belgian_eid_run-time_developers_kit%2025.pdf
- Available fields from identity provider : https://www.e-contract.be/developers/webapp/idp/openid

Define identity server url
==================

Simply define in your _ss_environment.php:

    define('EID_IDENTITY_SERVER','url_to_server');

Compatibility
==================
Tested with 3.7+

Maintainer
==================
LeKoala - thomas@lekoala.be

