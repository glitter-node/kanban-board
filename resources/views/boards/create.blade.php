<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ui-text-primary">
            Create New Board
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="ui-panel overflow-hidden sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('boards.store') }}">
                        @csrf

                        <div class="mb-6">
                            <label for="title" class="mb-1 block text-sm font-medium text-ui-text-secondary">
                                Board Title <span class="status-error">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}"
                                   class="ui-input w-full"
                                   placeholder="Enter project name" required autofocus>
                            @error('title')
                                <p class="mt-1 text-sm status-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="mb-1 block text-sm font-medium text-ui-text-secondary">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="ui-input w-full"
                                      placeholder="Enter a description for the board (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm status-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('boards.index') }}"
                               class="btn-secondary">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="btn-primary focus:ring-offset-canvas">
                                Create Board
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
