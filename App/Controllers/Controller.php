<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Controller
{
    protected $twig;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        require_once __DIR__ . '/../../config/config.php';

        $loader = new FilesystemLoader(__DIR__ . '/../views');        
        $this->twig = new Environment($loader, [
            // 'cache' => __DIR__ . '/../storage/cache',
            'debug' => true,
        ]);
        
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addGlobal('base_url', BASE_URL);
        
        $this->twig->addFunction(new TwigFunction('url', function ($path = '') {
            return url($path);
        }));
        
        $this->twig->addFunction(new TwigFunction('asset', function ($path = '') {
            return asset($path);
        }));
    }

    public function view($view, $data = []): void
    {
        if (file_exists("../App/views/{$view}.twig")) {
            echo $this->twig->render("{$view}.twig", $data);
        } else {
            if (file_exists('../App/views/errors/404.php')) {
                require_once '../App/views/errors/404.php';
            } else {
                die("404 - View not found: {$view}");
            }
        }
    }
}