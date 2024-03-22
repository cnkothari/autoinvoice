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
 
namespace CNTechnoLabs\Autoinvoice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;


class Autoinvoice implements ObserverInterface
{
	protected $_order;
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
		\CNTechnoLabs\Autoinvoice\Helper\Data $dataHelper,
		\Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
		\Magento\Sales\Model\Service\InvoiceService $invoiceService,
		\Magento\Framework\DB\TransactionFactory  $transactionFactory,
		\Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory
    ) {
         $this->_order = $order;
		 $this->dataHelper = $dataHelper;
		 $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
		 $this->_invoiceService = $invoiceService;
		 $this->_transactionFactory = $transactionFactory;   
		  $this->_convertOrderFactory = $convertOrderFactory;   
    }
    public function execute(\Magento\Framework\Event\Observer $observer) {
       
         $orderids = $observer->getEvent()->getOrderIds();
		 
		 $enableModule = $this->dataHelper->getEnableAutoInvoice();
		 $offlinePayments = $this->dataHelper->getOffLinePymentMethods();
		 $generateInvoice = $this->dataHelper->getGeneratedInvoice();
		 $generateShipment = $this->dataHelper->getGeneratedShipment();
		 $_offlinePayments 	= explode(",",$offlinePayments); //converts string to array
		 if($enableModule) {
			 foreach($orderids as $orderid){ 
				$order = $this->_order->load($orderid);
				$incrementId = $order->getIncrementId();
				//echo $incrementId;
				$order_invoice = $this->_invoiceCollectionFactory->create()
									  ->addAttributeToFilter('order_id', array('eq'=>$order->getId()));
									  
				$order_invoice->getSelect()->limit();
				if ((int)$order_invoice->count() !== 0) {
						return $this;
				}	
				if ($order->getState() == \Magento\Sales\Model\Order::STATE_NEW) {
					  try {
						  if(!$order->canInvoice()) {
							$order->addStatusHistoryComment('CNTechnoLabs_Autoinvoice: Order cannot be invoiced.', false);
							$order->save();  
						 }
						 $payment_method = $order->getPayment()->getMethodInstance()->getCode();
						 //if($payment_method == 'cashondelivery'){
						 if(in_array($payment_method, $_offlinePayments)){
							 /* code for invoice */
							 if($generateInvoice) {
								 $invoice = $this->_invoiceService->prepareInvoice($order);
								 $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
								 $invoice->register();
								 $invoice->getOrder()->setCustomerNoteNotify(false);          
								 $invoice->getOrder()->setIsInProcess(true);
								 $order->addStatusHistoryComment('Automatically INVOICED by CNTechnoLabs_Autoinvoice.', false);
								 
								 $transactionSave = $this->_transactionFactory->create();
								 $transactionSave->addObject($invoice); 
								 $transactionSave->addObject($invoice->getOrder()); 
								 $transactionSave->save();
							 }	 
							 
							 /* code for invoice */
							 
							 /* code for shipment */
							  if($generateShipment) {
									 $convertOrder = $this->_convertOrderFactory->create();
									 //$shipment = $order->prepareShipment();
									 $shipment = $convertOrder->toShipment($order);
									
									 // Loop through order items
									 foreach ($order->getAllItems() AS $orderItem) {
											// Check if order item has qty to ship or is virtual
											if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
												continue;
											}
											$qtyShipped = $orderItem->getQtyToShip();
											// Create shipment item with qty
											$shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
											// Add shipment item to shipment
											$shipment->addItem($shipmentItem);
									  }
									  
									 // Register shipment 
									 $shipment->register();
									 //$order->setIsInProcess(true);
									 $shipment->getOrder()->setIsInProcess(true);
									 $order->addStatusHistoryComment('Automatically SHIPPED by CNTechnoLabs_Autoinvoice.', false);
									 
									 $transactionSave = $this->_transactionFactory->create();
									 $transactionSave->addObject($shipment); 
									 $transactionSave->addObject($shipment->getOrder()); 
									 $transactionSave->save();
							  }	 
		
							 /* code for shipment */
						 }
						  
					  }catch (Exception $e) {
							$order->addStatusHistoryComment('CNTechnoLabs_Autoinvoice: Exception occurred during autoInvoice action. Exception message: '.$e->getMessage(), false);
							$order->save();
					  } 
				}
					 
			 }
		 }
		 return $this;		
    }
}