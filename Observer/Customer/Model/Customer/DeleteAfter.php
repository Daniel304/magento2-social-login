<?php

/**
 * This file is part of the Sulaeman Social Login package.
 *
 * @author Sulaeman <me@sulaeman.com>
 * @copyright Copyright (c) 2017
 * @package Sulaeman_SocialLogin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sulaeman\SocialLogin\Observer\Customer\Model\Customer;

use Magento\Framework\Model\Context;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

use Sulaeman\SocialLogin\Api\SocialLoginRepositoryInterface;

use Magento\Framework\Exception\NoSuchEntityException;

class DeleteAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Sulaeman\SocialLogin\Api\SocialLoginRepositoryInterface
     */
    protected $repository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Sulaeman\SocialLogin\Api\SocialLoginRepositoryInterface $repository
     */
    public function __construct(
        Context $context, 
        SocialLoginRepositoryInterface $repository
    )
    {
        $this->_logger    = $context->getLogger();
        $this->repository = $repository;
    }

    /**
     * Save customer social login information
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getDataObject();

        try {
            $items = $this->repository->getAllByCustomerId($customer->getId());

            foreach ($items as $item) {
                $item->delete();
            }
        } catch (NoSuchEntityException $e) {}

        return $this;
    }
}
