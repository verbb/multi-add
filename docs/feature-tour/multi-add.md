# Multi Add

Provides an alternative controller to assist in adding multiple items to your Craft Commerce cart at once.

Also provides some handy twig tags for adding items, removing a line item, and clearing the cart directly in your templates.

## Adding Multiple Items To Your Cart

Use the following code in your product template to make use of this new controller.

Notes:

- The array _must_ be named `items` and you must supply at least a `purchasableId` and a `qty`.
- Attributes are grouped together by a key in the form `items[key][attribute]` where key can be any value but must be the same for each of the attributes. `loop.index` is an obvious choice for this if iterative over products or variations in a loop.
- You can also POST an optional `note` per line item
- You can also POST arbitrary options, e.g. `[options][color]`
- Items with a zero `qty` are simply skipped and not added to the cart.

Here's some example code to get you started - if you get stuck, just ask on the Craft CMS Slack #craftcommerce channel for help.

This code displays all your products in one form:

```twig
<form method="POST" id="addToCart">
    <input type="hidden" name="action" value="multiAdd/multiAdd">
    <input type="hidden" name="redirect" value="/cart">
    {{ getCsrfInput() }}

    {% for product in craft.commerce.products.find() %}
        <input type="hidden" name="items[{{ loop.index }}][purchasableId]" value="{{ product.defaultVariant.id }}">
        <input type="hidden" name="items[{{ loop.index }}][qty]" value="1">
        <input type="text" name="items[{{ loop.index }}][note]">
        
        <select name="items[{{ loop.index }}][options][color]">
            <option value="blue">Blue</option>
            <option value="white">White</option>
            <option value="red">Red</option>
        </select>
    {% endfor %}
</form>
```

Or say you want to list the variants of a particular product out:

```twig
<form method="POST" id="addToCart">
    <input type="hidden" name="action" value="multiAdd/multiAdd">
    <input type="hidden" name="redirect" value="/cart">
    {{ getCsrfInput() }}

    {% set product = craft.commerce.products.slug('your-product') %}

    {% for variant in product.variants %}
        <input type="hidden" name="items[{{ loop.index }}][purchasableId]" value="{{ variant.id }}">
        <input type="hidden" name="items[{{ loop.index }}][qty]" value="1">
        <input type="text" name="items[{{ loop.index }}][note]">
    {% endfor %}
</form>
```

Alternatively, submit via Ajax & get a JSON response, which (on success) includes a comprehensive cart object with all the data you should need.

```js
$("#addToCart").submit(function(e) {
    e.preventDefault();

    var data = $(this).serialize();
    data[window.csrfTokenName] = window.csrfTokenValue;

    $.post('/actions/' + $('input[name=action]').val(), data, function(response) {
        if (response.success) {
            $("#addToCartButton").val("Added!");
            cart.update( response.cart );
        } else {
           $("#addToCartButton").val("Error!");
        }
    });
});
```

## Updating Multiple Items In Your Cart

When viewing your cart, it's currently not possible to update all your line items at once - instead it must be done for each line item as a separate event. This controller let's you update multiple line items at once. This might be desirable when a user has multiple line items in their cart, and wants to update quantities all at once by clicking an 'Update Cart' button.

To achieve this, create your cart template using the following guide:

```twig
<form method="POST">
    <input type="hidden" name="action" value="multiAdd/updateCart">
    <input type="hidden" name="redirect" value="/cart">
    {{ getCsrfInput() }}

    {% for item in cart.lineItems %}
        <input type="text" size="4" name="items[{{ item.id }}][qty]" value="{{ item.qty }}">
    {% endfor %}

    <button type="submit">Update Cart</button>
</form>
```