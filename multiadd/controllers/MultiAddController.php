<?php
namespace Craft;

class MultiAddController extends Commerce_BaseFrontEndController
{

    protected $allowAnonymous = true;
 
    private function logError($error){
        MultiAddPlugin::log($error, LogLevel::Error);
    }


    public function actionMultiAddFast()
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

        $cart = craft()->commerce_cart->getCart();

        $errors = array();

        $items = craft()->request->getPost('items');

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
                $error = "";
                if (!craft()->multiAdd_cart->multiAddToCart($cart, $items, $error)) {
                    $errors[] = $error;  
                }              
            }

            if ($errors) {
                foreach ($errors as $error) {
                    $this->logError($error);
                }
                craft()->urlManager->setRouteVariables(['error' => $errors]);
            } 
            else {
                craft()->userSession->setFlash('commerce', 'Products have been added');
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
                $this->returnJson(['success'=>true,'cart'=>$this->cartArray($cart)]);
            }
        }


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

        $cart = craft()->commerce_cart->getCart();

        $errors = array();
        $items = craft()->request->getPost('items');

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
                    $qty              = isset($item['qty']) ? (int)$item['qty'] : 0; 
                    $note             = isset($item['note']) ? $item['note'] : ""; 
                    $error            = "";
                    //the following line means you can pass abritrary options like this: items[0][options][note]
                    $options          = isset($item['options']) ? $item['options'] : [];         

                    $cart->setContentFromPost('fields');

                    if ($qty != 0) {
                        if ($debug){
                            echo 'Adding item: <pre>';
                            print_r($item);
                            echo '</pre>';
                        }
                        if (!craft()->commerce_cart->addToCart($cart, $purchasableId, $qty, $note, $options, $error)) {
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

                //ROLLBACK code to go here once Luke's new controller is available

            }

            if ($errors) {
                foreach ($errors as $error) {
                    $this->logError($error);
                }
                craft()->urlManager->setRouteVariables(['error' => $errors]);
            } 
            else {
                craft()->userSession->setFlash('commerce', 'Products have been added');
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
                $this->returnJson(['success'=>true,'cart'=>$this->cartArray($cart)]);
            }
        }


    }

}
