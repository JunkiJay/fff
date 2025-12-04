FROM node:16-alpine

WORKDIR /app

# Копирование файлов описания зависимостей и установка модулей
COPY package*.json ./
RUN npm install

# Открываем нужный порт
EXPOSE 8443

# Запуск приложения; убедитесь, что запуск идёт в нужном файле. 
CMD ["node", "server/app.js"]