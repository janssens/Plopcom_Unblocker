<?php
/**
 * Created by PhpStorm.
 * User: Gaëtan
 * Date: 23/08/2016
 * Time: 17:57
 */

class Plopcom_Unblocker_Adminhtml_Unblockeradmin_IndexController extends Mage_Adminhtml_Controller_Action
{


    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/plopcom_unblocker');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function uncancelItemAction()
    {

        if ($data = $this->getRequest()->getParams()) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $item = Mage::getModel('sales/order_item')->load($this->getRequest()->getParam('item_id'));
            $_order_helper = Mage::helper('unblocker/order');
            if ($_order_helper->isCanceled($item)){
                $item->setQtyCanceled(0)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Item cancel qty set to 0'));
            }
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $item->getOrderId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function fixOrderItemInvoicedQtyAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            $item = Mage::getModel('sales/order_item')->load($this->getRequest()->getParam('item_id'));
            $_invoice_helper = Mage::helper('unblocker/invoice');
            $real_qty_invoiced = $_invoice_helper->getQtyInvoiced($item);
            if ($real_qty_invoiced < $item->getQtyInvoiced()) {
                $item->setQtyInvoiced($real_qty_invoiced)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Invoice qty fixed'));
            } else if ($real_qty_invoiced > $item->getQtyInvoiced()) {
                $item->setQtyInvoiced($real_qty_invoiced)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Invoice qty fixed'));
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice($this->__('This item as not invoiced qty error'));
            }
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $item->getOrderId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    public function changeStateAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
            $state = $this->getRequest()->getParam('state');
            $status = $this->getRequest()->getParam('status');
            $isCustomerNotified = $this->getRequest()->getParam('notify') == '1';
            $dispatch = $this->getRequest()->getParam('dispatch') == '1';
            $comment = 'PLOPCOM UNBLOCKED'. ' ' . 'change state';
            if ($state == Mage::helper('unblocker/order')->realOrderState($order)){
                $order->setData('state', $state);
                $order->setStatus($status);
                $history = $order->addStatusHistoryComment($comment, false);
                $history->setIsCustomerNotified($isCustomerNotified);
                $order->save();
                if ($dispatch){
                    Mage::dispatchEvent('sales_order_status_after', array('order' => $order, 'state' => $state, 'status' => $status, 'comment' => $comment, 'isCustomerNotified' => $isCustomerNotified, 'shouldProtectState' => true));
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('State and status changed'));
            }else{
                Mage::getSingleton('adminhtml/session')->addError($this->__('Order cannot change state for %s',$state));
                Mage::getSingleton('adminhtml/session')->addError($this->__('state should be %s (but is %s)',$state,Mage::helper('unblocker/order')->realOrderState($order)));
            }
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function fixOrderItemShippedQtyAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            $item = Mage::getModel('sales/order_item')->load($this->getRequest()->getParam('item_id'));
            $_shipment_helper = Mage::helper('unblocker/shipment');
            $real_qty_shipped = $_shipment_helper->getQtyShipped($item);
            if ($real_qty_shipped < $item->getQtyShipped()) {
                $item->setQtyShipped($real_qty_shipped)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Invoice qty fixed'));
            } else if ($real_qty_shipped > $item->getQtyShipped()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This qty shipped is too low, you should create a new shipment'));
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice($this->__('This item as not shipped qty error'));
            }
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $item->getOrderId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    public function updateShipmentGridRecordsAction(){
        if ($data = $this->getRequest()->getParams()) {
            $shipment = Mage::getModel('sales/order_shipment')->load($this->getRequest()->getParam('entity_id'));
            $shipment->getResource()->updateGridRecords($shipment->getId());//.???
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('OK'));
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $shipment->getOrder()->getId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function fixOrderItemOrderedQtyAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            $item = Mage::getModel('sales/order_item')->load($this->getRequest()->getParam('item_id'));
            $qty = intval($item->getQtyOrdered());
            $_invoice_helper = Mage::helper('unblocker/invoice');
            $_shipment_helper = Mage::helper('unblocker/shipment');
            $real_qty_invoiced = $_invoice_helper->getQtyInvoiced($item);
            $real_qty_shipped = $_shipment_helper->getQtyShipped($item);
            if (($qty <= 0) || ($qty < $real_qty_shipped) || ($qty < $real_qty_invoiced)) {
                $item->setQtyOrdered(max(intval($real_qty_invoiced),intval($real_qty_shipped)))->save();

            }
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ordered qty fixed'));
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $item->getOrderId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function removeShipmentItemAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            $item = Mage::getModel('sales/order_shipment_item')->load($this->getRequest()->getParam('item_id'));
            $qty = intval($item->getQty());
            $item->setQty(0)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Shipped qty set to 0'));
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => Mage::getModel('sales/order_shipment')->load($item->getParentId())->getOrderId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function forceInvoiceAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
            try {
                if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                }
                if(!$order->canInvoice())
                {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
                }

                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }

                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transactionSave->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Invoice created'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            return $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId(), 'showTab' => 'unblocker'));
        } else {
            return $this->_redirect('adminhtml/sales_order/index');
        }
    }

}