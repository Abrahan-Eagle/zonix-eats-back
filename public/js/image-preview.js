/**
 * Vista previa de imágenes para formularios
 * Uso: Inicializar con ImagePreview.init('image-input-id', 'preview-image-id')
 */
(function() {
    'use strict';

    window.ImagePreview = {
        /**
         * Inicializa la vista previa de imágenes
         * @param {string} inputId - ID del input de archivo
         * @param {string} previewId - ID del elemento img para la vista previa
         * @param {Array} validTypes - Tipos MIME válidos (default: ['image/jpeg', 'image/png', 'image/gif'])
         * @param {number} maxSizeMB - Tamaño máximo en MB (default: 5)
         */
        init: function(inputId, previewId, validTypes, maxSizeMB) {
            const imgInput = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            if (!imgInput) {
                console.warn('ImagePreview: No se encontró el input de imagen');
                return;
            }

            validTypes = validTypes || ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            maxSizeMB = maxSizeMB || 5;

            imgInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                if (!file) {
                    return;
                }

                // Validar tipo
                if (!validTypes.includes(file.type)) {
                    alert('Por favor, sube una imagen válida (JPEG, PNG, GIF o WEBP)');
                    this.value = '';
                    if (preview) {
                        preview.style.display = 'none';
                    }
                    return;
                }

                // Validar tamaño
                const fileSizeMB = file.size / (1024 * 1024);
                if (fileSizeMB > maxSizeMB) {
                    alert(`La imagen es demasiado grande. Tamaño máximo: ${maxSizeMB}MB`);
                    this.value = '';
                    if (preview) {
                        preview.style.display = 'none';
                    }
                    return;
                }

                // Mostrar vista previa
                if (preview && window.FileReader) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    };
})();

