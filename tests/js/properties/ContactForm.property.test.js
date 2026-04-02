// Feature: portfolio-website, Property 10: Contact Form Validation Rejects Invalid Input

/**
 * Property 10: Contact Form Validation Rejects Invalid Input
 * Validates: Requirements 7.2, 7.3
 *
 * For any contact form submission where at least one field is invalid
 * (empty required field, malformed email, message length outside 10–2000 chars),
 * the frontend validator must reject the request and return field-specific error
 * messages without persisting any data.
 */

import { describe, it, expect } from 'vitest';
import * as fc from 'fast-check';
import { validateContactForm } from '../../../resources/js/utils/validators.js';

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** Valid base data — all fields pass validation */
const validName = fc.string({ minLength: 1, maxLength: 100 }).filter((s) => s.trim().length > 0);
const validEmail = fc.tuple(
    fc.stringMatching(/^[a-z]{1,10}$/),
    fc.stringMatching(/^[a-z]{1,10}$/),
    fc.stringMatching(/^[a-z]{2,5}$/)
).map(([local, domain, tld]) => `${local}@${domain}.${tld}`);
const validSubject = fc.string({ minLength: 1, maxLength: 200 }).filter((s) => s.trim().length > 0);
const validBody = fc.string({ minLength: 10, maxLength: 2000 }).filter((s) => s.trim().length >= 10 && s.trim().length <= 2000);

/** Short body: trimmed length 1–9 (always invalid) */
const shortBody = fc.string({ minLength: 1, maxLength: 9 }).filter((s) => s.trim().length >= 1 && s.trim().length < 10);

/** Long body: trimmed length > 2000 */
const longBody = fc.string({ minLength: 2001, maxLength: 2500 }).filter((s) => s.trim().length > 2000);

/** Invalid email: no @, no dot-after-@, or empty */
const invalidEmail = fc.oneof(
    fc.constant(''),
    fc.constant('notanemail'),
    fc.constant('@nodomain'),
    fc.constant('missing@'),
    fc.constant('missing@dot'),
    fc.string({ minLength: 1, maxLength: 30 }).filter((s) => !s.includes('@'))
);

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 10: Contact Form Validation Rejects Invalid Input', () => {

    it('messages shorter than 10 characters must always be rejected', () => {
        fc.assert(
            fc.property(validName, validEmail, validSubject, shortBody, (name, email, subject, body) => {
                const result = validateContactForm({ name, email, subject, body });

                expect(result.valid).toBe(false);
                expect(result.errors.body).not.toBe('');
            }),
            { numRuns: 20 }
        );
    });

    it('messages longer than 2000 characters must always be rejected', () => {
        fc.assert(
            fc.property(validName, validEmail, validSubject, longBody, (name, email, subject, body) => {
                const result = validateContactForm({ name, email, subject, body });

                expect(result.valid).toBe(false);
                expect(result.errors.body).not.toBe('');
            }),
            { numRuns: 20 }
        );
    });

    it('empty name must always be rejected with a name-specific error', () => {
        fc.assert(
            fc.property(validEmail, validSubject, validBody, (email, subject, body) => {
                const result = validateContactForm({ name: '', email, subject, body });

                expect(result.valid).toBe(false);
                expect(result.errors.name).not.toBe('');
            }),
            { numRuns: 20 }
        );
    });

    it('whitespace-only name must always be rejected', () => {
        fc.assert(
            fc.property(
                fc.string({ minLength: 1, maxLength: 20 }).map((s) => s.replace(/./g, ' ')),
                validEmail,
                validSubject,
                validBody,
                (name, email, subject, body) => {
                    const result = validateContactForm({ name, email, subject, body });

                    expect(result.valid).toBe(false);
                    expect(result.errors.name).not.toBe('');
                }
            ),
            { numRuns: 20 }
        );
    });

    it('invalid email format must always be rejected with an email-specific error', () => {
        fc.assert(
            fc.property(validName, invalidEmail, validSubject, validBody, (name, email, subject, body) => {
                const result = validateContactForm({ name, email, subject, body });

                expect(result.valid).toBe(false);
                expect(result.errors.email).not.toBe('');
            }),
            { numRuns: 20 }
        );
    });

    it('empty subject must always be rejected with a subject-specific error', () => {
        fc.assert(
            fc.property(validName, validEmail, validBody, (name, email, body) => {
                const result = validateContactForm({ name, email, subject: '', body });

                expect(result.valid).toBe(false);
                expect(result.errors.subject).not.toBe('');
            }),
            { numRuns: 20 }
        );
    });

    it('valid form data must always pass validation', () => {
        fc.assert(
            fc.property(validName, validEmail, validSubject, validBody, (name, email, subject, body) => {
                const result = validateContactForm({ name, email, subject, body });

                expect(result.valid).toBe(true);
                expect(result.errors.name).toBe('');
                expect(result.errors.email).toBe('');
                expect(result.errors.subject).toBe('');
                expect(result.errors.body).toBe('');
            }),
            { numRuns: 20 }
        );
    });

    it('errors object always contains field-specific keys regardless of input', () => {
        fc.assert(
            fc.property(
                fc.string({ maxLength: 50 }),
                fc.string({ maxLength: 50 }),
                fc.string({ maxLength: 50 }),
                fc.string({ maxLength: 50 }),
                (name, email, subject, body) => {
                    const result = validateContactForm({ name, email, subject, body });

                    expect(result).toHaveProperty('valid');
                    expect(result).toHaveProperty('errors');
                    expect(result.errors).toHaveProperty('name');
                    expect(result.errors).toHaveProperty('email');
                    expect(result.errors).toHaveProperty('subject');
                    expect(result.errors).toHaveProperty('body');
                }
            ),
            { numRuns: 20 }
        );
    });
});
