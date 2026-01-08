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
