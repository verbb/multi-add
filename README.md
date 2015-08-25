# MultiAdd

Provides an alternative controller to assist in adding multiple items to your Market Commerce cart at once.

Use the following code in your product template to make use of this new controller.

```
<form method="POST">
    <input type="hidden" name="action" value="multiAdd/multiAdd">
    <input type="hidden" name="redirect" value="/cart">
    {{ getCsrfInput() }}

    {% for product in craft.market.products.find() %}
	    <input type="hidden" name="items[{{ loop.index }}][purchasableId]" value="{{ product.id }}">
	    <input type="hidden" name="items[{{ loop.index }}][qty]" value="1">
    {% endfor %}
</form>
```

## Thanks

Thanks go out to [@lukeholder](https://github.com/lukeholder) and [@jdaalder](https://twitter.com/jdaalder).