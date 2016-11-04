API корпоративной интеграции Litera5
====================================

Файлы в этом каталоге:
----------------------
    .
    ├── example.pre-check
    │   ├── api_setup.php
    │   ├── api_user.php
    │   ├── _cms_editor.php
    │   ├── _cms_iframe.php
    │   ├── _cms_out.php
    │   ├── _cms.php
    │   ├── _cms_results.php
    │   ├── _cms_status.php
    │   ├── __footer.php
    │   ├── __header.php
    │   ├── index.php
    │   ├── js
    │   │   ├── bootstrap-wysiwyg.js
    │   │   └── jquery.hotkeys.js
    │   ├── _litera5_api_config.php
    │   ├── on_iframe_failure.php
    │   ├── on_initial_stats.php
    │   ├── on_save_corrected.php
    │   └── README.md
    ├── examples
    │   ├── api_check.existing_document.php
    │   ├── api_check.new_document.php
    │   ├── api_check.new_document_with_custom_meta.php
    │   ├── api_check.open_without_text.php
    │   ├── api_setup.php
    │   ├── api_user.change_password.php
    │   ├── api_user.create_generate_password.php
    │   ├── api_user.create_with_dictionary_permission.php
    │   ├── api_user.create_with_error_kinds.php
    │   ├── api_user.create_without_permissions.php
    │   ├── api_user.create_with_password.php
    │   ├── api_user.update_name.php
    │   ├── cms_on_iframe_failure.php
    │   ├── cms_on_initial_stats.php
    │   ├── cms_on_save_corrected.php
    │   ├── custom.css
    │   └── _litera5_api_config.php
    ├── litera5_api_v1.php
    └── README.md

* **litera5_api_v1.php** - собственно классы реализующие API
* **examples** - примеры реализации вызова к методам API для разных ситуаций
* **example.pre-check** - примеры реализации вызова к методам API для разных ситуаций

Дополнительную документацию по методам API можно найти по адресу: [http://litera5.ru/api/](http://litera5.ru/api/)

Изменения:
----------

### 2015-03-18
* `Litera5\SetupRequest` - Добавлены новые настройки
    * добавлена настройка `cancel_icon` - иконка для кнопки "Отменить правки и вернуться в CMS"
    * добавлена настройка `cancel_caption` - заголовок для кнопки "Отменить правки и вернуться в CMS"
    * добавлена настройка `allow_resize_images` - разрешение на изменение размеров картинок в редакторе
    * добавлена настройка `show_cancel_button` - показывать кнопку "Отменить правки и вернуться в CMS" в редакторе
* **examples/cms_on_save_corrected.php**
    * добавлен пример обработки отказа пользователя от сохраниения результатов

### 2015-05-15
* `Litera5\SetupRequest` - Добавлены новые настройки
    * добавлена настройка `editor_css` - файл стилей в редакторе Литеры
* `Litera5\CheckRequest` - Добавлены новые поля
    * добавлено поле `title` - текст заголовка статьи для проверки в Литере
    * добавлено поле `description` - текст описания статьи для проверки в Литере
    * добавлено поле `keywords` - ключевые слова для проверки в Литере
* `Litera5\SaveCorrectedRequest` - Добавлены новые поля
    * добавлено поле `title` - текст откорректированного заголовка статьи
    * добавлено поле `description` - текст откорректированного описания статьи
    * добавлено поле `keywords` - откорректированные ключевые слова
* **examples/custom.css**
    * добавлен файл с текущим стилем в редакторе Литеры для собственной подстройки под стили CMS

### 2015-09-15
* `Litera5\SetupRequest` - Добавлены новые настройки
    * добавлена настройка `get_stats` - возвращать статистический отчёт вместе с результатами
* `Litera5\SaveCorrectedRequest` - Добавлены новые поля
    * добавлено поле `stats` - JSON модель статистического отчёта

### 2015-10-30
* Изменена версия SSL. SSL v.3 -> TLS v.1 
    
### 2015-11-10
* `Litera5\SetupRequest` - Добавлены новые поля
    * добавлено поле `on_initial_stats` - метод CMS для получения уведомления о результатах первой проверки документа
* `Litera5\UserRequest` - Добавлены новые поля
    * добавлено поле `permissions` - список разрешений для пользователя
    * добавлено поле `ortho_kinds` - список типов аннотаций для проверок "Правописание"
    * добавлено поле `cicero_kinds` - список типов аннотаций для проверок "Синонимы/эпитеты"
* `Litera5\CheckRequest` - Добавлены новые поля
    * добавлено поле `custom` - список дополнительных произвольных полей для проверки
* `Litera5\SaveCorrectedRequest` - Добавлены новые поля
    * добавлено поле `custom` - список дополнительных произвольных полей для проверки
* **example.pre-check** - добавлен пример использования API для предварительной проверки перед публикацией
    * подробнее вариант использования описан [здесь](http://lib.litera5.ru/OGL/92176589.html).
    * для запуска изучите инструкцию в файле **example.pre-check/README.md**
         