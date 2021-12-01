<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) :void {
    $parameters = $containerConfigurator->parameters();
    // for "merge" command
    //$parameters->set(Option::DATA_TO_APPEND, [
    //    ComposerJsonSection::REQUIRE_DEV => [
    //        'phpunit/phpunit' => '^9.5',
    //    ],
    //]);
};
