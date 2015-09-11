# MultiAdd

Provides an alternative controller to assist in adding multiple items to your Market Commerce cart at once.

Use the following code in your product template to make use of this new controller.

```
<form method="POST" id="addToCart">
    <input type="hidden" name="action" value="multiAdd/multiAdd">
    <input type="hidden" name="redirect" value="/cart">
    {{ getCsrfInput() }}

    {% for product in craft.market.products.find() %}
	    <input type="hidden" name="items[{{ loop.index }}][purchasableId]" value="{{ product.id }}">
	    <input type="hidden" name="items[{{ loop.index }}][qty]" value="1">
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

## Thanks

Thanks go out to [@lukeholder](https://github.com/lukeholder) and [@bossanova808](https://github.com/bossanova808).
