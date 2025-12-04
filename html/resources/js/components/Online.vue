<template>
    <div class="left-menu__online online">
        <div class="online__circle">
            <div>
                <div></div>
            </div>
        </div>
        <span class="online__value">{{ onlinePlayers }}</span>
    </div>
</template>

<script>
import axios from "axios";
import socket from "../plugins/socket";

export default {
    data() {
        return {
            onlinePlayers: 1,
        };
    },
    methods: {
        fetchOnlinePlayers() {
            socket.on("stats:uniqueAuthorizedUsers", (data) => {
                    this.onlinePlayers = data.count;
                }
            );
        },
    },
    mounted() {
        this.fetchOnlinePlayers();
    },
};

</script>

<style lang="scss" scoped>
.online {
    display: flex;
    border-radius: 8px;
    height: 32px;
    align-items: center;
    padding: 0 8px;
    background-color: #47d52425;
    justify-content: space-between;
    align-items: center;

    &__circle {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: #00ff0010;
        display: flex;
        align-items: center;
        justify-content: center;

        >div {
            width: 8px;
            height: 8px;
            background-color: #00ff00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;

            >div {
                width: 4px;
                height: 4px;
                background-color: white;
                border-radius: 50%;
            }
        }
    }

    &__value {
        margin-left: 5px;
        font-size: 14px;
        font-weight: 600;
        font-size: 16px;
        color: #ffffff;
    }
}
</style>
