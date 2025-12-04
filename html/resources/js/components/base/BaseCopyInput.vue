<template>
    <div class="relative">
        <label class="block text-[var(--color-text)] mb-1">
            {{ label }}
        </label>

        <div class="flex items-center rounded-md shadow-sm">
            <input 
                ref="inputRef" 
                type="text" 
                :placeholder="placeholder" 
                :value="internalValue"
                readonly
                class="flex-1 font-normal min-w-0 block w-full px-3 py-2 text-[var(--color-text)] rounded-none bg-[#2E2A3640] rounded-l-md border border-[#CED4DA40] outline-none cursor-default select-all" 
            />

            <button 
                @click="copyToClipboard"
                class="inline-flex items-center px-3 py-[11px] border-l border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-text)] hover:bg-opacity-10 focus:outline-none rounded-r-md transition-colors"
                :class="{ 'bg-[var(--color-primary)] text-[var(--color-text)]': copySuccess }"
            >
                {{ copySuccess ? copiedText : copyText }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BaseCopyInput',
    props: {
        value: {
            type: [Number, String],
            required: true
        },
        label: {
            type: String,
            default: 'Сумма перевода, ₽'
        },
        copyText: {
            type: String,
            default: 'Скопировать'
        },
        copiedText: {
            type: String,
            default: 'Скопировано!'
        },
        placeholder: {
            type: String,
            default: 'Номер телефона'
        },
    },
    data() {
        return {
            copySuccess: false,
            internalValue: this.value?.toString() || ''
        }
    },
    watch: {
        value(newVal) {
            this.internalValue = newVal?.toString() || '';
        }
    },
    methods: {
        copyToClipboard() {
            const input = this.$refs.inputRef;
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices

            try {
                document.execCommand('copy');
                this.copySuccess = true;
                setTimeout(() => {
                    this.copySuccess = false;
                }, 2000);
            } catch (err) {
                console.error('Failed to copy text: ', err);
            }

            window.getSelection().removeAllRanges();
        },
        handleInput(event) {
            this.internalValue = event.target.value;
        },
        updateModelValue() {
            this.$emit('input', this.internalValue);
        },
    }
}
</script>