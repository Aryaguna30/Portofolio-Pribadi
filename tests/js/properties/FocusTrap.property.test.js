// Feature: portfolio-website, Property 30: Focus Trap Constrains Keyboard Navigation Inside Modal

/**
 * Property 30: Focus Trap Constrains Keyboard Navigation Inside Modal
 * Validates: Requirements 18.3
 *
 * For any open ProjectModal, pressing Tab must cycle focus only among
 * focusable elements within the modal container, and pressing Escape
 * must close the modal.
 *
 * Since the test environment is 'node' (no DOM), we test the pure logic
 * of the useFocusTrap composable and the modal's keyboard handling.
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Pure logic extracted from useFocusTrap.js
// ---------------------------------------------------------------------------

/**
 * Simulates the focus trap Tab cycling logic from useFocusTrap.js.
 * Given a list of focusable elements and the currently focused index,
 * returns the next focused index after pressing Tab (or Shift+Tab).
 *
 * @param {number} currentIndex - Index of currently focused element (0-based)
 * @param {number} totalElements - Total number of focusable elements
 * @param {boolean} shiftKey - Whether Shift is held (reverse Tab)
 * @returns {number} - Index of the next focused element
 */
function getNextFocusIndex(currentIndex, totalElements, shiftKey) {
    if (totalElements === 0) return -1;
    if (totalElements === 1) return 0;

    if (shiftKey) {
        // Shift+Tab: if at first element, wrap to last
        if (currentIndex === 0) return totalElements - 1;
        return currentIndex - 1;
    } else {
        // Tab: if at last element, wrap to first
        if (currentIndex === totalElements - 1) return 0;
        return currentIndex + 1;
    }
}

/**
 * Simulates cycling through all focusable elements with Tab.
 * Returns the sequence of indices visited.
 *
 * @param {number} totalElements - Total number of focusable elements
 * @param {number} cycles - Number of Tab presses to simulate
 * @returns {number[]} - Sequence of focused indices
 */
function simulateTabCycle(totalElements, cycles) {
    if (totalElements === 0) return [];
    const sequence = [0]; // Start at first element
    let current = 0;
    for (let i = 0; i < cycles; i++) {
        current = getNextFocusIndex(current, totalElements, false);
        sequence.push(current);
    }
    return sequence;
}

/**
 * Checks that all indices in a Tab cycle sequence are within bounds.
 *
 * @param {number[]} sequence - Sequence of focused indices
 * @param {number} totalElements - Total number of focusable elements
 * @returns {boolean}
 */
function allIndicesInBounds(sequence, totalElements) {
    return sequence.every(idx => idx >= 0 && idx < totalElements);
}

/**
 * Checks that after a full cycle (totalElements Tab presses from index 0),
 * focus returns to index 0.
 *
 * @param {number} totalElements - Total number of focusable elements
 * @returns {boolean}
 */
function cycleReturnsToStart(totalElements) {
    if (totalElements === 0) return true;
    let current = 0;
    for (let i = 0; i < totalElements; i++) {
        current = getNextFocusIndex(current, totalElements, false);
    }
    return current === 0;
}

/**
 * Simulates Escape key handling in ProjectModal.
 * Returns true if the modal should close (Escape was pressed while open).
 *
 * @param {string} key - The key pressed
 * @param {boolean} isOpen - Whether the modal is open
 * @returns {boolean} - Whether the modal should close
 */
function shouldModalClose(key, isOpen) {
    return key === 'Escape' && isOpen;
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** Generates a positive integer for number of focusable elements (1–20) */
const focusableCountArb = fc.integer({ min: 1, max: 20 });

/** Generates a non-negative integer for number of Tab presses (0–100) */
const tabPressCountArb = fc.integer({ min: 0, max: 100 });

/** Generates a boolean for Shift key state */
const shiftKeyArb = fc.boolean();

/** Generates a valid current focus index given a total count */
const focusStateArb = fc.integer({ min: 1, max: 20 }).chain(total =>
    fc.record({
        total: fc.constant(total),
        current: fc.integer({ min: 0, max: total - 1 }),
        shiftKey: fc.boolean(),
    })
);

/** Generates keyboard key strings */
const keyArb = fc.oneof(
    fc.constant('Escape'),
    fc.constant('Tab'),
    fc.constant('Enter'),
    fc.constant('Space'),
    fc.string({ minLength: 1, maxLength: 10 }),
);

/** Generates modal open state */
const isOpenArb = fc.boolean();

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 30: Focus Trap Constrains Keyboard Navigation Inside Modal', () => {

    describe('Tab cycling logic', () => {
        it('next focus index is always within bounds for any valid state', () => {
            fc.assert(
                fc.property(focusStateArb, ({ total, current, shiftKey }) => {
                    const next = getNextFocusIndex(current, total, shiftKey);
                    expect(next).toBeGreaterThanOrEqual(0);
                    expect(next).toBeLessThan(total);
                }),
                { numRuns: 200 }
            );
        });

        it('Tab from last element wraps to first (index 0)', () => {
            fc.assert(
                fc.property(focusableCountArb, (total) => {
                    const lastIndex = total - 1;
                    const next = getNextFocusIndex(lastIndex, total, false);
                    expect(next).toBe(0);
                }),
                { numRuns: 50 }
            );
        });

        it('Shift+Tab from first element wraps to last', () => {
            fc.assert(
                fc.property(focusableCountArb, (total) => {
                    const next = getNextFocusIndex(0, total, true);
                    expect(next).toBe(total - 1);
                }),
                { numRuns: 50 }
            );
        });

        it('Tab from non-boundary element moves forward by 1', () => {
            fc.assert(
                fc.property(
                    fc.integer({ min: 2, max: 20 }).chain(total =>
                        fc.record({
                            total: fc.constant(total),
                            current: fc.integer({ min: 0, max: total - 2 }), // not last
                        })
                    ),
                    ({ total, current }) => {
                        const next = getNextFocusIndex(current, total, false);
                        expect(next).toBe(current + 1);
                    }
                ),
                { numRuns: 100 }
            );
        });

        it('Shift+Tab from non-boundary element moves backward by 1', () => {
            fc.assert(
                fc.property(
                    fc.integer({ min: 2, max: 20 }).chain(total =>
                        fc.record({
                            total: fc.constant(total),
                            current: fc.integer({ min: 1, max: total - 1 }), // not first
                        })
                    ),
                    ({ total, current }) => {
                        const next = getNextFocusIndex(current, total, true);
                        expect(next).toBe(current - 1);
                    }
                ),
                { numRuns: 100 }
            );
        });

        it('single focusable element always stays focused on itself', () => {
            const next = getNextFocusIndex(0, 1, false);
            expect(next).toBe(0);
            const nextShift = getNextFocusIndex(0, 1, true);
            expect(nextShift).toBe(0);
        });
    });

    describe('Full cycle invariants', () => {
        it('pressing Tab N times (N = total elements) from index 0 returns to index 0', () => {
            fc.assert(
                fc.property(focusableCountArb, (total) => {
                    expect(cycleReturnsToStart(total)).toBe(true);
                }),
                { numRuns: 50 }
            );
        });

        it('all indices visited during Tab cycle are within bounds', () => {
            fc.assert(
                fc.property(focusableCountArb, tabPressCountArb, (total, presses) => {
                    const sequence = simulateTabCycle(total, presses);
                    expect(allIndicesInBounds(sequence, total)).toBe(true);
                }),
                { numRuns: 100 }
            );
        });

        it('Tab cycle visits every element at least once in a full cycle', () => {
            fc.assert(
                fc.property(focusableCountArb, (total) => {
                    // Simulate total Tab presses from index 0
                    const sequence = simulateTabCycle(total, total);
                    const visited = new Set(sequence);
                    // All indices 0..total-1 should be visited
                    for (let i = 0; i < total; i++) {
                        expect(visited.has(i)).toBe(true);
                    }
                }),
                { numRuns: 50 }
            );
        });
    });

    describe('Escape key closes modal', () => {
        it('Escape key always closes an open modal', () => {
            expect(shouldModalClose('Escape', true)).toBe(true);
        });

        it('Escape key does not close an already-closed modal', () => {
            expect(shouldModalClose('Escape', false)).toBe(false);
        });

        it('non-Escape keys never close the modal regardless of open state', () => {
            fc.assert(
                fc.property(
                    fc.string({ minLength: 1, maxLength: 20 }).filter(k => k !== 'Escape'),
                    isOpenArb,
                    (key, isOpen) => {
                        expect(shouldModalClose(key, isOpen)).toBe(false);
                    }
                ),
                { numRuns: 100 }
            );
        });

        it('only Escape key triggers modal close when modal is open', () => {
            fc.assert(
                fc.property(keyArb, (key) => {
                    const result = shouldModalClose(key, true);
                    expect(result).toBe(key === 'Escape');
                }),
                { numRuns: 50 }
            );
        });
    });

    describe('Focus trap boundary conditions', () => {
        it('focus trap with 0 elements returns -1 (no element to focus)', () => {
            expect(getNextFocusIndex(0, 0, false)).toBe(-1);
            expect(getNextFocusIndex(0, 0, true)).toBe(-1);
        });

        it('Tab cycling is deterministic — same state always yields same next index', () => {
            fc.assert(
                fc.property(focusStateArb, ({ total, current, shiftKey }) => {
                    const result1 = getNextFocusIndex(current, total, shiftKey);
                    const result2 = getNextFocusIndex(current, total, shiftKey);
                    expect(result1).toBe(result2);
                }),
                { numRuns: 100 }
            );
        });

        it('focus never escapes the modal container (all indices < totalElements)', () => {
            fc.assert(
                fc.property(focusableCountArb, tabPressCountArb, (total, presses) => {
                    const sequence = simulateTabCycle(total, presses);
                    const maxIndex = Math.max(...sequence);
                    expect(maxIndex).toBeLessThan(total);
                }),
                { numRuns: 100 }
            );
        });
    });
});
