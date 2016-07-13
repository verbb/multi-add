# MultiAdd

Provides an alternative controller to assist in adding multiple items to your Craft Commerce cart at once.

Use the following code in your product template to make use of this new controller.  

Notes:

* The array *must* be named `items` and you must supply at least a `purchasableId` and a `qty`. 
* Attributes are grouped together by a key in the form `items[key][attribute]` where key can be any value but must be the same for each of the attributes.  `loop.index` is an obvious choice for this if iterative over products or variations in a loop. 
* You can also POST an optional `note` per line item
* You can also POST arbitrary options, e.g. `[options][color]`
* Items with a zero `qty` are simply skipped and not added to the cart. 

Here's some example code to get you started - if you get stuck, just ask on the Craft CMS Slack #craftcommerce channel for help.

```
<form method="POST" id="addToCart">
    <input type="hidden" name="action" value="multiAdd/multiAdd">
    <input type="hidden" name="redirect" value="/cart">
    {{ getCsrfInput() }}

    {% for product in craft.market.products.find() %}
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

Alternatively, submit via Ajax & get a JSON response, which (on success) includes a comprehensive cart object with all the data you should need.

```
 $("#addToCart").submit(function(e) {

        e.preventDefault();
        var data = $(this).serialize();
        data[window.csrfTokenName] = window.csrfTokenValue;

        $.post('/actions/' + $('input[name=action]').val(), data, function(response) {

                if (response.success) {
                    $("#addToCartButton").val("Added!");
                    cart.update( response.cart );
                } 
                else {
                   $("#addToCartButton").val("Error!");
                }
        });
        
});
```

## Events

This plugin raises two events, much like normal the Commerce add to cart, which you can listen for in the same way.  They are:

`onBeforeMultiAddToCart` and `onMultiAddToCart`

In each case the event parameters are:

`order` (Commerce_OrderModel)
`lineItems` (an array of Commerce_LineItemModel)

## Compatibility

This plugin has been tested with Craft 2.5 and Craft Commerce 1.0.1187.

## Changelog

See [releases.json](https://raw.githubusercontent.com/engram-design/MultiAdd/master/releases.json)

## Credits

By [Josh Crawford](https://github.com/engram-design) (of S. Group) (@crawf on Craft CMS Slack) and [Jeremy Daalder](https://github.com/bossanova808) (@jeremydaalder), with thanks to [Luke Holder](https://github.com/lukeholder) (@lukeholder).
