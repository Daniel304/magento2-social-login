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

namespace Sulaeman\SocialLogin\Api;

/**
 * Interface providing token generation for Customers
 *
 * @api
 */
interface TokenServiceInterface
{
    /**
     * Create access token for admin given the customer credentials.
     *
     * @param string $socialId
     * @param string $socialType
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function createAccessToken($socialId, $socialType);

    /**
     * Revoke token by customer id.
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeAccessToken($customerId);
}
