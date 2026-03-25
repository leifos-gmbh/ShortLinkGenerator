# Shortlink Generator

## Installation
Navigate to the ILIAS root directory and execute the following commands:
```shell
    mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
```
```shell
    cd public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
```
```shell
    git clone --branch 10 https://github.com/leifos-gmbh/ShortLinkGenerator.git
```
Run the composer in the ILIAS root directory:
```bash
    composer du
```

### .htaccess patch
Important: Beginning with ILIAS 10, everytime the composer is executed, the .htaccess file is reset and the patch needs to be applied again.


Add the following lines to the .htaccess file located in at <ILIAS root>/public/.htaccess
directory, or Apache-Config, at the end of the section 'IfModule mod_rewrite.c':
```apacheconf
    RewriteEngine On # <-- Only needed if the rewrite engine is not already enabled.
    RewriteCond %{REQUEST_URI} ^/([A-Z]|[a-z]|[0-9]|_|-)+$
    RewriteRule ^(.*)$ /Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/ilShortLinkResolver.php [L]
```

If the 'IfModule mod_rewrite.c' section does not exist, instead add:
```apacheconf
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/([A-Z]|[a-z]|[0-9]|_|-)+$
    RewriteRule ^(.*)$ /Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/ilShortLinkResolver.php [L]
</IfModule>
```


## Configuration
Navigate to the ILIAS plugin administration and install the plugin. 
The shortlinks can be added, edited and deletede on the configure page of the plugin.
