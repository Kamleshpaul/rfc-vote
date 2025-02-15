<?php

namespace App\Http\Controllers;

use App\Actions\CreateVerificationRequest;
use App\Actions\Fortify\PasswordValidationRules;
use App\Actions\RequestEmailChange;
use App\Http\Requests\Profile\UpdateRequest;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

final readonly class ProfileController
{
    use PasswordValidationRules;

    public function edit(): View
    {
        $user = auth()->user();

        return view('profile-form', [
            'user' => $user,
        ]);
    }

    public function update(UpdateRequest $request): RedirectResponse
    {
        $attrs = collect($request->safe()->except('avatar'));

        if ($request->avatar !== null) {
            /** @var UploadedFile $avatar */
            $avatar = $request->file('avatar');

            $attrs->put('avatar', $avatar->store('public/avatars'));
        }

        /** @var User $user */
        $user = $request->user();

        $user->update($attrs->toArray());

        flash('Profile updated successfully');

        return redirect()->action([self::class, 'edit']);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'email_optin' => ['nullable'],
        ]);

        $user->update([
            'email_optin' => $validated['email_optin'] ?? false,
        ]);

        if ($validated['email'] !== $user->email) {
            (new RequestEmailChange)(
                user: $user,
                newEmail: $validated['email'],
            );

            flash('An email with a verification link has been sent to your new email address. Please click the link to verify your email.');

            return redirect()->action([self::class, 'edit']);
        }

        return redirect()->action([self::class, 'edit']);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->password) {
            $validated = $request->validate([
                'current_password' => ['required', 'string', 'current_password:web'],
                'new_password' => $this->passwordRules(),
            ]);

            $user->forceFill([
                'password' => Hash::make($validated['new_password']),
            ])->save();

            flash('Password updated successfully');
        } else {
            $validated = $request->validate([
                'password' => $this->passwordRules(),
            ]);

            $user->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();

            flash('Password set successfully');
        }

        return redirect()->action([self::class, 'edit']);
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        $emailChangeRequest = EmailChangeRequest::query()->where('token', $token)->first();

        if (! $emailChangeRequest) {
            abort(403, 'Email Expired');
        }

        if (now()->greaterThan($emailChangeRequest->created_at->addMinutes(30))) {
            abort(404, 'Link expired');
        }

        $emailChangeRequest->user->update([
            'email' => $emailChangeRequest->new_email,
        ]);

        $emailChangeRequest->delete();

        flash('Your email has been successfully changed.');

        return redirect()->action([self::class, 'edit']);
    }

    public function requestVerification(
        CreateVerificationRequest $createVerificationRequest,
        Request $request,
    ): RedirectResponse {
        $validated = $request->validate([
            'motivation' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $createVerificationRequest(
            user: $user,
            motivation: $validated['motivation']
        );

        flash('Your verification request was submitted.');

        return redirect()->action([self::class, 'edit']);
    }
}
