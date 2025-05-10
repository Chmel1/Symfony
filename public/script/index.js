document.addEventListener('turbo:load', function() {
    const input = document.querySelector('#book_coverImage');
    const previewContainer = document.getElementById('coverPreviewContainer');
    const previewImage = document.getElementById('coverPreview');
    const modalImage = document.getElementById('newCoverModalImage');

    // Проверка на наличие input
    if (!input) {
        console.warn('Input не найден!');
        return;
    }

    // 2. Очистка предыдущих обработчиков
    input.removeEventListener('change', handleFileSelect); // Убираем старые обработчики

    // 3. Обработчик для превью
    function handleFileSelect(event) {
        const file = event.target.files[0];
        
        if (!file) {
            previewContainer.classList.add('d-none');
            previewImage.src = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('Файл слишком большой (максимум 2 МБ)');
            input.value = ''; // Очищаем input
            return;
        }

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.classList.remove('d-none');
                
                // Обновление модального изображения
                modalImage.src = e.target.result;
            };
            
            reader.readAsDataURL(file);
        } else {
            previewContainer.classList.add('d-none');
            previewImage.src = '';
        }
    }

    // 4. Привязка нового обработчика
    input.addEventListener('change', handleFileSelect);

    // 5. Обработчик для открытия модалки
    previewImage.addEventListener('click', () => {
        if (previewImage.src) {
            $('#newCoverModal').modal('show');
        }
    });

    // 6. Инициализация текущего значения
    if (input.files.length > 0) {
        handleFileSelect({ target: input });
    }
});
