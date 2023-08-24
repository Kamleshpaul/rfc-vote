@component('layouts.base')

    <div class="bg-purple-100">
        <div class="container mx-auto">
            <nav class="py-3 px-6 md:px-2 flex items-center gap-8">
                <x-navbar.link
                    href="{{ action(\App\Http\Controllers\RfcAdminController::class) }}"
                    :isActive="request()->is('admin/rfc')">RFCs
                </x-navbar.link>

                <x-navbar.link
                    href="{{ action(\App\Http\Controllers\VerificationRequestsAdminController::class) }}"
                    :isActive="request()->is('admin/verification-requests')">Verification Requests
                </x-navbar.link>
            </nav>
        </div>
    </div>

    {{ $slot }}
@endcomponent