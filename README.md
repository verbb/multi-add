# MultiAdd

Provides an alternative controller to assist in adding multiple items to your Market Commerce cart at once.

Use the following code in your product template to make use of this new controller.

(N.B. version 0.0.6 introduced a new controller `multiaddfast` but the original controller `multiadd` is retained for backwards compatibility, and because the new controller does not yet support raising the standard Commerce hooks).

```
<form method="POST" id="addToCart">
    <input type="hidden" name="action" value="multiAdd/multiaddfast">
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

## Changelog

0.0.6 [Added] New `multiaddfast` controller to make adding large numbers of items to the cart much quicker. `multiadd` is kept for backwards compatibility.  This controller also appropriately fails & rolls back if any part of the transaction succeeds (vs. the original multiadd where you'd be left in an unknown state).

0.0.5 [Added] Add support for the options[whatever] system to multiadd

0.0.4 [Fixed] Updated to support Craft 2.5 and Commerce 0.9.1170+

0.0.3 [Added] Add support for line item notes & add JSON returns for ajax requests

0.0.2 [Added] Simple debugging support, JSON responses,Error logging to plugin log

0.0.1 [Added] Creation and initial version of multiadd

## Thanks

Thanks go out to [@lukeholder](https://github.com/lukeholder) and [Jeremy Daalder](https://github.com/bossanova808).
