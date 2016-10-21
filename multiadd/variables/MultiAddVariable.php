<?php
namespace Craft;

class MultiAddVariable
{

    public function multiAddToCart($cart, $items){
    
        $error = "";

        //Must be an array of items, each being a purchasable ID and a qty
        if(!is_array($items)){
            return "Must supply an array of items, each with a purchasableId and a qty (and optionally note, options)";
        }
        else {
            craft()->multiAdd_cart->multiAddToCart($cart, $items, $error);
        }

        if($error){
            return $error;
        }
        else {
            return "Items added to cart";
        }

    }

    public function removeAllLineItems($cart){

        craft()->commerce_cart->clearCart($cart);

    }

    public function removeLineItem($cart, $lineItemId){

        craft()->commerce_cart->removeFromCart($cart, $lineItemId);

    }


}
