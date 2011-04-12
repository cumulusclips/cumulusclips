<h1>Activate your Account</h1>

<?php if ($Success): ?>

    <div id="success">
        Thank you! Your account is now active. &nbsp;&nbsp;
        <a href="<?=HOST?>/myaccount/" title="My Account">Go to My Account</a>
    </div>

<?php elseif ($Error): ?>

    <div id="error">
        Invalid code! Please check you entered the code correctly.<br /><br />
        Your account may already be active. If you forgot your login,
        <a href="<?=HOST?>/login/forgot/" title="Forgot Login">click here</a>
        to retrieve it.
    </div>

<?php endif; ?>


