<?php
namespace Craft;

use Commerce\Helpers\CommerceDbHelper;

class MultiAdd_CartService extends BaseApplicationComponent
{


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
        CommerceDbHelper::beginStackedTransaction();

        //saving current cart if it's new and empty
        if (!$order->id) {
            if (!craft()->commerce_orders->saveOrder($order)) {
                throw new Exception(Craft::t('Error on creating empty cart'));
            }
        }

        //now loop through all our items and attempt to add them
        //if any one fails, the whole thing fails
        $lineItems = [];

        foreach ($items as $key => $item) {

            $qty = isset($item['qty']) ? (int)$item['qty'] : 0;

            //Save time by only dealing with items we're actually trying to add
            if ($qty > 0) {

                $purchasableId = $item['purchasableId'];
                $note = isset($item['note']) ? $item['note'] : "";
                $error = "";
                //the following line means you can pass arbitrary options like this: items[0][options][note]
                $options = isset($item['options']) ? $item['options'] : [];

                //filling item model
                $lineItem = craft()->commerce_lineItems->getLineItemByOrderPurchasableOptions($order->id, $purchasableId, $options);

                if ($lineItem)
                {
                    foreach ($order->getLineItems() as $item)
                    {
                        if ($item->id == $lineItem->id)
                        {
                            $lineItem = $item;
                        }
                    }
                    $lineItem->qty += $qty;
                }
                else
                {
                    $lineItem = craft()->commerce_lineItems->createLineItem($purchasableId, $order, $options, $qty);
                }

                if ($note)
                {
                    $lineItem->note = $note;
                }

                $lineItems[] = $lineItem;
            }
        }

        //Be bold, be brave, assume success...!
        $success = true;

        //raising event
        $event = new Event($this, [
            'lineItems' => $lineItems,
            'order' => $order,
        ]);
        $this->onBeforeMultiAddToCart($event);

        if(!$event->performAction){
            $success = false;
            CommerceDbHelper::rollbackStackedTransaction();
        }
        else{
            foreach ($lineItems as $lineItem){

                $lineItem->validate();
                $lineItem->purchasable->validateLineItem($lineItem);

                try {
                    if(!$lineItem->hasErrors()){
                        if (!craft()->commerce_lineItems->saveLineItem($lineItem)) {
                            $success = false;
                            break;
                        }
                    }
                    else{
                        $success = false;
                        $errors = $lineItem->getAllErrors();
                        break;
                    }              

                } catch (\Exception $e) {
                    $success = false;
                    CommerceDbHelper::rollbackStackedTransaction();
                    throw $e;
                }
            }
        }

        if($success){
            craft()->commerce_orders->saveOrder($order);
            CommerceDbHelper::commitStackedTransaction();

            //raising event
            $event = new Event($this, [
                'lineItems' => $lineItems,
                'order' => $order,
            ]);
            $this->onMultiAddToCart($event);   

            return true;
        }
        else{
            CommerceDbHelper::rollbackStackedTransaction();
            $error = array_pop($errors);
            return false;
        }
    }

 
}
