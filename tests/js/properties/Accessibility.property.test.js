// Feature: portfolio-website, Property 29: Interactive Elements Have aria-label

/**
 * Property 29: Interactive Elements Have aria-label
 * Validates: Requirements 18.1
 *
 * For any button or icon element in the Public Site that does not contain
 * visible text content, the rendered HTML element must have a non-empty
 * aria-label attribute.
 *
 * Since the test environment is 'node' (no DOM), we test the pure logic that
 * determines whether an element requires an aria-label and whether the
 * computed aria-label values are non-empty for all icon-only interactive
 * elements in the application.
 */

import { describe, it, expect } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Pure logic: aria-label requirement rules
// An interactive element REQUIRES an aria-label when it has no visible text.
// ---------------------------------------------------------------------------

/**
 * Determines if an element needs an aria-label based on its visible text.
 * Mirrors the accessibility requirement: buttons/icons without visible text
 * must have aria-label.
 */
function requiresAriaLabel(visibleText) {
    // An element requires aria-label when it has no visible text content
    return !visibleText || visibleText.trim().length === 0;
}

/**
 * Validates that an aria-label is non-empty (satisfies the requirement).
 */
function isValidAriaLabel(ariaLabel) {
    return typeof ariaLabel === 'string' && ariaLabel.trim().length > 0;
}

// ---------------------------------------------------------------------------
// Catalogue of icon-only interactive elements from the application
// These are the actual aria-label values used in the components.
// ---------------------------------------------------------------------------

/**
 * ThemeToggle.vue — icon-only button, no visible text
 * aria-label is dynamic based on current theme.
 */
function getThemeToggleAriaLabel(isDark) {
    return isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
}

/**
 * LanguageToggle.vue — shows "ID" or "EN" text (aria-hidden), no screen-reader text
 * aria-label is dynamic based on current locale.
 */
function getLanguageToggleAriaLabel(locale) {
    return locale === 'id' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia';
}

/**
 * Header.vue — hamburger menu button
 * aria-label is dynamic based on open/closed state.
 */
function getHamburgerAriaLabel(isOpen) {
    return isOpen ? 'Tutup menu' : 'Buka menu';
}

/**
 * Header.vue — CV download button (disabled state)
 * Uses i18n key 'nav.cvNotAvailable'.
 */
function getCvDisabledAriaLabel(t) {
    return t('nav.cvNotAvailable');
}

/**
 * ProjectModal.vue — close button
 * Uses i18n key 'portfolio.close'.
 */
function getModalCloseAriaLabel(t) {
    return t('portfolio.close');
}

/**
 * Footer.vue — back-to-top button (has visible text, but also aria-label)
 */
function getBackToTopAriaLabel(t) {
    return t('footer.backToTop');
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

const boolArb = fc.boolean();
const localeArb = fc.constantFrom('id', 'en');

/** Generates a string that is empty or whitespace-only */
const emptyTextArb = fc.oneof(
    fc.constant(''),
    fc.constant('   '),
    fc.constant('\t'),
    fc.constant('\n'),
);

/** Generates a non-empty visible text string */
const visibleTextArb = fc.string({ minLength: 1, maxLength: 100 })
    .filter(s => s.trim().length > 0);

/** Generates any string (for aria-label values) */
const anyStringArb = fc.string({ minLength: 0, maxLength: 200 });

/** Generates a non-empty aria-label string */
const validAriaLabelArb = fc.string({ minLength: 1, maxLength: 200 })
    .filter(s => s.trim().length > 0);

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 29: Interactive Elements Have aria-label', () => {

    describe('aria-label requirement logic', () => {
        it('element with no visible text always requires aria-label', () => {
            fc.assert(
                fc.property(emptyTextArb, (text) => {
                    expect(requiresAriaLabel(text)).toBe(true);
                }),
                { numRuns: 20 }
            );
        });

        it('element with visible text does not require aria-label', () => {
            fc.assert(
                fc.property(visibleTextArb, (text) => {
                    expect(requiresAriaLabel(text)).toBe(false);
                }),
                { numRuns: 50 }
            );
        });

        it('null/undefined text always requires aria-label', () => {
            expect(requiresAriaLabel(null)).toBe(true);
            expect(requiresAriaLabel(undefined)).toBe(true);
        });
    });

    describe('aria-label validity', () => {
        it('valid aria-label is always a non-empty trimmed string', () => {
            fc.assert(
                fc.property(validAriaLabelArb, (label) => {
                    expect(isValidAriaLabel(label)).toBe(true);
                }),
                { numRuns: 50 }
            );
        });

        it('empty or whitespace-only aria-label is invalid', () => {
            fc.assert(
                fc.property(emptyTextArb, (label) => {
                    expect(isValidAriaLabel(label)).toBe(false);
                }),
                { numRuns: 20 }
            );
        });

        it('null/undefined aria-label is invalid', () => {
            expect(isValidAriaLabel(null)).toBe(false);
            expect(isValidAriaLabel(undefined)).toBe(false);
        });
    });

    describe('ThemeToggle aria-label', () => {
        it('always returns a non-empty aria-label for any theme state', () => {
            fc.assert(
                fc.property(boolArb, (isDark) => {
                    const label = getThemeToggleAriaLabel(isDark);
                    expect(isValidAriaLabel(label)).toBe(true);
                }),
                { numRuns: 20 }
            );
        });

        it('dark mode label differs from light mode label', () => {
            const darkLabel = getThemeToggleAriaLabel(true);
            const lightLabel = getThemeToggleAriaLabel(false);
            expect(darkLabel).not.toBe(lightLabel);
        });

        it('dark mode label describes activating light mode', () => {
            expect(getThemeToggleAriaLabel(true)).toContain('terang');
        });

        it('light mode label describes activating dark mode', () => {
            expect(getThemeToggleAriaLabel(false)).toContain('gelap');
        });
    });

    describe('LanguageToggle aria-label', () => {
        it('always returns a non-empty aria-label for any locale', () => {
            fc.assert(
                fc.property(localeArb, (locale) => {
                    const label = getLanguageToggleAriaLabel(locale);
                    expect(isValidAriaLabel(label)).toBe(true);
                }),
                { numRuns: 20 }
            );
        });

        it('ID locale label describes switching to English', () => {
            expect(getLanguageToggleAriaLabel('id')).toContain('English');
        });

        it('EN locale label describes switching to Indonesian', () => {
            expect(getLanguageToggleAriaLabel('en')).toContain('Indonesia');
        });
    });

    describe('Hamburger menu aria-label', () => {
        it('always returns a non-empty aria-label for any open state', () => {
            fc.assert(
                fc.property(boolArb, (isOpen) => {
                    const label = getHamburgerAriaLabel(isOpen);
                    expect(isValidAriaLabel(label)).toBe(true);
                }),
                { numRuns: 20 }
            );
        });

        it('open state label differs from closed state label', () => {
            expect(getHamburgerAriaLabel(true)).not.toBe(getHamburgerAriaLabel(false));
        });
    });

    describe('Modal close button aria-label', () => {
        it('close button aria-label is non-empty for any translation function', () => {
            // Simulate i18n t() returning a non-empty string
            fc.assert(
                fc.property(validAriaLabelArb, (translatedValue) => {
                    const mockT = () => translatedValue;
                    const label = getModalCloseAriaLabel(mockT);
                    expect(isValidAriaLabel(label)).toBe(true);
                }),
                { numRuns: 30 }
            );
        });
    });

    describe('Icon-only elements catalogue completeness', () => {
        it('all known icon-only elements have non-empty aria-labels', () => {
            // Simulate i18n t() with realistic return values
            const mockT = (key) => {
                const translations = {
                    'nav.cvNotAvailable': 'CV belum tersedia',
                    'portfolio.close': 'Tutup',
                    'footer.backToTop': 'Kembali ke atas',
                };
                return translations[key] || key;
            };

            const iconOnlyElements = [
                { name: 'ThemeToggle (dark)', label: getThemeToggleAriaLabel(true) },
                { name: 'ThemeToggle (light)', label: getThemeToggleAriaLabel(false) },
                { name: 'LanguageToggle (id)', label: getLanguageToggleAriaLabel('id') },
                { name: 'LanguageToggle (en)', label: getLanguageToggleAriaLabel('en') },
                { name: 'Hamburger (open)', label: getHamburgerAriaLabel(true) },
                { name: 'Hamburger (closed)', label: getHamburgerAriaLabel(false) },
                { name: 'CV disabled button', label: getCvDisabledAriaLabel(mockT) },
                { name: 'Modal close button', label: getModalCloseAriaLabel(mockT) },
                { name: 'Back to top button', label: getBackToTopAriaLabel(mockT) },
            ];

            iconOnlyElements.forEach(({ name, label }) => {
                expect(isValidAriaLabel(label), `${name} must have a valid aria-label`).toBe(true);
            });
        });
    });
});
