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

namespace Sulaeman\SocialLogin\Observer\Customer\Model\ResourceModel\CustomerRepository;

use Magento\Framework\Model\Context;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SaveAfterDataObject implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ExtensionAttributesFactory
     */
    protected $_extensionAttributeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributeFactory
     */
    public function __construct(
        Context $context, 
        ExtensionAttributesFactory $extensionAttributeFactory
    )
    {
        $this->_logger = $context->getLogger();
        $this->_extensionAttributeFactory = $extensionAttributeFactory;
    }

    /**
     * Save customer social login information
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getOrigCustomerDataObject();

        $extension = $customer->getExtensionAttributes();
        if (is_null($extension)) {
            $extension = $this->_extensionAttributeFactory->create('\Magento\Customer\Api\Data\CustomerInterface');
        }

        $socialId = $extension->getSocialId();
        $socialType = $extension->getSocialType();

        if ( ! empty($socialId) && ! empty($socialType)) {
            $provider = \Magento\Framework\App\ObjectManager::getInstance()->get(
                sprintf('\Sulaeman\SocialLogin\Provider\%s', ucfirst($socialType))
            );

            $provider->createCustomer([
                'email'      => $customer->getEmail(),
                'identifier' => $socialId
            ]);
        }

        return $this;
    }
}
