<?php

$configurator->onCompile[] = function (Nette\Config\Configurator $configurator, Nette\Config\Compiler $compiler) {
    $compiler->addExtension('dibi', new DibiNetteExtension);
    $compiler->addExtension('twitter', new Netrium\Addons\Twitter\TwitterExtension);
};

\Nette\Forms\Container::extensionMethod('addDatePicker', function (\Nette\Forms\Container $container, $name, $label = NULL) {
    return $container[$name] = new \JanTvrdik\Components\DatePicker($label);
});
