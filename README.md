## COSCUP Website

### Prerequisite

* php interpreter

### Init submodules

* `git submodule init`
* `git submodule update`

### Configuration

Copy the following two configuration files and rename as `config.php`. Update according to your build environment.

* `deploy/config.example.php`  -> `deploy/config.php`
* `deploy/marksite/config.example.php`  -> `deploy/marksite/config.php` (optional, could be inherited from config.php above)

### HTML generation

Update web content by executing the following command:

    cd deploy ; php update.php

### Verify

* Launch a http server to host generated content.
