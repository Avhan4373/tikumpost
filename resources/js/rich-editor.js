// Editor WYSIWYG untuk isi artikel (Quill 2). Dimuat malas dari app.js
// agar halaman publik tidak ikut mengunduh aset editor.
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import '../css/editor.css';

const MAX_IMAGE_BYTES = 8 * 1024 * 1024;

// Dimensi maksimum gambar yang disisipkan ke artikel. Gambar yang lebih besar
// dari batas server diperkecil bertahap sampai ukurannya aman diunggah.
const MAX_IMAGE_DIMENSION = 2400;

const TOOLBAR = [
    [{ header: [2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'code-block'],
    ['link', 'image'],
    [{ align: [] }],
    ['clean'],
];

const HTML_ENTITIES = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
};

const escapeHtml = (value) => value.replace(/[&<>"']/g, (char) => HTML_ENTITIES[char]);

// Konten lama dari textarea bisa berupa teks biasa (bukan HTML). Ubah ke
// paragraf agar perilaku editor konsisten saat artikel lama diedit.
const looksLikeHtml = (value) => /<\/?[a-z][\s\S]*>/i.test(value);

const plainTextToHtml = (value) => value
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter((line) => line !== '')
    .map((line) => `<p>${escapeHtml(line)}</p>`)
    .join('');

const formatBytes = (bytes) => {
    if (bytes >= 1024 * 1024) {
        const value = bytes / 1024 / 1024;
        const formatted = Number.isInteger(value) ? value.toString() : value.toFixed(1).replace('.', ',');

        return `${formatted} MB`;
    }

    return `${Math.ceil(bytes / 1024)} KB`;
};

const loadImage = (file) => new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file);
    const image = new Image();

    image.onload = () => {
        URL.revokeObjectURL(url);
        resolve(image);
    };

    image.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error('Gambar tidak bisa dibaca.'));
    };

    image.src = url;
});

// Perkecil & kompres gambar di browser (WebP) sampai di bawah batas server.
const shrinkImage = async (file, maxBytes) => {
    const image = await loadImage(file);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    if (!context) {
        return null;
    }

    let scale = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(image.naturalWidth, image.naturalHeight));
    let quality = 0.92;

    for (let attempt = 0; attempt < 10; attempt += 1) {
        canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', quality));

        if (blob && blob.size <= maxBytes) {
            return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.webp', {
                type: 'image/webp',
                lastModified: Date.now(),
            });
        }

        quality -= 0.12;

        if (quality < 0.5) {
            quality = 0.85;
            scale *= 0.8;
        }
    }

    return null;
};

const setupEditor = (root) => {
    const form = root.closest('form');
    const input = root.querySelector('[data-rich-editor-input]');
    const surface = root.querySelector('[data-rich-editor-surface]');
    const errorBox = root.querySelector('[data-rich-editor-error]');

    if (!form || !input || !surface) {
        return;
    }

    let errorTimer = null;

    const clearError = () => {
        window.clearTimeout(errorTimer);
        errorBox?.classList.add('hidden');
        errorBox && (errorBox.textContent = '');
    };

    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        window.clearTimeout(errorTimer);
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
        errorTimer = window.setTimeout(clearError, 6000);
    };

    // Tampilkan surface editor; toolbar dibuat oleh Quill dari daftar format.
    surface.classList.remove('hidden');

    let quill;

    try {
        quill = new Quill(surface, {
            theme: 'snow',
            placeholder: root.dataset.placeholder || 'Tulis isi artikel di sini...',
            modules: {
                toolbar: {
                    container: TOOLBAR,
                    handlers: {
                        image: () => {
                            pickImage({
                                uploadUrl: root.dataset.uploadUrl,
                                maxBytes: Number(root.dataset.uploadMaxBytes) || MAX_IMAGE_BYTES,
                                quill,
                                onError: showError,
                            });
                        },
                    },
                },
            },
        });
    } catch (error) {
        console.error('Editor artikel gagal diinisialisasi:', error);
        surface.classList.add('hidden');
        return;
    }

    // Textarea asli tetap jadi sumber data form; sembunyikan setelah editor siap.
    input.classList.add('hidden');

    // Gambar/embed tidak dihitung oleh getText(), jadi cek juga elemen media.
    const hasContent = () => quill.getText().trim() !== ''
        || quill.root.querySelector('img, video, iframe') !== null;

    const initial = input.value.trim();

    if (initial) {
        quill.clipboard.dangerouslyPasteHTML(looksLikeHtml(initial) ? initial : plainTextToHtml(initial));
    }

    const sync = () => {
        // Jangan kirim HTML kosong seperti "<p></p>" agar validasi server tetap jalan.
        input.value = hasContent() ? quill.getSemanticHTML() : '';
    };

    quill.on('text-change', () => {
        sync();

        if (hasContent()) {
            clearError();
        }
    });

    sync();

    form.addEventListener('submit', (event) => {
        sync();

        if (hasContent()) {
            return;
        }

        // Body tidak memakai atribut `required` (textarea disembunyikan), jadi
        // validasi kosong dilakukan di sini sebelum dikirim ke server.
        event.preventDefault();
        showError('Isi artikel wajib diisi.');
        quill.focus();
    });
};

const pickImage = ({ uploadUrl, maxBytes, quill, onError }) => {
    if (!uploadUrl) {
        onError('Unggah gambar belum dikonfigurasi.');
        return;
    }

    const picker = document.createElement('input');
    picker.type = 'file';
    picker.accept = 'image/jpeg,image/png,image/webp';
    picker.hidden = true;
    document.body.appendChild(picker);

    picker.addEventListener('cancel', () => picker.remove(), { once: true });

    picker.addEventListener('change', async () => {
        const file = picker.files?.[0];

        picker.remove();

        if (!file) {
            return;
        }

        let upload = file;

        // File di atas batas PHP akan ditolak sebelum validasi Laravel, jadi
        // perkecil dulu di browser alih-alih menampilkan error yang membingungkan.
        if (file.size > maxBytes) {
            try {
                upload = await shrinkImage(file, maxBytes);
            } catch (error) {
                onError(error.message || 'Gambar tidak bisa dibaca.');
                return;
            }

            if (!upload) {
                onError(`Gambar terlalu besar dan gagal diperkecil otomatis. Maksimal ${formatBytes(maxBytes)}.`);
                return;
            }
        }

        const payload = new FormData();
        payload.append('file', upload);
        payload.append('alt_text', upload.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ').slice(0, 150));

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: payload,
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 413) {
                throw new Error(`File melebihi batas unggah server (${formatBytes(maxBytes)}).`);
            }

            if (!response.ok) {
                throw new Error(data.message || `Gagal mengunggah gambar (HTTP ${response.status}).`);
            }

            const range = quill.getSelection(true) ?? { index: quill.getLength(), length: 0 };
            quill.insertEmbed(range.index, 'image', data.url, 'user');
            quill.setSelection(range.index + 1, 0, 'user');
        } catch (error) {
            onError(error.message || 'Gagal mengunggah gambar.');
        }
    });

    picker.click();
};

export const initRichEditors = () => {
    document.querySelectorAll('[data-rich-editor]').forEach((root) => {
        setupEditor(root);
    });
};
