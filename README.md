# MultiAdd

Provides an alternative controller to assist in adding multiple items to your Craft Commerce cart at once.

Use the following code in your product template to make use of this new controller.

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

Alternatively, submit via Ajax & get JSON responses.  

```
 $("#addToCart").submit(function(e) {

        e.preventDefault();
        var data = $(this).serialize();
        data[window.csrfTokenName] = window.csrfTokenValue;

        $.post('/actions/' + $('input[name=action]').val(), data, function(response) {

                if (response.success) {
                    $("#addToCartButton").val("Added!");
                } 
                else {
                   $("#addToCartButton").val("Error!");
                }
        });
        
});
```

## Compatibility

This plugin has been tested with Craft 2.5 and Craft Commerce 1.0.1187.

## Changelog

0.0.6 [Added] Refactor as a controller & service, following the Commerce_CartService approach.  This makes it much faster and it supports rollback on failure too.

0.0.5 [Added] Add support for the new Commerce options[whatever] system

0.0.4 [Fixed] Updated with support Craft 2.5 and Commerce 0.9.1170+

0.0.3 [Added] Add support for line item notes & add JSON returns for ajax requests

0.0.2 [Added] Simple debugging support, JSON responses, error logging to plugin log

0.0.1 [Added] Creation and initial version of multiadd

## Thanks

Thanks go out to [@lukeholder](https://github.com/lukeholder) and [Jeremy Daalder](https://github.com/bossanova808).
