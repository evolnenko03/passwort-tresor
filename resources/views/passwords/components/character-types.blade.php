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
