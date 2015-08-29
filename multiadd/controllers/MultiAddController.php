<?php
namespace Craft;

// TO DEBUG
// Just change the debug var below to true - will stop cart submits and dump the cart array to the top of the page

class MultiAddController extends BaseController
{

    protected $allowAnonymous = true;
 
    private function logError($error){
        MultiAddPlugin::log($error, LogLevel::Error);
    }
   

    public function actionMultiAdd()
    {

        //Called via Ajax?
        $ajax = craft()->request->isAjaxRequest();

        //Get plugin settings
        $settings = craft()->plugins->getPlugin('multiAdd')->getSettings();
        //Settings to control behavour when testing - we don't want to debug via ajax or it stuffs up the JSON response...
        $debug = ($settings->debug and !$ajax);
      
        //Store items added to the cart in case of later failure & rollback required
        $rollback = array();

        //Require POST request
        $this->requirePostRequest();

        $errors = array();
        $items = craft()->request->getPost('item');

        if ($debug){
            echo '<h3>Items</h3><pre>';
            print_r($items);
            echo '</pre>';
        }

        if (!isset($items)) {
            $errors[] = "No items?";
            craft()->urlManager->setRouteVariables(['error' => 'No items?']);
        } 
        else {
            // Do some cart-adding!
            if (!$errors) {

                $needsRollback = false;

                foreach ($items as $key => $item) {
                    $purchasableId    = $item['purchasableId'];
                    $qty             = isset($item['qty']) ? (int)$item['qty'] : 0;
                    $cart            = craft()->market_cart->getCart();
                    $cart->setContentFromPost('fields');

                    if ($qty != 0) {
                        if ($debug){
                            echo 'Adding item: <pre>';
                            print_r($item);
                            echo '</pre>';
                        }
                        if (!craft()->market_cart->addToCart($cart, $purchasableId, $qty, $error)) {
                            $errors[] = $error;
                            $needsRollback = true;
                            break;
                        }
                        else{
                            //Store these for possible rollback later
                            $rollback[$purchasableId] = $qty;
                        }
                    }
                }

                //Hopefully all went well.  If not, we're in trouble...attempt to roll back the additions...
                //should use new update methods, but for now lets remove those items from the cart
                if ($needsRollback){
                    foreach ($rollback as $purchasableId => $qty){
                        echo '<pre>Rolling back item: ';
                        print_r($purchasableId);
                        echo '</pre>';
                        $cart = craft()->market_cart->getCart();
                        
                        try{
                            $lineItem = craft()->market_lineItem->getByOrderPurchasable($cart->id, $purchasableId);
                            craft()->market_cart->removeFromCart($cart, $lineItem->id);
                            craft()->market_order->save($cart);
                        }
                        catch (Exception $e) {
                            echo 'Failed rollback of item: ' . $e->getMessage() . '<pre>';
                            print_r($purchasableId);
                            echo '</pre>';   
                        }
                    }
                }

            }

            if ($errors) {
                foreach ($errors as $error) {
                    $this->logError($error);
                }
                craft()->urlManager->setRouteVariables(['error' => $errors]);
            } 
            else {
                craft()->userSession->setFlash('market', 'Products have been added');
                //only redirect if we're not debugging and we haven't submitted by ajax
                if (!$debug and !$ajax){
                    $this->redirectToPostedUrl();
                }
            }
        }

        // Appropriate Ajax responses...
        if($ajax){
            if($errors){
                $this->returnErrorJson($errors);
            }
            else{
                $this->returnJson(["success"=>true,"cart"=>craft()->market_cart->getCart()]);
            }
        }


    }

}
