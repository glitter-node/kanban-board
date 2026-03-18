<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-foreground">
            Edit Board
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="ui-panel overflow-hidden sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('boards.update', $board) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label bg-surface text-secondary border border-border for="title" class="mb-1 block text-sm font-medium text-secondary">
                                Board Title <span class="text-error">*</span>
                            </label bg-surface text-secondary border border-border>
                            <input type="text" name="title" id="title" value="{{ old('title', $board->title) }}"
                                   class="ui-input w-full"
                                   required autofocus>
                            @error('title')
                                <p class="mt-1 text-sm text-error text-secondary">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label bg-surface text-secondary border border-border for="description" class="mb-1 block text-sm font-medium text-secondary">
                                Description
                            </label bg-surface text-secondary border border-border>
                            <textarea name="description" id="description" rows="4"
                                      class="ui-input w-full">{{ old('description', $board->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-error text-secondary">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('boards.show', $board) }}"
                               class="btn-secondary">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="btn-primary focus:ring-offset-canvas bg-primary text-white">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
