## Installation

Navigate to the ILIAS main directory and create the folder structure with:

```bash
    mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
```

Clone the 6 branch of the shortlink-plugin from github:

```bash
    cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
    git clone --branch 6 https://github.com/leifos-gmbh/ShortLinkGenerator.git
```

Add the following lines to the .htaccess file located in the ILIAS main
directory, or Apache-Config, at the end of the section 'IfModule mod_rewrite.c':

```apacheconf
    RewriteEngine On # <-- Only needed if the rewrite engine is not already enabled.
    RewriteCond %{REQUEST_URI} /([A-Z]|[a-z]|[0-9]|_|-)+$
    RewriteRule ^(.*)$ /Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/ilShortLinkResolver.php [L]
```

If the 'IfModule mod_rewrite.c' section does not exist, instead add:

```apacheconf
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} /([A-Z]|[a-z]|[0-9]|_|-)+$
    RewriteRule ^(.*)$ /Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/ilShortLinkResolver.php [L]
</IfModule>
```

Finally, the shortlink-plugin needs to be installed by an administrator by selecting the install option for the shortlink-plugin on the "Administration->Plugins" page.

The shortlinks can be added, edited and deletede on the configure page of the plugin.
The plugin does not need to be activated to work.
