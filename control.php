<?php
require_once("./vendor/autoload.php");
require_once('business.php');
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
function theTwig(){

    $loader = new FilesystemLoader('views');
    $twig = new Environment($loader, [
        'autoescape' => false,  // Disable auto-escaping
        'sandbox' => false,     // Disable the sandbox mode
    ]);
    return $twig;
}
