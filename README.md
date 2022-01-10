## Name:

**Fucx\TorCurl** - Not a very complex thing, but very helpful curl wrapper utilizing Tor Proxy connection to be anonymous and for dynamically changing IP address.

## Version:

**1.0**

## Author:

Ole Chr. Thorsen <post@olethorsen.no>

## Requirements:

* PHP 7.1 or greater

## Description:

**Fucx\TorCurl** provides a curl wrapper that works together with Tor.
The main functionality is to start a Tor process and to perform curl requests (GET|POST).
This codepack is not perfect, but should be very helpful for those who wanna bypass ip-blocks when
performing to many requests against a server. Please use with good intent. I take no responsibiliy
for any misuse or damage caused by using this package and should be used with valid concent and at your own risk.

## Setup

Make sure you have Tor installed with CLI support. See [install instructions here](https://community.torproject.org/onion-services/setup/install/) for different OS.

## Troubleshooting

You can always add `verbose` mode to the ProxyConfig for full verbose reporting.

```
$config = new ProxyConfig();
$config->verbose();
```

- ProxyNotExistException: Means that Tor didn't start and it can't connect to the proxy or the proxy configuration was wrong. Check your /torrc file for proper IP and Port
- ProxyConnectionException: Problem getting identity from Tor connection. You have Tor installed? Test terminal command `tor`.
- TorCurlExecutionException: Your curl request returned some errors. Error should be thrown.
- NotImplementedException: I just didn't care to implement it.
- SingeltonException: Basically what it says. You have to initialize the curl wrapper through the static `Curl::init` method and not `new Curl`
- ProxyStartException: Problem starting proxy for some reason. Probably because of ProxyConfig parameter mismatch. Run verbose.

If you get SSL issues: `$curl->ignoreSSL()`.

Also, if Tor suddenly stop working properly, run `killall tor` and try again.

## Examples:

The source package comes with some examples of interacting with the
Tor Curl Wrapper. See the `examples/` directory in the source package.

## TODO: (I won't actually ever do this, so feel free to fork and do)

- Refactor the entire shit to look more tech savvy
- Implement all request methods that I didn't give a fucx about

## Copyright

Copyright (c) 2022 Ole Chr. Thorsen
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.