<template>
  <form class="registration-modal__form" @submit.prevent="handleSubmit">
    <div class="registration-modal__input-group">
      <div class="registration-modal__input-wrapper">
        <input
            type="email"
            id="email"
            class="registration-modal__input"
            placeholder="E-mail"
            v-model="form.email"
            required
        />
      </div>
    </div>

    <div class="registration-modal__input-group">
      <div class="registration-modal__input-wrapper">
        <input
            :type="showPassword ? 'text' : 'password'"
            id="password"
            class="registration-modal__input"
            placeholder="Пароль"
            v-model="form.password"
            required
        />
        <button type="button" class="registration-modal__toggle-password" @click="togglePassword">
          <img v-if="showPassword" src="/assets/image/eye-open.svg" alt="eye-open" />
          <img v-else src="/assets/image/eye-close.svg" alt="eye-close" />
        </button>
      </div>
    </div>

    <div v-if="isRegistration" class="registration-modal__input-group">
      <div class="registration-modal__input-wrapper">
        <input
            :type="showConfirmPassword ? 'text' : 'password'"
            id="confirm-password"
            class="registration-modal__input"
            placeholder="Повторите пароль"
            v-model="form.confirmPassword"
            required
        />
        <button type="button" class="registration-modal__toggle-password" @click="toggleConfirmPassword">
          <img v-if="showConfirmPassword" src="/assets/image/eye-open.svg" alt="eye-open" />
          <img v-else src="/assets/image/eye-close.svg" alt="eye-close" />
        </button>
      </div>
    </div>

    <div class="registration-modal__form-actions">
      <div class="registration-modal__checkbox-group">
        <input
            type="checkbox"
            id="remember"
            class="registration-modal__checkbox"
            v-model="form.remember"
        />
        <label for="remember" class="registration-modal__checkbox-label">
                    <span class="custom-checkbox" :class="{ checked: form.remember }">
                        <img v-if="form.remember" src="/assets/image/check-arrow.svg" alt="check-arrow" />
                    </span>
          Запомнить меня
        </label>
      </div>
      <button type="submit" class="registration-modal__submit-button" :disabled="isLoading">
        <span v-if="isLoading" class="loading-spinner"></span>
        <span v-else>{{ isRegistration ? 'Зарегистрироваться' : 'Войти' }}</span>
      </button>
    </div>

    <div v-if="errorMessages.length" class="error-messages">
      <div v-for="(message, index) in errorMessages" :key="index" class="error-message">
        {{ message }}
      </div>
    </div>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
  </form>
</template>

<script>
export default {
  name: 'AuthForm',
  props: {
    isRegistration: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      form: {
        email: '',
        password: '',
        confirmPassword: '',
        remember: false,
      },
      showPassword: false,
      showConfirmPassword: false,
      errorMessages: [],
      successMessage: '',
      isLoading: false,
    };
  },
  methods: {
      async handleSubmit() {
          this.errorMessages = [];
          this.successMessage = '';
          this.isLoading = true;

          try {
              if (this.isRegistration) {
                  await this.register();
              } else {
                  await this.login();
              }

              this.$emit('auth-success');

              setTimeout(() => {
                  this.$emit('close');
              }, 1000);
          } catch (error) {
              this.processErrorMessages(error.response?.data);
          } finally {
              this.isLoading = false;
          }
      },
      async register() {
          if (this.form.password !== this.form.confirmPassword) {
              this.errorMessages.push('Пароли не совпадают.');
              return;
          }
          const data = {
              email: this.form.email,
              password: this.form.password,
              confirmPassword: this.form.confirmPassword,
          };
          await this.$store.dispatch('register', data);
          this.successMessage = 'Регистрация прошла успешно!';
      },
      async login() {
          const data = {
              email: this.form.email,
              password: this.form.password,
              remember: this.form.remember,
          };
          await this.$store.dispatch('login', data);
          this.successMessage = 'Вход выполнен успешно!';
      },

    processErrorMessages(errorData) {
      if (errorData) {
        this.errorMessages = Object.values(errorData)
            .flat()
            .map(message => this.safeDecodeUnicode(message));
      } else {
        this.errorMessages.push('Произошла ошибка. Повторите попытку.');
      }
    },
    safeDecodeUnicode(str) {
      try {
        return decodeURIComponent(escape(str));
      } catch (e) {
        return str;
      }
    },
    togglePassword() {
      this.showPassword = !this.showPassword;
    },
    toggleConfirmPassword() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },
  },
};
</script>

<style lang="scss" scoped>
.registration-modal__form {
    display: flex;
    flex-direction: column;
    height: auto;
    gap: 12px;

    .registration-modal__input-group {
        .registration-modal__input-wrapper {
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 12px;
            input {
                font-family: Segoe UI, sans-serif;
                font-size: 14px;
                font-weight: 600;
                line-height: 18.62px;
                text-align: left;
            }
        }

        .registration-modal__input {
            width: 100%;
            height: 52px;
            padding: 0 16px;
            color: #fff;
            background-color: transparent;
        }

        .registration-modal__input::placeholder {
            color: #fff;
        }

        .registration-modal__toggle-password {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            cursor: pointer;
            img {
                width: 22px;
                height: 15px;
            }
        }
    }
    .registration-modal__checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;

        .registration-modal__checkbox {
            display: none; // Скрываем сам чекбокс
        }

        .registration-modal__checkbox-label {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 400;
            line-height: 17.92px;
            color: #fff;
            display: flex;
            align-items: center;
            cursor: pointer;

            .custom-checkbox {
                width: 18px;
                height: 18px;
                background-color: transparent;
                border: 1px solid rgba(255, 255, 255, 0.5);
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 8px;

                &.checked {
                    background-color: #8A23D1;
                }

                img {
                    width: 9px;
                    height: 6px;
                }
            }
        }
    }
    .registration-modal__form-actions {
        margin-top: auto;
        .registration-modal__submit-button {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 52px;
            background: linear-gradient(93.19deg, #8D24D5 0%, #49136F 100%);
            background-size: 200%;
            background-position: left;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            line-height: 19.36px;
            color: #fff;
            border-radius: 12px;
            transition: background-position 0.3s ease;

            &:hover {
                background-position: right;
            }
        }
    }
    .loading-spinner {
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-top-color: #fff;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
    }
}

.error-messages {
    color: red;
    font-size: 14px;
    margin-top: 10px;
}
.success-message {
    color: green;
    font-size: 14px;
    margin-top: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
