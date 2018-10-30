# Events Reference

This plugin raises two events, much like normal the Commerce add to cart, which you can listen for in the same way. They are:

`onBeforeMultiAddToCart` and `onMultiAddToCart`

In each case the event parameters are:

`order` (Commerce_OrderModel) `lineItems` (an array of Commerce_LineItemModel)

```php
craft()->on('multiAdd_cart.onBeforeMultiAddToCart', function($event) {
    $order = $event->params['order'];
    $lineItems = $event->params['lineItems'];
    $event->performAction = false;
});
```