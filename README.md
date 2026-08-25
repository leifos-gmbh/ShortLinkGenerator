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
## Apply the Patch
Navigate to the ILIAS root directory and apply the patch:
```shell
patch -l -p1 < public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/patches/shl_patch_10.diff
```
Afterwards run the composer, for example:
```shell
php composer du
```

## Remove the Patch
To remove the patch, navigate to the ILIAS root directory and execute the following command:
```shell
patch -R -p1 < public/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLinkGenerator/patches/shl_patch_10.diff
```


## Configuration
Navigate to the ILIAS plugin administration and install the plugin. 
The shortlinks can be added, edited and deletede on the configure page of the plugin.
