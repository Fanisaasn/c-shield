import 'trix';
import 'trix/dist/trix.css';

// C-SHIELD content is text-only: block image/file attachments in the editor.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
