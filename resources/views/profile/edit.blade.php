<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[15px] font-semibold text-white">Profile</h2>
    </x-slot>

    <div class="p-6 py-10 max-w-3xl mx-auto space-y-6">
        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Profile</p>
                <p class="section-desc">Your account details.</p>
            </div>
            <div class="flex-1 card-padded">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title">Password</p>
                <p class="section-desc">Update your password.</p>
            </div>
            <div class="flex-1 card-padded">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="flex gap-8">
            <div class="w-48 flex-shrink-0 pt-5">
                <p class="section-title text-red-400/80">Danger Zone</p>
                <p class="section-desc">Permanent actions.</p>
            </div>
            <div class="flex-1 card border-red-500/10 p-5">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
