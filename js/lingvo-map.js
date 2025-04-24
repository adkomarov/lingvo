(function($, Drupal) {
  Drupal.behaviors.lingvoMap = {
    attach: function(context, settings) {
      $('#lingvo-map-container', context).once('lingvo-map').each(function() {
        const container = $(this);
        const mapImage = new Image();
        mapImage.src = settings.lingvo.mapConfig.mapImage;
        
        mapImage.onload = function() {
          container.css({
            'background-image': `url(${mapImage.src})`,
            'background-size': 'contain',
            'width': mapImage.width + 'px',
            'height': mapImage.height + 'px'
          });

          // Добавление иконок
          Object.values(settings.lingvo.regionsData).forEach(region => {
            region.icons.forEach(icon => {
              const $icon = $('<div class="lingvo-icon"></div>')
                .addClass(`lingvo-icon-${icon.type}`)
                .css({
                  position: 'absolute',
                  left: this.calculateIconPosition(icon).x + 'px',
                  top: this.calculateIconPosition(icon).y + 'px',
                  'background-image': `url(${this.getIconPath(icon.type)})`
                })
                .data('icon-data', icon)
                .appendTo(container);
              
              // Обработчик клика
              $icon.on('click', function() {
                Drupal.ajax({
                  url: `/lingvo/icon/${icon.id}`,
                  element: this
                }).execute();
              });
            });
          });
        };
      });
    },

    calculateIconPosition: function(icon) {
      // Пример алгоритма из testmapnew
      const baseX = 120; // Начальная точка по X
      const baseY = 80;  // Начальная точка по Y
      const stepX = 60;  // Шаг между иконками по горизонтали
      const stepY = 45;  // Шаг по вертикали
      
      // Предположим, что позиция хранится в формате "row-column"
      const [row, col] = icon.position.split('-').map(Number);
      
      return {
        x: baseX + (col * stepX),
        y: baseY + (row * stepY)
      };
    },

    calculateGeoPosition: function(icon) {
      // Пример конвертации данных из БД в координаты
      const projection = settings.lingvo.mapConfig.projection;
      const x = (icon.lon - projection.minX) * (mapImage.width / (projection.maxX - projection.minX));
      const y = mapImage.height - (icon.lat - projection.minY) * (mapImage.height / (projection.maxY - projection.minY));
      
      return { x, y };
    },

    getIconPath: function(type) {
      const basePath = Drupal.url(drupalSettings.lingvo.mapConfig.basePath);
      return `${basePath}/images/icon-${type}.png`;
    },

    handleIconSelection: function(selectedIcon) {
      // Сброс предыдущего выбора
      $('.lingvo-icon').removeClass('active');
      
      // Выделение выбранной иконки
      selectedIcon.addClass('active');
      
      // Показ связанных данных
      const iconData = selectedIcon.data('icon-data');
      this.showSidebar(iconData);
    },

    showSidebar: function(iconData) {
      // Динамическая загрузка данных
      Drupal.ajax({
        url: `/lingvo/icon/${iconData.id}`,
        success: (response) => {
          $('#lingvo-sidebar').html(response);
        }
      }).execute();
    }
  };
})(jQuery, Drupal); 