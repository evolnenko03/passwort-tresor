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
