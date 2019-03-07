<?php


namespace Parsley\Core;


use AMQPy\Client\Delivery;
use AMQPy\Client\Properties;

class Payload
{
    protected $name;
    protected $arguments;
    protected $attributes;

    /**
     * @var Properties
     */
    protected $properties;
    /**
     * @var Delivery
     */
    protected $delivery;

    // TODO: properties? but they probably should be array, not AMQPy/Properties unless they will be too broker-specific
    public function __construct($name, array $arguments, Properties $properties, array $attributes = [])
    {
        $this->name       = $name;
        $this->arguments  = $arguments;
        $this->properties = $properties; // ? $properties : new Properties();
        $this->attributes = $attributes;
    }

    public function getId()
    {
        return $this->properties->getMessageId();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getHeaders()
    {
        return $this->properties->getHeaders();
    }

    /**
     * @param array | \Traversable $headers
     */
    public function setHeaders($headers)
    {
        $this->properties->setHeaders($headers);
    }

    public function hasDelivery()
    {
        return $this->delivery !== null;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function setDelivery(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}