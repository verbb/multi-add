<?php
namespace Craft;

class MultiAddController extends Commerce_BaseFrontEndController
{
    // Properties
    // =========================================================================

    protected $allowAnonymous = true;


    // Public Methods
    // =========================================================================

    public function actionMultiAdd()
    {
        // Called via Ajax?
        $ajax = craft()->request->isAjaxRequest();

        // Get plugin settings
        $settings = craft()->plugins->getPlugin('multiAdd')->getSettings();
        
        // Settings to control behavour when testing - we don't want to debug via ajax or it stuffs up the JSON response...
        $debug = ($settings->debugPOST and !$ajax);
      
        // Store items added to the cart in case of later failure & rollback required
        $rollback = array();

        // Require POST request & set up error handling
        $this->requirePostRequest();
        $errors = array();

        // Get the cart & form data
        $cart = craft()->commerce_cart->getCart();
        $items = craft()->request->getPost('items');

        // Some crude debugging support
        if ($debug) {
            echo '<h3>Items</h3><pre>';
            print_r($items);
            echo '</pre>';
        }

        if (!isset($items)) {
            $errors[] = "No items to add.";
        } else {
            $itemsToProcess = false;

            // Prevent submission of all 0 qtys            
            foreach ($items as $key => $item) {
                $qty = isset($item['qty']) ? (int)$item['qty'] : 0;

                if ($qty >0){
                    $itemsToProcess = true;
                    break;
                }
            }

            if (!$itemsToProcess) {                
                $errors[] = "All items have 0 quantity.";
            }
        }

        // Do some cart-adding using our new, faster, rollback-able service
        if (!$errors) {
            $error = "";

            if (!craft()->multiAdd_cart->multiAddToCart($cart, $items, $error)) {
                $errors[] = $error;  
            }              
        }

        if ($errors) {
            // Try to log referrer in case of misadventure - might help track down odd errors
            MultiAddPlugin::logError(craft()->request->getUrlReferrer());

            foreach ($errors as $error) {                            
                MultiAddPlugin::logError($error);
            }

            craft()->urlManager->setRouteVariables(['error' => $errors]);
        } else {
            craft()->userSession->setFlash('notice', 'Products have been added to cart');
            
            // Only redirect if we're not debugging and we haven't submitted by ajax
            if (!$debug and !$ajax) {
                $this->redirectToPostedUrl();
            }
        }

        // Appropriate Ajax responses...
        if ($ajax) {
            if ($errors) {
                $this->returnErrorJson($errors);
            } else {
                $this->returnJson(['success' => true, 'cart' => $this->cartArray($cart)]);
            }
        }

        // Not AJAX? We're done!
    }

    public function actionUpdateCart()
    {
        $this->requirePostRequest();
        $cart = craft()->commerce_cart->getCart();

        $errors = array();
        $items = craft()->request->getPost('items');

        foreach ($items as $lineItemId => $item) {
            $lineItem = craft()->commerce_lineItems->getLineItemById($lineItemId);

            // Fail silently if its not their line item or it doesn't exist.
            if (!$lineItem || !$lineItem->id || ($cart->id != $lineItem->orderId)) {
                return true;
            }

            $lineItem->qty = $item['qty'];

            if (!craft()->commerce_lineItems->updateLineItem($cart, $lineItem, $error)) {
                $errors[] = $error;
            }
        }

        // Set Coupon on Cart
        $couponCode = craft()->request->getPost('couponCode');

        if ($couponCode) {
            if (!craft()->commerce_cart->applyCoupon($cart, $couponCode, $error)) {
                $errors[] = $error;
            }
        }

        if ($errors) {
            craft()->userSession->setError(Craft::t('Couldn’t update line item: {message}', [
                'message' => $error
            ]));
            
            MultiAddPlugin::logError('Couldn’t update line item: [$error]');
        } else {
            craft()->userSession->setNotice(Craft::t('Items updated.'));
            $this->redirectToPostedUrl();
        }
    }
}
