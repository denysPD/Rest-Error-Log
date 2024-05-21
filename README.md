# KG Rest Error Log

This module adds plugin on rest api processing controller and on exception catch - logs error message.

Then, error is written to file var/log/rest-error.log. So, you can see same error response, as it was during execution

Extension catches the same exceptions as appear in original requests and then throws the same exception types again. This allows to keep same error codes and responses on API as it was before
