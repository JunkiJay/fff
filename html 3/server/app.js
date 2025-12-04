const { Socket, io } = require("./functions/socket");
const RedisClient = require("./functions/redis");
const config = require("./config");
const axios = require("axios");

var slotsHistory = [];

let games = [],
    timerBot = null;
interval = null;

RedisClient.subscribe("newGame");
RedisClient.subscribe("setNewBotTimer");
RedisClient.subscribe("slotsHistory");
RedisClient.subscribe("depositConfirmation");
RedisClient.subscribe("withdrawConfirmation");

RedisClient.on("connect", () => {
    console.log("Connected to Redis");
});

RedisClient.on("error", (err) => {
    console.error("Redis error:", err);
});

RedisClient.on("message", async (channel, message) => {
    console.log(`Message on channel ${channel}:`, message);
    if (channel === "setNewBotTimer") {
        clearInterval(interval);
        interval = null;
        timerBot = message;

        startBot();
    }
    if (channel === "newGame") {
        if (games.length >= 14) {
            games.pop();
        }
        console.log("Emitting newGame", JSON.parse(message));
        games.unshift(JSON.parse(message));
        Socket.emit(channel, JSON.parse(message));
    }

    if (channel === "slotsHistory") {
        let data = JSON.parse(message);
        slotsHistory.unshift(data);
        if (slotsHistory.length > 7) slotsHistory.pop();
        io.sockets.emit("slotsHistory", data);
        return;
    }

    if (channel === "depositConfirmation") {
        let data = JSON.parse(message);

        io.sockets.to(`user_${data.data.user_id}`).emit("depositConfirmation", data);
    }

    if (channel === "withdrawConfirmation") {
        let data = JSON.parse(message);

        io.sockets.to(`user_${data.data.user_id}`).emit("withdrawConfirmation", data);
    }
});

io.on("connection", (socket) => {
    socket.on("getHistory", () => {
        socket.emit("getHistory", slotsHistory);
        console.log(slotsHistory);
    });

    socket.emit("history", games);
});

const startBot = () => {
    interval = setInterval(() => {
        axios
            .post(`${config.domain}/api/fake`)
            .then((res) => {})
            .catch((err) => {});
    }, timerBot);
};

const getTimer = () => {
    axios
        .get(`${config.domain}/api/getTimer`)
        .then((res) => {
            timerBot = res.data;

            startBot();
        })
        .catch((err) => {});
};

getTimer();
