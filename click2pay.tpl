<div class="control-group">
    <label class="control-label" for="click2pay_username">{__("click2pay_username")}</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][username]" id="click2pay_username" value="{$processor_data.processor_params.username|escape}" size="40">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="click2pay_password">{__("click2pay_password")}</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][password]" id="click2pay_password" value="{$processor_data.processor_params.password|escape}" size="40">
    </div>
</div>
