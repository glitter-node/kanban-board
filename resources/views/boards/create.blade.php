<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            Create New Board
        </h2>
    </x-slot>

    <x-ui.section class="py-12" width="lg">
        <x-ui.card class="mx-auto max-w-2xl overflow-hidden sm:rounded-lg">
            <form method="POST" action="{{ route('boards.store') }}">
                @csrf

                <div class="mb-6">
                    <label for="title" class="mb-1 block text-sm font-medium text-secondary">
                        Board Title
                        <span class="text-error">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="ui-input w-full" placeholder="Enter project name" required autofocus>
                    @error('title')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="mb-1 block text-sm font-medium text-secondary">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4" class="ui-input w-full" placeholder="Enter a description for the board (optional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <x-ui.button as="a" href="{{ route('boards.index') }}" variant="secondary">Cancel</x-ui.button>
                    <x-ui.button type="submit" variant="primary">Create Board</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </x-ui.section>
</x-app-layout>
