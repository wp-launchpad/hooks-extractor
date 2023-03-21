<?php

namespace RocketLauncherHooksExtractor;

use RocketLauncherBuilder\App;
use RocketLauncherBuilder\ServiceProviders\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function attach_commands(App $app): App
    {

        return $app;
    }
}
