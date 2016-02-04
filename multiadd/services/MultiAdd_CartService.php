<?php
namespace Craft;

use Commerce\Helpers\CommerceDbHelper;

//This is a faster multi add to cart that will also fail if part of the transaction fails.

class MultiAdd_CartService extends BaseApplicationComponent
{

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

            $purchasableId    = $item['purchasableId'];
            $qty              = isset($item['qty']) ? (int)$item['qty'] : 0; 
            $note             = isset($item['note']) ? $item['note'] : ""; 
            $error            = "";
            //the following line means you can pass arbitrary options like this: items[0][options][note]
            $options          = isset($item['options']) ? $item['options'] : [];         


            //filling item model
            $lineItem = craft()->commerce_lineItems->getLineItemByOrderPurchasableOptions($order->id, $purchasableId, $options);

            if ($lineItem) {
                $lineItem->qty += $qty;
            } else {
                $lineItem = craft()->commerce_lineItems->createLineItem($purchasableId, $order->id, $options, $qty);
            }

            if ($note) {
                $lineItem->note = $note;
            }

            $lineItems[] = $lineItem;

        }

        $success = true;


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

        if($success){

            craft()->commerce_orders->saveOrder($order);
            CommerceDbHelper::commitStackedTransaction();

            return true;
        }
        else{

            CommerceDbHelper::rollbackStackedTransaction();
            $error = array_pop($errors);
            return false;
        }
    }

 
}
