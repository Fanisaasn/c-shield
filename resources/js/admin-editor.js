import 'trix';
import 'trix/dist/trix.css';

const MAX_FILE_SIZE = 20 * 1024 * 1024;
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];

// Trix only accepts full URLs (type="url"), so "google.com" silently failed.
// Add https:// when the admin types a bare domain.
const normalizeUrl = (value) => {
    const url = value.trim();
    if (url === '' || /^[a-z][a-z0-9+.-]*:/i.test(url) || url.startsWith('/') || url.startsWith('#')) return url;
    return `https://${url}`;
};

const normalizeLinkInput = (event) => {
    const dialog = event.target.closest?.('trix-toolbar [data-trix-dialog="href"]');
    if (!dialog) return;
    if (event.type === 'keydown' && event.key !== 'Enter') return;
    if (event.type === 'click' && !event.target.matches('[data-trix-method="setAttribute"]')) return;

    const input = dialog.querySelector('input[name="href"]');
    if (input) input.value = normalizeUrl(input.value);
};

// Capture phase so the URL is fixed before Trix validates it.
document.addEventListener('keydown', normalizeLinkInput, true);
document.addEventListener('mousedown', normalizeLinkInput, true);
document.addEventListener('click', normalizeLinkInput, true);

document.addEventListener('trix-initialize', (event) => {
    const toolbar = event.target.toolbarElement;
    const input = toolbar?.querySelector('input[name="href"]');
    if (!input) return;

    input.type = 'text';
    input.placeholder = 'Masukkan URL, mis. https://contoh.com';
    toolbar.querySelector('[data-trix-method="setAttribute"]')?.setAttribute('value', 'Tautkan');
    toolbar.querySelector('[data-trix-method="removeAttribute"]')?.setAttribute('value', 'Hapus tautan');
});

// Links inside the editor are editable text, so open them with Ctrl/Cmd + click.
document.addEventListener('click', (event) => {
    const link = event.target.closest?.('trix-editor a[href]');
    if (!link || !(event.ctrlKey || event.metaKey)) return;
    event.preventDefault();
    window.open(link.href, '_blank', 'noopener');
});

document.addEventListener('trix-file-accept', (event) => {
    const file = event.file;
    const extension = file.name.split('.').pop()?.toLowerCase();

    if (!ALLOWED_EXTENSIONS.includes(extension)) {
        event.preventDefault();
        alert('Format file tidak didukung. Gunakan gambar, PDF, Word, PowerPoint, Excel, TXT, atau ZIP.');
    } else if (file.size > MAX_FILE_SIZE) {
        event.preventDefault();
        alert('Ukuran file maksimal 20 MB.');
    }
});

document.addEventListener('trix-attachment-add', (event) => {
    const { attachment } = event;
    if (attachment.file) uploadAttachment(attachment, event.target);
});

function uploadAttachment(attachment, editor) {
    const form = editor.closest('form');
    const token = form?.querySelector('input[name="_token"]')?.value
        ?? document.querySelector('meta[name="csrf-token"]')?.content;
    const uploadUrl = editor.dataset.uploadUrl;

    const body = new FormData();
    body.append('file', attachment.file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl, true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);

    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) attachment.setUploadProgress((e.loaded / e.total) * 100);
    });

    xhr.addEventListener('load', () => {
        let response = {};
        try { response = JSON.parse(xhr.responseText); } catch { /* non-JSON error page */ }

        if (xhr.status >= 200 && xhr.status < 300 && response.url) {
            attachment.setAttributes({ url: response.url, href: response.url });
            return;
        }

        const message = response.errors?.file?.[0]
            ?? (xhr.status === 419 ? 'Sesi habis. Muat ulang halaman lalu coba lagi.' : response.message)
            ?? 'Gagal mengunggah file.';
        attachment.remove();
        alert(message);
    });

    xhr.addEventListener('error', () => {
        attachment.remove();
        alert('Gagal mengunggah file. Periksa koneksi lalu coba lagi.');
    });

    xhr.send(body);
}
