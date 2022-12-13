<div id="wsseller-session-container">
		<div class="container">
				<div class="row">
						<div class="col-xs-12">
								{$ERRORMESSAGE}
						</div>
				</div>
				
				<style>
						#wsseller-session-container { position: relative; }
						.login-container { 
								border-left: 7px solid #F1F2F6; border-right: 7px solid #F1F2F6; 
								border-top: 8px solid #F1F2F6; border-bottom: 8px solid #F1F2F6;
								margin-top: 100px;
						}
						.login-container h1 { 
								background-color: #CC0001; border: 1px solid #971718; color: #FFF; 
								text-align: center; margin-top: 0; text-transform: uppercase;
								font-size: 28px; padding: 6px 0px;
						}
						.login-container .form-group { width: 100%; }
						.login-container .input-group { width: 80%; }
						.login-container label { margin-right: 20px; margin-top: 20px; }
						.login-container input { width: 100%; }
						
						.login-container-subcontainer { padding-left: 20px; padding-right: 20px; }
						.fa { min-width: 16px; }
						.login-container-subcontainer .row { padding-bottom: 10px; }
						.login-container-subcontainer .action-row { text-align: center; }
						.login-container-subcontainer .action-row .btn { margin-top: 20px; padding-left: 30px; padding-right: 30px; }
						
				</style>
				
				<div class="row">
						<div class="col-xs-6 col-xs-offset-3">
								<div class="login-container">
										<h1>Standard-Seite</h1>
										
										<div class="login-container-subcontainer">								
												<form action="{$SITE_URL}?action=process" class="post-password-form form-inline" method="post">
														<div class="row">
																<div class="col-xs-12">
																		<div class="form-group">
																				<label for="login_name">Benutzer:</label>
																				<div class="input-group">
																						<div class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></div>
																						<input name="login_name" id="login_name" type="text" class="form-control" />
																				</div>
																		</div>
																</div>
														</div>
														<div class="row">
																<div class="col-xs-12">
																		<div class="form-group">
																				<label for="login_password">Passwort:</label>
																				<div class="input-group">
																						<div class="input-group-addon"><i class="fa fa-key" aria-hidden="true"></i></div>
																						<input name="login_password" id="login_password" type="password" class="form-control" />
                                                                                </div>
																		</div>
																</div>
														</div>
														<div class="row action-row">
																<div class="col-xs-12">								
																		<div class="form-group">
																				<button type="submit" class="btn btn-success">Jetzt anmelden</button>
																		</div>
																</div>
														</div>
												</form>
										</div>
								</div>
						</div>
				</div>
		</div>
</div>