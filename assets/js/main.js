/**
 * Sistema de Control de Impresoras
 * JavaScript Principal
 */

// ============================================
// TEMA CLARO/OSCURO
// ============================================
const ThemeManager = {
    // Obtener tema actual
    getTheme: function() {
        return localStorage.getItem('theme') || 'light';
    },
    
    // Establecer tema
    setTheme: function(theme) {
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
        
        // Agregar/remover clase dark-mode del body
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        
        this.updateIcon(theme);
    },
    
    // Alternar tema
    toggle: function() {
        const currentTheme = this.getTheme();
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
        
        // Animación suave
        document.body.style.transition = 'background-color 0.3s ease';
        
        // Refrescar DataTables si existen
        if ($.fn.DataTable) {
            $('.table').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().draw(false);
                }
            });
        }
        
        // Actualizar colores de badges de estado si la función existe
        if (typeof aplicarColoresEstados === 'function') {
            aplicarColoresEstados();
        }
        
        // Mostrar notificación
        this.showNotification(newTheme);
    },
    
    // Actualizar icono del botón
    updateIcon: function(theme) {
        const icon = document.querySelector('#themeToggle i');
        if (icon) {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    },
    
    // Mostrar notificación de cambio de tema
    showNotification: function(theme) {
        const message = theme === 'dark' ? 'Modo oscuro activado' : 'Modo claro activado';
        const icon = theme === 'dark' ? '🌙' : '☀️';
        
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = 'theme-toast';
        toast.innerHTML = `${icon} ${message}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: ${theme === 'dark' ? '#1e293b' : '#ffffff'};
            color: ${theme === 'dark' ? '#f1f5f9' : '#1f2937'};
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            font-weight: 500;
            animation: slideIn 0.3s ease;
            border: 1px solid ${theme === 'dark' ? '#334155' : '#e5e7eb'};
        `;
        
        document.body.appendChild(toast);
        
        // Eliminar después de 2 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    },
    
    // Inicializar tema
    init: function() {
        const theme = this.getTheme();
        this.setTheme(theme);
        
        // Event listener para el botón de tema
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggle());
        }
    }
};

// ============================================
// SIDEBAR MÓVIL
// ============================================
const SidebarManager = {
    init: function() {
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = this.createOverlay();
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            
            // Cerrar al hacer clic en el overlay
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    },
    
    createOverlay: function() {
        let overlay = document.getElementById('sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebar-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
                transition: opacity 0.3s ease;
            `;
            document.body.appendChild(overlay);
        }
        
        // Estilo para mostrar
        const style = document.createElement('style');
        style.textContent = `
            #sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
            
            @media (min-width: 769px) {
                #sidebar-overlay {
                    display: none !important;
                }
            }
        `;
        document.head.appendChild(style);
        
        return overlay;
    }
};

// ============================================
// TOOLTIPS Y POPOVERS
// ============================================
const BootstrapComponents = {
    init: function() {
        // Inicializar tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
};

// ============================================
// ANIMACIONES DE ENTRADA
// ============================================
const AnimationManager = {
    init: function() {
        // Observador de intersección para animaciones
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, {
            threshold: 0.1
        });
        
        // Observar cards y elementos animables
        document.querySelectorAll('.stat-card, .content-card').forEach(el => {
            observer.observe(el);
        });
    }
};

// ============================================
// DATATABLES CONFIGURACIÓN
// ============================================
const DataTableManager = {
    defaultConfig: {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    },
    
    init: function(selector, customConfig = {}) {
        const config = { ...this.defaultConfig, ...customConfig };
        return $(selector).DataTable(config);
    }
};

// ============================================
// SELECT2 CONFIGURACIÓN
// ============================================
const Select2Manager = {
    init: function(selector, placeholder = 'Seleccione...') {
        $(selector).select2({
            theme: 'bootstrap-5',
            placeholder: placeholder,
            allowClear: true,
            width: '100%'
        });
    }
};

// ============================================
// CONFIRMACIONES
// ============================================
const ConfirmManager = {
    delete: function(message = '¿Está seguro de eliminar este registro?') {
        return confirm(message);
    },
    
    custom: function(message) {
        return confirm(message);
    }
};

// ============================================
// VALIDACIÓN DE FORMULARIOS
// ============================================
const FormValidator = {
    init: function() {
        // Validación HTML5
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }
};

// ============================================
// MANEJO DE ERRORES AJAX
// ============================================
const AjaxManager = {
    handleError: function(xhr, status, error) {
        console.error('Error AJAX:', error);
        alert('Ocurrió un error al procesar la solicitud. Por favor, intente nuevamente.');
    }
};

// ============================================
// UTILIDADES
// ============================================
const Utils = {
    // Formatear número
    formatNumber: function(number) {
        return new Intl.NumberFormat('es-PE').format(number);
    },
    
    // Formatear moneda
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN'
        }).format(amount);
    },
    
    // Copiar al portapapeles
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copiado al portapapeles');
        });
    },
    
    // Descargar como CSV
    downloadCSV: function(data, filename) {
        const blob = new Blob([data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }
};

// ============================================
// ANIMACIONES CSS
// ============================================
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(styleSheet);

// ============================================
// INICIALIZACIÓN AL CARGAR LA PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tema
    ThemeManager.init();
    
    // Inicializar sidebar móvil
    SidebarManager.init();
    
    // Inicializar componentes de Bootstrap
    if (typeof bootstrap !== 'undefined') {
        BootstrapComponents.init();
    }
    
    // Inicializar animaciones
    AnimationManager.init();
    
    // Inicializar validación de formularios
    FormValidator.init();
    
    console.log('✓ Sistema inicializado correctamente');
});

// ============================================
// EXPORTAR FUNCIONES GLOBALES
// ============================================
window.ThemeManager = ThemeManager;
window.DataTableManager = DataTableManager;
window.Select2Manager = Select2Manager;
window.ConfirmManager = ConfirmManager;
window.Utils = Utils;
