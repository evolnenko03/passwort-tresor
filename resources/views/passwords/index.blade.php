<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} | Passwortgenerator</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-black via-slate-950 to-slate-900 text-slate-100">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center justify-center px-6 py-12">
            <section
                class="w-full max-w-lg rounded-2xl border border-slate-800/80 bg-slate-950/80 p-6 shadow-2xl shadow-black/60 backdrop-blur"
                x-data="{
                    storageKey: 'password-generator',
                    storageTimeout: null,
                    symbolCharacters: '!@#$%^&*()-_=+[]{};:,.?/',
                    messages: {
                        minTypesRequired: 'Mindestens ein Zeichentyp muss aktiviert sein.',
                        minValuesTooLarge: 'Die Summe der Mindestwerte darf die Passwortlänge nicht überschreiten.',
                        cryptoUnavailable: 'Sichere Zufallszahlen sind in diesem Browser nicht verfügbar.',
                        clipboardUnavailable: 'Zugriff auf die Zwischenablage ist nicht verfügbar.',
                        clipboardFailed: 'Kopieren fehlgeschlagen. Bitte manuell kopieren.'
                    },
                    minLength: 8,
                    maxLength: 128,
                    length: 16,
                    includeLowercase: true,
                    includeUppercase: true,
                    includeDigits: true,
                    includeSymbols: true,
                    minDigits: 1,
                    minSymbols: 1,
                    password: '',
                    errorMessage: '',
                    copied: false,
                    copyTimeout: null,
                    init() {
                        const loaded = this.loadStoredState();

                        this.clampLength();
                        this.ensureTypeSelection();
                        this.normalizeMinimums();

                        if (!loaded || !this.password) {
                            this.generatePassword();
                        } else {
                            this.sanitizePasswordInput();
                        }

                        this.$nextTick(() => {
                            this.resizePasswordField();
                        });

                        const watchedKeys = [
                            'length',
                            'includeLowercase',
                            'includeUppercase',
                            'includeDigits',
                            'includeSymbols',
                            'minDigits',
                            'minSymbols',
                            'password'
                        ];

                        watchedKeys.forEach((key) => {
                            this.$watch(key, () => this.scheduleStateSave());
                        });
                    },
                    isStorageAvailable() {
                        try {
                            const testKey = `${this.storageKey}-test`;
                            localStorage.setItem(testKey, '1');
                            localStorage.removeItem(testKey);
                            return true;
                        } catch (error) {
                            return false;
                        }
                    },
                    loadStoredState() {
                        if (!this.isStorageAvailable()) {
                            return false;
                        }

                        const raw = localStorage.getItem(this.storageKey);

                        if (!raw) {
                            return false;
                        }

                        try {
                            const data = JSON.parse(raw);

                            if (Number.isFinite(data.length)) {
                                this.length = data.length;
                            }

                            if (typeof data.includeLowercase === 'boolean') {
                                this.includeLowercase = data.includeLowercase;
                            }

                            if (typeof data.includeUppercase === 'boolean') {
                                this.includeUppercase = data.includeUppercase;
                            }

                            if (typeof data.includeDigits === 'boolean') {
                                this.includeDigits = data.includeDigits;
                            }

                            if (typeof data.includeSymbols === 'boolean') {
                                this.includeSymbols = data.includeSymbols;
                            }

                            if (Number.isFinite(data.minDigits)) {
                                this.minDigits = data.minDigits;
                            }

                            if (Number.isFinite(data.minSymbols)) {
                                this.minSymbols = data.minSymbols;
                            }

                            if (typeof data.password === 'string') {
                                this.password = data.password;
                            }
                        } catch (error) {
                            return false;
                        }

                        return true;
                    },
                    saveStoredState() {
                        if (!this.isStorageAvailable()) {
                            return;
                        }

                        const payload = {
                            length: this.length,
                            includeLowercase: this.includeLowercase,
                            includeUppercase: this.includeUppercase,
                            includeDigits: this.includeDigits,
                            includeSymbols: this.includeSymbols,
                            minDigits: this.minDigits,
                            minSymbols: this.minSymbols,
                            password: this.password
                        };

                        try {
                            localStorage.setItem(this.storageKey, JSON.stringify(payload));
                        } catch (error) {
                            return;
                        }
                    },
                    scheduleStateSave() {
                        if (!this.isStorageAvailable()) {
                            return;
                        }

                        if (this.storageTimeout) {
                            clearTimeout(this.storageTimeout);
                        }

                        this.storageTimeout = setTimeout(() => {
                            this.saveStoredState();
                        }, 200);
                    },
                    clampLength() {
                        const numericValue = Number.parseInt(this.length, 10);

                        if (!Number.isFinite(numericValue)) {
                            this.length = this.minLength;
                            return;
                        }

                        if (numericValue < this.minLength) {
                            this.length = this.minLength;
                            return;
                        }

                        if (numericValue > this.maxLength) {
                            this.length = this.maxLength;
                            return;
                        }

                        this.length = numericValue;
                    },
                    clampNumber(value, min, max) {
                        const numericValue = Number.parseInt(value, 10);

                        if (!Number.isFinite(numericValue)) {
                            return min;
                        }

                        if (numericValue < min) {
                            return min;
                        }

                        if (numericValue > max) {
                            return max;
                        }

                        return numericValue;
                    },
                    ensureTypeSelection(typeKey = null) {
                        if (this.includeLowercase || this.includeUppercase || this.includeDigits || this.includeSymbols) {
                            return true;
                        }

                        if (typeKey) {
                            this[typeKey] = true;
                        } else {
                            this.includeLowercase = true;
                        }

                        this.errorMessage = this.messages.minTypesRequired;
                        return false;
                    },
                    normalizeMinimums() {
                        if (!this.includeDigits) {
                            this.minDigits = 0;
                        }

                        if (!this.includeSymbols) {
                            this.minSymbols = 0;
                        }

                        this.minDigits = this.clampNumber(this.minDigits, 0, this.maxLength);
                        this.minSymbols = this.clampNumber(this.minSymbols, 0, this.maxLength);

                        let total = this.minDigits + this.minSymbols;

                        if (total <= this.length) {
                            return;
                        }

                        this.errorMessage = this.messages.minValuesTooLarge;
                        let overflow = total - this.length;
                        const order = ['minSymbols', 'minDigits'];

                        order.forEach((key) => {
                            if (overflow <= 0) {
                                return;
                            }

                            const reduction = Math.min(this[key], overflow);
                            this[key] -= reduction;
                            overflow -= reduction;
                        });
                    },
                    resizePasswordField() {
                        const field = this.$refs.passwordField;

                        if (!field) {
                            return;
                        }

                        field.style.height = 'auto';
                        field.style.height = `${field.scrollHeight}px`;
                    },
                    sanitizePasswordInput() {
                        const current = this.password ?? '';
                        let sanitized = '';

                        for (const character of current) {
                            if (this.includeLowercase && /[a-z]/.test(character)) {
                                sanitized += character;
                                continue;
                            }

                            if (this.includeUppercase && /[A-Z]/.test(character)) {
                                sanitized += character;
                                continue;
                            }

                            if (this.includeDigits && /[0-9]/.test(character)) {
                                sanitized += character;
                                continue;
                            }

                            if (this.includeSymbols && this.symbolCharacters.includes(character)) {
                                sanitized += character;
                            }
                        }

                        if (sanitized.length > this.length) {
                            sanitized = sanitized.slice(0, this.length);
                        }

                        if (sanitized !== current) {
                            this.password = sanitized;
                            this.copied = false;
                        }

                        this.$nextTick(() => {
                            this.resizePasswordField();
                        });
                    },
                    resetError() {
                        this.errorMessage = '';
                    },
                    handleLengthChange() {
                        this.resetError();
                        this.clampLength();
                        this.normalizeMinimums();
                        this.sanitizePasswordInput();
                    },
                    handleMinDigitsChange() {
                        this.resetError();
                        this.minDigits = this.clampNumber(this.minDigits, 0, this.maxLength);
                        this.normalizeMinimums();
                    },
                    handleMinSymbolsChange() {
                        this.resetError();
                        this.minSymbols = this.clampNumber(this.minSymbols, 0, this.maxLength);
                        this.normalizeMinimums();
                    },
                    handleTypeChange(typeKey) {
                        this.resetError();

                        if (!this[typeKey]) {
                            if (!this.ensureTypeSelection(typeKey)) {
                                return;
                            }

                            if (typeKey === 'includeDigits') {
                                this.minDigits = 0;
                            }

                            if (typeKey === 'includeSymbols') {
                                this.minSymbols = 0;
                            }
                        } else {
                            if (typeKey === 'includeDigits' && this.minDigits < 1) {
                                this.minDigits = 1;
                            }

                            if (typeKey === 'includeSymbols' && this.minSymbols < 1) {
                                this.minSymbols = 1;
                            }
                        }

                        this.normalizeMinimums();
                        this.sanitizePasswordInput();
                    },
                    handlePasswordChange() {
                        this.copied = false;
                        this.sanitizePasswordInput();
                    },
                    get score() {
                        return this.calculateScore();
                    },
                    get strengthLabel() {
                        if (this.score >= 70) {
                            return 'Stark';
                        }

                        if (this.score >= 35) {
                            return 'Mittel';
                        }

                        return 'Schwach';
                    },
                    get strengthBarClass() {
                        if (this.score >= 70) {
                            return 'bg-red-500';
                        }

                        if (this.score >= 35) {
                            return 'bg-red-400';
                        }

                        return 'bg-red-300';
                    },
                    get strengthTextClass() {
                        if (this.score >= 70) {
                            return 'text-red-300';
                        }

                        if (this.score >= 35) {
                            return 'text-red-400';
                        }

                        return 'text-red-500';
                    },
                    calculateScore() {
                        const passwordValue = this.password ?? '';
                        const lengthValue = passwordValue.length;
                        let score = 0;

                        if (lengthValue >= 12) {
                            score += 15;
                        }

                        if (lengthValue >= 16) {
                            score += 20;
                        }

                        if (lengthValue >= 20) {
                            score += 20;
                        }

                        let variety = 0;

                        if (/[a-z]/.test(passwordValue)) {
                            variety += 1;
                        }

                        if (/[A-Z]/.test(passwordValue)) {
                            variety += 1;
                        }

                        if (/[0-9]/.test(passwordValue)) {
                            variety += 1;
                        }

                        if (/[^A-Za-z0-9]/.test(passwordValue)) {
                            variety += 1;
                        }

                        score += variety * 10;

                        if (/[0-9]/.test(passwordValue) && /[^A-Za-z0-9]/.test(passwordValue)) {
                            score += 10;
                        }

                        return Math.min(score, 100);
                    },
                    getCharacterSets() {
                        return {
                            lowercase: 'abcdefghijklmnopqrstuvwxyz',
                            uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                            digits: '0123456789',
                            symbols: this.symbolCharacters
                        };
                    },
                    hasCrypto() {
                        return Boolean(window.crypto && window.crypto.getRandomValues);
                    },
                    cryptoRandomIndex(max) {
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
                        if (!characters.length) {
                            return '';
                        }

                        return characters.charAt(this.cryptoRandomIndex(characters.length));
                    },
                    shuffle(characters) {
                        for (let i = characters.length - 1; i > 0; i -= 1) {
                            const j = this.cryptoRandomIndex(i + 1);
                            [characters[i], characters[j]] = [characters[j], characters[i]];
                        }

                        return characters;
                    },
                    generatePassword() {
                        this.resetError();
                        this.clampLength();

                        if (!this.ensureTypeSelection()) {
                            return;
                        }

                        this.normalizeMinimums();

                        if (!this.hasCrypto()) {
                            this.errorMessage = this.messages.cryptoUnavailable;
                            return;
                        }

                        const sets = this.getCharacterSets();
                        const lettersPool = [];
                        const otherPool = [];

                        if (this.includeLowercase) {
                            lettersPool.push(sets.lowercase);
                        }

                        if (this.includeUppercase) {
                            lettersPool.push(sets.uppercase);
                        }

                        if (this.includeDigits) {
                            otherPool.push(sets.digits);
                        }

                        if (this.includeSymbols) {
                            otherPool.push(sets.symbols);
                        }

                        const result = [];

                        for (let i = 0; i < this.minDigits; i += 1) {
                            result.push(this.randomChar(sets.digits));
                        }

                        for (let i = 0; i < this.minSymbols; i += 1) {
                            result.push(this.randomChar(sets.symbols));
                        }

                        const remaining = this.length - result.length;

                        if (remaining < 0) {
                            this.errorMessage = this.messages.minValuesTooLarge;
                            return;
                        }

                        const letterPoolValue = lettersPool.join('');
                        const fallbackPoolValue = otherPool.join('');
                        const fillPool = letterPoolValue.length ? letterPoolValue : fallbackPoolValue;

                        if (!fillPool.length) {
                            this.errorMessage = this.messages.minTypesRequired;
                            return;
                        }

                        for (let i = 0; i < remaining; i += 1) {
                            result.push(this.randomChar(fillPool));
                        }

                        this.password = this.shuffle(result).join('');
                        this.copied = false;

                        this.$nextTick(() => {
                            this.resizePasswordField();
                        });
                    },
                    async copyPassword() {
                        this.resetError();

                        if (!this.password) {
                            return;
                        }

                        if (!navigator.clipboard || !navigator.clipboard.writeText) {
                            this.errorMessage = this.messages.clipboardUnavailable;
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
                            this.errorMessage = this.messages.clipboardFailed;
                        }
                    }
                }"
            >
                <header class="flex flex-col gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-400">Sichere Passwörter</p>
                    <h1 class="text-2xl font-semibold text-white">Passwortgenerator</h1>
                    <p class="text-sm text-slate-300">
                        Erstellen Sie sichere Passwörter direkt im Browser.
                    </p>
                </header>

                <div class="mt-6 flex flex-col gap-6">
                    <div class="flex flex-col gap-3">
                        <label for="length" class="text-sm font-medium text-slate-200">Passwortlänge</label>
                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_5.5rem]">
                            <input
                                id="length"
                                type="range"
                                min="8"
                                max="128"
                                step="1"
                                class="w-full accent-red-500"
                                x-model.number="length"
                                @input="handleLengthChange()"
                            />
                            <input
                                type="number"
                                min="8"
                                max="128"
                                step="1"
                                class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-sm font-semibold text-white focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                                x-model.number="length"
                                @input="handleLengthChange()"
                                @blur="handleLengthChange()"
                            />
                        </div>
                    </div>

                    <fieldset class="flex flex-col gap-3">
                        <legend class="text-sm font-medium text-slate-200">Zeichentypen</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm text-slate-200">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 accent-red-500"
                                    x-model="includeLowercase"
                                    @change="handleTypeChange('includeLowercase')"
                                />
                                Kleinbuchstaben (a-z)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm text-slate-200">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 accent-red-500"
                                    x-model="includeUppercase"
                                    @change="handleTypeChange('includeUppercase')"
                                />
                                Großbuchstaben (A-Z)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm text-slate-200">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 accent-red-500"
                                    x-model="includeDigits"
                                    @change="handleTypeChange('includeDigits')"
                                />
                                Zahlen (0-9)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm text-slate-200">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 accent-red-500"
                                    x-model="includeSymbols"
                                    @change="handleTypeChange('includeSymbols')"
                                />
                                Sonderzeichen (!@#)
                            </label>
                        </div>
                    </fieldset>

                    <div class="flex flex-col gap-3">
                        <p class="text-sm font-medium text-slate-200">Mindestanzahlen</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm text-slate-200">
                                Mindestanzahl Zahlen
                                <input
                                    type="number"
                                    min="0"
                                    max="128"
                                    step="1"
                                    class="w-20 rounded-lg border border-slate-800 bg-slate-900 px-2 py-1 text-sm font-semibold text-white focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30 disabled:cursor-not-allowed disabled:bg-slate-800 disabled:text-slate-500"
                                    x-model.number="minDigits"
                                    :disabled="!includeDigits"
                                    @input="handleMinDigitsChange()"
                                    @blur="handleMinDigitsChange()"
                                />
                            </label>
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm text-slate-200">
                                Mindestanzahl Sonderzeichen
                                <input
                                    type="number"
                                    min="0"
                                    max="128"
                                    step="1"
                                    class="w-20 rounded-lg border border-slate-800 bg-slate-900 px-2 py-1 text-sm font-semibold text-white focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30 disabled:cursor-not-allowed disabled:bg-slate-800 disabled:text-slate-500"
                                    x-model.number="minSymbols"
                                    :disabled="!includeSymbols"
                                    @input="handleMinSymbolsChange()"
                                    @blur="handleMinSymbolsChange()"
                                />
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-red-900/40 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:ring-offset-2 focus:ring-offset-slate-950"
                            @click="generatePassword()"
                        >
                            Passwort erzeugen
                        </button>
                        <p
                            class="text-sm text-red-400"
                            role="alert"
                            aria-live="polite"
                            x-show="errorMessage"
                            x-text="errorMessage"
                        ></p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <label for="password" class="text-sm font-medium text-slate-200">Passwort</label>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                            <textarea
                                id="password"
                                rows="1"
                                class="min-h-[2.75rem] w-full flex-1 resize-none overflow-hidden rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-sm font-medium text-white placeholder:text-slate-500 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                                placeholder="Ihr Passwort erscheint hier"
                                x-model="password"
                                x-ref="passwordField"
                                :maxlength="length"
                                @input="handlePasswordChange()"
                            ></textarea>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="password.length === 0"
                                @click="copyPassword()"
                            >
                                Kopieren
                            </button>
                        </div>
                        <p class="text-xs font-semibold text-red-300" x-show="copied" x-transition>
                            Kopiert!
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-200">Passwortstärke</span>
                            <span class="text-sm font-semibold" :class="strengthTextClass" x-text="`${strengthLabel} (${score})`"></span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-800">
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
