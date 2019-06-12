<?php

namespace Litera5 {

    const VERSION = '1.20170412';

    if (!defined('API_SERVER_URL')) {
        define('API_SERVER_URL', 'https://litera5.ru');
    }

    if (!defined('API_IFRAME_URL')) {
        define('API_IFRAME_URL', 'https://litera5.ru');
    }

    function url($url)
    {
        return API_SERVER_URL . $url;
    }

    function iframeUrl($url)
    {
        return API_IFRAME_URL . $url;
    }

    function _bool_2_str($val)
    {
        return ($val) ? 'true' : 'false';
    }

    function _is_filled($val)
    {
        return ($val !== null && $val !== "");
    }

    function _get_or_null($arr, $key)
    {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        } else {
            return null;
        }
    }

    /**
     * Class Signature
     * Вспомогательный класс для работы с подписями
     */
    class Signature
    {
        private $api_key;

        function __construct($api_key)
        {
            $this->api_key = $api_key;
        }

        /**
         * Возвращает подпись для переданного массива параметров.
         * Пример: sign(["значение1", "значение2", "значение3"])
         * @param $values array
         * @return string
         */
        function sign($values)
        {
            $sig_values = $values;
            array_push($sig_values, $this->api_key);
            $md5_str = implode("", $sig_values);
            return md5($md5_str);
        }

        /**
         * Проверяет правильность подписи для переданного массива параметров
         * @param $signature string
         * @param $values array
         * @return bool
         */
        function is_valid($signature, $values)
        {
            return strcasecmp($signature, $this->sign($values)) == 0;
        }
    }


    /*****************************************************
     *                                                   *
     *                                                   *
     *                  Litera5 API                      *
     *                                                   *
     *                                                   *
     *****************************************************/
    abstract class BaseAPIRequest
    {
        protected $time = null;
        protected $company = null;
        protected $signature = null;

        /**
         * @param $company string
         */
        protected function __construct($company)
        {
            $this->time = "" . time();
            $this->company = $company;
        }

        protected abstract function _query(&$query);

        /**
         * @return array
         */
        private function mk_query()
        {
            $query = [$this->time, $this->company];
            $this->_query($query);
            return $query;
        }

        protected abstract function _json(&$json);

        /**
         * Возвращает строку с JSON представлением объекта запроса
         * @return string
         */
        function json()
        {
            $result["time"] = $this->time;
            $result["company"] = $this->company;
            $this->_json($result);
            $result["signature"] = $this->signature;
            return json_encode($result);
        }

        /**
         * Подписывает запрос соответствующим ключом.
         * @param $sign Signature
         * @return $this
         */
        function sign($sign)
        {
            $this->signature = $sign->sign($this->mk_query());
            return $this;
        }
    }

    abstract class BaseAPIResponse
    {
        private $sign;

        public $error_code = null;
        public $error_message = null;

        protected $time = null;
        protected $signature = null;

        /**
         * @param $sign Signature
         * @param $http_code int
         * @param $content string
         */
        function __construct($sign, $http_code, $content)
        {
            $this->sign = $sign;
            if ($http_code == 200) {
                $json = json_decode($content, true);
                $this->time = _get_or_null($json, 'time');
                $this->_parse_json($json);
                $this->signature = _get_or_null($json, "signature");
            } else
                $this->_set_error($http_code, $content);
        }

        /**
         * @param $code int
         * @param $message string
         */
        private function _set_error($code, $message)
        {
            $this->error_code = $code;
            $this->error_message = $message;
        }

        /**
         * @param $json array
         */
        protected abstract function _parse_json($json);

        /**
         * Запрос успешно завершился
         * @return bool
         */
        function is_success()
        {
            return $this->error_code == null;
        }

        /**
         * Запрос окончился неудачей
         * @return bool
         */
        function is_failure()
        {
            return !$this->is_success();
        }

        /**
         * @param $query array
         */
        protected abstract function _query(&$query);

        /**
         * @return array
         */
        protected function mk_query()
        {
            $query = [$this->time];
            $this->_query($query);
            return $query;
        }

        /**
         * Проверяет подпись ответа
         * @return bool
         */
        function has_valid_signature()
        {
            return $this->sign->is_valid($this->signature, $this->mk_query());
        }

    }

    /**
     * Запрос на установку или обновление настроек API
     * Class SetupRequest
     * @package Litera5
     */
    class SetupRequest extends BaseAPIRequest
    {
        private $on_save_corrected = null;
        private $on_iframe_failure = null;
        private $on_initial_stats = null;
        private $return_icon = null;
        private $return_caption = null;
        private $cancel_icon = null;
        private $cancel_caption = null;
        private $allow_resize_images = null;
        private $show_cancel_button = null;
        private $editor_css = null;
        private $get_stats = null;
        private $hide_editor_toolbar = null;


        /**
         * @param $val string
         * @return $this
         */
        function on_save_corrected($val)
        {
            $this->on_save_corrected = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function on_iframe_failure($val)
        {
            $this->on_iframe_failure = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function on_initial_stats($val)
        {
            $this->on_initial_stats = $val;
            return $this;
        }

        /**
         * @param $val
         * @return $this
         */
        function return_icon($val)
        {
            $this->return_icon = $val;
            return $this;
        }

        /**
         * @param $val
         * @return $this
         */
        function return_caption($val)
        {
            $this->return_caption = $val;
            return $this;
        }

        /**
         * @param $val
         * @return $this
         */
        function cancel_icon($val)
        {
            $this->cancel_icon = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function cancel_caption($val)
        {
            $this->cancel_caption = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function allow_resize_images($val)
        {
            $this->allow_resize_images = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function show_cancel_button($val)
        {
            $this->show_cancel_button = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function editor_css($val)
        {
            $this->editor_css = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function get_stats($val)
        {
            $this->get_stats = $val;
            return $this;
        }

        /**
         * @param $val boolean
         * @return $this
         */
        function hide_editor_toolbar($val)
        {
            $this->hide_editor_toolbar = $val;
            return $this;
        }

        protected function _query(&$query)
        {
            if (_is_filled($this->on_save_corrected)) array_push($query, $this->on_save_corrected);
            if (_is_filled($this->on_iframe_failure)) array_push($query, $this->on_iframe_failure);
            if (_is_filled($this->on_initial_stats)) array_push($query, $this->on_initial_stats);
            if (_is_filled($this->return_icon)) array_push($query, $this->return_icon);
            if (_is_filled($this->return_caption)) array_push($query, $this->return_caption);
            if (_is_filled($this->cancel_icon)) array_push($query, $this->cancel_icon);
            if (_is_filled($this->cancel_caption)) array_push($query, $this->cancel_caption);
            if (_is_filled($this->allow_resize_images)) array_push($query, _bool_2_str($this->allow_resize_images));
            if (_is_filled($this->show_cancel_button)) array_push($query, _bool_2_str($this->show_cancel_button));
            if (_is_filled($this->editor_css)) array_push($query, $this->editor_css);
            if (_is_filled($this->get_stats)) array_push($query, _bool_2_str($this->get_stats));
            if (_is_filled($this->hide_editor_toolbar)) array_push($query, _bool_2_str($this->hide_editor_toolbar));
        }

        protected function _json(&$json)
        {
            if (_is_filled($this->on_save_corrected)) $json["onSaveCorrected"] = $this->on_save_corrected;
            if (_is_filled($this->on_iframe_failure)) $json["onIFrameFailure"] = $this->on_iframe_failure;
            if (isset($this->on_initial_stats)) $json["onInitialStats"] = $this->on_initial_stats;
            if (_is_filled($this->return_icon)) $json["returnIcon"] = $this->return_icon;
            if (_is_filled($this->return_caption)) $json["returnCaption"] = $this->return_caption;
            if (_is_filled($this->cancel_icon)) $json["cancelIcon"] = $this->cancel_icon;
            if (_is_filled($this->cancel_caption)) $json["cancelCaption"] = $this->cancel_caption;
            if (_is_filled($this->allow_resize_images)) $json["allowResizeImages"] = $this->allow_resize_images;
            if (_is_filled($this->show_cancel_button)) $json["showCancelButton"] = $this->show_cancel_button;
            if (_is_filled($this->editor_css)) $json["editorCss"] = $this->editor_css;
            if (_is_filled($this->get_stats)) $json["getStats"] = $this->get_stats;
            if (_is_filled($this->hide_editor_toolbar)) $json["hideEditorToolbar"] = $this->hide_editor_toolbar;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @return SetupRequest
         */
        static function start($company)
        {
            return new SetupRequest($company);
        }

    }

    class SetupResponse extends BaseAPIResponse
    {
        /**
         * @param $json array
         */
        protected function _parse_json($json)
        {
        }

        /**
         * @param $query array
         */
        protected function _query(&$query)
        {
        }
    }

    /**
     * Типы разрешений для пользователя
     * Class UserPermission
     * @package Litera5
     */
    class UserPermission
    {
        /**
         * Пользователь имеет право работать с корпоративным словарём. Добавлять новые слова, редактировать словарь
         */
        const USE_DICTIONARY = "USE_DICTIONARY";
        /**
         * Пользователю запрещено работать с закладкой "Красота"
         * @deprecated используйте параметр `checksCicero` чтобы контролировать режим работы с закладкой "Красота"
         */
        const DISABLE_CICERO = "DISABLE_CICERO";
        /**
         * Пользователю запрещено работать с закладкой "Качество"
         * @deprecated используйте параметр `checksQuality` чтобы контролировать режим работы с закладкой "Качество"
         */
        const DISABLE_QUALITY = "DISABLE_QUALITY";
        /**
         * Пользователю запрещено при работе через апи делать повторные проверки
         * @deprecated используйте параметр `checksTotal` чтобы контролировать режим работы с закладкой "Грамотность"
         */
        const DISABLE_API_SECONDARY_ORFO_CHECKS = "DISABLE_API_SECONDARY_ORFO_CHECKS";
        /**
         * Пользователю нельзя пользоваться веб-интерфейсом (только API)
         */
        const DISABLE_WEB_INTERFACE = "DISABLE_WEB_INTERFACE";
    }

    /**
     * Типы ошибок на закладке "Правописание"
     * Class OrthoKind
     * @package Litera5
     */
    class OrthoKind
    {
        /**
         * Орфография
         */
        const SPELLING = "mkSpelling";
        /**
         * Грамматика
         */
        const GRAMMAR = "mkGrammar";
        /**
         * Пунктуация
         */
        const PUNCTUATION = "mkPunctuation";
        /**
         * Стилистика
         */
        const STYLE = "mkStyle";
        /**
         * Семантика
         */
        const SEMANTIC = "mkSemantic";
        /**
         * Типографика
         */
        const TYPOGRAPHY = "mkTypography";
        /**
         * Буква Ё
         */
        const YO = "mkYo";
        /**
         * Оформление
         */
        const PAPER_STRUCTURE = "mkPaperStructure";
    }

    /**
     * Типы ошибок на закладке "Красота"
     * Class CiceroKind
     * @package Litera5
     */
    class CiceroKind
    {
        /**
         * Тавтологии
         */
        const TAUTOLOGY = "mkTautology";
        /**
         * Неблагозвучие
         */
        const PHONICS = "mkPhonics";
        /**
         * Орфоэпия
         */
        const ORTHOEPY = "mkOrthoepy";
        /**
         * Синонимы
         */
        const SYNONYM = "mkSynonym";
        /**
         * Эпитеты
         */
        const EPITHET = "mkEpithet";
        /**
         * Родная речь
         */
        const NATIVE_SPEECH = "mkNativeSpeech";
    }

    /**
     * Типы ошибок на закладке "Качество"
     * Class QualityKind
     * @package Litera5
     */
    class QualityKind
    {
        /**
         * Водность
         */
        const WATER = "mkWater";
    }

    /**
     * Запрос на создание или обновление информации о пользователях
     * Class UserRequest
     * @package Litera5
     */
    class UserRequest extends BaseAPIRequest
    {
        private $login = null;
        private $name = null;
        private $password = null;
        private $permissions = null;
        private $ortho_kinds = null;
        private $cicero_kinds = null;
        private $quality_kinds = null;
        private $checks_ortho = null;
        private $checks_cicero = null;
        private $checks_quality = null;
        private $checks_total = null;

        /**
         * @param $company string
         * @param $login string
         */
        protected function __construct($company, $login)
        {
            parent::__construct($company);
            $this->login = $login;
        }

        /**
         * @param $val string
         * @return $this
         */
        function name($val)
        {
            $this->name = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function password($val)
        {
            $this->password = $val;
            return $this;
        }

        /**
         * @see @UserPermission
         * @param $val array
         * @return $this
         */
        function permissions($val)
        {
            $this->permissions = $val;
            return $this;
        }

        /**
         * @see @OrthoKind
         * @param $val array
         * @return $this
         */
        function ortho_kinds($val)
        {
            $this->ortho_kinds = $val;
            return $this;
        }

        /**
         * @see @CiceroKind
         * @param $val array
         * @return $this
         */
        function cicero_kinds($val)
        {
            $this->cicero_kinds = $val;
            return $this;
        }

        /**
         * @see @QualityKind
         * @param $val
         * @return $this
         */
        function quality_kinds($val)
        {
            $this->quality_kinds = $val;
            return $this;
        }

        /**
         * @param $val int
         * @return $this
         */
        function checks_ortho($val)
        {
            $this->checks_ortho = $val;
            return $this;
        }

        /**
         * @param $val int
         * @return $this
         */
        function checks_cicero($val)
        {
            $this->checks_cicero = $val;
            return $this;
        }

        /**
         * @param $val int
         * @return $this
         */
        function checks_quality($val)
        {
            $this->checks_quality = $val;
            return $this;
        }

        /**
         * @param $val int
         * @return $this
         */
        function checks_total($val)
        {
            $this->checks_total = $val;
            return $this;
        }

        protected function _query(&$query)
        {
            array_push($query, $this->login);
            if (_is_filled($this->name)) array_push($query, $this->name);
            if (_is_filled($this->password)) array_push($query, $this->password);
            if (_is_filled($this->permissions)) {
                foreach ($this->permissions as $p) {
                    array_push($query, $p);
                }
            };
            if (_is_filled($this->ortho_kinds)) {
                foreach ($this->ortho_kinds as $p) {
                    array_push($query, $p);
                }
            };
            if (_is_filled($this->cicero_kinds)) {
                foreach ($this->cicero_kinds as $p) {
                    array_push($query, $p);
                }
            };
            if (_is_filled($this->quality_kinds)) {
                foreach ($this->quality_kinds as $p) {
                    array_push($query, $p);
                }
            }
            if (_is_filled($this->checks_ortho)) {
                array_push($query, $this->checks_ortho);
            }
            if (_is_filled($this->checks_cicero)) {
                array_push($query, $this->checks_cicero);
            }
            if (_is_filled($this->checks_quality)) {
                array_push($query, $this->checks_quality);
            }
            if (_is_filled($this->checks_total)) {
                array_push($query, $this->checks_total);
            }
        }

        protected function _json(&$json)
        {
            $json["login"] = $this->login;
            if (_is_filled($this->name)) $json["name"] = $this->name;
            if (_is_filled($this->password)) $json["password"] = $this->password;
            if (_is_filled($this->permissions)) $json["permissions"] = $this->permissions;
            if (_is_filled($this->ortho_kinds)) $json["orthoKinds"] = $this->ortho_kinds;
            if (_is_filled($this->cicero_kinds)) $json["ciceroKinds"] = $this->cicero_kinds;
            if (_is_filled($this->quality_kinds)) $json["qualityKinds"] = $this->quality_kinds;
            if (_is_filled($this->checks_ortho)) $json["checksOrtho"] = $this->checks_ortho;
            if (_is_filled($this->checks_cicero)) $json["checksCicero"] = $this->checks_cicero;
            if (_is_filled($this->checks_quality)) $json["checksQuality"] = $this->checks_quality;
            if (_is_filled($this->checks_total)) $json["checksTotal"] = $this->checks_total;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @param $login string
         * @return UserRequest
         */
        static function start($company, $login)
        {
            return new UserRequest($company, $login);
        }
    }

    class UserResponse extends BaseAPIResponse
    {

        public $password = null;

        protected function _parse_json($json)
        {
            $this->password = _get_or_null($json, "password");
        }

        protected function _query(&$query)
        {
            if (_is_filled($this->password)) array_push($query, $this->password);
        }

    }

    /**
     * Class CheckRequest
     * Запрос на проверку документа
     */
    class CheckRequest extends BaseAPIRequest
    {
        private $login = null;
        private $token = null;
        private $document = null;
        private $name = null;
        private $title = null;
        private $description = null;
        private $keywords = null;
        private $custom = null;
        private $html = null;

        /**
         * @param $company string
         * @param $login string
         * @param $token string
         */
        protected function __construct($company, $login, $token)
        {
            parent::__construct($company);
            $this->login = $login;
            $this->token = $token;
        }

        protected function _query(&$query)
        {
            array_push($query, $this->login);
            array_push($query, $this->token);
            if (_is_filled($this->document)) array_push($query, $this->document);
            if (_is_filled($this->name)) array_push($query, $this->name);
            if (_is_filled($this->title)) array_push($query, $this->title);
            if (_is_filled($this->description)) array_push($query, $this->description);
            if (_is_filled($this->keywords)) array_push($query, $this->keywords);
            if (_is_filled($this->custom)) {
                foreach ($this->custom as $custom) {
                    array_push($query, $custom["name"]);
                    array_push($query, $custom["value"]);
                }
            }
            if (_is_filled($this->html)) array_push($query, $this->html);
        }

        protected function _json(&$json)
        {
            $json["login"] = $this->login;
            $json["token"] = $this->token;
            if (_is_filled($this->document)) $json["document"] = $this->document;
            if (_is_filled($this->name)) $json["name"] = $this->name;
            if (_is_filled($this->title)) $json["title"] = $this->title;
            if (_is_filled($this->description)) $json["description"] = $this->description;
            if (_is_filled($this->keywords)) $json["keywords"] = $this->keywords;
            if (_is_filled($this->custom)) $json["custom"] = $this->custom;
            if (_is_filled($this->html)) $json["html"] = $this->html;
        }

        /**
         * @param $val string
         * @return $this
         */
        function document($val)
        {
            $this->document = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function name($val)
        {
            $this->name = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function title($val)
        {
            $this->title = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function description($val)
        {
            $this->description = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function keywords($val)
        {
            $this->keywords = $val;
            return $this;
        }

        /**
         * @param $name string
         * @param $value string
         * @return $this
         */
        function custom($name, $value)
        {
            if ($this->custom == null)
                $this->custom = [];
            array_push($this->custom, array(
                "name" => $name,
                "value" => $value
            ));
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function html($val)
        {
            $this->html = $val;
            return $this;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @param $login string
         * @param $token string
         * @return CheckRequest
         */
        static function start($company, $login, $token)
        {
            return new CheckRequest($company, $login, $token);
        }
    }

    /**
     * Class CheckResponse
     * Ответ на запрос о проверке документа
     */
    class CheckResponse extends BaseAPIResponse
    {

        public $document = null;
        public $url = null;

        protected function _parse_json($json)
        {
            $this->document = _get_or_null($json, "document");
            $this->url = _get_or_null($json, "url");
        }

        protected function _query(&$query)
        {
            array_push($query, $this->document);
            array_push($query, $this->url);
        }

    }

    /**
     * Class CheckProfile
     * @package Litera5
     */
    class CheckProfile
    {
        /**
         * Проверка правописания
         */
        const ORTHO = "ortho";
        /**
         * Проверка красоты текста
         */
        const CICERO = "cicero";
        /**
         * Проверка качества текста
         */
        const QUALITY = "quality";
    }

    /**
     * Class CheckOgxtRequest
     * @package Litera5
     */
    class CheckOgxtRequest extends BaseAPIRequest
    {
        public $login = null;
        public $profile = null;
        public $document = null;
        public $name = null;
        public $html = null;
        public $ogxt = null;

        /**
         * @param $company string
         * @param $login string
         * @param $html string
         * @param $ogxt string
         */
        protected function __construct($company, $login, $html, $ogxt)
        {
            parent::__construct($company);
            $this->login = $login;
            $this->html = $html;
            $this->ogxt = $ogxt;
        }

        protected function _query(&$query)
        {
            array_push($query, $this->login);
            if (_is_filled($this->profile)) array_push($query, $this->profile);
            if (_is_filled($this->document)) array_push($query, $this->document);
            if (_is_filled($this->name)) array_push($query, $this->name);
            array_push($query, $this->html);
            array_push($query, $this->ogxt);
        }

        protected function _json(&$json)
        {
            $json["login"] = $this->login;
            if (_is_filled($this->profile)) $json["profile"] = $this->profile;
            if (_is_filled($this->document)) $json["document"] = $this->document;
            if (_is_filled($this->name)) $json["name"] = $this->name;
            $json["html"] = $this->html;
            $json["ogxt"] = $this->ogxt;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @param $login string
         * @param $html string
         * @param $ogxt string
         * @return CheckOgxtRequest
         */
        static function start($company, $login, $html, $ogxt)
        {
            return new CheckOgxtRequest($company, $login, $html, $ogxt);
        }

        /**
         * @param $val string
         * @return $this
         * @see CheckProfile
         */
        function profile($val)
        {
            $this->profile = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function document($val)
        {
            $this->document = $val;
            return $this;
        }

        /**
         * @param $val string
         * @return $this
         */
        function name($val)
        {
            $this->name = $val;
            return $this;
        }

    }

    /**
     * Class CheckOgxtResponse
     * Ответ на запрос о проверке ogxt документа
     * @package Litera5
     */
    class CheckOgxtResponse extends BaseAPIResponse
    {

        public $document = null;
        public $check = null;

        protected function _parse_json($json)
        {
            $this->document = _get_or_null($json, "document");
            $this->check = _get_or_null($json, "check");
        }

        protected function _query(&$query)
        {
            array_push($query, $this->document);
            array_push($query, $this->check);
        }

    }

    /**
     * Class CheckOgxtResultsRequest
     * @package Litera5
     */
    class CheckOgxtResultsRequest extends BaseAPIRequest
    {
        public $check = null;

        /**
         * @param $company string
         * @param $check string
         */
        protected function __construct($company, $check)
        {
            parent::__construct($company);
            $this->check = $check;
        }

        protected function _query(&$query)
        {
            array_push($query, $this->check);
        }

        protected function _json(&$json)
        {
            $json["check"] = $this->check;
        }

        /**
         * Создаёт новый объект запроса
         * @param $company string
         * @param $check string
         * @return CheckOgxtResultsRequest
         */
        static function start($company, $check)
        {
            return new CheckOgxtResultsRequest($company, $check);
        }
    }

    class CheckState
    {
        /**
         * Проверка создана
         */
        const CREATED = "CREATED";
        /**
         * Документ загружен на сервер
         */
        const UPLOADED = "UPLOADED";
        /**
         * Проверка ожидает в очереди на оценку
         */
        const WAITING_ESTIMATION = "WAITING_ESTIMATION";
        /**
         * Документ оценивается
         */
        const ESTIMATING = "ESTIMATING";
        /**
         * Оценка завершена
         */
        const ESTIMATED_SUCCESS = "ESTIMATED_SUCCESS";
        /**
         * Оценка завершилась с ошибкой
         */
        const ESTIMATED_ERROR = "ESTIMATED_ERROR";
        /**
         * В оценке отказано
         */
        const ESTIMATED_REJECT = "ESTIMATED_REJECT";
        /**
         * Проверка отменена
         */
        const CANCELLED = "CANCELLED";
        /**
         * Документ ожидает в очереди на проверку
         */
        const WAITING_CHECK = "WAITING_CHECK";
        /**
         * Документ проверяется
         */
        const CHECKING = "CHECKING";
        /**
         * В проверке отказано
         */
        const REJECTED = "REJECTED";
        /**
         * Проверка благополучна завершилась
         */
        const CHECKED_SUCCESS = "CHECKED_SUCCESS";
        /**
         * Во время проверки произошла непредвиденная ошибка
         */
        const CHECKED_ERROR = "CHECKED_ERROR";
    }

    /**
     * Class CheckOgxtResultsResponse
     * @package Litera5
     */
    class CheckOgxtResultsResponse extends BaseAPIResponse
    {
        /**
         * @var string
         * @see CheckState
         */
        public $state = null;
        public $progress = null;
        public $message = null;
        public $html = null;
        public $annotations = null;
        public $stats = null;

        protected function _parse_json($json)
        {
            $this->state = _get_or_null($json, "state");
            $this->progress = _get_or_null($json, "progress");
            $this->message = _get_or_null($json, "message");
            $this->html = _get_or_null($json, "html");
            $this->annotations = _get_or_null($json, "annotations");
            $this->stats = _get_or_null($json, "stats");
        }

        protected function _query(&$query)
        {
            array_push($query, $this->state);
            array_push($query, $this->progress);
            array_push($query, $this->message);
            if (_is_filled($this->html)) array_push($query, $this->html);
        }
    }

    /**
     * Class API
     * API для проверки документов на сайте litera5.ru
     * @package Litera5
     */
    class API
    {

        private $sign;

        function __construct($apiKey)
        {
            $this->sign = new Signature($apiKey);
        }

        function _assert($val, $message)
        {
            if (!$val) {
                error_log($message);
            }
        }

        function _setopt($ch, $option, $value)
        {
            $opt_set = curl_setopt($ch, $option, $value);
            $this->_assert($opt_set, "!!! FAILED !!!    curl_setopt(\$ch, " . $option . ", " . print_r($value, true) . ")");
        }

        function _query($url, $request)
        {
            $data_string = $request;
            try {
                $ch = curl_init($url);
                $this->_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                $this->_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                $this->_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $this->_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length: ' . strlen($data_string))
                );

                $this->_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $this->_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $this->_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

                $data = curl_exec($ch);

                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                $body = $data;

                curl_close($ch);
            } catch (Exception $e) {
                $code = "999";
                $body = $e->getMessage();
            }
            return array(
                'code' => $code,
                'body' => $body
            );
        }

        /**
         * Производит настройку API
         * @param $request SetupRequest
         * @return SetupResponse
         */
        function setup($request)
        {
            $resp = $this->_query(url("/api/pub/setup/"), $request->sign($this->sign)->json());
            return new SetupResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Настраивает пользователей
         * @param $request UserRequest
         * @return UserResponse
         */
        function user($request)
        {
            $resp = $this->_query(url("/api/pub/user/"), $request->sign($this->sign)->json());
            return new UserResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Инициирует процедуру проверки документа
         * @param $request CheckRequest
         * @return CheckResponse
         */
        function check($request)
        {
            $resp = $this->_query(url("/api/pub/check/"), $request->sign($this->sign)->json());
            return new CheckResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Запускает проверку подготовленного текстового документа (без участия пользователя)
         * @param $request CheckOgxtRequest
         * @return CheckOgxtResponse
         */
        function checkOgxt($request)
        {
            $resp = $this->_query(url("/api/pub/check-ogxt/"), $request->sign($this->sign)->json());
            return new CheckOgxtResponse($this->sign, $resp['code'], $resp['body']);
        }

        /**
         * Проверяет текущее состояние проверки и получает результаты проверки
         * @param $request CheckOgxtResultsRequest
         * @return CheckOgxtResultsResponse
         */
        function checkOgxtResults($request)
        {
            $resp = $this->_query(url("/api/pub/check-ogxt-results/"), $request->sign($this->sign)->json());
            return new CheckOgxtResultsResponse($this->sign, $resp['code'], $resp['body']);
        }
    }

    /*****************************************************
     *                                                   *
     *                                                   *
     *                    CMS API                        *
     *                                                   *
     *                                                   *
     *****************************************************/
    abstract class BaseCMSRequest
    {
        private $sign = null;

        protected $time = null;
        protected $signature = null;

        /**
         * @param $sign Signature
         * @param $json array
         */
        function __construct($sign, $json)
        {
            $this->sign = $sign;
            $this->time = _get_or_null($json, 'time');
            $this->signature = _get_or_null($json, 'signature');
            $this->_parse_json($json);
        }

        protected abstract function _parse_json($json);

        protected abstract function _query(&$query);

        /**
         * @return array
         */
        private function mk_query()
        {
            $result = [$this->time];
            $this->_query($result);
            return $result;
        }

        /**
         * Проверяет подпись запроса
         * @return bool
         */
        function has_valid_signature()
        {
            return $this->sign->is_valid($this->signature, $this->mk_query());
        }
    }

    abstract class BaseCMSResponse
    {
        protected $time = null;
        protected $signature = null;

        function __construct()
        {
            $this->time = "" . time();
        }

        protected abstract function _query(&$query);

        protected abstract function _json(&$json);

        /**
         * @return array
         */
        private function mk_query()
        {
            $result = [$this->time];
            $this->_query($result);
            return $result;
        }

        /**
         * @return string
         */
        function json()
        {
            $result = array();
            $result["time"] = $this->time;
            $this->_json($result);
            $result["signature"] = $this->signature;
            return json_encode($result);
        }

        /**
         * Подписывает запрос соответствующим ключом.
         * @param $sign Signature
         * @return $this
         */
        function sign($sign)
        {
            $this->signature = $sign->sign($this->mk_query());
            return $this;
        }
    }

    /**
     * Class SaveCorrectedRequest
     * Запрос на сохранение откорректированного текста в CMS
     * @package Litera5
     */
    class SaveCorrectedRequest extends BaseCMSRequest
    {

        public $token = null;
        public $title = null;
        public $description = null;
        public $keywords = null;
        private $custom_src = null;
        public $custom = null;
        public $html = null;
        public $stats = null;

        protected function _parse_json($json)
        {
            $this->token = _get_or_null($json, 'token');
            $this->title = _get_or_null($json, 'title');
            $this->description = _get_or_null($json, 'description');
            $this->keywords = _get_or_null($json, 'keywords');
            $this->custom_src = _get_or_null($json, 'custom');
            $this->custom = array();
            if (_is_filled($this->custom_src)) {
                foreach ($this->custom_src as $custom) {
                    $this->custom[$custom["name"]] = $custom["value"];
                }
            }
            $this->html = _get_or_null($json, 'html');
            $this->stats = _get_or_null($json, 'stats');
        }

        protected function _query(&$query)
        {
            array_push($query, $this->token);
            if (_is_filled($this->title))
                array_push($query, $this->title);
            if (_is_filled($this->description))
                array_push($query, $this->description);
            if (_is_filled($this->keywords))
                array_push($query, $this->keywords);
            if (_is_filled($this->custom_src)) {
                foreach ($this->custom_src as $custom) {
                    array_push($query, $custom["name"]);
                    array_push($query, $custom["value"]);
                }
            }
            if (_is_filled($this->html))
                array_push($query, $this->html);
        }

    }

    class IFrameFailureRequest extends BaseCMSRequest
    {

        public $token = null;
        public $url = null;
        public $code = null;
        public $message = null;

        protected function _parse_json($json)
        {
            $this->token = _get_or_null($json, 'token');
            $this->url = _get_or_null($json, 'url');
            $this->code = _get_or_null($json, 'code');
            $this->message = _get_or_null($json, 'message');
        }

        protected function _query(&$query)
        {
            array_push($query, $this->token);
            array_push($query, $this->url);
            array_push($query, $this->code);
            array_push($query, $this->message);
        }
    }

    abstract class RedirectUrlResponse extends BaseCMSResponse
    {
        private $url = null;

        function __construct($url)
        {
            parent::__construct();
            $this->url = $url;
        }

        protected function _query(&$query)
        {
            array_push($query, $this->url);
        }

        protected function _json(&$json)
        {
            $json["url"] = $this->url;
        }
    }

    class SaveCorrectedResponse extends RedirectUrlResponse
    {
    }

    class IFrameFailureResponse extends RedirectUrlResponse
    {
    }

    /**
     * Class InitialStatsRequest
     * Запрос на сохранение откорректированного текста в CMS
     * @package Litera5
     */
    class InitialStatsRequest extends BaseCMSRequest
    {

        public $token = null;
        public $error = null;
        public $stats = null;

        protected function _parse_json($json)
        {
            $this->token = _get_or_null($json, 'token');
            $this->error = _get_or_null($json, 'error');
            $this->stats = _get_or_null($json, 'stats');
        }

        protected function _query(&$query)
        {
            array_push($query, $this->token);
            if (_is_filled($this->error))
                array_push($query, $this->error);
        }

    }

    class InitialStatsResponse extends BaseCMSResponse
    {

        public $cancel = false;

        function __construct($cancel)
        {
            parent::__construct();
            $this->cancel = $cancel;
        }

        protected function _query(&$query)
        {
            array_push($query, _bool_2_str($this->cancel));
        }

        protected function _json(&$json)
        {
            $json['cancel'] = $this->cancel;
        }
    }

    /**
     * Class CMS
     * API для расширения функциональности CMS для работы с Litera5
     * @package Litera5
     */
    class CMS
    {
        private $sign;

        function __construct($apiKey)
        {
            $this->sign = new Signature($apiKey);
        }

        private function _get_post_data()
        {
            $json = file_get_contents('php://input');
            return json_decode($json, true);
        }

        private function _respondJson($json)
        {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Length: ' . strlen($json));
            echo $json;
        }

        /**
         * Обрабатывает текущий запрос и возвращает его удобную модель
         * @return SaveCorrectedRequest
         */
        function extractSaveCorrectedRequest()
        {
            return new SaveCorrectedRequest($this->sign, $this->_get_post_data());
        }

        /**
         * Создаёт подписанную модель ответа и возвращает его в виде строки JSON
         * @param $url string ссылка для редиректа
         */
        function respondSaveCorrectedResponseJSON($url)
        {
            $response = new SaveCorrectedResponse($url);
            $this->_respondJson($response->sign($this->sign)->json());
        }

        function extractIFrameFailureRequest()
        {
            return new IFrameFailureRequest($this->sign, $this->_get_post_data());
        }

        function respondIFrameFailureResponseJSON($url)
        {
            $response = new IFrameFailureResponse($url);
            $this->_respondJson($response->sign($this->sign)->json());
        }

        /**
         * Обрабатывает текущий запрос и возвращает его удобную модель
         * @return InitialStatsRequest
         */
        function extractInitialStatsRequest()
        {
            return new InitialStatsRequest($this->sign, $this->_get_post_data());
        }

        /**
         * Создаёт подписанную модель ответа и возвращает его в виде строки JSON
         * @param $cancel boolean если true, то дальнейшую работу над документом нужно отменить
         */
        function respondInitialStatsResponseJSON($cancel)
        {
            $response = new InitialStatsResponse($cancel);
            $this->_respondJson($response->sign($this->sign)->json());
        }
    }

}
