const TelegramBot = require('node-telegram-bot-api');
const mysql       = require('mysql');
const config      = require('./config')
const { Socket }  = require('./functions/socket')

const bot = new TelegramBot(config.token, {
    polling: {
        interval: 300,
        params: {
            timeout: 10
        }
    }
})
const client = mysql.createPool({
    connectionLimit: 50,
    ...config.database
});

bot.on('message', async msg => {
    let chat_id = msg.chat.id
    let text = msg.text 

    if(text && text.toLowerCase().startsWith('/bind')) {
        try {
            let id = text.split("/bind ")[1] ? text.split("/bind ")[1] : 'undefined';
            id = id.replace(/[^A-Za-z0-9\s]/gi, "");
    
            let user = await db(`SELECT * FROM users WHERE unique_id = '${id}'`);
            let check = await db(`SELECT * FROM users WHERE tg_id = ${chat_id}`);
    
            if(user.length < 1) {
                return bot.sendMessage(chat_id, 'Неверный id');
            }
            
            if(check.length >= 1 || user[0].tg_id != 0) {
                return bot.sendMessage(chat_id, 'Ошибка! Данный Telegram аккаунт уже привязан к другому аккаунту на сайте!');
            }
    
            await db(`UPDATE users SET tg_id = ${chat_id} WHERE unique_id = '${id}'`);
    
            Socket.emit('connectTelegram', {
                user_id: user[0].id
            })
    
            return bot.sendMessage(chat_id, `👍 Вы успешно привязали свой аккаунт`);
        } catch(e) {
            return bot.sendMessage(chat_id, `Что-то пошло не так. Попробуйте ещё раз`);
        }
    }

    return bot.sendMessage(chat_id, `
👍 Привет, это официальный Telegram Bot сайта Stimule.

📌 Наши официальные соцсети:
1️⃣ Актуальный домен stimule2.site
2️⃣ VK: vk.com/club224975600
3️⃣ TG: t.me/stimule_tg
    `, {
        parse_mode: "HTML",
        disable_web_page_preview: true
    })
});

function db(databaseQuery) {
    return new Promise(data => {
        client.query(databaseQuery, function (error, result) {
            if (error) {
                console.log(error);
                throw error;
            }
            try {
                data(result);

            } catch (error) {
                data({});
                throw error;
            }

        });

    });
    client.end()
}
