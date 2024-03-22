<?php

/**
 * CNTechnoLabs
 * Copyright (C) 2021 CNTechnoLabs 
 *
 * @category  CNTechnoLabs
 * @package   CNTechnoLabs_Autoinvoice
 * @copyright Copyright (c) 2021 CNTechnoLabs
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author    CNTechnoLabs
 */

namespace CNTechnoLabs\Autoinvoice\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class OfflinePayment implements ArrayInterface
{
     
    /**
    * Core store config
    *
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    private $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Payment\Model\Config $paymentConfig,
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->scopeConfig = $scopeConfig;
		$this->paymentConfig = $paymentConfig;
        $this->shippingConfig = $shippingConfig;
		
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $methods []= ['value' => '', 'label' => ''];
        
		$paymentMethodList  = $this->scopeConfig->getValue('payment');
		
		foreach ($paymentMethodList  as $code => $method){
			
			if(isset($method['title'])){
			
				$methodTitle = '';
				if(isset($method['group'])){
					if($method['group'] == 'offline') {
						$methodTitle .= $method['title'];
					}
				}			
				if($methodTitle != ''){
					$methods[] = [
						'value' => $code,
						'label' => $methodTitle,
					];	
				}
			}
		}
		
		return $methods;
		
    }
}