<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} | Password Generator</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-amber-50 text-slate-900">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center justify-center px-6 py-12">
            <section
                class="w-full max-w-lg rounded-2xl border border-slate-200/70 bg-white/90 p-6 shadow-xl backdrop-blur"
                x-data="{
                    minLength: 8,
                    maxLength: 64,
                    length: 16,
                    includeLowercase: true,
                    includeUppercase: true,
                    includeDigits: true,
                    includeSymbols: false,
                    password: '',
                    errorMessage: '',
                    copied: false,
                    copyTimeout: null,
                    clampLength() {
                        if (!Number.isFinite(this.length)) {
                            this.length = this.minLength;
                            return;
                        }

                        if (this.length < this.minLength) {
                            this.length = this.minLength;
                        }

                        if (this.length > this.maxLength) {
                            this.length = this.maxLength;
                        }
                    },
                    get hasAnyType() {
                        return this.includeLowercase || this.includeUppercase || this.includeDigits || this.includeSymbols;
                    },
                    get score() {
                        return this.calculateScore();
                    },
                    get strengthLabel() {
                        if (this.score >= 70) {
                            return 'Strong';
                        }

                        if (this.score >= 35) {
                            return 'Medium';
                        }

                        return 'Weak';
                    },
                    get strengthBarClass() {
                        if (this.score >= 70) {
                            return 'bg-emerald-500';
                        }

                        if (this.score >= 35) {
                            return 'bg-amber-500';
                        }

                        return 'bg-rose-500';
                    },
                    get strengthTextClass() {
                        if (this.score >= 70) {
                            return 'text-emerald-600';
                        }

                        if (this.score >= 35) {
                            return 'text-amber-600';
                        }

                        return 'text-rose-600';
                    },
                    calculateScore() {
                        let score = 0;

                        if (this.length >= 12) {
                            score += 15;
                        }

                        if (this.length >= 16) {
                            score += 20;
                        }

                        if (this.length >= 20) {
                            score += 20;
                        }

                        let variety = 0;

                        if (this.includeLowercase) {
                            variety += 1;
                        }

                        if (this.includeUppercase) {
                            variety += 1;
                        }

                        if (this.includeDigits) {
                            variety += 1;
                        }

                        if (this.includeSymbols) {
                            variety += 1;
                        }

                        score += variety * 10;

                        if (this.includeDigits && this.includeSymbols) {
                            score += 10;
                        }

                        return Math.min(score, 100);
                    },
                    getCharacterSets() {
                        return {
                            lowercase: 'abcdefghijklmnopqrstuvwxyz',
                            uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                            digits: '0123456789',
                            symbols: '!@#$%^&*()-_=+[]{}<>?.,;:|~'
                        };
                    },
                    hasCrypto() {
                        return Boolean(window.crypto && window.crypto.getRandomValues);
                    },
                    randomInt(max) {
                        if (!this.hasCrypto()) {
                            return 0;
                        }

                        if (max <= 0) {
                            return 0;
                        }

                        const maxUint32 = 0x100000000;
                        const limit = maxUint32 - (maxUint32 % max);
                        const array = new Uint32Array(1);
                        let value = 0;

                        do {
                            window.crypto.getRandomValues(array);
                            value = array[0];
                        } while (value >= limit);

                        return value % max;
                    },
                    randomChar(characters) {
                        return characters.charAt(this.randomInt(characters.length));
                    },
                    shuffle(characters) {
                        for (let i = characters.length - 1; i > 0; i -= 1) {
                            const j = this.randomInt(i + 1);
                            [characters[i], characters[j]] = [characters[j], characters[i]];
                        }

                        return characters;
                    },
                    generate() {
                        this.errorMessage = '';
                        this.clampLength();

                        if (!this.hasAnyType) {
                            this.errorMessage = 'Please select at least one character type.';
                            return;
                        }

                        if (!this.hasCrypto()) {
                            this.errorMessage = 'Secure randomness is not available in this browser.';
                            return;
                        }

                        const sets = this.getCharacterSets();
                        const activeSets = [];

                        if (this.includeLowercase) {
                            activeSets.push(sets.lowercase);
                        }

                        if (this.includeUppercase) {
                            activeSets.push(sets.uppercase);
                        }

                        if (this.includeDigits) {
                            activeSets.push(sets.digits);
                        }

                        if (this.includeSymbols) {
                            activeSets.push(sets.symbols);
                        }

                        let pool = '';
                        const result = [];

                        activeSets.forEach((set) => {
                            pool += set;
                            result.push(this.randomChar(set));
                        });

                        const remaining = this.length - result.length;

                        for (let i = 0; i < remaining; i += 1) {
                            result.push(this.randomChar(pool));
                        }

                        this.password = this.shuffle(result).join('');
                        this.copied = false;
                    },
                    async copyPassword() {
                        this.errorMessage = '';

                        if (!this.password) {
                            return;
                        }

                        if (!navigator.clipboard || !navigator.clipboard.writeText) {
                            this.errorMessage = 'Clipboard access is not available.';
                            return;
                        }

                        try {
                            await navigator.clipboard.writeText(this.password);
                            this.copied = true;

                            if (this.copyTimeout) {
                                clearTimeout(this.copyTimeout);
                            }

                            this.copyTimeout = setTimeout(() => {
                                this.copied = false;
                            }, 2000);
                        } catch (error) {
                            this.errorMessage = 'Copy failed. Please copy manually.';
                        }
                    }
                }"
            >
                <header class="flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Secure Passwords</p>
                    <h1 class="text-2xl font-semibold text-slate-900">Password Generator</h1>
                    <p class="text-sm text-slate-600">
                        Create strong, custom passwords locally in your browser.
                    </p>
                </header>

                <div class="mt-6 flex flex-col gap-6">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <label for="length" class="text-sm font-medium text-slate-700">Length</label>
                            <span class="text-sm font-semibold text-slate-900" x-text="length"></span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_5.5rem]">
                            <input
                                id="length"
                                type="range"
                                min="8"
                                max="64"
                                step="1"
                                class="w-full accent-slate-900"
                                x-model.number="length"
                            />
                            <input
                                type="number"
                                min="8"
                                max="64"
                                step="1"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
                                x-model.number="length"
                                @change="clampLength()"
                                @blur="clampLength()"
                            />
                        </div>
                    </div>

                    <fieldset class="flex flex-col gap-3">
                        <legend class="text-sm font-medium text-slate-700">Include characters</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" class="h-4 w-4 accent-slate-900" x-model="includeLowercase" />
                                Lowercase (a-z)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" class="h-4 w-4 accent-slate-900" x-model="includeUppercase" />
                                Uppercase (A-Z)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" class="h-4 w-4 accent-slate-900" x-model="includeDigits" />
                                Digits (0-9)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-700">
                                <input type="checkbox" class="h-4 w-4 accent-slate-900" x-model="includeSymbols" />
                                Symbols (!@#)
                            </label>
                        </div>
                    </fieldset>

                    <div class="flex flex-col gap-3">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-slate-200/70 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2"
                            @click="generate()"
                        >
                            Generate
                        </button>
                        <p
                            class="text-sm text-rose-600"
                            role="alert"
                            aria-live="polite"
                            x-show="errorMessage"
                            x-text="errorMessage"
                        ></p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <label for="password" class="text-sm font-medium text-slate-700">Generated password</label>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <input
                                id="password"
                                type="text"
                                readonly
                                class="w-full flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
                                placeholder="Your password will appear here"
                                x-model="password"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:border-slate-400 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="password.length === 0"
                                @click="copyPassword()"
                            >
                                Copy
                            </button>
                        </div>
                        <p class="text-xs font-semibold text-emerald-600" x-show="copied" x-transition>
                            Copied!
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Strength</span>
                            <span class="text-sm font-semibold" :class="strengthTextClass" x-text="`${strengthLabel} (${score})`"></span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full transition-all duration-300"
                                :class="strengthBarClass"
                                :style="`width: ${score}%`"
                            ></div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
