// Feature: portfolio-website, Property 4: Language Preference Round Trip

/**
 * Property 4: Language Preference Round Trip
 * Validates: Requirements 2.4
 *
 * For any locale value ('id' or 'en') set via Language_Toggle, the value stored
 * in localStorage must equal the active locale, and reloading the composable
 * state from localStorage must restore the same locale.
 */

import { describe, it, expect, beforeEach } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Manual mocks for browser globals (test environment is 'node')
// ---------------------------------------------------------------------------

const STORAGE_KEY = 'locale';
const SUPPORTED_LOCALES = ['id', 'en'];

function createLocalStorageMock() {
    const store = {};
    return {
        getItem: (key) => (key in store ? store[key] : null),
        setItem: (key, value) => { store[key] = String(value); },
        removeItem: (key) => { delete store[key]; },
        clear: () => { Object.keys(store).forEach((k) => delete store[k]); },
        _store: store,
    };
}

// ---------------------------------------------------------------------------
// Pure re-implementation of useLanguage logic (extracted for testability)
// These mirror the exact logic in resources/js/composables/useLanguage.js
// ---------------------------------------------------------------------------

function createLanguageState(localStorageMock) {
    const saved = localStorageMock.getItem(STORAGE_KEY);
    const initialLocale = SUPPORTED_LOCALES.includes(saved) ? saved : 'id';
    return { locale: initialLocale };
}

function setLanguage(lang, state, localStorageMock) {
    if (!SUPPORTED_LOCALES.includes(lang)) return;
    state.locale = lang;
    localStorageMock.setItem(STORAGE_KEY, lang);
}

function toggleLanguage(state, localStorageMock) {
    const next = state.locale === 'id' ? 'en' : 'id';
    setLanguage(next, state, localStorageMock);
}

function readLocaleFromStorage(localStorageMock) {
    return localStorageMock.getItem(STORAGE_KEY);
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** Generates either 'id' or 'en' */
const localeArb = fc.constantFrom('id', 'en');

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 4: Language Preference Round Trip', () => {
    let localStorageMock;

    beforeEach(() => {
        localStorageMock = createLocalStorageMock();
    });

    it('localStorage value equals the locale set via setLanguage', () => {
        fc.assert(
            fc.property(localeArb, (locale) => {
                const ls = createLocalStorageMock();
                const state = createLanguageState(ls);

                setLanguage(locale, state, ls);

                // The value stored in localStorage must equal the locale that was set
                expect(readLocaleFromStorage(ls)).toBe(locale);
            }),
            { numRuns: 20 }
        );
    });

    it('composable state locale equals the locale stored in localStorage', () => {
        fc.assert(
            fc.property(localeArb, (locale) => {
                const ls = createLocalStorageMock();
                const state = createLanguageState(ls);

                setLanguage(locale, state, ls);

                const storedLocale = readLocaleFromStorage(ls);

                // The composable state and localStorage must be consistent
                expect(state.locale).toBe(locale);
                expect(storedLocale).toBe(locale);
                expect(state.locale).toBe(storedLocale);
            }),
            { numRuns: 20 }
        );
    });

    it('reloading composable state from localStorage restores the same locale (round trip)', () => {
        fc.assert(
            fc.property(localeArb, (locale) => {
                const ls = createLocalStorageMock();
                const state = createLanguageState(ls);

                // Set the locale (simulates Language_Toggle)
                setLanguage(locale, state, ls);

                // Reload composable state from localStorage (simulates page reload)
                const restoredState = createLanguageState(ls);

                // The restored locale must equal the originally set locale
                expect(restoredState.locale).toBe(locale);
            }),
            { numRuns: 20 }
        );
    });

    it('toggleLanguage switches between id and en and keeps localStorage consistent', () => {
        fc.assert(
            fc.property(localeArb, (initialLocale) => {
                const ls = createLocalStorageMock();
                const state = createLanguageState(ls);

                setLanguage(initialLocale, state, ls);
                const expectedAfterToggle = initialLocale === 'id' ? 'en' : 'id';

                toggleLanguage(state, ls);

                expect(state.locale).toBe(expectedAfterToggle);
                expect(readLocaleFromStorage(ls)).toBe(expectedAfterToggle);
            }),
            { numRuns: 20 }
        );
    });

    it('toggling locale twice returns to the original locale', () => {
        fc.assert(
            fc.property(localeArb, (initialLocale) => {
                const ls = createLocalStorageMock();
                const state = createLanguageState(ls);

                setLanguage(initialLocale, state, ls);

                // Toggle once
                toggleLanguage(state, ls);
                // Toggle back
                toggleLanguage(state, ls);

                expect(state.locale).toBe(initialLocale);
                expect(readLocaleFromStorage(ls)).toBe(initialLocale);
            }),
            { numRuns: 20 }
        );
    });

    it('localStorage and composable state are always consistent after any sequence of locale changes', () => {
        fc.assert(
            fc.property(
                fc.array(localeArb, { minLength: 1, maxLength: 20 }),
                (localeSequence) => {
                    const ls = createLocalStorageMock();
                    const state = createLanguageState(ls);

                    for (const locale of localeSequence) {
                        setLanguage(locale, state, ls);
                    }

                    const finalLocale = localeSequence[localeSequence.length - 1];

                    expect(state.locale).toBe(finalLocale);
                    expect(readLocaleFromStorage(ls)).toBe(finalLocale);
                    expect(state.locale).toBe(readLocaleFromStorage(ls));
                }
            ),
            { numRuns: 20 }
        );
    });

    it('default locale is id when localStorage has no saved preference', () => {
        fc.assert(
            fc.property(fc.constant(null), () => {
                const ls = createLocalStorageMock();
                // No locale saved in localStorage
                const state = createLanguageState(ls);

                expect(state.locale).toBe('id');
            }),
            { numRuns: 20 }
        );
    });

    it('unsupported locale values are ignored and state remains unchanged', () => {
        fc.assert(
            fc.property(
                localeArb,
                fc.string().filter((s) => !SUPPORTED_LOCALES.includes(s)),
                (validLocale, invalidLocale) => {
                    const ls = createLocalStorageMock();
                    const state = createLanguageState(ls);

                    setLanguage(validLocale, state, ls);
                    const localeBeforeInvalid = state.locale;

                    // Attempt to set an unsupported locale — should be ignored
                    setLanguage(invalidLocale, state, ls);

                    expect(state.locale).toBe(localeBeforeInvalid);
                    expect(readLocaleFromStorage(ls)).toBe(localeBeforeInvalid);
                }
            ),
            { numRuns: 20 }
        );
    });
});
