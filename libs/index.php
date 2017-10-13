<?php

require_once SEARCHSTRAP_PLUGIN_PATH . 
             '/libs/Symfony/Component/ClassLoader/ClassLoader.php';
use Symfony\Component\ClassLoader\ClassLoader;

$loader = new ClassLoader();
$loader->register();

// load Solarium
$loader->addPrefix('Solarium', 
                   SEARCHSTRAP_PLUGIN_PATH . '/libs');
// load symfony
$loader->addPrefix('Symfony', 
                   SEARCHSTRAP_PLUGIN_PATH . '/libs');
