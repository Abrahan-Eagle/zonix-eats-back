/**
 * Generador de slugs automático para formularios
 * Uso: Inicializar con initSlugGenerator('name-input-id', 'slug-input-id')
 */
(function() {
    'use strict';

    window.SlugGenerator = {
        /**
         * Inicializa el generador de slugs
         * @param {string} sourceId - ID del input fuente (ej: 'name')
         * @param {string} targetId - ID del input destino (ej: 'slug')
         * @param {boolean} locked - Si el slug está bloqueado (solo lectura)
         */
        init: function(sourceId, targetId, locked) {
            const sourceInput = document.getElementById(sourceId);
            const targetInput = document.getElementById(targetId);
            
            if (!sourceInput || !targetInput) {
                console.warn('SlugGenerator: No se encontraron los inputs especificados');
                return;
            }

            let slugLocked = locked || false;

            // Función mejorada para generar slugs
            const generateSlug = (text) => {
                return text.toLowerCase()
                    .replace(/á/gi, 'a')
                    .replace(/é/gi, 'e')
                    .replace(/í/gi, 'i')
                    .replace(/ó/gi, 'o')
                    .replace(/ú/gi, 'u')
                    .replace(/ñ/gi, 'n')
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            };

            // Generar slug al escribir (solo si no está bloqueado)
            sourceInput.addEventListener('input', function() {
                if (!slugLocked) {
                    targetInput.value = generateSlug(this.value);
                }
            });

            // Generar slug inicial si hay valor pero no slug
            if (sourceInput.value && !targetInput.value) {
                targetInput.value = generateSlug(sourceInput.value);
            }

            // Bloquear/desbloquear slug en edición
            const lockButton = document.getElementById('lock-slug');
            if (lockButton) {
                lockButton.addEventListener('click', function() {
                    slugLocked = !slugLocked;
                    targetInput.readOnly = slugLocked;
                    this.innerHTML = slugLocked 
                        ? '<i class="fas fa-lock-open"></i>' 
                        : '<i class="fas fa-lock"></i>';
                    this.classList.toggle('btn-outline-secondary');
                    this.classList.toggle('btn-outline-success');
                });
            }
        }
    };
})();

