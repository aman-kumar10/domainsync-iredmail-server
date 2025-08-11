{include file=$tplVar.header}


<div class="top-alertMsgs"></div>

<div class="domainSync-container">
    <form action="" method="post" class="form-horizontal">
        <h2 class="domainSync-form">{$LANG['domainsync_form']}</h2>
        <div class="form-group">
            <label for="inputClient" class="col-lg-6 col-sm-6 control-label">{$LANG['selectClientsTitle']}</label>
            <div class="col-lg-6 col-sm-6">
                <select id="selectUserid" name="userid"
                    class="form-control selectize selectize-client-search" data-value-field="id"
                    data-active-label="Active" data-inactive-label="Inactive"
                    placeholder="Start Typing to Search Clients">
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="selectedProduct" class="col-lg-6 col-sm-6 control-label">{$LANG['selectProductsTitle']}</label>
            <div class="col-lg-6 col-sm-6">
                <select name="products" id="selectedProduct" class="form-control required-field">
                    <option value="" hidden>{$LANG['selectProductsOp']} </option>
                    {foreach from=$tplVar['products'] item=product}
                        <option value="{$product->id}">{$product->name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        
        <div id="cstmFldHead" style="display: none;">
            <h2>{$LANG['customFieldsHead']}</h2>
        </div>

        <div class="customfields-loader"></div>

        <div id="customFieldsContainer"></div>

        <div class="btn-container">
            <input type="submit" value="{$LANG['addOrder_btn']}" class="btn btn-primary" id="btnAddOrder">
        </div>

    </form>
</div>