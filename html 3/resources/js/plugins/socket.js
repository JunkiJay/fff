import io from 'socket.io-client';

const socket = io('wss://stimule1.win', {
    path: '/socket.io',
    transports: ['polling', 'websocket'],
    timeout: 60000
});

socket.on('connect', () => {
    console.log('Connected to WebSocket server');
});

socket.on('disconnect', () => {
    console.log('Disconnected from WebSocket server');
});

export default socket;
