{block name="title" prepend}{$LNG.adm_login}{/block}
{block name="content"}
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{$LNG.adm_login}</h3>
                </div>
                <div class="panel-body">
                    <form role="form" method="post">
                        <input type="hidden" name="mode" value="verify">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Username" type="text" value="{$username}" readonly>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="password" type="password" value="" autofocus>
                            </div>
                            <!-- Change this to a button or input when using this as a form -->
                            <button type="submit" class="btn btn-lg btn-success btn-block">{$LNG.adm_login}</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}