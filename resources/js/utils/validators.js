const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/**
 * Validate contact form fields.
 * @param {{ name: string, email: string, subject: string, body: string }} data
 * @returns {{ valid: boolean, errors: { name: string, email: string, subject: string, body: string } }}
 */
export function validateContactForm({ name = '', email = '', subject = '', body = '' }) {
    const errors = { name: '', email: '', subject: '', body: '' };

    if (!name.trim()) {
        errors.name = 'Nama wajib diisi.';
    }

    if (!email.trim()) {
        errors.email = 'Email wajib diisi.';
    } else if (!EMAIL_REGEX.test(email.trim())) {
        errors.email = 'Format email tidak valid.';
    }

    if (!subject.trim()) {
        errors.subject = 'Subjek wajib diisi.';
    }

    const bodyLength = body.trim().length;
    if (bodyLength === 0) {
        errors.body = 'Pesan wajib diisi.';
    } else if (bodyLength < 10) {
        errors.body = 'Pesan minimal 10 karakter.';
    } else if (bodyLength > 2000) {
        errors.body = 'Pesan maksimal 2000 karakter.';
    }

    const valid = Object.values(errors).every((e) => e === '');

    return { valid, errors };
}
