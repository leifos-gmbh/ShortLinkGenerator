Installation:
Navigate to the ILIAS_6 directory and create the folder structure with:

    mkdir Customizing/global/plugins/Services/UIComponent/UserInterfaceHook

Clone the Plugin from github:

    cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
    git clone ### TODO

Add the following two lines to the .htaccess file located in the ILIAS_6
directory at the end of the section 'IfModule mod_rewrite.c':

    RewriteCond %{REQUEST_URI} /([a-z]|[A-Z])([A-Z]|[a-z]|[0-9])+$
    RewriteRule ^(.*)$ /ILIAS_6/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/ilShortLinkResolver.php [L]

Finally the shortlinkplugin needs to be installed by selecting the install option of the shortlinkplugin on the plugin page.

The shortlinks can be added, edited and deletede on the configure page of the plugin.
The plugin does not need to be activated to work.