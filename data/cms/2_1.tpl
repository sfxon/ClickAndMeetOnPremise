<div class="login-con">
    <div class="c-card u-mb-xsmall">
        <form class="c-card__body" action="{$SITE_URL}?action=process" method="POST">
            <div class="d-field u-mb-small">
                {$ERRORMESSAGE}
            </div>
            
            <div class="c-field u-mb-small">
                <label class="c-field__label" for="login_name">E-Mail Adresse </label> 
                <input class="c-input" type="text" id="login_name" name="login_name" placeholder="E-Mail Adresse" /> 
            </div>

            <div class="c-field u-mb-small">
                <label class="c-field__label" for="login_password">Passwort</label> 
                <input class="c-input" type="password" id="login_password" name="login_password" placeholder="Passwort"> 
            </div>
    
            <br><button class="c-btn c-btn--danger c-btn--fullwidth" type="submit">Anmelden</button>
        </form>
    </div>
</div>