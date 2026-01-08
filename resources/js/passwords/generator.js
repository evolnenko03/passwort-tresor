const SYMBOLS_ALLOWED = '!@#$%^&*()-_=+[]{};:,.?/';
const STORAGE_KEY = 'password-generator';
const CHARSETS = {
    lowercase: 'abcdefghijklmnopqrstuvwxyz',
    uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    digits: '0123456789',
    symbols: SYMBOLS_ALLOWED
};

export function passwordGenerator() {
    return {
        storageTimeout: null,
        copyTimeout: null,
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
        init() {
            const loaded = this.loadFromStorage();

            this.clampLength();
            this.ensureTypeSelection();
            this.normalizeMinimums();

            if (!loaded || !this.password) {
                this.generatePassword();
            } else {
                this.sanitizePassword();
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
                this.$watch(key, () => this.scheduleStorageSave());
            });
        },
        isStorageAvailable() {
            try {
                const testKey = `${STORAGE_KEY}-test`;
                localStorage.setItem(testKey, '1');
                localStorage.removeItem(testKey);
                return true;
            } catch (error) {
                return false;
            }
        },
        loadFromStorage() {
            if (!this.isStorageAvailable()) {
                return false;
            }

            const raw = localStorage.getItem(STORAGE_KEY);

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
        saveToStorage() {
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
                localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            } catch (error) {
                return;
            }
        },
        scheduleStorageSave() {
            if (!this.isStorageAvailable()) {
                return;
            }

            if (this.storageTimeout) {
                clearTimeout(this.storageTimeout);
            }

            this.storageTimeout = setTimeout(() => {
                this.saveToStorage();
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
        sanitizePassword() {
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

                if (this.includeSymbols && SYMBOLS_ALLOWED.includes(character)) {
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
        resizePasswordField() {
            const field = this.$refs.passwordField;

            if (!field) {
                return;
            }

            field.style.height = 'auto';
            field.style.height = `${field.scrollHeight}px`;
        },
        resetError() {
            this.errorMessage = '';
        },
        handleLengthChange() {
            this.resetError();
            this.clampLength();
            this.normalizeMinimums();
            this.sanitizePassword();
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
            this.sanitizePassword();
        },
        handlePasswordChange() {
            this.copied = false;
            this.sanitizePassword();
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
        shuffleArray(characters) {
            for (let i = characters.length - 1; i > 0; i -= 1) {
                const j = this.cryptoRandomIndex(i + 1);
                [characters[i], characters[j]] = [characters[j], characters[i]];
            }

            return characters;
        },
        fillMinimums(result) {
            for (let i = 0; i < this.minDigits; i += 1) {
                result.push(this.randomChar(CHARSETS.digits));
            }

            for (let i = 0; i < this.minSymbols; i += 1) {
                result.push(this.randomChar(CHARSETS.symbols));
            }
        },
        fillRemainingCharacters(result, pool, count) {
            for (let i = 0; i < count; i += 1) {
                result.push(this.randomChar(pool));
            }
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

            const lettersPool = [];
            const otherPool = [];

            if (this.includeLowercase) {
                lettersPool.push(CHARSETS.lowercase);
            }

            if (this.includeUppercase) {
                lettersPool.push(CHARSETS.uppercase);
            }

            if (this.includeDigits) {
                otherPool.push(CHARSETS.digits);
            }

            if (this.includeSymbols) {
                otherPool.push(CHARSETS.symbols);
            }

            const result = [];

            this.fillMinimums(result);

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

            this.fillRemainingCharacters(result, fillPool, remaining);

            this.password = this.shuffleArray(result).join('');
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
    };
}
