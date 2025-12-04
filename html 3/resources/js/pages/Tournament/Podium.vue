<template>
    <div class="podium">
        <div class="col col--2">
            <div class="badge badge--2">2 Место</div>
            <div class="card">
                <div class="photo"><img :src="places[1].avatar" /></div>
                <div class="name">{{ places[1].name }}</div>
                <div class="stats">
                    <div class="stat"><span>Оборот</span><b>{{ places[1].turnover }} ₽</b></div>
                    <div class="stat"><span>Награда</span><b class="green">{{ places[1].reward }} ₽</b></div>
                </div>
            </div>
        </div>

        <div class="col col--1 raised"> <!-- ПОДНИМАЕМ КОЛОНКУ, не .card -->
            <div class="badge badge--1">1 Место</div>
            <div class="card">
                <div class="photo"><img :src="places[0].avatar" /></div>
                <div class="name">{{ places[0].name }}</div>
                <div class="stats">
                    <div class="stat"><span>Оборот</span><b>{{ places[0].turnover }} ₽</b></div>
                    <div class="stat"><span>Награда</span><b class="green">{{ places[0].reward }} ₽</b></div>
                </div>
            </div>
        </div>

        <div class="col col--3">
            <div class="badge badge--3">3 Место</div>
            <div class="card">
                <div class="photo"><img :src="places[2].avatar" /></div>
                <div class="name">{{ places[2].name }}</div>
                <div class="stats">
                    <div class="stat"><span>Оборот</span><b>{{ places[2].turnover }} ₽</b></div>
                    <div class="stat"><span>Награда</span><b class="green">{{ places[2].reward }} ₽</b></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Podium',
    props: {
        places: { type: Array, required: true } // [ {avatar,name,turnover,reward}, ... ]
    }
}
</script>

<style scoped>
.podium{
    display:flex;
    justify-content:center;
    align-items:flex-end;      /* общая НИЖНЯЯ линия */
    gap:32px;
}

/* колонка */
.col{
    position:relative;
    width:360px;               /* подгони под макет */
    display:flex;              /* важно: чтобы transform поднимал ВСЮ колонку */
    justify-content:flex-end;  /* чтобы карточка стояла на «полу» */
}
.raised{ transform:translateY(-28px); } /* подняли центр */

/* плашка места */
.badge{
    position:absolute; top:-12px; left:16px;
    padding:8px 14px; border-radius:10px 10px 10px 0;
    font-weight:700; font-size:14px; color:#fff;
    box-shadow:0 6px 18px rgba(0,0,0,.35);
}
.badge--1{ background:#ff8b1f; }
.badge--2{ background:#9aa0a6; }
.badge--3{ background:#b87333; }

/* карточка фиксированной высоты — ключ к одинаковому низу */
.card{
    background:#15131a;
    border-radius:20px;
    box-shadow:0 8px 30px rgba(0,0,0,.35);
    padding:22px;
    color:#fff;
    width:100%;
    height:420px;              /* одинаковая высота для всех */
    display:flex;
    flex-direction:column;     /* чтобы нижний блок всегда внизу */
}

/* фото одинаковой высоты */
.photo{
    background:#1e1b24;
    border-radius:14px;
    padding:12px;
}
.photo img{
    width:100%;
    height:210px;              /* фикс — не «прыгает» */
    object-fit:cover;
    border-radius:10px;
}

.name{
    font-size:18px; font-weight:600;
    margin:12px 0 10px;
}

/* блок со статистикой «прибит» к низу карточки */
.stats{
    margin-top:auto;           /* <<< прибивает вниз */
    display:grid; grid-template-columns:1fr 1fr; gap:14px;
}
.stat{
    background:#1e1b24;
    border-radius:12px;
    padding:14px 12px;
}
.stat span{ font-size:13px; color:#9aa0a6; display:block; margin-bottom:6px; }
.stat b{ font-size:16px; font-weight:700; }
.green{ color:#29d46a; }
</style>