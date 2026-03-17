<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ui-text-primary">
            새 보드 만들기
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
                                보드 제목 <span class="status-error">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}"
                                   class="ui-input w-full"
                                   placeholder="프로젝트 이름을 입력하세요" required autofocus>
                            @error('title')
                                <p class="mt-1 text-sm status-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="mb-1 block text-sm font-medium text-ui-text-secondary">
                                설명
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="ui-input w-full"
                                      placeholder="보드에 대한 설명을 입력하세요 (선택)">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm status-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('boards.index') }}"
                               class="btn-secondary">
                                취소
                            </a>
                            <button type="submit"
                                    class="btn-primary focus:ring-offset-canvas">
                                보드 생성
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
