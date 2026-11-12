{{-- skm.go.id menolak ditampilkan dalam iframe, jadi survei dibuka di jendela kecil terpisah (tab baru bila popup diblokir). --}}
<a href="{{ config('services.skm.url') }}" target="_blank" rel="noopener" data-survey-popup class="{{ $class ?? '' }} inline-flex items-center gap-2 rounded-md border border-teal-500 px-4 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
        <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.2 0-2.3-.25-3.3-.7L3 20l1.2-4.4A8.5 8.5 0 1 1 21 11.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
    </svg>
    Survei
</a>

@pushOnce('scripts')
    <script>
        document.querySelectorAll('[data-survey-popup]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const width = Math.min(960, screen.availWidth);
                const height = Math.min(800, screen.availHeight);
                const left = Math.round((screen.availWidth - width) / 2);
                const top = Math.round((screen.availHeight - height) / 2);
                const popup = window.open(link.href, 'skm-survey', `popup,width=${width},height=${height},left=${left},top=${top}`);

                if (popup) {
                    popup.focus();
                    event.preventDefault();
                }
            });
        });
    </script>
@endPushOnce
