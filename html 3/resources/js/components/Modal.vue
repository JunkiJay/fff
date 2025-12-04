<template>
    <portal-target v-if="isOpen" to="modal">
        <div
            @click="close"
            :class="[
                {
                    '[animation:fadeIn]': open && isProcessing,
                    '[animation:fadeOut]': !open && isProcessing,
                },
                'cursor-pointer w-full h-full inset-0 fixed z-50 flex items-center justify-center bg-[#000000]/[75%] backdrop-blur-md',
            ]"
            :style="{
                animationDuration: `${speed}ms`,
            }"
        >
            <div class="cursor-default" @click.stop>
                <slot></slot>
            </div>
        </div>
    </portal-target>
</template>

<script>
export default {
    props: {
        open: {
            type: Boolean,
            default: true,
        },
        speed: {
            type: Number,
            default: 300,
        },
    },

    emits: ["close"],

    data() {
        return {
            timeoutId: null,
            isOpen: false,
            isProcessing: false,
        };
    },

    watch: {
        open() {
            if (this.timeoutId !== null) clearTimeout(this.timeoutId);

            document.body.style.overflow = this.open ? "hidden" : "auto";

            this.isProcessing = true;

            if (this.open) {
                this.isOpen = true;
                this.timeoutId = setTimeout(() => {
                    this.isProcessing = false;
                }, this.speed);
            }

            if (!this.open) {
                this.timeoutId = setTimeout(() => {
                    this.isOpen = false;
                    this.isProcessing = false;
                }, this.speed);
            }
        },
    },

    methods: {
        close() {
            this.$emit("close");
        },
    },
};
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>
