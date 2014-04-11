Opauth-Elance
=============
[Opauth 1.0][1] strategy for Elance authentication.

Implemented based on https://www.elance.com/q/api2/getting-started

Getting started
----------------
1. Install Opauth-Elance:
   ```bash
   cd path/to/app/root
   composer require opauth/elance:~1.0
   ```

2. Request an Elance API key at https://www.elance.com/q/api/request-key

3. Configure Opauth-Elance strategy with at least `Client ID` and `Client Secret`.

4. Direct user to `http://path_to_opauth/elance` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Elance' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
)
```

Currently, the only valid value for `scope` parameter is `basicInfo`.

Refer to [Elance API guide](https://www.elance.com/q/api) for complete Elance API documentation and support.

License
---------
Opauth-Elance is MIT Licensed
Copyright © 2014 Opauth (https://opauth.org)

[1]: https://opauth.org
