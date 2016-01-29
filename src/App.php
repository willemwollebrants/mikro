<?php

namespace Studiow\Mikro;

use Studiow\Mikro\Config;
use Studiow\PHPTemplate\Engine;
use League\Container\Container;
use League\Route\RouteCollection;
use League\Event\Emitter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class App
{

    private $dir;
    private $request;

    /**
     * DI Container
     * @var \League\Container\Container 
     */
    public $container;

    public function __construct($baseDir)
    {
        $this->dir = realpath($baseDir);
        $this->buildContainer();
    }

    private function buildContainer()
    {
        $self = $this;
        
        $this->container = new Container();

        $this->container->singleton(Config::class, function() use($self) {
            $filename = "{$self->dir}/config/config.php";
            $settings = file_exists($filename) ? include $filename : [];
            return new Config($settings);
        });

        $this->container->singleton(Engine::class, function() use ($self) {
            return new Engine("{$self->dir}/templates");
        });

        $this->container->singleton(RouteCollection::class, function() use ($self) {
            $router = new RouteCollection($self->container);
            $routerfile = "{$self->dir}/config/router.php";
            if (file_exists($routerfile)) {
                include $routerfile;
            }
            return $router;
        });


        $this->container->singleton(Emitter::class);
    }

    /**
     * @return \Studiow\Mikro\Config
     */
    public function config()
    {
        return $this->container->get(Config::class);
    }

    /**
     * @return \League\Event\Emitter
     */
    public function events()
    {
        return $this->container->get(Emitter::class);
    }

    /**
     * Get the current request
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = Request::createFromGlobals();
        }
        return $this->request;
    }

    /**
     * Execute a request
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function execute(Request $request = null)
    {
        $this->request = $request? : $this->getRequest();
        $router = $this->container->get(RouteCollection::class);
        $response = $router->getDispatcher()->dispatch($this->request->getMethod(), $this->request->getPathInfo());
        $response->send();
    }

}
