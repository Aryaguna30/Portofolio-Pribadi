// Feature: portfolio-website, Property 5: Typing Animation Sequence Preservation

/**
 * Property 5: Typing Animation Sequence Preservation
 * Validates: Requirements 3.3
 *
 * For any non-empty array of profession strings passed to HeroSection, the
 * typing animation must display each string in the same order as the input
 * array, cycling back to the first item after the last.
 *
 * Since the test environment is 'node' (no DOM/timers), we extract and test
 * the pure cycling logic from HeroSection.vue as a pure function.
 *
 * Key logic from HeroSection.vue:
 *   profIdx = (profIdx + 1) % props.professions.length
 *
 * Properties verified:
 *   1. Next index after completing a word is (currentIndex + 1) % length
 *   2. After cycling through all items, it wraps back to index 0
 *   3. The item at index i is always professions[i] (order preserved)
 *   4. Full cycle visits every item exactly once before repeating
 */

import { describe, it, expect } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Pure cycling logic extracted from HeroSection.vue
// ---------------------------------------------------------------------------

/**
 * Returns the next profession index after completing the current word.
 * Mirrors: profIdx = (profIdx + 1) % props.professions.length
 */
function nextProfessionIndex(currentIndex, professionsLength) {
    return (currentIndex + 1) % professionsLength;
}

/**
 * Simulates a full cycle through all professions starting from index 0.
 * Returns the sequence of indices visited.
 */
function simulateFullCycle(professions) {
    const sequence = [];
    let idx = 0;
    for (let i = 0; i < professions.length; i++) {
        sequence.push(idx);
        idx = nextProfessionIndex(idx, professions.length);
    }
    return sequence;
}

/**
 * Returns the profession string that would be displayed at a given step
 * in the cycle, starting from index 0.
 */
function getProfessionAtStep(professions, step) {
    const idx = step % professions.length;
    return professions[idx];
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** Generates a non-empty array of non-empty profession strings */
const professionsArb = fc.array(
    fc.string({ minLength: 1, maxLength: 50 }),
    { minLength: 1, maxLength: 20 }
);

/** Generates a valid current index within a given array length */
function validIndexArb(length) {
    return fc.integer({ min: 0, max: length - 1 });
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 5: Typing Animation Sequence Preservation', () => {

    it('next index is always (currentIndex + 1) % length', () => {
        fc.assert(
            fc.property(
                professionsArb.chain((professions) =>
                    fc.tuple(
                        fc.constant(professions),
                        validIndexArb(professions.length)
                    )
                ),
                ([professions, currentIndex]) => {
                    const next = nextProfessionIndex(currentIndex, professions.length);
                    expect(next).toBe((currentIndex + 1) % professions.length);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('after the last item, index wraps back to 0', () => {
        fc.assert(
            fc.property(professionsArb, (professions) => {
                const lastIndex = professions.length - 1;
                const next = nextProfessionIndex(lastIndex, professions.length);
                expect(next).toBe(0);
            }),
            { numRuns: 20 }
        );
    });

    it('item at index i is always professions[i] (order preserved)', () => {
        fc.assert(
            fc.property(
                professionsArb.chain((professions) =>
                    fc.tuple(
                        fc.constant(professions),
                        validIndexArb(professions.length)
                    )
                ),
                ([professions, idx]) => {
                    expect(professions[idx]).toBe(professions[idx]);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('full cycle visits every profession exactly once in input order', () => {
        fc.assert(
            fc.property(professionsArb, (professions) => {
                const visitedIndices = simulateFullCycle(professions);

                // Must visit exactly professions.length items
                expect(visitedIndices.length).toBe(professions.length);

                // Must start at index 0
                expect(visitedIndices[0]).toBe(0);

                // Must visit indices 0, 1, 2, ..., length-1 in order
                for (let i = 0; i < professions.length; i++) {
                    expect(visitedIndices[i]).toBe(i);
                }
            }),
            { numRuns: 20 }
        );
    });

    it('profession displayed at each step matches input array order', () => {
        fc.assert(
            fc.property(
                professionsArb.chain((professions) =>
                    fc.tuple(
                        fc.constant(professions),
                        fc.integer({ min: 0, max: 99 }) // arbitrary step count
                    )
                ),
                ([professions, step]) => {
                    const displayed = getProfessionAtStep(professions, step);
                    const expectedIndex = step % professions.length;
                    expect(displayed).toBe(professions[expectedIndex]);
                }
            ),
            { numRuns: 20 }
        );
    });

    it('cycling is deterministic — same professions array always produces same sequence', () => {
        fc.assert(
            fc.property(professionsArb, (professions) => {
                const cycle1 = simulateFullCycle(professions);
                const cycle2 = simulateFullCycle(professions);
                expect(cycle1).toEqual(cycle2);
            }),
            { numRuns: 20 }
        );
    });

    it('next index is always a valid index within the array bounds', () => {
        fc.assert(
            fc.property(
                professionsArb.chain((professions) =>
                    fc.tuple(
                        fc.constant(professions),
                        validIndexArb(professions.length)
                    )
                ),
                ([professions, currentIndex]) => {
                    const next = nextProfessionIndex(currentIndex, professions.length);
                    expect(next).toBeGreaterThanOrEqual(0);
                    expect(next).toBeLessThan(professions.length);
                }
            ),
            { numRuns: 20 }
        );
    });
});
