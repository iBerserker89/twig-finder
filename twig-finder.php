#!/usr/bin/env php
<?php
    require __DIR__ . '/vendor/autoload.php';

    use Symfony\Component\Console\Application;
    use TwigFinder\Command\SearchCommand;

    $app = new Application('Twig Finder CLI', '0.1.0');
    $app->add(new SearchCommand());
    $app->run();
