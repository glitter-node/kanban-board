<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ui-text-primary">
            보드 수정
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
                            <label for="title" class="mb-1 block text-sm font-medium text-ui-text-secondary">
                                보드 제목 <span class="status-error">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $board->title) }}"
                                   class="ui-input w-full"
                                   required autofocus>
                            @error('title')
                                <p class="mt-1 text-sm status-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="mb-1 block text-sm font-medium text-ui-text-secondary">
                                설명
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="ui-input w-full">{{ old('description', $board->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm status-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('boards.show', $board) }}"
                               class="btn-secondary">
                                취소
                            </a>
                            <button type="submit"
                                    class="btn-primary focus:ring-offset-canvas">
                                수정 완료
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
