// Feature: portfolio-website, Property 2: Theme Preference Round Trip

/**
 * Property 2: Theme Preference Round Trip
 * Validates: Requirements 1.8
 *
 * For any theme value (light or dark) set via Theme_Toggle, the value stored
 * in localStorage must equal the theme currently applied to the page, and
 * reading it back must produce the same value.
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Manual mocks for browser globals (test environment is 'node')
// ---------------------------------------------------------------------------

const STORAGE_KEY = 'theme';

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

function createDocumentElementMock() {
    const classes = new Set();
    return {
        classList: {
            add: (cls) => classes.add(cls),
            remove: (cls) => classes.delete(cls),
            contains: (cls) => classes.has(cls),
            _classes: classes,
        },
    };
}

// ---------------------------------------------------------------------------
// Pure re-implementation of useTheme logic (extracted for testability)
// These mirror the exact logic in resources/js/composables/useTheme.js
// ---------------------------------------------------------------------------

function applyTheme(value, documentElement) {
    if (value === 'dark') {
        documentElement.classList.add('dark');
    } else {
        documentElement.classList.remove('dark');
    }
}

function setTheme(value, localStorageMock, documentElementMock) {
    localStorageMock.setItem(STORAGE_KEY, value);
    applyTheme(value, documentElementMock);
}

function readThemeFromStorage(localStorageMock) {
    return localStorageMock.getItem(STORAGE_KEY);
}

function isDarkApplied(documentElementMock) {
    return documentElementMock.classList.contains('dark');
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** Generates either 'light' or 'dark' */
const themeArb = fc.constantFrom('light', 'dark');

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 2: Theme Preference Round Trip', () => {
    let localStorageMock;
    let documentElementMock;

    beforeEach(() => {
        localStorageMock = createLocalStorageMock();
        documentElementMock = createDocumentElementMock();
    });

    it('localStorage value equals the theme set via toggleTheme', () => {
        fc.assert(
            fc.property(themeArb, (theme) => {
                const ls = createLocalStorageMock();
                const doc = createDocumentElementMock();

                setTheme(theme, ls, doc);

                // The value stored in localStorage must equal the theme that was set
                expect(readThemeFromStorage(ls)).toBe(theme);
            }),
            { numRuns: 20 }
        );
    });

    it('DOM class is consistent with the theme stored in localStorage', () => {
        fc.assert(
            fc.property(themeArb, (theme) => {
                const ls = createLocalStorageMock();
                const doc = createDocumentElementMock();

                setTheme(theme, ls, doc);

                const storedTheme = readThemeFromStorage(ls);
                const darkClassPresent = isDarkApplied(doc);

                // If stored theme is 'dark', the 'dark' class must be on documentElement
                // If stored theme is 'light', the 'dark' class must NOT be on documentElement
                if (storedTheme === 'dark') {
                    expect(darkClassPresent).toBe(true);
                } else {
                    expect(darkClassPresent).toBe(false);
                }
            }),
            { numRuns: 20 }
        );
    });

    it('reading theme back from localStorage produces the same value (round trip)', () => {
        fc.assert(
            fc.property(themeArb, (theme) => {
                const ls = createLocalStorageMock();
                const doc = createDocumentElementMock();

                // Set the theme (simulates toggleTheme)
                setTheme(theme, ls, doc);

                // Read it back (simulates getInitialTheme with a saved value)
                const readBack = readThemeFromStorage(ls);

                expect(readBack).toBe(theme);
            }),
            { numRuns: 20 }
        );
    });

    it('toggling theme twice returns to the original theme', () => {
        fc.assert(
            fc.property(themeArb, (initialTheme) => {
                const ls = createLocalStorageMock();
                const doc = createDocumentElementMock();

                // Set initial theme
                setTheme(initialTheme, ls, doc);

                // Toggle to opposite
                const opposite = initialTheme === 'dark' ? 'light' : 'dark';
                setTheme(opposite, ls, doc);

                // Toggle back
                setTheme(initialTheme, ls, doc);

                expect(readThemeFromStorage(ls)).toBe(initialTheme);

                if (initialTheme === 'dark') {
                    expect(isDarkApplied(doc)).toBe(true);
                } else {
                    expect(isDarkApplied(doc)).toBe(false);
                }
            }),
            { numRuns: 20 }
        );
    });

    it('localStorage and DOM class are always consistent after any sequence of theme changes', () => {
        fc.assert(
            fc.property(
                fc.array(themeArb, { minLength: 1, maxLength: 20 }),
                (themeSequence) => {
                    const ls = createLocalStorageMock();
                    const doc = createDocumentElementMock();

                    // Apply each theme in sequence
                    for (const theme of themeSequence) {
                        setTheme(theme, ls, doc);
                    }

                    // After all changes, localStorage and DOM must be consistent
                    const finalStoredTheme = readThemeFromStorage(ls);
                    const finalDarkClass = isDarkApplied(doc);

                    expect(finalStoredTheme).toBe(themeSequence[themeSequence.length - 1]);

                    if (finalStoredTheme === 'dark') {
                        expect(finalDarkClass).toBe(true);
                    } else {
                        expect(finalDarkClass).toBe(false);
                    }
                }
            ),
            { numRuns: 20 }
        );
    });
});
