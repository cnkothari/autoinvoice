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

namespace CNTechnoLabs\Autoinvoice\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	
	 const ENABLE_AUTOINVOICE = 'cntech_autoinvoice/general/enable_autoinvoice';
	 const OFFLNE_PAYMENT_METHODS = 'cntech_autoinvoice/general/offline_payment_method';
	 const GENEREATE_INVOICE = 'cntech_autoinvoice/general/generate_auto_invoive';
	 const GENEREATE_SHIPMENT = 'cntech_autoinvoice/general/generate_auto_shipment';
	 
	 public function __construct(
		\Magento\Framework\App\Helper\Context $context
	 ){
		 parent::__construct($context);		 
	 }
	 
	 
	 
	 public function getEnableAutoInvoice(){	
		return $this->scopeConfig->getValue(self::ENABLE_AUTOINVOICE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getOffLinePymentMethods(){	
		return $this->scopeConfig->getValue(self::OFFLNE_PAYMENT_METHODS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getGeneratedInvoice(){
		return $this->scopeConfig->getValue(self::GENEREATE_INVOICE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getGeneratedShipment(){
		return $this->scopeConfig->getValue(self::GENEREATE_SHIPMENT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
}
