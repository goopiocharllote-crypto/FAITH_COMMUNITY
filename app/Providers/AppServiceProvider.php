<?php

namespace App\Providers;


use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addNamespace('user-components', resource_path('users/components'));

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Reset your Faith Community password')
                ->greeting('Hello'.($notifiable->name ? ', '.$notifiable->name : '').'!')
                ->line('We received a request to reset the password for your Faith Community account.')
                ->action('Reset password', $url)
                ->line("This link will expire in {$minutes} minutes and can be used only once.")
                ->line('If you did not request this, you can safely ignore this email. Your password will not change.')
                ->salutation('God bless, Parish of Saint Peter the Apostle, Loboc');
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
