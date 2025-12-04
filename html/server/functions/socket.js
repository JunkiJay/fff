const app = require('express')();
const config = require('../config');

const server = require('http').createServer(app);
const io = require('socket.io')(server, {
    // Включаем поддержку старых клиентов (EIO=3). Уберите после миграции клиентов.
    allowEIO3: true,
    // На время диагностики можно принудить только websocket:
    // transports: ['websocket'],
});
const port = 8443;
const host = '0.0.0.0';

const Socket = {};
Socket.emit = (channel, data) => io.sockets.emit(channel, data);

// userId -> Set<socketId>
const userSockets = new Map();
// socketId -> userId
const socketToUser = new Map();

function bindAuth(socket, userId) {
    // Если сокет уже был привязан к другому пользователю — отвяжем
    const prevUser = socketToUser.get(socket.id);
    if (prevUser && prevUser !== userId) {
        const prevSet = userSockets.get(prevUser);
        if (prevSet) {
            prevSet.delete(socket.id);
            if (prevSet.size === 0) userSockets.delete(prevUser);
        }
    }

    socketToUser.set(socket.id, userId);

    let set = userSockets.get(userId);
    if (!set) {
        set = new Set();
        userSockets.set(userId, set);
    }
    set.add(socket.id);
}

function unbindAuth(socket) {
    const userId = socketToUser.get(socket.id);
    if (!userId) return;

    const set = userSockets.get(userId);
    if (set) {
        set.delete(socket.id);
        if (set.size === 0) userSockets.delete(userId);
    }
    socketToUser.delete(socket.id);
}

function getUniqueAuthorizedCount() {
    return userSockets.size;
}

io.on('connection', (socket) => {
    console.log('Новое подключение', socket.id);

    socket.on('authenticate', (userId) => {
        if (userId === undefined || userId === null || userId === '') {
            socket.emit('auth_error', { message: 'userId отсутствует' });
            return;
        }
        const uid = String(userId);

        console.log('Аутентификация пользователя', uid, 'socket:', socket.id);
        bindAuth(socket, uid);

        // Персональная комната пользователя (опционально)
        socket.join(`user_${uid}`);

        // Ответ текущему сокету
        socket.emit('authenticated', {
            room: `user_${uid}`,
            uniqueAuthorizedCount: getUniqueAuthorizedCount(),
        });

        // Моментально оповестим всех о новом количестве авторизованных
        io.sockets.emit('stats:uniqueAuthorizedUsers', {
            count: getUniqueAuthorizedCount(),
            ts: Date.now(),
        });
    });

    // Запрос текущих метрик (по требованию клиента)
    socket.on('stats:get', () => {
        socket.emit('stats:uniqueAuthorizedUsers', {
            count: getUniqueAuthorizedCount(),
            ts: Date.now(),
        });
    });

    // Опционально: явный выход пользователя
    socket.on('logout', () => {
        unbindAuth(socket);
        io.sockets.emit('stats:uniqueAuthorizedUsers', {
            count: getUniqueAuthorizedCount(),
            ts: Date.now(),
        });
    });

    socket.on('disconnect', (reason) => {
        unbindAuth(socket);
        console.log(`Сокет ${socket.id} отключился по причине: ${reason}`);
        io.sockets.emit('stats:uniqueAuthorizedUsers', {
            count: getUniqueAuthorizedCount(),
            ts: Date.now(),
        });
    });
});

// Периодическая рассылка каждые 5 секунд (на случай дрейфа клиентов)
const INTERVAL_MS = 5000;
setInterval(() => {
    io.sockets.emit('stats:uniqueAuthorizedUsers', {
        count: getUniqueAuthorizedCount(),
        ts: Date.now(),
    });
}, INTERVAL_MS);

server.listen(port, host, () => {
    console.clear();
    console.log(`Server started on port = ${port}`);
});

module.exports = { Socket, io };