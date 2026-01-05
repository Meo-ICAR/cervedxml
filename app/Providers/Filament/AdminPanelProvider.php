<?php

namespace App\Providers\Filament;

use App\Models\User;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Support\Colors;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;  // Importante per usare Blade::render
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;  // Già che ci sei, servirà anche questo per Str::random
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
// use SocialiteProviders\LinkedIn\LinkedInExtendSocialite;  // Fai attenzione alle maiuscole: LinkedIn
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, [
            MicrosoftExtendSocialite::class,
            'handle',
        ]);
        Event::listen(function (SocialiteWasCalled $event) {
            // Google
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
            // LinkedIn
            $event->extendSocialite('linkedin', \SocialiteProviders\LinkedIn\Provider::class);
            // Instagram

            /*
             * $event->extendSocialite('instagram_basic', \SocialiteProviders\InstagramBasic\Provider::class);
             */
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandLogo(asset('images/cerved.png'))  // Percorso del tuo logo
            //    ->brandLogoHeight('3rem')  // Altezza del logo
            ->favicon(asset('images/favicon.ico'))  // Opzionale: favicon personalizzata
            ->registration()
            ->passwordReset()
            ->emailVerification()
            //   ->emailChangeVerification()
            ->profile()
            ->login()
            // --- INIZIO CONFIGURAZIONE RENDER HOOK ---
            ->renderHook(
                'panels::auth.register.form.after',  // Specifica il "punto" dove apparirà
                fn (): string => Blade::render('<div class="mt-4"><x-filament-socialite::buttons /></div>'),
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('instagram_basic')
                            ->label('Instagram')
                            ->icon('fab-instagram')
                            ->color('success'),
                        Provider::make('google')
                            ->label('Google')
                            ->icon('fab-google')
                            ->color('success'),
                        Provider::make('linkedin')
                            ->label('LinkedIn')
                            ->icon('fab-linkedin')
                            ->color('primary'),
                        Provider::make('microsoft')
                            ->label('Microsoft')
                            ->icon('fab-microsoft')
                            ->color('info'),
                    ])
                    ->registration(true)  // Abilita la registrazione automatica per nuovi utenti
                    // Questo forza il plugin a mostrare i bottoni in entrambe le pagine
                    //  ->showNotAssociatedMessage(true)
                    ->createUserUsing(function (string $provider, $oauthUser, $plugin) {
                        // Logica personalizzata per creare l'utente
                        return User::create([
                            'name' => $oauthUser->getName(),
                            'email' => $oauthUser->getEmail(),
                            'password' => null,  // Password nullable obbligatoria per Socialite
                            'avatar_url' => $oauthUser->getAvatar(),  // Salva l'URL di Google
                            'email_verified_at' => now(),  // Google certifica l'email, quindi la segniamo come verificata
                            'password' => Hash::make(Str::random(32)),  // Password casuale sicura
                        ]);
                    }),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
