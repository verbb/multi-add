<?php
namespace Craft;

class MultiAddController extends Commerce_BaseFrontEndController
{

    protected $allowAnonymous = true;

    /**
     * @param $error
     */
    private function logError($error){
        MultiAddPlugin::log($error, LogLevel::Error);
    }


    /**
     * @throws HttpException
     */
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

        //Require POST request & set up erro handling
        $this->requirePostRequest();
        $errors = array();

        //Get the cart & form data
        $cart = craft()->commerce_cart->getCart();
        $items = craft()->request->getPost('items');

        //some crude debugging support
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
            // Do some cart-adding using our new, faster, rollback-able service
            if (!$errors) {
                $error = "";
                if (!craft()->multiAdd_cart->multiAddToCart($cart, $items, $error)) {
                    $errors[] = $error;  
                }              
            }

            //trouble?
            if ($errors) {
                foreach ($errors as $error) {
                    $this->logError($error);
                }
                craft()->urlManager->setRouteVariables(['error' => $errors]);
            }
            //everything went fine!
            else {
                craft()->userSession->setFlash('notice', 'Products have been multiadd-ed');
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

        //Not AJAX? We're done!
    }
}
