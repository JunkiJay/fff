export function extractErrorMessages(error) {
  const messages = [];

  const data = error?.response?.data;
  const errors = data?.errors;

  if (errors && typeof errors === 'object' && !Array.isArray(errors)) {
    for (const [field, arr] of Object.entries(errors)) {
      if (Array.isArray(arr)) {
        for (const item of arr) {
          if (typeof item === 'string') {
            messages.push(item);
          } else if (item && typeof item.message === 'string') {
            messages.push(item.message);
          } else if (item != null) {
            messages.push(String(item));
          }
        }
      } else if (typeof arr === 'string') {
        messages.push(arr);
      }
    }
  } else if (typeof data?.message === 'string') {
    messages.push(data.message);
  } else if (typeof data === 'string') {
    messages.push(data);
  } else if (typeof error?.message === 'string') {
    messages.push(error.message);
  } else {
    messages.push('Произошла неизвестная ошибка');
  }

  return messages;
}
