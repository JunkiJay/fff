<template>
    <section class="overflow-hidden lg:bg-no-repeat lg:pl-8 lg:mt-8 border border-solid bg-neutral-800 bg-opacity-30 border-white border-opacity-30 rounded-[32px] md:pl-5 max-md:max-w-full"
    :style="{
      backgroundImage: `url('images/ref_gradient.png')`,
      backgroundSize: '421px 421px'
    }">
      <div class="flex gap-5 max-md:flex-col bg-no-repeat bg-right"
      :style="{ 
        backgroundImage: `url('images/ref_money.png')`,
        backgroundSize: '700px 500px'
        }"
    >
        <div class="flex flex-col w-[55%] max-sm:p-4 max-md:w-full"
        style="">
          <div class="flex flex-col self-stretch my-auto mr-0 text-white max-md:mt-8 max-md:max-w-full md:pt-8 pb-4">
            <div class="flex flex-col max-md:max-w-full">
              <h1 class="text-4xl font-bold max-md:max-w-full">
                ПРИГЛАШАЙ И ЗАРАБАТЫВАЙ 50% ОТ СТАВОК
              </h1>
              <p class="mt-3.5 text-xl opacity-60 font-medium tracking-normal max-md:max-w-full">
                Пригласив пару друзей вы сможете выйти на стабильный пассивный доход
              </p>
            </div>
            <div class="flex flex-col mt-28 max-w-full text-base w-[527px] max-md:mt-10">
              <label for="referralLink" class="font-medium max-md:max-w-full">
                Ваша реферальная ссылка
              </label>
              <div class="flex flex-wrap items-center mt-2 w-full whitespace-nowrap max-md:max-w-full">
                <input
                  id="referralLink"
                  type="text"
                  readonly
                  value="https://example.dot/ref=?"
                  class="w-2/3 font-[inter] self-stretch px-3 py-4 my-auto rounded-l-2xl border bg-zinc-800 bg-opacity-30 border-gray-300 border-opacity-30 min-h-[52px]"
                  aria-label="Your referral link"
                />
                <button class="w-1/3 py-4 max-sm:pl-[10px] max-sm:text-[16px] my-auto font-[inter] border border-purple-700 bg-purple-700 rounded-r-2xl min-h-[52px] max-md:px-5"
                >
                  Скопировать
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="flex flex-col ml-5 lg:w-[45%] max-md:ml-0 max-md:w-full">
          <img
            loading="lazy"
            src="/images/ref_girl.png"
            alt="Illustration representing the referral program"
            class="w-full h-full max-md:max-w-full max-sm:hidden"
          />
          <img
            loading="lazy"
            src="/images/ref_girl_mob.png"
            alt="Illustration representing the referral program"
            class="w-full h-full max-md:max-w-full lg:hidden"
          />
        </div>
      </div>
    </section>
  </template>
  
  <script>
  export default {
    data() {
      return {
        referralLink: "https://example.dot/?ref=?"
      };
    },
    methods: {
      copyLink() {
        navigator.clipboard.writeText(this.referralLink).then(
          () => {
            alert("Ссылка скопирована!");
          },
          (err) => {
            console.error("Ошибка копирования: ", err);
          }
        );
      },
    },
  };
  </script>
  