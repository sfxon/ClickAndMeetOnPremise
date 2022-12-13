<div class="login-con">
    <div class="c-card u-mb-xsmall">
        <header class="c-card__header u-pt-large mv-card-lower-padding">
            <a class="c-card__logo mv-c-card__logo" href="#!">
                <img src="{$TEMPLATE_URL}data/templates/{$TEMPLATE}/img/logo.png" alt="">
            </a>
            <h1 class="u-h3 u-text-center u-mb-zero mv-default-text-size-important">Willkommen zurück! Bitte melden Sie sich an.</h1>
        </header>
        
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