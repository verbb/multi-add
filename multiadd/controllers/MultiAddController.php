<?php
namespace Craft;

class MultiAddController extends BaseController
{
    protected $allowAnonymous = array('actionMultiAdd');

    public function actionMultiAdd()
    {
        $this->requirePostRequest();

        $errors = array();
        $items = craft()->request->getPost('items');

        if (!isset($items)) {
            craft()->urlManager->setRouteVariables(array('error' => 'You must select some items.'));
        } else {

            // Do some cart-adding!
            if (!$errors) {
                foreach ($items as $key => $item) {
                    $purchasableId   = $item['purchasableId'];
                    $qty             = isset($item['qty']) ? (int)$item['qty'] : 0;
                    $cart            = craft()->market_cart->getCart();
                    $cart->setContentFromPost('fields');

                    if ($qty > 0) {
                        if (!craft()->market_cart->addToCart($cart, $purchasableId, $qty, $error)) {
                            $errors[] = $error;
                        }
                    }
                }
            }

            if ($errors) {
                craft()->urlManager->setRouteVariables(array('error' => $errors));
            } else {
                craft()->userSession->setFlash('market', 'Product has been added');
                $this->redirectToPostedUrl();
            }
        }
    }

}