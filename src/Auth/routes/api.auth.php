<?php


    declare(strict_types = 1);

    use BetterWP\Application\Config;
    use BetterWP\Auth\Controllers\AccountController;
    use BetterWP\Auth\Controllers\AuthConfirmationEmailController;
    use BetterWP\Auth\Controllers\AuthSessionController;
    use BetterWP\Auth\Controllers\ConfirmedAuthSessionController;
    use BetterWP\Auth\Controllers\ForgotPasswordController;
    use BetterWP\Auth\Controllers\LoginMagicLinkController;
    use BetterWP\Auth\Controllers\RecoveryCodeController;
    use BetterWP\Auth\Controllers\RegistrationLinkController;
    use BetterWP\Auth\Controllers\ResetPasswordController;
    use BetterWP\Auth\Controllers\TwoFactorAuthSessionController;
    use BetterWP\Auth\Controllers\TwoFactorAuthPreferenceController;
    use BetterWP\Routing\Router;

    /** @var Router $router */
    /** @var Config $config */

    // Dynamic endpoints which the developer can configure in the config to his likings.
    $login = $config->get('auth.endpoints.login');
    $magic_link = $config->get('auth.endpoints.magic-link');
    $confirm = $config->get('auth.endpoints.confirm');

    // Login
    $router->middleware('guest')->group(function (Router $router) use ($config, $login, $magic_link) {


        $router->get("/$login", [AuthSessionController::class, 'create'])
               ->name('login');

        $router->post("/$login", [AuthSessionController::class, 'store'])
               ->middleware('csrf');

        // Magic-link
        if ($config->get('auth.authenticator') === 'email') {

            $router->post("$login/$magic_link", [LoginMagicLinkController::class, 'store'])
                   ->middleware('csrf')
                   ->name('login.create-magic-link');

            $router->get("$login/$magic_link", [AuthSessionController::class, 'store'])
                   ->name('login.magic-link');

        }

    });

    // Logout
    $router->get('/logout/{user_id}', [AuthSessionController::class, 'destroy'])
           ->middleware('signed:absolute')
           ->name('logout')
           ->andNumber('user_id');

    // Auth Confirmation
    $router->middleware(['auth', 'auth.unconfirmed'])->group(function (Router $router) use ($magic_link, $confirm) {


        $router->get("$confirm", [ConfirmedAuthSessionController::class, 'create'])->name('confirm');

        $router->post("$confirm", [ConfirmedAuthSessionController::class, 'store'])
               ->middleware('csrf');

        $router->get("$confirm/$magic_link", [ConfirmedAuthSessionController::class, 'store'])
               ->name('confirm.magic-link');

        $router->post("$confirm/$magic_link", [AuthConfirmationEmailController::class, 'store'])
               ->middleware('csrf')
               ->name('confirm.email');

    });

    // 2FA
    if ($config->get('auth.features.2fa')) {

        $two_factor = $config->get('auth.endpoints.2fa');
        $challenge = $config->get('auth.endpoints.challenge');

        $router->post("$two_factor/preferences", [TwoFactorAuthPreferenceController::class, 'store'])
               ->middleware(['auth', 'auth.confirmed'])
               ->name('two-factor.preferences');

        $router->delete("$two_factor/preferences", [
            TwoFactorAuthPreferenceController::class, 'destroy',
        ])
               ->middleware(['auth', 'auth.confirmed']);

        $router->get("$two_factor/$challenge", [TwoFactorAuthSessionController::class, 'create'])
               ->name('2fa.challenge');

        // recovery codes.
        $router->name('2fa.recovery-codes')->middleware(['auth', 'auth.confirmed'])
               ->group(function (Router $router) {

                   $router->get('two-factor/recovery-codes', [
                       RecoveryCodeController::class, 'index',
                   ]);

                   $router->put('two-factor/recovery-codes', [
                       RecoveryCodeController::class, 'update',
                   ])->middleware('csrf:persist');


               });


    }

    // password resets
    if ($config->get('auth.features.password-resets')) {

        $forgot = $config->get('auth.endpoints.forgot-password');
        $reset = $config->get('auth.endpoints.reset-password');

        // forgot-password
        $router->get("/$forgot", [ForgotPasswordController::class, 'create'])
               ->middleware('guest')
               ->name('forgot.password');

        $router->post("/$forgot", [ForgotPasswordController::class, 'store'])
               ->middleware(['csrf', 'guest']);

        // reset-password
        $router->get("/$reset", [ResetPasswordController::class, 'create'])
               ->middleware('signed:absolute')
               ->name('reset.password');

        $router->put("/$reset", [ResetPasswordController::class, 'update'])
               ->middleware(['csrf', 'signed:absolute']);

    }

    // registration
    if ($config->get('auth.features.registration')) {

        $register = $config->get('auth.endpoints.register');

        $router->middleware('guest')->group(function ($router) use ($register) {

            $router->get("/$register", [RegistrationLinkController::class, 'create'])
                   ->name('register');

            $router->post("/$register", [RegistrationLinkController::class, 'store']);

        });

        $router->name('accounts')->group(function (Router $router) use ($config) {

            $accounts = $config->get('auth.endpoints.accounts');
            $create = $config->get('auth.endpoints.accounts_create');

            $router->get("/$accounts/$create", [AccountController::class, 'create'])
                   ->middleware(['guest', 'signed:absolute'])
                   ->name('create');

            $router->post("/$accounts", [AccountController::class, 'store'])
                   ->middleware(['guest', 'csrf', 'signed'])
                   ->name('store');

            $router->delete("/$accounts/{user_id}", [AccountController::class, 'destroy'])
                   ->middleware(['auth', 'csrf'])
                   ->andNumber('user_id');

        });





    }










