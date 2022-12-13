<div class="container-fluid">
		<nav class="navbar navbar-default mv-navbar" role="navigation">
				<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
								<span class="sr-only">{literal}{{/literal}$smarty.const.TEXT_TOGGLE_NAVIGATION{literal}}{/literal}</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
						</button>
				</div>
				
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<div class="navbar-left navbar-text">
								{literal}{{/literal}$NAVBAR_TITLE{literal}}{/literal}
						</div>
				</div>
		</nav>
		
		<div class="mvbox">
				<div class="mvbox-body">
						<div class="row">
								<div class="col-xs-12">					
										<form role="form" action="{literal}{{/literal}$DATA.url{literal}}{/literal}" method="POST" class="form">
												<h1>Datensatz löschen</h1>
												<p>Möchten Sie diesen Datensatz wirklich unwiderruflich löschen? <span class="text-danger">WARNUNG! Diese Aktion kann nicht rückgängig gemacht werden!</span></p>
												
												<div class="row">
														<div class="form-group col-sm-12">
																<input type="hidden" name="id" id="id" value="{literal}{{/literal}$DATA.data.id{literal}}{/literal}" />
																<button type="submit" class="btn btn-danger" name="button_delete" value="delete">{literal}{{/literal}$smarty.const.TEXT_BUTTON_DELETE{literal}}{/literal}</button>
																<button type="submit" class="btn btn-primary" name="button_do_not_delete" value="not_delete">{literal}{{/literal}$smarty.const.TEXT_BUTTON_CANCEL{literal}}{/literal}</button>
														</div>
												</div>
										</form>
								</div>
						</div>
				</div>
		</div>
</div>