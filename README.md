## COSCUP Website

### Prerequisite

* You need to clone [COSCUP/marksite](https://github.com/COSCUP/marksite.git) to generate *.html from *.md. Put it under `deploy` folder.
* php interpreter

### Configuration

Copy the following two configuration files and rename as `config.php`. Update according to your build environment.

* `deploy/config.example.php`  -> `deploy/config.php`
* `deploy/marksite/config.example.php`  -> `deploy/marksite/config.php`

### HTML generation

Update web content by executing the following command:

    cd deploy ; php update.php

### Verify

* Launch a http server to host generated content.
* `ln -s coscup2014-website/theme <HTTP_ROOT>/2014-theme` 
