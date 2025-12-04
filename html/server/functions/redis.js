const redis = require("redis");

const redisHost = process.env.REDIS_HOST || 'redis';
const RedisClient = redis.createClient({
    host: redisHost,
    port: process.env.REDIS_PORT || 6379,
});

RedisClient.on('error', error => {
    console.error('Redis error:', error);
});

module.exports = RedisClient;