<template>
    <input
      type="string"
      :value="value"
      @input="updateValue($event.target.value)"
      @change="emitTypeBet"
      :disabled="isDisabled"
    />
  </template>
  
  <script>
  export default {
    name: 'MainInput',
    props: {
      value: {
        type: String,
        required: true,
      },
      isDisabled: {
        type: Boolean,
        default: false,
      }
      
    },
    methods: {
      updateValue(value) {
        this.$emit('input', Number(value));
      },
      emitTypeBet() {
        this.$emit('typeBet', 'default');
      }
    }
  };
  </script>
  