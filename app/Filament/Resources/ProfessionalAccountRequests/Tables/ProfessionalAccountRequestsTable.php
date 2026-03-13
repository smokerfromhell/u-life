<?php

namespace App\Filament\Resources\ProfessionalAccountRequests\Tables;

use App\Models\ProfessionalAccountRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProfessionalAccountRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('organization')->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])->default('pending'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn(ProfessionalAccountRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (ProfessionalAccountRequest $record) {
                        $role = Role::firstOrCreate(['name' => 'Professional']);
                        $password = null;

                        $user = User::where('email', $record->email)->first();
                        $password = $record->desired_password;
                        if (!$user) {
                            $user = User::create([
                                'name' => $record->name,
                                'email' => $record->email,
                                'password' => $password, // Already hashed from model
                                'email_verified_at' => now(),
                                'is_guest' => false,
                            ]);
                        } else {
                            $user->update([
                                'password' => $password,
                                'is_guest' => false,
                                'name' => $record->name,
                                'email_verified_at' => now(),
                            ]);
                        }
                        $user->syncRoles([$role->name]);

                        $record->status = 'approved';
                        $record->created_user_id = $user->id;
                        $record->approved_by = Auth::id();
                        $record->approved_at = now();
                        $record->save();

                        $body = "Login email: {$user->email}";
                        if ($password) {
                            $body .= "\nTemporary password: {$password}\n(Shown once - copy it now.)";
                        } else {
                            $body .= "\n(Existing user detected - password not changed.)";
                        }

                        Notification::make()
                            ->title('Professional account created')
                            ->body($body)
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn(ProfessionalAccountRequest $record) => $record->status === 'pending')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('rejection_reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (ProfessionalAccountRequest $record, array $data) {
                        $record->status = 'rejected';
                        $record->rejected_by = Auth::id();
                        $record->rejected_at = now();
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();

                        Notification::make()
                            ->title('Request rejected')
                            ->danger()
                            ->send();
                    }),
            ]);
    }
}
