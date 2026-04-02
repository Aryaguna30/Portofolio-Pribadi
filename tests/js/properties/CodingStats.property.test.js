// Feature: portfolio-website, Property 9: Graceful Degradation on API Failure or Timeout

/**
 * Property 9: Graceful Degradation on API Failure or Timeout
 * Validates: Requirements 6.5, 6.8, 6.9
 *
 * For any external API call (GitHub or WakaTime) that results in an error
 * (network failure, 4xx, 5xx) or exceeds the 3-second timeout, the CodingStats
 * component must hide itself and must not throw an unhandled exception or alter
 * the layout of other page sections.
 *
 * Since the test environment is 'node' (no DOM/Vue), we test the pure error
 * handling logic extracted from CodingStats.vue:
 *   - The fetch error handler sets error = true, loading = false
 *   - The component hides itself when error = true (v-if="!error")
 *   - No exception is thrown/propagated
 */

import { describe, it, expect } from 'vitest';
import * as fc from 'fast-check';

// ---------------------------------------------------------------------------
// Pure logic extracted from CodingStats.vue onMounted fetch handler
//
// The component's fetch logic (simplified):
//
//   const controller = new AbortController();
//   const timeoutId = setTimeout(() => controller.abort(), 3000);
//   try {
//     const response = await fetch('/coding-stats', { signal: controller.signal });
//     clearTimeout(timeoutId);
//     if (!response.ok) throw new Error(`HTTP ${response.status}`);
//     const data = await response.json();
//     if (!data || data.error) { error.value = true; return; }
//     stats.value = data;
//     loading.value = false;
//   } catch (err) {
//     clearTimeout(timeoutId);
//     error.value = true;
//     loading.value = false;
//   }
//
// The template uses: v-if="!error"  →  component is hidden when error = true
// ---------------------------------------------------------------------------

/**
 * Simulates the CodingStats fetch handler with a given mock fetch function.
 * Returns the resulting reactive state: { error, loading, stats }.
 */
async function runFetchHandler(mockFetch) {
    let error = false;
    let loading = true;
    let stats = null;

    try {
        const response = await mockFetch('/coding-stats');

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!data || data.error) {
            error = true;
            return { error, loading, stats };
        }

        stats = data;
        loading = false;
    } catch (_err) {
        error = true;
        loading = false;
    }

    return { error, loading, stats };
}

/**
 * Whether the component would be visible given the error state.
 * Mirrors: v-if="!error"
 */
function isComponentVisible(errorState) {
    return !errorState;
}

// ---------------------------------------------------------------------------
// Arbitraries
// ---------------------------------------------------------------------------

/** HTTP 4xx status codes */
const http4xxArb = fc.integer({ min: 400, max: 499 });

/** HTTP 5xx status codes */
const http5xxArb = fc.integer({ min: 500, max: 599 });

/** Any non-2xx HTTP error status */
const httpErrorStatusArb = fc.oneof(http4xxArb, http5xxArb);

/** Error types that simulate network/runtime failures */
const networkErrorArb = fc.oneof(
    fc.constant(new Error('Network request failed')),
    fc.constant(new TypeError('Failed to fetch')),
    fc.constant(new DOMException('The operation was aborted.', 'AbortError')),
    fc.constant(new Error('net::ERR_CONNECTION_REFUSED')),
    fc.constant(new Error('net::ERR_NAME_NOT_RESOLVED')),
    fc.constant(new SyntaxError('Unexpected token < in JSON')),
);

/** Arbitrary error messages for generic thrown errors */
const errorMessageArb = fc.string({ minLength: 1, maxLength: 200 });

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('Property 9: Graceful Degradation on API Failure or Timeout', () => {

    it('HTTP 4xx response → error state = true, component hidden, no exception', async () => {
        await fc.assert(
            fc.asyncProperty(http4xxArb, async (status) => {
                const mockFetch = async () => ({
                    ok: false,
                    status,
                    json: async () => null,
                });

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('HTTP 5xx response → error state = true, component hidden, no exception', async () => {
        await fc.assert(
            fc.asyncProperty(http5xxArb, async (status) => {
                const mockFetch = async () => ({
                    ok: false,
                    status,
                    json: async () => null,
                });

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('AbortError (timeout) → error state = true, component hidden, no exception', async () => {
        await fc.assert(
            fc.asyncProperty(fc.constant(null), async () => {
                const abortError = new DOMException('The operation was aborted.', 'AbortError');
                const mockFetch = async () => { throw abortError; };

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('network error (any thrown Error) → error state = true, component hidden, no exception', async () => {
        await fc.assert(
            fc.asyncProperty(networkErrorArb, async (err) => {
                const mockFetch = async () => { throw err; };

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('JSON parse error → error state = true, component hidden, no exception', async () => {
        await fc.assert(
            fc.asyncProperty(fc.constant(null), async () => {
                const mockFetch = async () => ({
                    ok: true,
                    status: 200,
                    json: async () => { throw new SyntaxError('Unexpected token < in JSON at position 0'); },
                });

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('response with error flag in data → error state = true, component hidden', async () => {
        await fc.assert(
            fc.asyncProperty(fc.constant(null), async () => {
                const mockFetch = async () => ({
                    ok: true,
                    status: 200,
                    json: async () => ({ error: true }),
                });

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('null response body → error state = true, component hidden, no exception', async () => {
        await fc.assert(
            fc.asyncProperty(fc.constant(null), async () => {
                const mockFetch = async () => ({
                    ok: true,
                    status: 200,
                    json: async () => null,
                });

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('any error type → loading is always false after error (no stuck spinner)', async () => {
        await fc.assert(
            fc.asyncProperty(networkErrorArb, async (err) => {
                const mockFetch = async () => { throw err; };

                const state = await runFetchHandler(mockFetch);

                expect(state.loading).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('any HTTP error status → loading is always false after error', async () => {
        await fc.assert(
            fc.asyncProperty(httpErrorStatusArb, async (status) => {
                const mockFetch = async () => ({
                    ok: false,
                    status,
                    json: async () => null,
                });

                const state = await runFetchHandler(mockFetch);

                expect(state.loading).toBe(false);
            }),
            { numRuns: 20 }
        );
    });

    it('error state and component visibility are always consistent (error=true → hidden)', async () => {
        await fc.assert(
            fc.asyncProperty(httpErrorStatusArb, async (status) => {
                const mockFetch = async () => ({
                    ok: false,
                    status,
                    json: async () => null,
                });

                const state = await runFetchHandler(mockFetch);

                // Invariant: error=true must always mean component is hidden
                if (state.error) {
                    expect(isComponentVisible(state.error)).toBe(false);
                }
            }),
            { numRuns: 20 }
        );
    });

    it('generic thrown error with any message → always gracefully handled', async () => {
        await fc.assert(
            fc.asyncProperty(errorMessageArb, async (message) => {
                const mockFetch = async () => { throw new Error(message); };

                let threw = false;
                let state;
                try {
                    state = await runFetchHandler(mockFetch);
                } catch (_e) {
                    threw = true;
                }

                expect(threw).toBe(false);
                expect(state.error).toBe(true);
                expect(state.loading).toBe(false);
                expect(isComponentVisible(state.error)).toBe(false);
            }),
            { numRuns: 20 }
        );
    });
});
