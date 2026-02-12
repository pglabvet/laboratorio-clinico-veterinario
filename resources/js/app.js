// Importar Quill para el editor de texto enriquecido
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// Hacer Quill disponible globalmente para Alpine.js
window.Quill = Quill;

// ─── Centro de Notificaciones (Alpine Store) ─────────────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.store('notifications', {
        items: JSON.parse(localStorage.getItem('app_notifications') || '[]'),
        maxItems: 50,

        get unreadCount() {
            return this.items.filter(n => !n.read).length;
        },

        add(type, message) {
            const notification = {
                id: Date.now() + Math.random().toString(36).substring(2, 7),
                type: type,
                message: message,
                time: new Date().toLocaleString('es-BO', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }),
                read: false,
            };

            this.items.unshift(notification);

            // Limitar cantidad
            if (this.items.length > this.maxItems) {
                this.items = this.items.slice(0, this.maxItems);
            }

            this._persist();
        },

        markAsRead(id) {
            const item = this.items.find(n => n.id === id);
            if (item) {
                item.read = true;
                this._persist();
            }
        },

        markAllAsRead() {
            this.items.forEach(n => n.read = true);
            this._persist();
        },

        clearAll() {
            this.items = [];
            this._persist();
        },

        _persist() {
            localStorage.setItem('app_notifications', JSON.stringify(this.items));
        },
    });
});
