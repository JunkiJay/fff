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
                @input="handleInput"
                @blur="updateModelValue" 
                @keyup.enter="updateModelValue"
                class="flex-1 font-normal min-w-0 block w-full px-3 py-2 text-[var(--color-text)] bg-[#2E2A3640] rounded-md border border-[#CED4DA40] outline-none" 
            />
        </div>  
    </div>
</template>

<script>
export default {
    name: 'BaseUserInput',
    props: {
        value: {
            type: [Number, String],
            required: true
        },
        label: {
            type: String,
            default: 'Сумма перевода, ₽'
        },
        placeholder: {
            type: String,
            default: 'Номер телефона'
        }
    },
    data() {
        return {
            internalValue: this.value?.toString() || ''
        }
    },
    watch: {
        value(newVal) {
            this.internalValue = newVal?.toString() || '';
        }
    },
    methods: {
        handleInput(event) {
            this.internalValue = event.target.value;
        },
        updateModelValue() {
            this.$emit('input', this.internalValue);
        }
    },
    mounted() {
        // Initialize with proper value
        this.internalValue = this.value?.toString() || '';
    }
}
</script>