Logchecker
==========

A CD rip logchecker, used for analyzing the generated logs for any problems that would potentially
indicate a non-perfect rip was produced. Of course, just because a log doesn't score a perfect 100%
does not mean that the produced rip isn't bit perfect, it's just less likely. While this library can
largely run on both Linux and Windows, validating of checksums is only really supported for Linux.

While this library will analyze most parts of a log, unfortunately it cannot properly validate the checksums
for all types of logs. This is due to creators of these programs making their logchecker closed source
and involves some amount of custom mathematical work to produce it. Therefore, we have to fallback on
external methods to validate the checksums of EAC and XLD. If the logchecker detects that we do not have
the necessary programs, then we will just skip this external step and assume the checksum is valid. For
setting up the necessary programs to validate the checksum, see below for the given program you care about.

## Installation
```
$ composer require apollorip/logchecker
```

## Usage
```
<?php

use ApolloRIP\Logchecker\Logchecker;

include('vendor/autoload.php');

$logchecker = new Logchecker();
$logchecker->newFile('path/to/log.log');
var_dump($logchecker->getLogcheckerVersion());
var_dump($logchecker->getProgram());
$logchecker->parse();
var_dump($logchecker->getScore());
var_dump($logchecker->getDetails());
```

## Supported Programs
### Exact Audio Copy (EAC)
#### Description
Perhaps the most popular CD ripping program, [Exact Audio Copy (EAC)](http://exactaudiocopy.de) is a
windows application (but can be partially run on Linux under wine) that is great for generating bit 
perfect rips (assuming the CD is in good enough shape). Please see this 
[wiki article](https://example.com) to "properly" configure your installation such that it 
will generate a rip that will pass this logchecker (and most likely other sites' logcheckers).

#### Validating Checksums
Install a copy of EAC on a Windows machine or under Wine. You then need to navigate to the installed 
directory and copy `CheckLog.exe` (renaming it to `eac_logchecker.exe`) and `HelperFunctions.dll` to 
`/usr/local/bin/`.

### X Lossless Decoder (XLD)
#### Description


#### Validating Checksums
Clone the repository https://github.com/itismadness/xld_sign and build it following the readme. 
Move the generated binary (renaming it to xld_logchecker) to `/usr/local/bin`.

## Planned Programs:
* [whipper](https://github.com/JoeLametta/whipper) (waiting for full release)