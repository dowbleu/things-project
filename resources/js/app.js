import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Функция для отображения уведомлений broadcast
function showBroadcastNotification(message, type = 'info') {
    // Создаем контейнер для уведомлений, если его еще нет
    let container = document.getElementById('broadcast-notifications');
    if (!container) {
        container = document.createElement('div');
        container.id = 'broadcast-notifications';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
    }

    // Создаем элемент уведомления
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    container.appendChild(alertDiv);

    // Автоматически закрываем уведомление через 5 секунд
    setTimeout(() => {
        if (alertDiv.parentNode) {
            // Используем Bootstrap API для закрытия уведомления
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertDiv);
            bsAlert.close();
            // Удаляем элемент после анимации закрытия
            alertDiv.addEventListener('closed.bs.alert', () => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            });
        }
    }, 5000);
}

// Подписка на события broadcast после загрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    if (window.Echo) {
        console.log('Echo initialized, subscribing to channels...');
        
        // Подписка на канал вещей
        window.Echo.channel('things')
            .listen('.thing.created', (data) => {
                console.log('Thing created event received:', data);
                if (data && data.thing) {
                    showBroadcastNotification(
                        `Создана новая вещь: ${data.thing.name} (Владелец: ${data.thing.master || 'Неизвестно'})`,
                        'info'
                    );
                }
            });

        // Подписка на канал мест
        window.Echo.channel('places')
            .listen('.place.created', (data) => {
                console.log('Place created event received:', data);
                if (data && data.place) {
                    showBroadcastNotification(
                        `Создано новое место: ${data.place.name}`,
                        'success'
                    );
                }
            });
    } else {
        console.warn('Echo is not initialized. Check VITE_PUSHER_APP_KEY in .env');
    }
});