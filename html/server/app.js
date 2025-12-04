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
        
        console.log('depositConfirmation raw data:', JSON.stringify(data, null, 2));
        
        // Laravel Broadcasting может отправлять в разных форматах:
        // 1. {event: "deposit-confirmation", data: {payment_id, user_id, amount}}
        // 2. {payment_id, user_id, amount} (прямо)
        // 3. {data: {payment_id, user_id, amount}}
        
        let userId = null;
        let paymentId = null;
        let amount = null;
        
        // Пробуем разные варианты извлечения данных
        if (data.data && data.data.user_id) {
            // Формат: {event: "...", data: {...}}
            userId = data.data.user_id;
            paymentId = data.data.payment_id;
            amount = data.data.amount;
        } else if (data.user_id) {
            // Формат: {user_id, payment_id, amount}
            userId = data.user_id;
            paymentId = data.payment_id;
            amount = data.amount;
        }
        
        if (userId) {
            console.log(`Sending depositConfirmation to user_${userId}`, {
                payment_id: paymentId,
                user_id: userId,
                amount: amount
            });
            
            io.sockets.to(`user_${userId}`).emit("depositConfirmation", {
                data: {
                    payment_id: paymentId,
                    user_id: userId,
                    amount: amount
                }
            });
        } else {
            console.error('depositConfirmation: user_id not found in data:', JSON.stringify(data, null, 2));
        }
    }

    if (channel === "withdrawConfirmation") {
        let data = JSON.parse(message);
        
        // Laravel Broadcasting публикует в формате: {event: "...", data: {...}}
        const userId = data.data?.user_id || data.user_id;
        
        if (userId) {
            console.log(`Sending withdrawConfirmation to user_${userId}`, data);
            io.sockets.to(`user_${userId}`).emit("withdrawConfirmation", {
                data: {
                    withdraw_id: data.data?.withdraw_id || data.withdraw_id,
                    user_id: userId,
                    amount: data.data?.amount || data.amount
                }
            });
        } else {
            console.error('withdrawConfirmation: user_id not found', data);
        }
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
