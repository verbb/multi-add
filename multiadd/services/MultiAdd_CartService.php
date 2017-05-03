<?php
namespace Craft;

use Commerce\Helpers\CommerceDbHelper;

class MultiAdd_CartService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Most of this is cribbed from the standard Commerce_CartService
     *
     * @param $order - a commerce cart
     * @param $items - array of items to multiadd, see github docs for an example.  Supports notes and options.
     * @param string $error - will return an error message after rolling back the transaction if failure
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function multiAddToCart($order, $items, &$error = '')
    {
        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;        

        // Saving current cart if it's new and empty
        if (!$order->id) {
            if (!craft()->commerce_orders->saveOrder($order)) {
                if ($transaction !== null)
                {
                    $transaction->rollback();
                }
                $error = Craft::t('Error on creating empty cart: ') . print_r($order->getAllErrors(), true);                                
                MultiAddPlugin::logError($error);
                throw new Exception($error);
            }
        }

        // Now loop through all our items and attempt to add them if any one fails, the whole thing fails
        $lineItems = [];

        foreach ($items as $key => $item) {
            $qty = isset($item['qty']) ? (int)$item['qty'] : 0;

            // Save time by only dealing with items we're actually trying to add
            if ($qty > 0) {

                $purchasableId = $item['purchasableId'];
                $note = isset($item['note']) ? $item['note'] : "";
                $error = "";

                // The following line means you can pass arbitrary options like this: items[0][options][note]
                $options = isset($item['options']) ? $item['options'] : [];

                // Filling item model
                $lineItem = craft()->commerce_lineItems->getLineItemByOrderPurchasableOptions($order->id, $purchasableId, $options);

                if ($lineItem) {
                    foreach ($order->getLineItems() as $item) {
                        if ($item->id == $lineItem->id) {
                            $lineItem = $item;
                        }
                    }

                    $lineItem->qty += $qty;
                } 
                else {                        
                    $lineItem = craft()->commerce_lineItems->createLineItem($purchasableId, $order, $options, $qty);
                }

                if ($note) {
                    $lineItem->note = $note;
                }

                $lineItems[] = $lineItem;
            }
        }

        // Be bold, be brave, assume success...!
        $success = true;

        // Raising event
        $event = new Event($this, [
            'lineItems' => $lineItems,
            'order' => $order,
        ]);
        $this->onBeforeMultiAddToCart($event);

        if (!$event->performAction) {
            $success = false;
        } 
        else {
            foreach ($lineItems as $lineItem) {
                $lineItem->validate();
                $lineItem->purchasable->validateLineItem($lineItem);

                if (!$lineItem->hasErrors()) {
                    if (!craft()->commerce_lineItems->saveLineItem($lineItem)) {
                        MultiAddPlugin::logError('Error when saving lineItem: ' . print_r($lineItem->getAllErrors(), true));                            
                        $success = false;
                        $errors = $lineItem->getAllErrors();
                        break;
                    }
                } 
                else {
                    MultiAddPlugin::logError('lineItem failed vaildation: ' . print_r($lineItem->getAllErrors(), true));
                    $success = false;
                    $errors = $lineItem->getAllErrors();
                    break;
                }              

            }
        }

        if ($success) {
            $orderSaveSuccess = craft()->commerce_orders->saveOrder($order);

            if ($orderSaveSuccess) {
                if ($transaction !== null)
                {
                    $transaction->commit();
                }
            } 
            else {
                MultiAddPlugin::logError('Error when saving order: ' . print_r($order->getAllErrors(), true));

                $errors = $order->getErrors();
                $error = array_pop($errors);

                if ($transaction !== null)
                {
                    $transaction->rollback();
                }

                return false;
            }

            // Raising event
            $event = new Event($this, [
                'lineItems' => $lineItems,
                'order' => $order,
            ]);
            $this->onMultiAddToCart($event);   

            return true;
        } 
        else {

            if ($transaction !== null)
            {
                $transaction->rollback();
            }            

            $errors = $lineItem->getAllErrors();
            $error = array_pop($errors);

            return false;
        }
    }


    // Event Handlers
    // =========================================================================

    /**
     * Before Event
     * Event params: order(Commerce_OrderModel), lineItems (array of Commerce_LineItemModel)
     *
     * @param \CEvent $event
     *
     * @throws \CException
     */
    public function onBeforeMultiAddToCart(\CEvent $event)
    {
        $params = $event->params;

        if (empty($params['order']) || !($params['order'] instanceof Commerce_OrderModel)) {
            throw new Exception('onBeforeMultiAddToCart event requires "order" param with OrderModel instance');
        }

        if (empty($params['lineItems'])) {
            throw new Exception('onBeforeMultiAddToCart event requires "lineItems" param with array of LineItemModel instances');
        }

        $this->raiseEvent('onBeforeMultiAddToCart', $event);
    }

    /**
     * Event method.
     * Event params: order(Commerce_OrderModel), lineItems (array of Commerce_LineItemModel)
     *
     * @param \CEvent $event
     *
     * @throws \CException
     */
    public function onMultiAddToCart(\CEvent $event)
    {
        $params = $event->params;

        if (empty($params['order']) || !($params['order'] instanceof Commerce_OrderModel)) {
            throw new Exception('onMultiAddToCart event requires "order" param with OrderModel instance');
        }

        if (empty($params['lineItems'])) {
            throw new Exception('onMultiAddToCart event requires "lineItems" param with array of LineItemModel instances');
        }

        $this->raiseEvent('onMultiAddToCart', $event);
    }

}
