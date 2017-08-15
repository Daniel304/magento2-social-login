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
namespace Sulaeman\SocialLogin\Plugin\Store\Model\Service;

use Magento\Framework\Model\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Store\Model\Service\StoreConfigManager as MagentoStoreConfigManager;

use Sulaeman\SocialLogin\Helper\Social;

class StoreConfigManager
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
     * @var \Sulaeman\PaypalWebService\Helper\Service
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributeFactory
     * @param \Sulaeman\SocialLogin\Helper\Social $helper
     */
    public function __construct(
        Context $context, 
        EncryptorInterface $encryptor, 
        ExtensionAttributesFactory $extensionAttributeFactory, 
        Social $helper
    )
    {
        $this->_logger = $context->getLogger();
        $this->_extensionAttributeFactory = $extensionAttributeFactory;
        $this->_helper = $helper;

        $this->_helper->setEncryptor($encryptor);
    }

    /**
     * Interceptor getStoreConfigs.
     *
     * @param \Magento\Store\Model\Service\StoreConfigManager $subject
     *
     * {@inheritdoc}
     */
    public function afterGetStoreConfigs(
        MagentoStoreConfigManager $subject, 
        array $storeConfigs
    ) {
        foreach ($storeConfigs as $index => $config) {
            $this->_helper->buildXmlPath('facebook');
            $facebookAppId = $this->_helper->getClientId($config->getId());
            $facebookAppSecret = $this->_helper->getClientSecret($config->getId());

            $this->_helper->buildXmlPath('google');
            $googleClientId = $this->_helper->getClientId($config->getId());
            $googleClientSecret = $this->_helper->getClientSecret($config->getId());
            $googleAndroidClientId = $this->_helper->getAndroidClientId($config->getId());
            $googleIosClientId = $this->_helper->getIosClientId($config->getId());
            $googlePermissions = $this->_helper->getPermissions($config->getId());

            $this->_helper->buildXmlPath('paypal');
            $paypalClientId = $this->_helper->getClientId($config->getId());
            $paypalClientSecret = $this->_helper->getClientSecret($config->getId());
            
            $extension = $config->getExtensionAttributes();
            if (is_null($extension)) {
                $extension = $this->_extensionAttributeFactory->create('\Magento\Store\Api\Data\StoreConfigInterface');
            }

            if (method_exists($extension, 'setSocialFacebookAppId')) {
                $extension->setSocialFacebookAppId($facebookAppId);
            }

            if (method_exists($extension, 'setSocialFacebookAppSecret')) {
                $extension->setSocialFacebookAppSecret($facebookAppSecret);
            }

            if (method_exists($extension, 'setSocialGoogleClientId')) {
                $extension->setSocialGoogleClientId($googleClientId);
            }

            if (method_exists($extension, 'setSocialGoogleClientSecret')) {
                $extension->setSocialGoogleClientSecret($googleClientSecret);
            }

            if (method_exists($extension, 'setSocialGoogleAndroidClientId')) {
                $extension->setSocialGoogleAndroidClientId($googleAndroidClientId);
            }
            
            if (method_exists($extension, 'setSocialGoogleIosClientId')) {
                $extension->setSocialGoogleIosClientId($googleIosClientId);
            }

            if (method_exists($extension, 'setSocialGooglePermissions')) {
                $extension->setSocialGooglePermissions($googlePermissions);
            }
            
            if (method_exists($extension, 'setSocialPaypalClientId')) {
                $extension->setSocialPaypalClientId($paypalClientId);
            }

            if (method_exists($extension, 'setSocialPaypalClientSecret')) {
                $extension->setSocialPaypalClientSecret($paypalClientSecret);
            }

            $config->setExtensionAttributes($extension);
        }

        return $storeConfigs;
    }
}
