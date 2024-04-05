<?php

namespace Arharp\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class PlanningCenterResourceOwner implements ResourceOwnerInterface
{
    private $response;

    /**
     * Creates a new instance of PlanningCenterResourceOwner class.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response['data']['id'] ?: null;
    }

    /**
     * Gets display name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['data']['attributes']['name'] ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }
}
