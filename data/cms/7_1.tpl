<div class="mv-container-min-full">
		<main class="form-signin">
        <form action="{$SITE_URL}?action=process" method="post">
            <div class="login-con">
                <div class="c-card u-mb-xsmall">
                    <h1 class="u-h3 u-text-center u-mb-zero mv-default-text-size-important">Admin-Login</h1>
                        <div class="d-field u-mb-small">
                            {$ERRORMESSAGE}
                        </div>
                        
                        <div class="c-field u-mb-small">
                            <label for="login_name" class="visually-hidden">E-Mail Adresse </label> 
                            <input class="form-control" type="text" id="login_name" name="login_name" placeholder="E-Mail Adresse" /> 
                        </div>
            
                        <div class="c-field u-mb-small">
                            <label class="visually-hidden" for="login_password">Passwort</label> 
                            <input class="form-control" type="password" id="login_password" name="login_password" placeholder="Passwort"> 
                        </div>
                
                        <button class="w-100 btn btn-lg btn-primary" type="submit">Einloggen</button>
                </div>
            </div>
        </form>
    </main>
</div>