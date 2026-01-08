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
