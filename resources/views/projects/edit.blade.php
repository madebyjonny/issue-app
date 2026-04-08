<x-app-layout>
    <x-slot name="project">{{ $project }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.show', $project) }}" class="text-gray-600 hover:text-gray-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>
            <h2 class="text-[15px] font-semibold text-white">Edit Project</h2>
        </div>
    </x-slot>

    <div class="p-6 py-10 max-w-xl mx-auto">
        <div class="card-padded">
            <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Project Name')" />
                    <x-text-input id="name" name="name" class="block mt-1.5 w-full" :value="old('name', $project->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="key" :value="__('Key')" />
                    <x-text-input id="key" name="key" class="block mt-1.5 w-full uppercase" :value="old('key', $project->key)" required maxlength="10" />
                    <p class="mt-1.5 text-[12px] text-gray-600">Changing the key won't rename existing tickets.</p>
                    <x-input-error :messages="$errors->get('key')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="input-dark block mt-1.5 w-full resize-none">{{ old('description', $project->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>Save Changes</x-primary-button>
                    <a href="{{ route('projects.show', $project) }}" class="btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
