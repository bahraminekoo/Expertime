<?php

namespace Expertime\Iclient\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Expertime\Iclient\Traits\UsersTrait;

class Users extends Template
{
    use UsersTrait;

    /**
     * CollectionFactory
     * @var null|CollectionFactory
     */
    protected $curl;

    public $_storeManager;

    const API_URL = 'https://reqres.in/api/users';

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Curl $curl,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->curl = $curl;
        parent::__construct($context, $data);
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        $json = $this->GetUsersByApi(self::API_URL);
        return json_decode($json, true);
    }

    /**
     * For a given user, returns its url
     * @param Post $post
     * @return string
     */
    public function getUserUrl(
        Post $post
    ) {
        return $this->_storeManager->getStore()->getBaseUrl() . 'blog/post/view/id/' . $post->getId();
    }

}