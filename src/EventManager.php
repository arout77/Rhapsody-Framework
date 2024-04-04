<?php

namespace Src;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventManager extends Event {

    protected $app;
    protected $data = [];
    protected $db;
    protected $config;
    protected $plugin;

    public function __construct($app, Array $data = null)
    {
        $this->app = $app;
        $this->data = $data;
        $this->db = $app['database'];
        $this->config = $app['config'];
        $this->plugin = $app['middleware'];
    }
}