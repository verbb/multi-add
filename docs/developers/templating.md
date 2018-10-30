# Templating

MultiAdd also provides a few extra useful twig tags that you can use to perform cart operations directly in your templates.

Example - Setup:

```twig
{% set cart = craft.commerce.cart %}
{% set items = [] %}

{% set items = items|merge([{"purchasableId":1385,"qty":1, "note":"Test note"}]) %}
{% set items = items|merge([{"purchasableId":854,"qty":2, "options": {"colour":"green"} }]) %}
```

Items is an array, and now holds:

```php
array(2) {
  [0]=>
  array(3) {
    ["purchasableId"]=>
    int(1385)
    ["qty"]=>
    int(1)
    ["note"]=>
    string(9) "Test note"
  }
  [1]=>
  array(3) {
    ["purchasableId"]=>
    int(854)
    ["qty"]=>
    int(2)
    ["options"]=>
    array(1) {
      ["colour"]=>
      string(9) "green"
    }
  }
}
```

Example Twig Operations:

```twig
# add items to cart
{{ craft.multiAdd.multiAddToCart(cart, items) }}

# remove the first line item
{{ craft.multiAdd.removeLineItem(cart, cart.lineItems[0].id ) }}

# clear the cart
{{ craft.multiAdd.removeAllLineItems(cart) }}
```