<template>
    <component
        :is="isRouterLink ? 'router-link' : 'button'"
        :to="isRouterLink ? to : undefined"
        :class="['button', variantClass]"
        :disabled="isDisabled"
        @keydown.enter.exact.prevent
    >
        <slot></slot>
    </component>
</template>

<script>
export default {
    props: {
        variant: {
            type: String,
            default: "primary",
        },
        to: {
            type: String,
            default: null,
        },
        isDisabled: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        variantClass() {
            return this.variant ? `button_${this.variant}` : "button_primary";
        },
        isRouterLink() {
            return !!this.to;
        },
    },
};
</script>

<style lang="scss" scoped>
@property --myColor1 {
    syntax: "<color>";
    initial-value: rgba(141, 36, 213, 1);
    inherits: false;
}

@property --myColor2 {
    syntax: "<color>";
    initial-value: rgba(73, 19, 111, 1);
    inherits: false;
}

.button {
    display: flex;
    align-items: center;
    justify-content: center;
    $c: &;
    border-radius: 8px;
    transition-duration: 0.05s;
    transition: all 0.2s;

   /* @media (max-width: 1024px) {
        width: 144px;
        height: 34px;
        font-size: 14px;
    }*/

    &_default {
        background-color: none;
        border: none;
    }

    &_primary {
        color: #ffffff;
        background: var(--color-primary);
        font-weight: 500;
        height: 44px;
        padding: 0 20px;
        font-size: 16px;
        &:active {
            color: white !important;
        }
        &:disabled,
        &[disabled] {
            background: none;
            color: white;
            cursor: not-allowed;
            opacity: .7;
            pointer-events: none;
        }
    }
    &_test {
        color: #fff;
        background: var(--color-primary);
        font-weight: 500;
        height: 54px;
        padding: 0 20px;
        font-size: 16px;
        &:active {
            color: white !important;
        }
    }
    &_disabled {
        color: #6D6E77;
        background-color: #1F1B29;
        font-weight: 500;
        height: 54px;
        padding: 0 10px;
        font-size: 16px;
        &:active {
            color: white !important;
        }
    }
    &_add {
        background: var(--color-primary);
        @media (hover: hover) {
            &:hover {
                background: var(--color-primary) !important;
            }
        }
    }
    &:not(.router-link-exact-active, .link_primary) {
        @media (hover: hover) {
            &:hover {
                background: var(--color-primary-hover);
                color: var(--color-text);
            }
        }
    }

    &_gradient {
        background: linear-gradient(45deg, var(--myColor1), var(--myColor2));
        transition: --myColor1 0.2s, --myColor2 0.2s;
        @media (hover: hover) {
            &:hover {
                --myColor1: var(--color-primary-hover);
                --myColor2: var(--color-primary-hover);
                background: linear-gradient(45deg, var(--myColor1), var(--myColor2)) !important;
            }
        }
    }

    &_secondary {
        color: var(--color-text);
        background: var(--color-secondary);
        font-weight: 500;
        height: 44px;
        padding: 0 20px;
        font-size: 16px;
    }

    @media (hover: hover) {
        &:hover {
            color: white !important;
            border-color: var(--color-primary-hover);
        }
    }
    &:active {
        background: var(--color-primary-active) !important;
        border-color: var(--color-primary-active);
        color: white !important;
    }
    &_provider {
        @media (hover: hover) {
            &:hover {
                background: rgb(41, 39, 51) !important;
            }
        }
        &:active {
            background: rgba(41, 39, 51, 0.8) !important;
        }
    }
    &_slot {
        &:hover {
            background: #ffffff20 !important;
        }
        &:active {
            background: #ffffff40 !important;
        }
    }
}
</style>
