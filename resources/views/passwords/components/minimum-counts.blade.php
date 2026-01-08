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
