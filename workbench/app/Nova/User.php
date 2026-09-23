<?php

declare(strict_types=1);

namespace Workbench\App\Nova;

use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    /**
     * @var class-string<\Workbench\App\Models\User>
     */
    public static $model = \Workbench\App\Models\User::class;

    /**
     * @var string
     */
    public static $title = 'name';

    /**
     * @var array<int, string>
     */
    public static $search = ['id', 'name', 'email'];

    /**
     * @return array<int, Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('Role')
                ->sortable(),

            Boolean::make('SSO allowed', 'is_sso_allowed')
                ->sortable(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules(['required', PasswordRule::defaults()])
                ->updateRules(['nullable', PasswordRule::defaults()]),

            DateTime::make('Logged at', 'logged_at')
                ->exceptOnForms()
                ->sortable(),
        ];
    }
}
