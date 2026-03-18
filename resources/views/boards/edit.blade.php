<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            Edit Board
        </h2>
    </x-slot>

    <x-ui.section class="py-12" width="lg">
        <x-ui.card class="mx-auto max-w-2xl overflow-hidden sm:rounded-lg">
            <form method="POST" action="{{ route('boards.update', $board) }}">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="title" class="mb-1 block text-sm font-medium text-secondary">
                        Board Title
                        <span class="text-error">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $board->title) }}" class="ui-input w-full" required autofocus>
                    @error('title')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="mb-1 block text-sm font-medium text-secondary">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4" class="ui-input w-full">{{ old('description', $board->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <x-ui.button as="a" href="{{ route('boards.show', $board) }}" variant="secondary">Cancel</x-ui.button>
                    <x-ui.button type="submit" variant="primary">Save Changes</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </x-ui.section>
</x-app-layout>
