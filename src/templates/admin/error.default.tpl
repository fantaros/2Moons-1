{block name="title" prepend}{$LNG.fcm_info}{/block}
{block name="content"}
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{$LNG.fcm_info}</h3>
                </div>
                <div class="panel-body">
                    <p>{$message}</p>
                    {if !empty($redirectButtons)}<p id="infoButtons">{foreach $redirectButtons as $button}<a href="{$button.url}"><button class="btn btn-lg btn-success">{$button.label}</button></a>{/foreach}</p>{/if}
                </div>
            </div>
        </div>
    </div>
</div>
{/block}