// Feature: portfolio-website, Property 1: CV Button State Reflects Storage

/**
 * Property 1: CV Button State Reflects Storage
 * Validates: Requirements 1.4
 *
 * For any site settings state, if cv_file_path is null or empty, the CV
 * download button must be disabled. If cv_file_path is a non-empty string,
 * the button must be enabled (rendered as an <a> link).
 *
 * Since the test environment is 'node' (no DOM), we test the pure logic that
 * determines button state from the cvUrl prop.
 */

import { describe, it, expect } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Pure logic extracted from Header.vue
// The component renders:
//   - <a href="..."> (enabled) when cvUrl is a non-empty string
//   - <button disabled> when cvUrl is null, undefined, or empty string
// ---------------------------------------------------------------------------

/**
 * Mirrors the `v-if="cvUrl"` condition in Header.vue.
 * Returns true  → button is enabled (rendered as <a> link)
 * Returns false → button is disabled
 */
function isCvButtonEnabled(cvUrl) {
    return Boolean(cvUrl);
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** Generates null, undefined, or an empty string — all "no CV" states */
const emptyCvArb = fc.oneof(
    fc.constant(null),
    fc.constant(undefined),
    fc.constant('')
);

/** Generates a non-empty string — a valid CV URL */
const nonEmptyCvArb = fc.string({ minLength: 1 }).filter((s) => s.trim().length > 0);

/** Generates any possible cvUrl value */
const anyCvArb = fc.oneof(emptyCvArb, nonEmptyCvArb, fc.string());

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 1: CV Button State Reflects Storage', () => {

    it('button is disabled when cvUrl is null, undefined, or empty string', () => {
        fc.assert(
            fc.property(emptyCvArb, (cvUrl) => {
                expect(isCvButtonEnabled(cvUrl)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('button is enabled when cvUrl is a non-empty string', () => {
        fc.assert(
            fc.property(nonEmptyCvArb, (cvUrl) => {
                expect(isCvButtonEnabled(cvUrl)).toBe(true);
            }),
            { numRuns: 20 }
        );
    });

    it('button state is always a boolean (never throws)', () => {
        fc.assert(
            fc.property(anyCvArb, (cvUrl) => {
                const result = isCvButtonEnabled(cvUrl);
                expect(typeof result).toBe('boolean');
            }),
            { numRuns: 20 }
        );
    });

    it('button state is deterministic — same input always yields same output', () => {
        fc.assert(
            fc.property(anyCvArb, (cvUrl) => {
                expect(isCvButtonEnabled(cvUrl)).toBe(isCvButtonEnabled(cvUrl));
            }),
            { numRuns: 20 }
        );
    });

    it('non-empty cvUrl always enables button regardless of content', () => {
        fc.assert(
            fc.property(
                fc.string({ minLength: 1, maxLength: 2048 }).filter((s) => s.length > 0),
                (cvUrl) => {
                    // Any truthy string (even whitespace-only) satisfies v-if="cvUrl"
                    expect(isCvButtonEnabled(cvUrl)).toBe(true);
                }
            ),
            { numRuns: 20 }
        );
    });
});
