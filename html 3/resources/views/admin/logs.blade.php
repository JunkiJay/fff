<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Логи (реальное время)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; margin: 16px; }
        #log { white-space: pre-wrap; background:#111; color:#ddd; padding:12px; line-height:1.4; height:80vh; overflow:auto; border-radius:6px; }
        .line.error { color:#ff6b6b; }
        .line.warning { color:#ffd166; }
        .line.info { color:#6bc1ff; }
        .toolbar { margin: 10px 0; display:flex; gap:10px; align-items:center; flex-wrap: wrap; }
        .toolbar input[type="text"] { min-width: 220px; }
        .hint { font-size: 12px; color:#aaa; }
        .tags { color:#9ad; font-size: 12px; margin-left: 8px; opacity:.8; }
        .line[hidden] { display: none; }
    </style>
</head>
<body>
<div class="toolbar">
    <button id="clear">Очистить</button>
    <label><input type="checkbox" id="autoscroll" checked> Автопрокрутка</label>
    <input type="text" id="filter" placeholder="Фильтр по тексту (RegExp или текст)" style="flex:1;">
    <input type="text" id="tagsFilter" placeholder="Теги: db,queue,!debug (ч/з запятую)">
    <span class="hint">Источники тегов: #tag, [tag:x], JSON tags/tag, канал, уровень (level:error)</span>
</div>
<pre id="log"></pre>

<script>
    (function () {
        const logEl = document.getElementById('log');
        const filterEl = document.getElementById('filter');
        const tagsFilterEl = document.getElementById('tagsFilter');
        const autoscrollEl = document.getElementById('autoscroll');
        document.getElementById('clear').onclick = () => { logEl.textContent = ''; lines.length = 0; };

        // Хранилище всех строк для пере-фильтрации
        const lines = [];

        // Текстовый фильтр (RegExp)
        let re = null;
        filterEl.addEventListener('input', () => {
            const v = filterEl.value.trim();
            try { re = v ? new RegExp(v, 'i') : null; filterEl.style.borderColor = ''; }
            catch (_) { re = null; filterEl.style.borderColor = 'crimson'; }
            applyFilters();
        });

        // Фильтр по тегам (включающие/исключающие)
        let includeTags = new Set();
        let excludeTags = new Set();
        tagsFilterEl.addEventListener('input', () => {
            parseTagsFilter(tagsFilterEl.value);
            applyFilters();
        });
        function parseTagsFilter(raw) {
            includeTags.clear();
            excludeTags.clear();
            raw.split(/[,\s]+/).forEach(t => {
                t = t.trim();
                if (!t) return;
                // убрать возможный префикс # и пробелы
                let neg = false;
                if (t[0] === '!' || t[0] === '-') { neg = true; t = t.slice(1); }
                if (t[0] === '#') t = t.slice(1);
                t = t.toLowerCase();
                if (!t) return;
                (neg ? excludeTags : includeTags).add(t);
            });
        }

        // Извлечение тегов из строки лога
        function extractTags(line) {
            const set = new Set();

            // Канал и уровень: "[..] channel.LEVEL: "
            const m = line.match(/\]\s+([a-z0-9_.-]+)\.([A-Z]+):/i);
            if (m) {
                set.add(m[1].toLowerCase()); // канал, например "local"
                set.add('level:' + m[2].toLowerCase()); // уровень, например "level:info"
            }

            // Хэштеги: #tag
            const hashRe = /(^|\s)#([a-z0-9_-]{1,50})/ig;
            let hm;
            while ((hm = hashRe.exec(line))) {
                set.add(hm[2].toLowerCase());
            }

            // [tag:foo,bar]
            const tagBracketRe = /\[tag:([^\]]+)\]/ig;
            let tm;
            while ((tm = tagBracketRe.exec(line))) {
                tm[1].split(/[,\s]+/).forEach(t => {
                    t = t.trim().toLowerCase();
                    if (t) set.add(t);
                });
            }

            // JSON блоки в конце строки Monolog: {...} [...]
            const jsonRe = /\{[^{}]*\}/g;
            let jm;
            while ((jm = jsonRe.exec(line))) {
                try {
                    const obj = JSON.parse(jm[0]);
                    if (obj) {
                        if (Array.isArray(obj.tags)) {
                            obj.tags.forEach(t => {
                                if (typeof t === 'string') set.add(t.toLowerCase());
                            });
                        }
                        if (typeof obj.tag === 'string') set.add(obj.tag.toLowerCase());
                    }
                } catch (_) { /* игнор */ }
            }

            return set;
        }

        function cls(line) {
            if (/ERROR|CRITICAL|ALERT|EMERGENCY/i.test(line)) return 'error';
            if (/WARNING|WARN/i.test(line)) return 'warning';
            return 'info';
        }

        function passesFilters(text, tagsSet) {
            // Текстовый фильтр
            if (re && !re.test(text)) return false;

            // Исключающие теги
            for (const t of excludeTags) {
                if (tagsSet.has(t)) return false;
            }

            // Включающие теги: требуется пересечение
            if (includeTags.size > 0) {
                for (const t of includeTags) {
                    if (tagsSet.has(t)) return true;
                }
                return false;
            }

            return true;
        }

        function renderLine(entry) {
            const { text, tags } = entry;
            const div = document.createElement('div');
            div.className = 'line ' + cls(text);
            // Показываем сам текст
            div.textContent = text;

            // Добавим визуализацию найденных тегов (как подсказку)
            if (tags.size) {
                const t = document.createElement('span');
                t.className = 'tags';
                t.textContent = ' [' + Array.from(tags).join(', ') + ']';
                div.appendChild(t);
            }

            entry.el = div;
            logEl.appendChild(div);
        }

        function append(text) {
            const tags = extractTags(text);
            const entry = { text, tags, el: null };
            lines.push(entry);

            renderLine(entry);
            applyFilters();

            if (autoscrollEl.checked) logEl.scrollTop = logEl.scrollHeight;
        }

        function applyFilters() {
            lines.forEach(entry => {
                const { el, text, tags } = entry;
                const visible = passesFilters(text, tags);
                el.hidden = !visible;
            });
        }

        let es;
        function connect() {
            es = new EventSource("{{ route('logs.stream') }}", { withCredentials: true });
            es.addEventListener('log', (e) => {
                try { const data = JSON.parse(e.data); append(data.line); } catch (_) {}
            });
            es.onerror = () => {
                es.close();
                setTimeout(connect, 1500);
            };
        }
        connect();
    })();
</script>
</body>
</html>