Light_Kit_Store
===========
2021-04-06 -> 2021-06-24



A store where **website creators** can purchase [light kit](https://github.com/lingtalfi/Light_Kit) products.




This is a [Light plugin](https://github.com/lingtalfi/Light/blob/master/doc/pages/plugin.md).

This is part of the [universe framework](https://github.com/karayabin/universe-snapshot).


Install
==========

Using the [planet installer](https://github.com/lingtalfi/Light_PlanetInstaller) via [light-cli](https://github.com/lingtalfi/Light_Cli)
```bash
lt install Ling.Light_Kit_Store
```

Using the [uni](https://github.com/lingtalfi/universe-naive-importer) command.
```bash
uni import Ling/Light_Kit_Store
```

Or just download it and place it where you want otherwise.






Summary
===========
- [Light_Kit_Store api](https://github.com/lingtalfi/Light_Kit_Store/blob/master/doc/api/Ling/Light_Kit_Store.md) (generated with [DocTools](https://github.com/lingtalfi/DocTools))
- [Services](#services)






Services
=========


Here is an example of the service configuration:

```yaml

kit_store:
    instance: Ling\Light_Kit_Store\Service\LightKitStoreService
    methods:
        setContainer:
            container: @container()
        setOptions:
            options:
                captchaKeys: ${kit_store_vars.captcha_keys}
                notFoundRoute: ${kit_store_vars.not_found_route}



kit_store_vars:
    front_theme: Ling.Light_Kit_Store/theme1
    not_found_route: lks_route-404
    captcha_keys: []




# --------------------------------------
# hooks
# --------------------------------------
$vars.methods_collection:
    -
        method: setVar
        args:
            key: kit_store_vars
            value: ${kit_store_vars}


$user_manager.methods_collection:
    -
        method: addPrepareUserCallback
        args:
            callback:
                instance: @service(kit_store)
                callable_method: prepareUser
#    -
#        method: setSessionDuration
#        args:
#            sessionTime: 500
```



History Log
=============


- 0.0.5 -- 2021-06-24

    - add route to api, and events to register
  
- 0.0.4 -- 2021-06-21

    - updated routes
  
- 0.0.3 -- 2021-06-19

    - fix functional typo in front_theme
  
- 0.0.2 -- 2021-06-18

    - add home controller
  
- 0.0.1 -- 2021-04-06

    - initial commit