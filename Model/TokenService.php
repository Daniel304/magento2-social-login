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

namespace Sulaeman\SocialLogin\Model;

use Magento\Framework\Model\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;

class TokenService implements \Sulaeman\SocialLogin\Api\TokenServiceInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    /**
     * Initialize service
     *
     * @param \Magento\Framework\Model\Context $context
     * @param TokenModelFactory $tokenModelFactory
     * @param AccountManagementInterface $accountManagement
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     */
    public function __construct(
        Context $context, 
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper
    ) {
        $this->_logger = $context->getLogger();
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createAccessToken($socialId, $socialType)
    {
        $this->getRequestThrottler()->throttle($socialType.'-'.$socialId, RequestThrottler::USER_TYPE_CUSTOMER);

        $provider = \Magento\Framework\App\ObjectManager::getInstance()->get(
            sprintf('\Sulaeman\SocialLogin\Provider\%s', ucfirst($socialType))
        );

        $customerDataObject = $provider->getCustomer($socialId);

        if (is_null($customerDataObject)) {
            $this->getRequestThrottler()->logAuthenticationFailure($socialType.'-'.$socialId, RequestThrottler::USER_TYPE_CUSTOMER);
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }

        $this->getRequestThrottler()->resetAuthenticationFailuresCount($socialType.'-'.$socialId, RequestThrottler::USER_TYPE_CUSTOMER);
        
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($customerId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByCustomerId($customerId);
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The tokens could not be revoked.'));
        }
        return true;
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }
}
