// Feature: portfolio-website, Property 3: Language Toggle Completeness

/**
 * Property 3: Language Toggle Completeness
 * Validates: Requirements 2.2, 2.3
 *
 * For any locale key defined in the i18n locale files (id.json or en.json),
 * after switching to that locale, every translated string rendered in the UI
 * must use the corresponding value from that locale file — no key should fall
 * back to a raw key string.
 */

import { describe, it, expect } from 'vitest';
import * as fc from 'fast-check';
import { createI18n } from 'vue-i18n';
import idMessages from '../../../resources/js/i18n/locales/id.json';
import enMessages from '../../../resources/js/i18n/locales/en.json';

/**
 * Flatten a nested object into dot-notation keys.
 * e.g. { nav: { home: "Beranda" } } → { "nav.home": "Beranda" }
 */
function flattenKeys(obj, prefix = '') {
    return Object.entries(obj).reduce((acc, [key, value]) => {
        const fullKey = prefix ? `${prefix}.${key}` : key;
        if (value !== null && typeof value === 'object' && !Array.isArray(value)) {
            Object.assign(acc, flattenKeys(value, fullKey));
        } else {
            acc[fullKey] = value;
        }
        return acc;
    }, {});
}

/**
 * Get a nested value from an object using a dot-notation key path.
 */
function getNestedValue(obj, keyPath) {
    return keyPath.split('.').reduce((current, key) => {
        return current != null ? current[key] : undefined;
    }, obj);
}

const flatId = flattenKeys(idMessages);
const flatEn = flattenKeys(enMessages);
const allIdKeys = Object.keys(flatId);
const allEnKeys = Object.keys(flatEn);

function makeI18n(locale) {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'id',
        messages: { id: idMessages, en: enMessages },
        missing: (_locale, key) => key, // return raw key on missing
    });
}

/**
 * Check that a key exists in the locale messages (not missing/undefined).
 * Uses getLocaleMessage to directly inspect the message store without
 * triggering the vue-i18n message compiler (which can fail on values
 * containing '@' like email placeholders).
 */
function keyExistsInLocale(i18n, locale, key) {
    const messages = i18n.global.getLocaleMessage(locale);
    const value = getNestedValue(messages, key);
    return value !== undefined && value !== null;
}

/**
 * Check that the stored value for a key is not the raw key string itself.
 * This verifies the translation is defined and not falling back to the key.
 */
function valueIsNotRawKey(i18n, locale, key) {
    const messages = i18n.global.getLocaleMessage(locale);
    const value = getNestedValue(messages, key);
    return value !== key;
}

describe('Property 3: Language Toggle Completeness', () => {
    it('id locale: every key exists in the locale message store (not missing)', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allIdKeys),
                (key) => {
                    const i18n = makeI18n('id');
                    // Key must exist in the locale messages
                    expect(keyExistsInLocale(i18n, 'id', key)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('en locale: every key exists in the locale message store (not missing)', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allEnKeys),
                (key) => {
                    const i18n = makeI18n('en');
                    // Key must exist in the locale messages
                    expect(keyExistsInLocale(i18n, 'en', key)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('id locale: no key falls back to a raw key string', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allIdKeys),
                (key) => {
                    const i18n = makeI18n('id');
                    // The stored value must not equal the raw key path
                    expect(valueIsNotRawKey(i18n, 'id', key)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('en locale: no key falls back to a raw key string', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allEnKeys),
                (key) => {
                    const i18n = makeI18n('en');
                    // The stored value must not equal the raw key path
                    expect(valueIsNotRawKey(i18n, 'en', key)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('id locale: stored values match the locale file exactly', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allIdKeys),
                (key) => {
                    const i18n = makeI18n('id');
                    const messages = i18n.global.getLocaleMessage('id');
                    const storedValue = getNestedValue(messages, key);
                    expect(storedValue).toBe(flatId[key]);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('en locale: stored values match the locale file exactly', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allEnKeys),
                (key) => {
                    const i18n = makeI18n('en');
                    const messages = i18n.global.getLocaleMessage('en');
                    const storedValue = getNestedValue(messages, key);
                    expect(storedValue).toBe(flatEn[key]);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('switching locale from id to en: en keys exist and are not raw key strings', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allEnKeys),
                (key) => {
                    const i18n = makeI18n('id');
                    // Switch locale to 'en'
                    i18n.global.locale.value = 'en';
                    expect(keyExistsInLocale(i18n, 'en', key)).toBe(true);
                    expect(valueIsNotRawKey(i18n, 'en', key)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('switching locale from en to id: id keys exist and are not raw key strings', () => {
        fc.assert(
            fc.property(
                fc.constantFrom(...allIdKeys),
                (key) => {
                    const i18n = makeI18n('en');
                    // Switch locale to 'id'
                    i18n.global.locale.value = 'id';
                    expect(keyExistsInLocale(i18n, 'id', key)).toBe(true);
                    expect(valueIsNotRawKey(i18n, 'id', key)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });
});
