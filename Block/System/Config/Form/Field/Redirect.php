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

namespace Sulaeman\SocialLogin\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;

use Sulaeman\SocialLogin\Helper\Social;

/**
 * Backend system config datetime field renderer
 */
class Redirect extends Field
{
    /**
     * @var \Sulaeman\SocialLogin\Helper\Social
     */
    protected $helper;

    /**
     * @var string
     */
    protected $socialType = '';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Sulaeman\SocialLogin\Helper\Social $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Social $helper,
        array $data = []
    )
    {
        $this->helper = $helper;
        
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        switch ($this->socialType) {
            case 'Facebook':
                $url = $this->helper->getBaseAuthUrl();
                break;
            
            default:
                $url = $this->helper->getAuthUrl($this->socialType);
                break;
        }

        $html = '<input ';
        $html .= 'style="opacity:1;" readonly ';
        $html .= 'id="' . $element->getHtmlId() . '" ';
        $html .= 'class="input-text admin__control-text" ';
        $html .= 'value="' . $url . '" ';
        $html .= 'onclick="this.select()" type="text" />';

        return $html;
    }
}
